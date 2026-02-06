<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request; // ★ নতুন যোগ করা
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse; // ★ নতুন যোগ করা
use Google\Client; // ★ নতুন যোগ করা (google/apiclient এর জন্য)

class GoogleLoginController extends Controller
{
    // ★ ১. ঐতিহ্যবাহী Google Login Button পদ্ধতি (Socialite ব্যবহার করে) ★

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                    'status' => 'active', // স্ট্যাটাস সরাসরি এখানে দিন
                ]);

                // নতুন ইউজার হলে রোল অ্যাসাইন করুন
                $user->assignRole('user');
            }

            Auth::login($user, true);

            // Set last_logged_user cookie
            Cookie::queue(
                Cookie::make(
                    'last_logged_user',
                    encrypt($user->id), // encrypt for security
                    60 * 24 * 365,      // 1 year
                    null,
                    null,
                    true,               // secure HTTPS
                    true                // HttpOnly
                )
            );

            return redirect()->intended('/');

        } catch (\Exception $e) {
            \Log::error('Google OAuth Error (Socialite): ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => 'Failed to authenticate with Google. Please try again.'
            ]);
        }
    }

    // ------------------------------------------------------------------------------------------------ //

    // ★ ২. Google One Tap (JWT Token) হ্যান্ডেল করার জন্য নতুন পদ্ধতি ★

    public function handleOneTapToken(Request $request): JsonResponse
    {
        // One Tap ফ্রন্টএন্ড থেকে JWT টোকেনটি আসে
        $idToken = $request->input('token');

        if (!$idToken) {
            return response()->json(['success' => false, 'message' => 'Token not provided.'], 400);
        }

        try {
            // Google Client ব্যবহার করে টোকেন যাচাই করা হবে
            $client = new Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($idToken);

            if (!$payload) {
                // টোকেন যাচাই ব্যর্থ হলে
                return response()->json(['success' => false, 'message' => 'Invalid Google Token.'], 401);
            }

            // payload থেকে ব্যবহারকারীর তথ্য বের করা
            $email = $payload['email'];
            $name = $payload['name'] ?? 'Google User';
            $isEmailVerified = $payload['email_verified'] ?? false;

            // 1. ব্যবহারকারীকে ডাটাবেসে খুঁজে বের করা বা তৈরি করা
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => $isEmailVerified ? now() : null,
                    'status' => 'active', // স্ট্যাটাস এখানে দিন
                ]);

                // নতুন ইউজার হলে রোল অ্যাসাইন করুন
                $user->assignRole('user');
            }

            // 2. ব্যবহারকারীকে লগইন করানো
            Auth::login($user, true);

            // 3. One Tap-এর জন্য কুকি সেট করা
            Cookie::queue(
                Cookie::make(
                    'last_logged_user',
                    encrypt($user->id),
                    60 * 24 * 365,
                    null,
                    null,
                    true,
                    true
                )
            );

            return response()->json(['success' => true, 'redirect' => url()->intended('/')]);

        } catch (\Exception $e) {
            \Log::error('Google One Tap Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Authentication Failed.'], 500);
        }
    }
}
