<?php

use App\Http\Controllers\Api\ActivitySyncController;
use App\Http\Controllers\Api\TrackingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\HolidayCalendarController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\AppResourceController;
use App\Http\Controllers\Api\SidebarController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\IntroBdController;
use App\Http\Controllers\Api\TourismBdController;
use App\Http\Controllers\Api\HistoryBdController;
use App\Http\Controllers\Api\EstablishmentBdController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\BasicIslamController;
use App\Http\Controllers\Api\DowaController;
use App\Http\Controllers\Api\SignController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\UserPublicProfileController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Auth\GoogleLoginController;

Route::get('/analytics/user-count', [AnalyticsController::class, 'index']);
Route::get('/holidays-calendar', [HolidayCalendarController::class, 'index']);

Route::prefix('holidays')->group(function () {

    // List
    Route::get('/', [HolidayController::class, 'index']);

    // ← এই দুটোকে {slug} এর আগে রাখতে হবে
    Route::post('/{id}/react', [HolidayController::class, 'react'])
        ->middleware('auth:sanctum');

    Route::get('/{id}/reaction-status', [HolidayController::class, 'reactionStatus'])
        ->middleware('auth:sanctum');

    // Single (সবচেয়ে শেষে রাখো)
    Route::get('/{slug}', [HolidayController::class, 'show']);
});
Route::middleware(['throttle:120,1'])->prefix('tracking')->group(function () {
    Route::post('event', [TrackingController::class, 'trackEvent']);
    Route::post('sync-pwa', [TrackingController::class, 'syncPwaStatus']);
    Route::post('sync', [ActivitySyncController::class, 'sync']);
});


Route::prefix('intro-bd')->group(function () {
    Route::get('/', [IntroBdController::class, 'index']);
    Route::get('/creators', [IntroBdController::class, 'creators']);

    Route::post('/{id}/react', [IntroBdController::class, 'react'])
        ->middleware('auth:sanctum')
        ->whereNumber('id');

    Route::get('/{id}/reaction-status', [IntroBdController::class, 'reactionStatus'])
        ->middleware('auth:sanctum')
        ->whereNumber('id');

    Route::get('/{id}/creators', [IntroBdController::class, 'itemCreators'])
        ->whereNumber('id');

    Route::get('/{slug}', [IntroBdController::class, 'show']);
});

Route::prefix('tourism-bd')->group(function () {
    Route::get('/', [TourismBdController::class, 'index']);
    Route::get('/filters', [TourismBdController::class, 'filters']); // divisions + types
    Route::get('/districts', [TourismBdController::class, 'districts']);
    Route::get('/thanas', [TourismBdController::class, 'thanas']);
    Route::get('/creators', [TourismBdController::class, 'creators']);

    Route::post('/{id}/react', [TourismBdController::class, 'react'])
        ->middleware('auth:sanctum')
        ->whereNumber('id');

    Route::get('/{id}/reaction-status', [TourismBdController::class, 'reactionStatus'])
        ->middleware('auth:sanctum')
        ->whereNumber('id');

    Route::get('/{id}/creators', [TourismBdController::class, 'itemCreators'])
        ->whereNumber('id');

    Route::get('/{slug}', [TourismBdController::class, 'show']);
});

Route::prefix('history-bd')->group(function () {
    Route::get('/', [HistoryBdController::class, 'index']);
    Route::get('/filters', [HistoryBdController::class, 'filters']);
    Route::get('/districts', [HistoryBdController::class, 'districts']);
    Route::get('/thanas', [HistoryBdController::class, 'thanas']);
    Route::get('/creators', [HistoryBdController::class, 'creators']);

    Route::post('/{id}/react', [HistoryBdController::class, 'react'])
        ->middleware('auth:sanctum')
        ->whereNumber('id');

    Route::get('/{id}/reaction-status', [HistoryBdController::class, 'reactionStatus'])
        ->middleware('auth:sanctum')
        ->whereNumber('id');

    Route::get('/{id}/creators', [HistoryBdController::class, 'itemCreators'])
        ->whereNumber('id');

    Route::get('/{slug}', [HistoryBdController::class, 'show']);
});

