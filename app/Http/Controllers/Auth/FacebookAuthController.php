<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Log, Cookie, DB};
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

            // ১. ইউজার খুঁজে বের করা বা আপডেট করা (Atomic operation)
            $user = DB::transaction(function () use ($facebookUser) {
                $user = User::updateOrCreate(
                    ['email' => $facebookUser->getEmail()],
                    [
                        'name' => $facebookUser->getName(),
                        'facebook_id' => $facebookUser->getId(),
                        'password' => Hash::make(Str::random(24)),
                        'email_verified_at' => now(),
                        'status' => 'active',
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $user->assignRole('user');
                }

                return $user;
            });

            // ২. প্রোফাইল পিকচার সিঙ্ক (Spatie Media Library)
            if ($facebookUser->getAvatar()) {
                $user->clearMediaCollection('avatars');
                $user->addMediaFromUrl($facebookUser->getAvatar())
                    ->toMediaCollection('avatars');
            }

            // ৩. সেশন এবং অথেন্টিকেশন
            Auth::login($user, true);
            $request->session()->regenerate();

            // ৪. মাল্টিপল ইউজার কুকি সিঙ্ক করা (ইম্প্রুভড মেথড)
            $this->syncSavedAccounts($user->id);

            return redirect()->intended('/');

        } catch (\Exception $e) {
            Log::error('Facebook Auth Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => 'ফেসবুক লগইন ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।'
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

        // ১. বিদ্যমান কুকি থেকে ডেটা রিড করা
        if ($existingCookie = request()->cookie($cookieName)) {
            try {
                $userIds = json_decode(decrypt($existingCookie), true) ?: [];
            } catch (\Exception $e) {
                $userIds = [];
            }
        }

        // ২. বর্তমান আইডি যদি লিস্টে না থাকে তবে যুক্ত করা
        if (!in_array($userId, $userIds)) {
            $userIds[] = $userId;
        }

        // ৩. কুকিটি 'Forever' হিসেবে সেভ করা যাতে লগআউট করলেও থেকে যায়
        Cookie::queue(
            cookie()->forever(
                $cookieName,
                encrypt(json_encode($userIds))
            )
        );

        // ৪. পুরনো সিঙ্গেল ইউজার কুকি থাকলে তা ক্লিনআপ করা
        Cookie::queue(Cookie::forget('last_logged_user'));
    }
}