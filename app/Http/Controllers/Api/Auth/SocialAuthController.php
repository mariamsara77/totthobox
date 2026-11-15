<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SocialAuthController extends Controller
{
    public function login(Request $request, string $provider): JsonResponse
    {
        $request->validate([
            'access_token' => ['required', 'string', 'max:4096'],
        ]);

        try {
            $payload = match ($provider) {
                'google'   => $this->fetchGoogleUser($request->access_token),
                'github'   => $this->fetchGithubUser($request->access_token),
                'facebook' => $this->fetchFacebookUser($request->access_token),
                'twitter'  => $this->fetchTwitterUser($request->access_token),
                default    => null,
            };

            // Provider ID is mandatory. Email is optional.
            if (! $payload || empty($payload['id'])) {
                return response()->json([
                    'message' => ucfirst($provider) . ' authentication failed.',
                ], 401);
            }

            $socialId         = (string) $payload['id'];
            $email            = ! empty($payload['email']) ? strtolower(trim($payload['email'])) : null;
            $providerIdColumn = $provider . '_id'; // google_id, github_id, facebook_id, twitter_id

            $user = DB::transaction(function () use ($payload, $email, $socialId, $providerIdColumn) {

                // 1. First try to find by provider ID
                $user = User::where($providerIdColumn, $socialId)->first();

                // 2. If not found and email exists → find by email
                if (! $user && $email) {
                    $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
                }

                if ($user) {
                    // Update existing user
                    $user->forceFill([
                        $providerIdColumn  => $user->$providerIdColumn ?: $socialId,
                        'email_verified_at' => $user->email_verified_at ?? ($email ? now() : null),
                        'status'            => $user->status ?: 'active',
                        'avatar'            => $user->avatar ?: ($payload['avatar'] ?? null),
                        'name'              => $user->name ?: ($payload['name'] ?? 'User'),
                    ])->save();

                    return $user;
                }

                // 3. Create new user (email can be null)
                return User::create([
                    'name'              => $payload['name'] ?? 'User',
                    'email'             => $email, // can be null
                    $providerIdColumn   => $socialId,
                    'avatar'            => $payload['avatar'] ?? null,
                    'email_verified_at' => $email ? now() : null,
                    'password'          => bcrypt(Str::random(40)),
                    'status'            => 'active',
                ]);
            });

            return $this->tokenResponse($user, $request);
        } catch (Throwable $e) {
            Log::error(ucfirst($provider) . ' Auth Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Authentication failed due to server error.',
            ], 500);
        }
    }

    // ────────────────────────────────────────────────
    // Provider Fetchers
    // ────────────────────────────────────────────────

    private function fetchGoogleUser(string $token): ?array
    {
        $res = Http::acceptJson()
            ->get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'access_token' => $token,
            ]);

        if ($res->failed()) {
            return null;
        }

        return [
            'id'     => $res->json('sub'),
            'email'  => $res->json('email'),
            'name'   => $res->json('name'),
            'avatar' => $res->json('picture'),
        ];
    }

    private function fetchGithubUser(string $token): ?array
    {
        $res = Http::withToken($token)
            ->acceptJson()
            ->get('https://api.github.com/user');

        if ($res->failed()) {
            return null;
        }

        $email = $res->json('email');

        // GitHub often hides email → fetch primary verified email
        if (! $email) {
            $emailsRes = Http::withToken($token)
                ->acceptJson()
                ->get('https://api.github.com/user/emails');

            if ($emailsRes->successful()) {
                foreach ($emailsRes->json() ?? [] as $em) {
                    if (($em['primary'] ?? false) && ($em['verified'] ?? false)) {
                        $email = $em['email'];
                        break;
                    }
                }
            }
        }

        return [
            'id'     => (string) $res->json('id'),
            'email'  => $email,
            'name'   => $res->json('name') ?? $res->json('login'),
            'avatar' => $res->json('avatar_url'),
        ];
    }

    private function fetchFacebookUser(string $token): ?array
    {
        $res = Http::acceptJson()
            ->get('https://graph.facebook.com/v19.0/me', [
                'fields'       => 'id,name,email,picture.type(large)',
                'access_token' => $token,
            ]);

        if ($res->failed()) {
            return null;
        }

        return [
            'id'     => $res->json('id'),
            'email'  => $res->json('email'), // often missing
            'name'   => $res->json('name'),
            'avatar' => data_get($res->json(), 'picture.data.url'),
        ];
    }

    private function fetchTwitterUser(string $token): ?array
    {
        // X API v2 – email is rarely available
        $res = Http::withToken($token)
            ->acceptJson()
            ->get('https://api.twitter.com/2/users/me', [
                'user.fields' => 'profile_image_url,name,username',
            ]);

        if ($res->failed()) {
            return null;
        }

        $data = $res->json('data');

        if (! $data || empty($data['id'])) {
            return null;
        }

        return [
            'id'     => $data['id'],
            'email'  => $data['email'] ?? null, // almost always null
            'name'   => $data['name'] ?? $data['username'] ?? null,
            'avatar' => $data['profile_image_url'] ?? null,
        ];
    }

    // ────────────────────────────────────────────────
    // Token Response
    // ────────────────────────────────────────────────

    private function tokenResponse(User $user, Request $request): JsonResponse
    {
        $fingerprint = substr(md5($request->userAgent() . $request->ip()), 0, 16);

        // Remove previous token of same device/browser
        $user->tokens()
            ->where('name', 'web_social_' . $fingerprint)
            ->delete();

        $accessToken  = $user->createToken('web_social_' . $fingerprint)->plainTextToken;
        $refreshToken = RefreshToken::issue($user, $fingerprint);

        return response()->json([
            'access_token'  => $accessToken,
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