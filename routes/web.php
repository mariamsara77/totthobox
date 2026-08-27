<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Public & Content Routes (Cached)
|--------------------------------------------------------------------------
*/
Route::middleware(['responsecache', 'can:view-dashboard'])->group(function () {
    // Basic Routes
    Route::view('/', 'welcome')->name('home');
    Volt::route('/privacy-policy', 'global.privacy-policy')->name('privacy.policy');
    Volt::route('/contact-us', 'global.contact')->name('contact.us');
    Volt::route('/about-us', 'global.about')->name('about.us');
    Route::view('/help', 'livewire.global.help')->name('help.us');
    Volt::route('/status', 'global.status')->name('system.status');
    Volt::route('/terms-of-service', 'global.terms-of-service')->name('terms.service');
    Route::view('/offline', 'offline')->name('offline');

    // Bangladesh Section
    Route::prefix('bangladesh')->name('bangladesh.')->group(function () {
        Volt::route('introduction', 'website.bangladesh.introbd')->name('introduction');
        Volt::route('introduction/{slug}', 'website.bangladesh.introbd-show')->name('introduction.show');
        Volt::route('tourism', 'website.bangladesh.tourism')->name('tourism');
        Volt::route('tourism/{slug}', 'website.bangladesh.tourism-show')->name('tourism.show');
        Volt::route('history', 'website.bangladesh.historybd')->name('history');
        Volt::route('history/{slug}', 'website.bangladesh.historybd-show')->name('history.show');
        Volt::route('establishment', 'website.bangladesh.establishment')->name('establishment');
        Volt::route('establishment/{slug}', 'website.bangladesh.establishment-show')->name('establishment.show');
        Volt::route('public-figure', 'website.bangladesh.public-figure')->name('public-figure');
        Volt::route('public-figure/{slug}', 'website.bangladesh.public-figure-show')->name('public-figure.show');
    });

    // Apps & International
    Route::prefix('software')->name('software.')->group(function () {
        Volt::route('all/{platform?}', 'website.apps.all-apps')->name('all');
        Volt::route('{slug}', 'website.apps.app')->name('show');
    });
    Route::prefix('international')->name('international.')->group(function () {
        Route::livewire('all-country', 'website.international.all-country')->name('all-country');
        Route::livewire('country/{slug}', 'website.international.single-country')->name('country');
    });

    // Blogs/News & Islam
    // Route::prefix('news')->name('news.')->group(function () {
    //     Route::livewire('/headlines', 'website.news.news-headlines')->name('headlines');
    //     Route::livewire('/source/{source_slug}', 'website.news.news-headlines')->name('source');
    // });

    Route::prefix('islam')->name('islam.')->group(function () {
        Route::livewire('basic', 'website.islam.basic-islam')->name('basicislam');
        Route::livewire('basic/{slug}', 'website.islam.basic-islam-show')->name('basicislam.show');
        Volt::route('dowan', 'website.islam.dowan')->name('dowan');
        Volt::route('dowan/{slug}', 'website.islam.dowan-show')->name('dowan.show');
        // Volt::route('al-quran', 'website.islam.al-quran')->name('al-quran');
    });

    // Health, Education & others
    Route::prefix('health')->name('health.')->group(function () {
        Volt::route('calorie-chart', 'website.health.calorie-chart')->name('calorie-chart');
        Volt::route('food-nutrients', 'website.health.food-nutrients')->name('food-nutrients');
        Volt::route('basic-health', 'website.health.basic-health')->name('basic-health');
    });
    Volt::route('contact/{slug}', 'website.contacts.contact')->name('contact.number');

    Route::prefix('education/child')->name('education.child.')->group(function () {
        Volt::route('practice', 'website.education.child.practice')->name('practice');
    });
    Route::prefix('bangla')->name('calendar.')->group(function () {
        Volt::route('calendar', 'website.calendar.calendar')->name('calendar');
        Volt::route('holiday', 'website.calendar.holiday')->name('holiday');
        Volt::route('holiday/{slug}', 'website.calendar.holiday-show')->name('holiday.show');
    });

    Route::prefix('converter')->name('converter.')->group(function () {
        Volt::route('number-to-word', 'website.converter.number-converter')->name('number-to-word');
        Volt::route('adarshalipi', 'website.converter.adosholipi-converter')->name('adarshalipi');
        Volt::route('currency', 'website.converter.currency-converter')->name('currency');
        Volt::route('length', 'website.converter.length-converter')->name('length');
        Volt::route('weight', 'website.converter.weight-converter')->name('weight');
        Volt::route('area', 'website.converter.area-converter')->name('area');
        Volt::route('volume', 'website.converter.volume-converter')->name('volume');
        Volt::route('temperature', 'website.converter.temperature-converter')->name('temperature');
        Volt::route('speed', 'website.converter.speed-converter')->name('speed');
        Volt::route('time', 'website.converter.time-converter')->name('time');
        Volt::route('data', 'website.converter.data-converter')->name('unit-data');
        Volt::route('energy', 'website.converter.energy-converter')->name('energy');
        Volt::route('land', 'website.converter.land-converter')->name('land');
        Route::livewire('image', 'website.converter.image')->name('image');
        Route::livewire('document', 'website.converter.document')->name('document');
        Route::livewire('media', 'website.converter.media')->name('media');
        Route::livewire('file-data', 'website.converter.data')->name('file-data');
    });

    Route::prefix('tools')->name('tools.')->group(function () {
        Route::livewire('image-resizer', 'website.tools.image-resize')->name('image-resizer');
        Route::livewire('age-calculator', 'website.tools.age-calculator')->name('age-calculator');
        Route::livewire('word-and-character-counter', 'website.tools.word-counter')->name('word-counter');
        Route::livewire('zodiac-calculator', 'website.tools.zodiac-calculator')->name('zodiac-calculator');
        Route::livewire('percentage-calculator', 'website.tools.percentage-calculator')->name('percentage-calculator');
        Route::livewire('qrcode-generator', 'website.tools.qrcode-generator')->name('qrcode-generator');
        Route::livewire('id-card-generator', 'website.tools.id-card-generator')->name('id-card-generator');
        Route::livewire('id-card/{idCard}', 'website.tools.id-card-show')->name('id-card.show');
    });

    Route::prefix('signs')->name('signs.')->group(function () {
        Route::livewire('/{category}/{sign}', 'website.signs.show')->name('show');
        Route::livewire('all', 'website.signs.sign')->name('sign.all');
        Route::livewire('{slug}', 'website.signs.sign')->name('sign');
    });
    Route::prefix('buysell')->name('buysell.')->group(function () {
        Volt::route('category/all', 'website.buysell.buysell')->name('all');
        Volt::route('prodict/{slug}', 'website.buysell.buysell-single')->name('buysell-single');
        Volt::route('category/{categorySlug}', 'website.buysell.buysell-category')->name('category');
    });
    Route::livewire('/excel-expert/{slug?}', 'website.excel.excel')->name('excel.view');
    Volt::route('/users/{slug}', 'website.users.show')->name('users.show');
});

