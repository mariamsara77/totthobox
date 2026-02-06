<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class FacebookAuthController extends Controller
{
   // Redirect to Facebook
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    // Handle Facebook callback
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            // ১. ইউজার খুঁজে বের করা বা নতুন তৈরি করা
            // wasRecentlyCreated প্রপার্টি ব্যবহার করে চেক করবো ইউজার কি এইমাত্র তৈরি হলো কি না
            $user = User::firstOrCreate(
                ['facebook_id' => $facebookUser->id],
                [
                    'name' => $facebookUser->name,
                    'email' => $facebookUser->email,
                    'password' => bcrypt(str()->random(24)), // পাসওয়ার্ড নাল রাখা উচিত নয়
                    'status' => 'active', // স্ট্যাটাস সেট করা
                ]
            );

            // ২. যদি ইউজার নতুন তৈরি হয়ে থাকে তবেই রোল অ্যাসাইন করবো
            if ($user->wasRecentlyCreated) {
                $user->assignRole('user');
            }

            Auth::login($user);

            // ৩. কুকি সেট করা
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

            return redirect()->intended('/home');

        } catch (\Exception $e) {
            \Log::error('Facebook Auth Error: ' . $e->getMessage());
            return redirect('/login')->withErrors('Facebook login failed. Please try again.');
        }
    }
}
