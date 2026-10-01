<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Google\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    // ১. Standard Socialite Login
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // প্রথমে Google ID অথবা Email দিয়ে ইউজার খুঁজুন
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if (!$user) {
                // ইউজার একদম নতুন হলে তৈরি করুন এবং অবতার সেট করুন
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]);
                $user->assignRole('user');

                if ($googleUser->getAvatar()) {
                    $user->addMediaFromUrl($googleUser->getAvatar())
                        ->toMediaCollection('avatars');
                }
            }
            // ইউজার আগে থেকে থাকলে (else block) কোনো ডাটাবেজ বা মিডিয়া আপডেট হবে না, সরাসরি লগইন হবে।

            Auth::login($user, true);
            $this->syncSavedAccounts($user->id);

            return redirect()->intended('/');

        } catch (\Exception $e) {
            \Log::error('Google Auth Error: ' . $e->getMessage());

            return redirect()->route('login')->withErrors(['email' => 'Google authentication failed.']);
        }
    }

    // ২. Google One Tap (JWT Token)
    public function handleOneTapToken(Request $request): JsonResponse
    {
        $idToken = $request->input('token');

        if (!$idToken) {
            return response()->json(['success' => false, 'message' => 'Token not provided.'], 400);
        }

        try {
            $client = new Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($idToken);

            if (!$payload) {
                return response()->json(['success' => false, 'message' => 'Invalid Google Token.'], 401);
            }

            $googleId = $payload['sub'] ?? null;
            $user = User::where('google_id', $googleId)
                ->orWhere('email', $payload['email'])
                ->first();

            if (!$user) {
                // ইউজার একদম নতুন হলে তৈরি করুন এবং অবতার সেট করুন
                $user = User::create([
                    'name' => $payload['name'] ?? 'Google User',
                    'email' => $payload['email'],
                    'google_id' => $googleId,
                    'avatar' => $payload['picture'] ?? null,
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => ($payload['email_verified'] ?? false) ? now() : null,
                    'status' => 'active',
                ]);
                $user->assignRole('user');

                if (isset($payload['picture'])) {
                    $user->addMediaFromUrl($payload['picture'])
                        ->toMediaCollection('avatars');
                }
            }
            // ইউজার আগে থেকে থাকলে (else block) কোনো ডাটাবেজ বা মিডিয়া আপডেট হবে না, সরাসরি লগইন হবে।

            Auth::login($user, true);
            $request->session()->regenerate();
            $this->syncSavedAccounts($user->id);

            return response()->json(['success' => true, 'redirect' => url()->intended('/')]);

        } catch (\Exception $e) {
            \Log::error('Google One Tap Error: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Authentication Failed.'], 500);
        }
    }

    private function syncSavedAccounts($userId)
    {
        $cookieName = 'saved_accounts';
        $userIds = [];

        if ($existingCookie = request()->cookie($cookieName)) {
            try {
                $userIds = json_decode(decrypt($existingCookie), true) ?: [];
            } catch (\Exception $e) {
                $userIds = [];
            }
        }

        if (!in_array($userId, $userIds)) {
            $userIds[] = $userId;
        }

        Cookie::queue(
            cookie()->forever(
                $cookieName,
                encrypt(json_encode($userIds))
            )
        );

        Cookie::queue(Cookie::forget('last_logged_user'));
    }
}