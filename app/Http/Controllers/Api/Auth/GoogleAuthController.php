<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'access_token' => ['required', 'string', 'max:4096'],
        ]);

        try {
            $googleResponse = Http::timeout(5)
                ->acceptJson()
                ->get('https://www.googleapis.com/oauth2/v3/userinfo', [
                    'access_token' => $request->access_token,
                ]);

            if ($googleResponse->failed()) {
                return response()->json([
                    'message' => 'Google authentication failed.',
                ], 401);
            }

            $payload = $googleResponse->json();
            $email = strtolower(trim((string) ($payload['email'] ?? '')));
            $googleId = trim((string) ($payload['sub'] ?? ''));

            if ($email === '' || $googleId === '' || empty($payload['email_verified'])) {
                return response()->json([
                    'message' => 'Google account verification failed.',
                ], 403);
            }

            $user = DB::transaction(function () use ($payload, $email, $googleId): User {
                $user = User::where('google_id', $googleId)->first();

                if (! $user) {
                    $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
                }

                if ($user) {
                    $user->forceFill([
                        'google_id' => $user->google_id ?: $googleId,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                        'status' => $user->status ?: 'active',
                    ])->save();

                    return $user;
                }

                $user = User::create([
                    'name' => $payload['name'] ?? 'User',
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $payload['picture'] ?? null,
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(32)),
                    'status' => 'active',
                ]);

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('user');
                }

                return $user;
            });

            return $this->tokenResponse($user, $request);
        } catch (Throwable $e) {
            Log::error('Google Auth Error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Authentication failed due to server error.',
            ], 500);
        }
    }

    /**
     * Exchange a short-lived server-side OAuth code for application tokens.
     * The code is single-use and expires after two minutes.
     */
    public function exchange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:64'],
        ]);

        $userId = Cache::pull('google_exchange:' . $validated['code']);

        if (! $userId) {
            return response()->json([
                'message' => 'Google authentication code is invalid or expired.',
            ], 401);
        }

        $user = User::find($userId);

        if (! $user || ($user->status ?? 'active') !== 'active') {
            return response()->json([
                'message' => 'Google account is not available.',
            ], 403);
        }

        return $this->tokenResponse($user, $request);
    }

    /**
     * Store a one-time code for the legacy Socialite browser redirect flow.
     */
    public static function issueExchangeCode(User $user): string
    {
        $code = Str::random(64);

        Cache::put(
            'google_exchange:' . $code,
            $user->id,
            now()->addMinutes(2)
        );

        return $code;
    }

    private function tokenResponse(User $user, Request $request): JsonResponse
    {
        $fingerprint = substr(
            md5($request->userAgent() . $request->ip()),
            0,
            12
        );

        $user->tokens()
            ->where('name', 'web_google_' . $fingerprint)
            ->delete();

        $accessToken = $user
            ->createToken('web_google_' . $fingerprint)
            ->plainTextToken;

        $refreshToken = RefreshToken::issue($user, $fingerprint);

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user' => $user->only([
                'id',
                'name',
                'email',
                'slug',
                'avatar_url',
            ]),
        ]);
    }
}
