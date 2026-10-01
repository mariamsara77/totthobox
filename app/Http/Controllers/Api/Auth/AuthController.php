<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ── POST /v1/login ──────────────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'ইমেইল দিতে হবে।',
            'email.email'       => 'সঠিক ইমেইল ফরম্যাট দিন।',
            'password.required' => 'পাসওয়ার্ড দিতে হবে।',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['এই ইমেইল দিয়ে কোনো অ্যাকাউন্ট পাওয়া যায়নি।'],
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['পাসওয়ার্ডটি সঠিক নয়।'],
            ]);
        }

        return $this->issueTokenResponse($user, $request);
    }

    // ── POST /v1/auth/refresh ───────────────────────────────────────────────
    // Saved profile one-click: refresh_token দিয়ে নতুন access_token নাও
    public function refresh(Request $request): JsonResponse
    {
        $plain = $request->input('refresh_token'); // Next.js server পাঠাবে

        if (! $plain) {
            return response()->json(['message' => 'Refresh token missing.'], 401);
        }

        $rt = RefreshToken::findValid($plain);

        if (! $rt) {
            return response()->json(['message' => 'সেশন শেষ হয়ে গেছে।'], 401);
        }

        $user = $rt->user;

        // Refresh token rotate করো (security best practice)
        $rt->delete();

        return $this->issueTokenResponse($user, $request);
    }

    // ── GET /v1/me ──────────────────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->only(['id', 'name', 'email', 'slug', 'avatar_url']),
        ]);
    }

    // ── POST /v1/logout ─────────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();

            // এই device-এর refresh token মুছি
            $plain = $request->input('refresh_token');
            if ($plain) {
                RefreshToken::findValid($plain)?->delete();
            }
        }

        return response()->json(['success' => true]);
    }

    // ── Shared: access + refresh token issue ────────────────────────────────
    private function issueTokenResponse(User $user, Request $request): JsonResponse
    {
        $fingerprint = $this->fingerprint($request);

        // Access token — Sanctum (1 দিন)
        $user->tokens()->where('name', 'web_' . $fingerprint)->delete();
        $accessToken = $user->createToken('web_' . $fingerprint)->plainTextToken;

        // Refresh token — নিজস্ব table (30 দিন)
        $refreshToken = RefreshToken::issue($user, $fingerprint);

        return response()->json([
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'user'          => $user->only(['id', 'name', 'email', 'slug', 'avatar_url']),
        ]);
    }

    private function fingerprint(Request $request): string
    {
        return substr(md5($request->userAgent() . $request->ip()), 0, 12);
    }
}