Route::prefix('establishment-bd')->group(function () {
    Route::get('/', [EstablishmentBdController::class, 'index']);
    Route::get('/filters', [EstablishmentBdController::class, 'filters']);
    Route::get('/districts', [EstablishmentBdController::class, 'districts']);
    Route::get('/thanas', [EstablishmentBdController::class, 'thanas']);
    Route::get('/creators', [EstablishmentBdController::class, 'creators']);

    Route::post('/{id}/react', [EstablishmentBdController::class, 'react'])
        ->middleware('auth:sanctum')
        ->whereNumber('id');

    Route::get('/{id}/reaction-status', [EstablishmentBdController::class, 'reactionStatus'])
        ->middleware('auth:sanctum')
        ->whereNumber('id');

    Route::get('/{id}/creators', [EstablishmentBdController::class, 'itemCreators'])
        ->whereNumber('id');

    Route::get('/{slug}', [EstablishmentBdController::class, 'show']);
});

Route::prefix('people')->group(function () {
    Route::get('/', [PersonController::class, 'index']);
    Route::get('/filters', [PersonController::class, 'filters']);
    Route::get('/creators', [PersonController::class, 'creators']);

    Route::post('/{id}/react', [PersonController::class, 'react'])
        ->middleware('auth:sanctum')
        ->whereNumber('id');

    Route::get('/{id}/reaction-status', [PersonController::class, 'reactionStatus'])
        ->middleware('auth:sanctum')
        ->whereNumber('id');

    Route::get('/{id}/creators', [PersonController::class, 'itemCreators'])
        ->whereNumber('id');

    Route::get('/{slug}', [PersonController::class, 'show']);
});

Route::prefix('apps')->group(function () {
    Route::get('/', [AppResourceController::class, 'index']);
    Route::get('/creators', [AppResourceController::class, 'creators']);
    Route::get('/{slug}', [AppResourceController::class, 'show']);
    Route::get('/{id}/creators', [AppResourceController::class, 'appCreators']);
    Route::post('/{id}/download', [AppResourceController::class, 'download']);
    Route::post('/{id}/react', [AppResourceController::class, 'react'])->middleware('auth:sanctum');
    Route::get('/{id}/reaction-status', [AppResourceController::class, 'reactionStatus'])
    ->middleware('auth:sanctum');
});


// Contact APIs
Route::prefix('contacts')->group(function () {
    Route::get('/categories/{slug}', [ContactController::class, 'category']);
    Route::get('/', [ContactController::class, 'index']);
    Route::get('/divisions', [ContactController::class, 'divisions']);
    Route::get('/districts', [ContactController::class, 'districts']);
    Route::get('/thanas', [ContactController::class, 'thanas']);
    Route::get('/types', [ContactController::class, 'types']);
});

Route::prefix('islam/basic')->group(function () {
    Route::get('/', [BasicIslamController::class, 'index']);

    // আগে specific routes
    Route::post('/{id}/react', [BasicIslamController::class, 'react'])
        ->whereNumber('id')
        ->middleware('auth:sanctum');

    Route::get('/{id}/reaction-status', [BasicIslamController::class, 'reactionStatus'])
        ->whereNumber('id')
        ->middleware('auth:sanctum');

    // পরে generic slug
    Route::get('/{slug}', [BasicIslamController::class, 'show']);
});

Route::prefix('islam/dowan')->group(function () {
    Route::get('/', [DowaController::class, 'index']);

    Route::post('/{id}/react', [DowaController::class, 'react'])
        ->whereNumber('id')
        ->middleware('auth:sanctum');

    Route::get('/{id}/reaction-status', [DowaController::class, 'reactionStatus'])
        ->whereNumber('id')
        ->middleware('auth:sanctum');

    Route::get('/{slug}', [DowaController::class, 'show']);
});

Route::prefix('signs')->group(function () {
    Route::post('/{id}/react', [SignController::class, 'react'])
        ->whereNumber('id')
        ->middleware('auth:sanctum');

    Route::get('/{category}/{sign}', [SignController::class, 'show']);
    Route::get('/{slug}', [SignController::class, 'index']);
});

