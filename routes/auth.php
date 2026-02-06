<?php

use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\FacebookAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('login', 'auth.login')
        ->name('login');

    Volt::route('register', 'auth.register')
        ->name('register');

    Volt::route('forgot-password', 'auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'auth.reset-password')
        ->name('password.reset');

    // Google OAuth routes
    Route::get('auth/google/redirect', [GoogleLoginController::class, 'redirectToGoogle'])
        ->name('auth.google.redirect');

    Route::get('auth/google/callback', [GoogleLoginController::class, 'handleCallback'])
        ->name('auth.google.callback');

    Route::post('/api/google/verify-token', [GoogleLoginController::class, 'handleOneTapToken']);

    Route::get('login/facebook', [FacebookAuthController::class, 'redirectToFacebook'])->name('login.facebook');
    Route::get('login/facebook/callback', [FacebookAuthController::class, 'handleFacebookCallback']);


});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'auth.confirm-password')
        ->name('password.confirm');
});

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');
