<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Api\BuySellApi;
use App\Http\Controllers\Api\ActivitySyncController;


Route::post('/sync-offline-data', function (Request $request) {
    $data = $request->input('data');

    // এখানে আপনি ডাটাবেসে সেভ করার লজিক লিখবেন
    // যেমন: Log::info($data);

    return response()->json(['status' => 'success']);
});

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::get('/buy-sell', BuySellApi::class);


Route::post('/sync-offline-data', [ActivitySyncController::class, 'sync']);