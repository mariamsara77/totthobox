<?php

use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\FacebookAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('login', 'auth.login')->name('login');
    Volt::route('register', 'auth.register')->name('register');
    Volt::route('forgot-password', 'auth.forgot-password')->name('password.request');
    Volt::route('reset-password/{token}', 'auth.reset-password')->name('password.reset');

    // Social Authentication Group
    Route::prefix('auth')->group(function () {
        // Google OAuth
        Route::get('google/redirect', [GoogleLoginController::class, 'redirectToGoogle'])->name('auth.google.redirect');
        Route::get('google/callback', [GoogleLoginController::class, 'handleCallback'])->name('auth.google.callback');
        Route::post('google/one-tap', [GoogleLoginController::class, 'handleOneTapToken'])->name('auth.google.one-tap');

        // Facebook OAuth
        Route::get('facebook/redirect', [FacebookAuthController::class, 'redirectToFacebook'])->name('login.facebook');
        Route::get('facebook/callback', [FacebookAuthController::class, 'handleFacebookCallback'])->name('login.facebook.callback');
    });
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'auth.verify-email')->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'auth.confirm-password')->name('password.confirm');
});

// Logout handles both web and social sessions
Route::post('logout', App\Livewire\Actions\Logout::class)->name('logout');