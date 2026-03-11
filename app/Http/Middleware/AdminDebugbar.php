<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminDebugbar
{
    public function handle(Request $request, Closure $next): Response
    {
        // ১. চেক করুন ডিবাগবার ক্লাসটি আছে কি না
        if (class_exists('\Barryvdh\Debugbar\Facades\Debugbar')) {

            // ২. চেক করুন ইউজার কি অ্যাডমিন?
            if (Auth::check() && Auth::id() === 1) { // আপনার আইডি যদি ১ হয়
                \Barryvdh\Debugbar\Facades\Debugbar::enable();
            } else {
                // অ্যাডমিন না হলে পুরোপুরি ডিজেবল
                \Barryvdh\Debugbar\Facades\Debugbar::disable();
            }
        }

        return $next($request);
    }
}
