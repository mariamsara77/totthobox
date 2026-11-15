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
        // Google সরাসরি access_token দেয়, কিন্তু GitHub/Others 'code' দেয়।
        // তাই রিকোয়েস্টে access_token অথবা code যেকোনো একটি থাকতে পারে।
        $request->validate([
            'access_token' => ['nullable', 'string', 'max:4096'],
            'code' => ['nullable', 'string', 'max:4096'],
        ]);

        try {
            $tokenOrCode = $request->input('access_token') ?? $request->input('code');

            if (! $tokenOrCode) {
                return response()->json(['message' => 'Authorization token or code is missing.'], 422);
            }

            // ১. প্রোভাইডার অনুযায়ী ইউজার ইনফো ফেচ করা
            $payload = match ($provider) {
                'google'   => $this->fetchGoogleUser($tokenOrCode),
                'github'   => $this->fetchGithubUserByCode($tokenOrCode), // কোড এক্সচেঞ্জ করার জন্য নতুন মেথড
                'facebook' => $this->fetchFacebookUser($tokenOrCode),
                'twitter'  => $this->fetchTwitterUser($tokenOrCode),
                default    => null,
            };

            if (! $payload || empty($payload['email'])) {
                return response()->json(['message' => ucfirst($provider) . ' authentication failed.'], 401);
            }

            $email = strtolower(trim($payload['email']));
            $socialId = trim($payload['id']);
            $providerIdColumn = $provider . '_id'; // e.g., github_id, facebook_id

            // ২. ডেটাবেজ ট্রানজেকশন (ইউজার তৈরি বা আপডেট)
            $user = DB::transaction(function () use ($payload, $email, $socialId, $providerIdColumn) {
                $user = User::where($providerIdColumn, $socialId)->first()
                    ?? User::whereRaw('LOWER(email) = ?', [$email])->first();

                if ($user) {
                    $user->forceFill([
                        $providerIdColumn => $user->$providerIdColumn ?: $socialId,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                        'status' => $user->status ?: 'active',
                    ])->save();

                    return $user;
                }

                return User::create([
                    'name' => $payload['name'] ?? 'User',
                    'email' => $email,
                    $providerIdColumn => $socialId,
                    'avatar' => $payload['avatar'] ?? null,
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(32)),
                    'status' => 'active',
                ]);
            });

            return $this->tokenResponse($user, $request);
        } catch (Throwable $e) {
            Log::error(ucfirst($provider) . ' Auth Error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Authentication failed due to server error.'], 500);
        }
    }

    private function fetchGoogleUser(string $token): ?array
    {
        $res = Http::acceptJson()->get('https://www.googleapis.com/oauth2/v3/userinfo', ['access_token' => $token]);
        if ($res->failed()) return null;
        return [
            'id' => $res->json('sub'),
            'email' => $res->json('email'),
            'name' => $res->json('name'),
            'avatar' => $res->json('picture'),
        ];
    }

    private function fetchGithubUserByCode(string $code): ?array
    {
        // GitHub-এর authorization code দিয়ে access_token নেওয়া
        $tokenResponse = Http::acceptJson()->post('https://github.com/login/oauth/access_token', [
            'client_id' => config('services.github.client_id'),
            'client_secret' => config('services.github.client_secret'),
            'code' => $code,
        ]);

        $accessToken = $tokenResponse->json('access_token');
        if (! $accessToken) {
            return null;
        }

        // এবার ওই access_token দিয়ে GitHub user info আনা
        $res = Http::withToken($accessToken)->acceptJson()->get('https://api.github.com/user');
        if ($res->failed()) return null;

        $email = $res->json('email');
        if (! $email) {
            $emailsRes = Http::withToken($accessToken)->acceptJson()->get('https://api.github.com/user/emails');
            foreach ($emailsRes->json() ?? [] as $em) {
                if (($em['primary'] ?? false) && ($em['verified'] ?? false)) {
                    $email = $em['email'];
                    break;
                }
            }
        }

        return [
            'id' => (string) $res->json('id'),
            'email' => $email,
            'name' => $res->json('name') ?? $res->json('login'),
            'avatar' => $res->json('avatar_url'),
        ];
    }

    private function fetchFacebookUser(string $token): ?array
    {
        $res = Http::acceptJson()->get('https://graph.facebook.com/v18.0/me', [
            'fields' => 'id,name,email,picture',
            'access_token' => $token,
        ]);
        if ($res->failed()) return null;
        return [
            'id' => $res->json('id'),
            'email' => $res->json('email'),
            'name' => $res->json('name'),
            'avatar' => data_get($res->json(), 'picture.data.url'),
        ];
    }

    private function fetchTwitterUser(string $token): ?array
    {
        $res = Http::withToken($token)->acceptJson()->get('https://api.twitter.com/2/users/me', [
            'user.fields' => 'profile_image_url',
        ]);
        if ($res->failed()) return null;

        $data = $res->json('data');
        return [
            'id' => $data['id'] ?? null,
            'email' => $data['email'] ?? null,
            'name' => $data['name'] ?? null,
            'avatar' => $data['profile_image_url'] ?? null,
        ];
    }

    private function tokenResponse(User $user, Request $request): JsonResponse
    {
        $fingerprint = substr(md5($request->userAgent() . $request->ip()), 0, 12);

        $user->tokens()->where('name', 'web_social_' . $fingerprint)->delete();
        $accessToken = $user->createToken('web_social_' . $fingerprint)->plainTextToken;
        $refreshToken = RefreshToken::issue($user, $fingerprint);

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user' => $user->only(['id', 'name', 'email', 'slug', 'avatar_url']),
        ]);
    }
}