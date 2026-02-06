<?php

use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

// --- Public & Common Routes ---
Route::get('/quick-login/{id}', function ($id) {
    if (!request()->hasValidSignature()) {
        abort(403, 'Unauthorized or expired link.');
    }
    $user = User::findOrFail($id);
    Auth::login($user);
    return redirect()->route('home');
})->name('quick.login');

Route::view('/', 'welcome')->name('home');
Route::view('/offline', 'offline')->name('offline');
Route::get('/api/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('api.csrf-token');

Route::get('/clean-project', function () {
    // Artisan::call ব্যবহার করে কমান্ড রান করা
    Artisan::call('super:clean');

    // কমান্ডের আউটপুট দেখতে চাইলে
    return "Project is cleaned successfully! <br><pre>" . Artisan::output() . "</pre>";
});

 Volt::route('/users/{slug}', 'website.users.show')->name('users.show');

Route::post('/track-event', [TrackingController::class, 'trackEvent'])->name('track.event');

// --- Front-end Content Routes (Public) ---
Route::prefix('bangladesh/')->name('bangladesh.')->group(function () {
    Volt::route('introduction', 'website.bangladesh.introbd')->name('introduction');
    Volt::route('tourism', 'website.bangladesh.tourism')->name('tourism');
    Volt::route('history', 'website.bangladesh.historybd')->name('history');
    Volt::route('establishment', 'website.bangladesh.establishment')->name('establishment');
    Volt::route('minister', 'website.bangladesh.minister')->name('minister');
});

Route::prefix('international/')->name('international.')->group(function () {
    Volt::route('all-country', 'website.international.all-country')->name('all-country');
});

Route::prefix('islam/')->name('islam.')->group(function () {
    Volt::route('basicislam', 'website.islam.basic-islam')->name('basicislam');
    Volt::route('dowa', 'website.islam.dowa')->name('dowa');
    Volt::route('al-quran', 'website.islam.al-quran')->name('al-quran');
});

Route::prefix('health/')->name('health.')->group(function () {
    Volt::route('calorie-chart', 'website.health.calorie-chart')->name('calorie-chart');
    Volt::route('food-nutrients', 'website.health.food-nutrients')->name('food-nutrients');
    Volt::route('basic-health', 'website.health.basic-health')->name('basic-health');
});

Route::prefix('contact/')->name('contact.')->group(function () {
    Volt::route('{slug}', 'website.contacts.contact')->name('number');
});

Route::prefix('mcq/')->name('mcq.')->group(function () {
    Volt::route('', 'website.education.mcq-home')->name('home');
    Volt::route('subject/{subjectId}', 'website.education.mcq-subject')->name('subject');
    Volt::route('test/{testId}', 'website.education.mcq-take-test')->name('take-test');
    Volt::route('test-result', 'website.education.test-attempts')->name('test-result');
});

Route::prefix('education/child/')->name('education.child.')->group(function () {
    Volt::route('practice', 'website.education.child.practice')->name('practice');
});
Route::prefix('calendar/')->name('calendar.')->group(function () {
    Volt::route('', 'website.calendar.calendar')->name('calendar');
    Volt::route('holiday', 'website.calendar.holiday')->name('holiday');
});

Route::prefix('converter')->name('converter.')->group(function () {
    Volt::route('currency', 'website.converter.currency-converter')->name('currency');
    Volt::route('length', 'website.converter.length-converter')->name('length');
    Volt::route('weight', 'website.converter.weight-converter')->name('weight');
    Volt::route('area', 'website.converter.area-converter')->name('area');
    Volt::route('volume', 'website.converter.volume-converter')->name('volume');
    Volt::route('temperature', 'website.converter.temperature-converter')->name('temperature');
    Volt::route('speed', 'website.converter.speed-converter')->name('speed');
    Volt::route('time', 'website.converter.time-converter')->name('time');
    Volt::route('data', 'website.converter.data-converter')->name('data');
    Volt::route('energy', 'website.converter.energy-converter')->name('energy');
    Volt::route('land', 'website.converter.land-converter')->name('land');
});

Route::prefix('signs/')->name('signs.')->group(function () {
    Volt::route('{slug}', 'website.signs.sign')->name('sign');
});
Route::prefix('buysell/')->name('buysell.')->group(function () {
    Volt::route('category/all', 'website.buysell.buysell')->name('all'); // Changed this line
    Volt::route('prodict/{slug}', 'website.buysell.buysell-single')->name('buysell-single');
    Volt::route('category/{categorySlug}', 'website.buysell.buysell-category')->name('category');
});

Volt::route('/excel-expert/{slug?}', 'website.excel.excel')->name('excel.view');


// --- Auth Protected Routes ---
Route::middleware(['auth'])->group(function () {
    Volt::route('buysell/produc/create', 'website.buysell.buysell-postad')->name('buysell.post-ad');
    Volt::route('/messages/{slug}', 'chat.messaging')->name('messages');
    Volt::route('notifications', 'chat.notification-bell')->name('notifications');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('', 'profile');
        Volt::route('/profile', 'settings.profile')->name('profile');
        Volt::route('/profile/{slug}', 'settings.profile-view')->name('profile.view');
        Volt::route('/password', 'settings.password')->name('password');
        Volt::route('/appearance', 'settings.appearance')->name('appearance');
    });
});

