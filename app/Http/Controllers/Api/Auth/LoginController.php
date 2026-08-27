<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Login with email/password and issue a Sanctum personal access token.
     */
    public function login(Request $request)
    {
        $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'min:8'],
            ],
            [
                'email.required' => 'ইমেইল অ্যাড্রেসটি প্রয়োজন।',
                'email.email' => 'সঠিক ইমেইল ফরম্যাট ব্যবহার করুন।',
                'password.required' => 'পাসওয়ার্ডটি অবশ্যই দিতে হবে।',
                'password.min' => 'পাসওয়ার্ডটি কমপক্ষে ৮ অক্ষরের হতে হবে।',
            ]
        );

        $email = Str::lower(trim($request->input('email')));

        $throttleKey = 'login:' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()
                ->json([
                    'message' => "অতিরিক্ত বার চেষ্টার ফলে অ্যাকাউন্ট সাময়িকভাবে লক হয়েছে। {$seconds} সেকেন্ড পর আবার চেষ্টা করুন।",
                    'errors' => [
                        'email' => [
                            "অতিরিক্ত বার চেষ্টার ফলে অ্যাকাউন্ট সাময়িকভাবে লক হয়েছে। {$seconds} সেকেন্ড পর আবার চেষ্টা করুন।"
                        ],
                    ],
                ], 429)
                ->header('Cache-Control', 'no-store');
        }

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return response()
                ->json([
                    'message' => 'ইমেইল অথবা পাসওয়ার্ড সঠিক নয়।',
                    'errors' => [
                        'password' => [
                            'আপনার দেওয়া পাসওয়ার্ডটি সঠিক নয় অথবা ইমেইল ভুল।'
                        ],
                    ],
                ], 401)
                ->header('Cache-Control', 'no-store');
        }

        RateLimiter::clear($throttleKey);

        /*
         * IMPORTANT:
         *
         * Do NOT delete all tokens here.
         *
         * The Next.js frontend revokes the previous browser token before
         * starting a new login. Keeping other tokens allows the same user
         * to remain logged in on another device/browser.
         */
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json([
                'message' => 'লগইন সফল হয়েছে!',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'slug' => $user->slug,
                    'avatar_url' => $user->avatar_url,
                ],
            ], 200)
            ->header(
                'Cache-Control',
                'private, no-store, no-cache, must-revalidate, max-age=0'
            )
            ->header('Pragma', 'no-cache');
    }

    /**
     * Logout ONLY the current Sanctum token.
     */
  public function logout(Request $request)
{
    $user = $request->user();

    if ($user && $user->currentAccessToken()) {
        $user->currentAccessToken()->delete();
    }

    return response()->json([
        'success' => true,
        'message' => 'লগআউট সফল হয়েছে।',
    ]);
}
}