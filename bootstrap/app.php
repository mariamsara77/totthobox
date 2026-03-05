<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {


    $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'api/tracking/*',    
            'api/sync-offline-data'
        ]);
        
        // $middleware->alias([

        // ]);

        // Add to web group with priority (runs after session start)
        $middleware->web(append: [
            \App\Http\Middleware\TrackVisitors::class,
            \App\Http\Middleware\UpdateLastActive::class,
            \App\Http\Middleware\AdminDebugbar::class,
            \App\Http\Middleware\TranslateMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();