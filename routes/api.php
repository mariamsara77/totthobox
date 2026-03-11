<?php

use Illuminate\Http\Request; // এটি অবশ্যই থাকতে হবে
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivitySyncController;
use App\Livewire\Api\BuySellApi;
use App\Http\Controllers\Api\TrackingController;


Route::post('/tracking/event', [TrackingController::class, 'trackEvent']);
Route::post('/tracking/sync-pwa', [TrackingController::class, 'syncPwaStatus']);

// ১. ডুপ্লিকেট রুট মুছে ক্লিন করা হলো
Route::post('/sync-offline-data', [ActivitySyncController::class, 'sync']);

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () { // Sanctum বা আপনার পছন্দমত গার্ড
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});