/*
|--------------------------------------------------------------------------
| Dynamic, Auth & Admin Routes (NO Caching)
|--------------------------------------------------------------------------
*/
// Public utility/API routes
Route::get('/quick-login/{id}', function ($id) {
    if (!request()->hasValidSignature()) {
        abort(403);
    }
    Auth::login(User::findOrFail($id));

    return redirect()->route('home');
})->name('quick.login');
Route::get('/api/csrf-token', fn() => response()->json(['token' => csrf_token()]))->name('api.csrf-token');
Route::get('/clean-project', fn() => Artisan::call('super:clean') ? 'Done' : Artisan::output());

// AI Routes
Route::prefix('ai')->name('ai.')->group(function () {
    Volt::route('chat/{uuid?}', 'ai.tutor')->name('chat.show');
});

// Authenticated User Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('buysell/produc/create', 'website.buysell.buysell-postad')->name('buysell.post-ad');
    Volt::route('/messages/{slug}', 'chat.messaging')->name('messages');
    Volt::route('notifications', 'chat.notification-bell')->name('notifications');
    Route::prefix('profile')->name('profile.')->group(function () {
        Volt::route('/', 'settings.profile-view')->name('view');
        Volt::route('/settings', 'settings.profile')->name('settings');
        Volt::route('/remove', 'settings.delete-user-form')->name('remove');
        Volt::route('/password', 'settings.password')->name('password');
        Volt::route('/appearance', 'settings.appearance')->name('appearance');
        Volt::route('/activity', 'settings.activity')->name('activity');
    });
});

Route::middleware(['auth'])->group(function () {

    Route::post('/push-subscribe', function () {
        $data = request()->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'device_label' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        $user->updatePushSubscription(
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
        );

        $user->pushSubscriptions()
            ->where('endpoint', $data['endpoint'])
            ->update([
                'device_label' => $data['device_label'] ?? request()->userAgent(),
                'last_active_at' => now(),
            ]);

        return response()->json(['success' => true]);
    });

    Route::post('/push-unsubscribe', function () {
        $data = request()->validate(['endpoint' => 'required|string']);
        auth()->user()->deletePushSubscription($data['endpoint']);

        return response()->json(['success' => true]);
    });

    Route::get('/push-devices', function () {
        return auth()->user()->pushSubscriptions()
            ->latest('last_active_at')
            ->get(['id', 'device_label', 'last_active_at', 'created_at']);
    });
});
// Admin Section
require __DIR__ . '/admin.php'; // বা আপনার বর্তমান অ্যাডমিন রাউটগুলো নিচে এখানে বসান
require __DIR__ . '/auth.php';