Route::prefix('ai')->group(function () {

    // ✅ Public routes — guest এবং logged-in উভয়ের জন্য
    Route::get('/guest-usage', [ChatController::class, 'guestUsage']);
    Route::post('/ask', [ChatController::class, 'ask']); // middleware নেই — controller-এ auth('sanctum')->user() দিয়ে check হচ্ছে

    // ✅ Auth-only routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/sessions', [ChatController::class, 'sessions']);
        Route::get('/sessions/{uuid}', [ChatController::class, 'show']);
        Route::delete('/sessions/{id}', [ChatController::class, 'destroy'])->whereNumber('id');
        Route::post('/regenerate', [ChatController::class, 'regenerate']);
        Route::post('/edit-regenerate', [ChatController::class, 'editAndRegenerate']);
    });
});
Route::prefix('sidebar')->group(function () {
    Route::get('/news-sources', [SidebarController::class, 'newsSources']);
    Route::get('/buysell-categories', [SidebarController::class, 'buysellCategories']);
    Route::get('/contact-categories', [SidebarController::class, 'contactCategories']);
    Route::get('/sign-categories', [SidebarController::class, 'signCategories']);
    Route::get('/excel-chapters', [SidebarController::class, 'excelChapters']);
    Route::get('/software-platforms', [SidebarController::class, 'softwarePlatforms']);
});


Route::get('/users/{slug}/profile', [UserPublicProfileController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
// routes/api.php

Route::post('/login', [LoginController::class, 'login']);

Route::prefix('auth')->group(function () {
    // Register OTP
    Route::post('/register/send-otp', [RegisterController::class, 'sendOtp']);
    Route::post('/register/verify', [RegisterController::class, 'verifyAndRegister']);
    Route::post('/register/resend-otp', [RegisterController::class, 'resendOtp']);

    // Route::get(
    //     '/google/redirect',
    //     [GoogleLoginController::class, 'redirectToGoogle']
    // );

    // Route::get(
    //     '/google/callback',
    //     [GoogleLoginController::class, 'handleCallback']
    // );
});

// ── Public Google Auth Routes ─────────────────────────────────────
Route::prefix('auth/google')->group(function () {

    // Next.js থেকে call করবে → Google OAuth URL পাবে
    Route::get('/redirect', [GoogleLoginController::class, 'redirectToGoogle'])
        ->name('auth.google.redirect');

    // Google redirect করে এখানে আসবে → token সহ frontend-এ যাবে
    Route::get('/callback', [GoogleLoginController::class, 'handleCallback'])
        ->name('auth.google.callback');

});

// ── Protected Routes (Sanctum) ────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [GoogleLoginController::class, 'me'])
        ->name('auth.me');

    Route::post('/logout', [GoogleLoginController::class, 'logout'])
        ->name('auth.logout');
});

Route::post('/auth/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);

Route::middleware('auth:sanctum')->group(function () {
    
    //  Route::get('/user', [UserController::class, 'getUser'])
    //     ->name('api.user');

    Route::get('/users/search', [UserController::class, 'search'])
         ->name('api.users.search');
    
    // Route::post('/logout', [LoginController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Conversation users (sorted by last message)
    Route::get('/messages/users', [MessageController::class, 'users']);

    // Online users (non-admin, active, isOnline)
    Route::get('/messages/online', [MessageController::class, 'onlineUsers']);

    // Messages with a specific user (paginated, newest first, then reversed on frontend if needed)
    Route::get('/messages/{user}', [MessageController::class, 'index']);

    // Send message (text + optional file)
    Route::post('/messages', [MessageController::class, 'store']);

    // Edit own message
    Route::put('/messages/{message}', [MessageController::class, 'update']);

    // Delete message (sender or receiver)
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

    // Mark all messages from a user as read
    Route::post('/messages/{user}/read', [MessageController::class, 'markAsRead']);

    // Block / Unblock
    Route::post('/users/{user}/block', [UserController::class, 'toggleBlock']);
    Route::get('/users/{user}/block-status', [UserController::class, 'blockStatus']);
});