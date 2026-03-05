<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke()
    {
        // ১. ইউজারকে লগআউট করা (Web Guard use kore)
        Auth::guard('web')->logout();

        // ২. সেশন পুরোপুরি ইনভ্যালিডেট করা (পুরানো সেশন আইডি ইনভ্যালিড হবে)
        Session::invalidate();

        // ৩. CSRF টোকেন রিজেনারেট করা (সিকিউরিটির জন্য অত্যন্ত জরুরি)
        Session::regenerateToken();

        /**
         * নোট: আমরা এখানে Cookie::forget('saved_accounts') কল করছি না। 
         * এর ফলে ইউজার লগআউট করলেও 'Account Switcher' লিস্টে তার নাম থেকে যাবে।
         */

        // ৪. ইউজারকে রিডাইরেক্ট করা
        // login পেজে পাঠানোই ভালো যাতে সে অন্য অ্যাকাউন্ট সিলেক্ট করতে পারে
        return redirect()->route('login')
            ->with('status', 'সফলভাবে লগআউট করা হয়েছে।');
    }
}