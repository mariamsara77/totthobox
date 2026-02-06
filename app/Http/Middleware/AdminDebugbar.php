<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminDebugbar
{
    public function handle(Request $request, Closure $next): Response
    {
        // ১. ডিফল্টভাবে ডিবাবার বন্ধ রাখুন
        if (class_exists('\Debugbar')) {
            \Debugbar::disable();
        }

        // ২. চেক করুন ইউজার লগইন আছে কিনা এবং সে আপনার মডেল অনুযায়ী Admin কিনা
        if (Auth::check()) {
            $user = Auth::user();

            // আপনার মডেলের মেথডগুলো ব্যবহার করছি
            if ( $user->id === 1) {
                \Debugbar::enable();
            }
        }

        return $next($request);
    }
}