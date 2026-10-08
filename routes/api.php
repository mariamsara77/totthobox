<?php

use App\Http\Controllers\Api\ActivitySyncController;
use App\Http\Controllers\Api\TrackingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
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
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\GlobalSearchController;
// All Auth API Controller
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\NewPasswordController;
use App\Http\Controllers\Api\Auth\SwitchProfileController;
// Profile manage controllers
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\AccountController;
use App\Http\Controllers\Api\PublicProfileController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Api\ContactUsController;




Route::prefix('v1')->group(function () {

    // Public
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/auth/google', [GoogleAuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/auth/google/exchange', [GoogleAuthController::class, 'exchange'])->middleware('throttle:10,1');
    
    Route::prefix('auth')->group(function () {
        Route::post('/register/send-otp', [RegisterController::class, 'sendOtp'])->middleware('throttle:5,1');
        Route::post('/register/verify', [RegisterController::class, 'verifyAndRegister'])->middleware('throttle:10,1');
        Route::post('/register/resend-otp', [RegisterController::class, 'resendOtp'])->middleware('throttle:3,1');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->middleware('throttle:5,10');
        Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:10,10');
        Route::post('/switch', [SwitchProfileController::class, 'switch'])->middleware('throttle:10,1');
        Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:30,1');
    });

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    return \Illuminate\Support\Facades\Broadcast::auth($request);
})->middleware('auth:sanctum');

Route::post('/contact', [ContactUsController::class, 'store']);

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
    // Software list
    Route::get('/', [
        AppResourceController::class,
        'index',
    ]);

    // All software creators
    Route::get('/creators', [
        AppResourceController::class,
        'creators',
    ]);

    // Single software
    Route::get('/{slug}', [
        AppResourceController::class,
        'show',
    ]);

    // Creators of a specific software
    Route::get('/{id}/creators', [
        AppResourceController::class,
        'appCreators',
    ])->whereNumber('id');

    // Official source only — never serves local files
    Route::post('/{id}/download', [
        AppResourceController::class,
        'download',
    ])->whereNumber('id');

    // Reactions
    Route::post('/{id}/react', [
        AppResourceController::class,
        'react',
    ])
        ->whereNumber('id')
        ->middleware('auth:sanctum');

    Route::get('/{id}/reaction-status', [
        AppResourceController::class,
        'reactionStatus',
    ])
        ->whereNumber('id')
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




Route::prefix('v1')->group(function () {

    // Public profile
    Route::get('/users/{slug}/profile', [PublicProfileController::class, 'show']);

    // Authenticated profile / settings
    Route::middleware('auth:sanctum')->prefix('profile')->group(function () {

        // Current user profile (for settings form)
        Route::get('/', [ProfileController::class, 'show']);

        // Update profile information + avatar + role + location
        Route::post('/', [ProfileController::class, 'update']);          // multipart for avatar
        Route::put('/', [ProfileController::class, 'update']);           // JSON only (no avatar)

        // Avatar only
        Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
        Route::delete('/avatar', [ProfileController::class, 'removeAvatar']);

        // Role helpers
        Route::get('/available-roles', [ProfileController::class, 'availableRoles']);
        Route::delete('/role', [ProfileController::class, 'removeRole']);

        // Location cascade helpers (optional – you can also do them client-side)
        Route::get('/divisions', [ProfileController::class, 'divisions']);
        Route::get('/districts/{division}', [ProfileController::class, 'districts']);
        Route::get('/thanas/{district}', [ProfileController::class, 'thanas']);
        Route::get('/class-levels', [ProfileController::class, 'classLevels']);
    });

    // Password
    Route::middleware('auth:sanctum')->prefix('password')->group(function () {
        Route::put('/', [PasswordController::class, 'update']);
    });

    // Account deletion
    Route::middleware('auth:sanctum')->prefix('account')->group(function () {
        Route::delete('/', [AccountController::class, 'destroy']);
    });
});


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
// routes/api.php

Route::prefix('auth')->group(function () {
    // Register OTP
    Route::post('/register/send-otp', [RegisterController::class, 'sendOtp'])->middleware('throttle:5,1');
    Route::post('/register/verify', [RegisterController::class, 'verifyAndRegister'])->middleware('throttle:10,1');
    Route::post('/register/resend-otp', [RegisterController::class, 'resendOtp'])->middleware('throttle:3,1');
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

     Route::get('/users/search', [UserController::class, 'search'])
         ->name('api.users.search');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications', [NotificationController::class, 'clearAll']);
});


Route::get('/search', [GlobalSearchController::class, 'search']);
Route::get('/search/meta', [GlobalSearchController::class, 'meta']);