// --- ADMIN SECTION (With Individual Permissions) ---
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {

    // ১. ড্যাশবোর্ড এক্সেস
    Route::middleware(['can:view-dashboard'])->group(function () {
        Volt::route('/dashboard', 'admin.dashboard.dashboard')->name('dashboard');
        Volt::route('/dashboard/manage', 'admin.dashboard.session-manage')->name('dashboard.session');
        Volt::route('/dashboard/visitor-dashboard', 'admin.dashboard.visitor-dashboard')->name('dashboard.visitor');
        Volt::route('/dashboard/visitor-analytics/{visitorId}', 'admin.dashboard.visitor-details')->name('dashboard.visitor.details');

        Volt::route('/dashboard/missing-data', 'admin.dashboard.missing-data-manager')->name('dashboard.missing-data');
        });

    // ২. ইউজার ও রোল ম্যানেজমেন্ট (সবচেয়ে সেনসিটিভ)
    Route::prefix('users')->name('users.')->group(function () {
      
            Volt::route('/manage', 'admin.users.users-manage')->name('manage');
      
        Route::middleware(['can:manage-roles'])->group(function () {
            Volt::route('/role-manage', 'admin.users.role-manage')->name('role.manage');
            Volt::route('/permission-manage', 'admin.users.permission-manage')->name('permission.manage');
        });
    });

    // ৩. বাংলাদেশ কন্টেন্ট ম্যানেজমেন্ট
    Route::prefix('bangladesh')->middleware(['can:manage-bangladesh'])->name('bangladesh.')->group(function () {
        Volt::route('/introduction', 'admin.bangladesh.intro-manage')->name('introduction');
        Volt::route('/tourism', 'admin.bangladesh.tourism-manage')->name('tourism');
        Volt::route('/history', 'admin.bangladesh.historybd-manage')->name('history');
        Volt::route('/establishment', 'admin.bangladesh.establishmentbd-manage')->name('establishment');
        Volt::route('/holiday', 'admin.bangladesh.holiday-manage')->name('holiday');
        Volt::route('/minister', 'admin.bangladesh.minister-manage')->name('minister');
    });

    // ৪. ইসলাম ম্যানেজমেন্ট
    Route::prefix('islam')->middleware(['can:manage-islam'])->name('islam.')->group(function () {
        Volt::route('basicislam', 'admin.islam.basicislam-manage')->name('basicislam');
        Volt::route('dowa', 'admin.islam.dowa-manage')->name('dowa');
        Volt::route('para', 'admin.islam.para-manage')->name('para');
        Volt::route('surah', 'admin.islam.surah-manage')->name('surah');
        Volt::route('quran', 'admin.islam.quran-manage')->name('quran');
    });

    // ৫. হেলথ ম্যানেজমেন্ট
    Route::prefix('health')->middleware(['can:manage-health'])->name('health.')->group(function () {
        Volt::route('/food/category', 'admin.health.food-category-manage')->name('food.category');
        Volt::route('/food', 'admin.health.food-manage')->name('food');
        Volt::route('/nutrient', 'admin.health.nutrient-manage')->name('nutrient');
        Volt::route('/vitamins', 'admin.health.vitamins-manage')->name('vitamins');
        Volt::route('/food/nutrient', 'admin.health.food-nutrient-manage')->name('food.nutrient');
        Volt::route('/basic-health', 'admin.health.basic-health-manage')->name('basic-health');
    });

    // ৬. এডুকেশন বা MCQ ম্যানেজমেন্ট
    Route::prefix('education')->middleware(['can:manage-education'])->name('education.')->group(function () {
        Volt::route('class', 'admin.education.class-manage')->name('class');
        Volt::route('subject', 'admin.education.subject-manage')->name('subject');
        Volt::route('test', 'admin.education.test-manage')->name('test');
        Volt::route('questions', 'admin.education.question-manage')->name('questions');
        Volt::route('test-questions', 'admin.education.test-question-manage')->name('test-questions');
    });

    // ৭. কন্টাক্ট, সাইন এবং বাই-সেল
    Route::prefix('contact')->middleware(['can:manage-contacts'])->name('contact.')->group(function () {
        Volt::route('/contact-category', 'admin.contact.contact-category-manage')->name('contact-category');
        Volt::route('/contact-number', 'admin.contact.contact-number-manage')->name('contact-number');
    });

    Route::prefix('sign')->middleware(['can:manage-signs'])->name('sign.')->group(function () {
        Volt::route('category-manage', 'admin.sign.sign-category-manage')->name('category-manage');
        Volt::route('manage', 'admin.sign.sign-manage')->name('manage');
    });

    Route::prefix('buysell')->middleware(['can:manage-buysell'])->name('buysell.')->group(function () {
        Volt::route('category-manage', 'admin.buysell.buysell-category-manage')->name('category-manage');
        Volt::route('item-manage', 'admin.buysell.buysell-item-manage')->name('item-manage');
        Volt::route('manage', 'admin.buysell.buysell-manage')->name('manage');
    });

    Route::prefix('excel')->middleware(['can:manage-excel'])->name('excel.')->group(function () {
        Volt::route('formula-manage', 'admin.excel.excel-manage')->name('formula-manage');
    });
});

require __DIR__ . '/auth.php';