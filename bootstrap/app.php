<?php

use App\Http\Middleware\AdminDebugbar;
use App\Http\Middleware\TrackVisitors;
use App\Http\Middleware\UpdateLastActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Illuminate\Http\Request; // এটি যুক্ত করা হয়েছে

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'responsecache' => CacheResponse::class,
        ]);

        $middleware->trustProxies(at: '*');

        // api/tracking/* — trackEvent, syncPwaStatus, sync সব cover হয়
        $middleware->validateCsrfTokens(except: [
            'api/tracking/*',
            'api/login',
        ]);

        $middleware->web(append: [
            TrackVisitors::class,
            UpdateLastActive::class,
            // AdminDebugbar::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // API রিকোয়েস্টে এরর হলে সবসময় JSON রিটার্ন করার জন্য
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
    })->create();