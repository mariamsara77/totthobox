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
        if (class_exists('\Barryvdh\Debugbar\Facades\Debugbar')) {

            if (Auth::check() && Auth::id() === 1) {
                \Barryvdh\Debugbar\Facades\Debugbar::enable();
            } else {
                \Barryvdh\Debugbar\Facades\Debugbar::disable();
            }
        }
        return $next($request);
    }
}