<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookAuthController extends Controller
{
    /**
     * Redirect to Facebook with specific scopes
     */
    public function redirectToFacebook(): RedirectResponse
    {
        return Socialite::driver('facebook')
            ->scopes(['email', 'public_profile'])
            ->redirect();
    }

    /**
     * Handle Facebook callback
     */
    public function handleFacebookCallback(Request $request)
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            // ১. ইউজার খুঁজে বের করা বা তৈরি করা
            $user = DB::transaction(function () use ($facebookUser) {
                $user = User::where('email', $facebookUser->getEmail())->first();

                if (! $user) {
                    $user = User::create([
                        'name' => $facebookUser->getName(),
                        'email' => $facebookUser->getEmail(),
                        'facebook_id' => $facebookUser->getId(),
                        'password' => Hash::make(Str::random(24)),
                        'email_verified_at' => now(),
                        'status' => 'active',
                    ]);

                    $user->assignRole('user');

                    // শুধুমাত্র নতুন ইউজারের জন্য অবতার সেভ করা হবে
                    if ($facebookUser->getAvatar()) {
                        $user->addMediaFromUrl($facebookUser->getAvatar())
                            ->toMediaCollection('avatars');
                    }
                }

                return $user;
            });

            // ২. সেশন এবং অথেন্টিকেশন
            Auth::login($user, true);
            $request->session()->regenerate();

            // ৩. মাল্টিপল ইউজার কুকি সিঙ্ক করা
            $this->syncSavedAccounts($user->id);

            return redirect()->intended('/');

        } catch (\Exception $e) {
            Log::error('Facebook Auth Error: '.$e->getMessage());

            return redirect()->route('login')->withErrors([
                'email' => 'ফেসবুক লগইন ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।',
            ]);
        }
    }

    /**
     * একাধিক ইউজার আইডি সেভ রাখার জন্য ইম্প্রুভড কুকি লজিক
     */
    private function syncSavedAccounts($userId): void
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

        if (! in_array($userId, $userIds)) {
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
