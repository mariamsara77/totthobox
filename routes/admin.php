<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::prefix('admin')->middleware(['auth', 'verified'])->name('admin.')->group(function () {

    // 1. Dashboard & System Management
    Route::middleware(['can:view-dashboard'])->group(function () {
        Route::livewire('/dashboard', 'admin.dashboard.dashboard')->name('dashboard');
        Volt::route('/dashboard/session-manage', 'admin.dashboard.session-manage')->name('dashboard.session');
        Volt::route('/dashboard/visitor-dashboard', 'admin.dashboard.visitor-dashboard')->name('dashboard.visitor');
        Volt::route('/dashboard/visitor-analytics/{visitorId}', 'admin.dashboard.visitor-details')->name('dashboard.visitor.details');
        Volt::route('/dashboard/universal-ai', 'admin.dashboard.universal-ai-create')->name('dashboard.universal-ai');
        Volt::route('/dashboard/missing-data', 'admin.dashboard.missing-data-manager')->name('dashboard.missing-data');

        Route::middleware(['can:system-manager'])->group(function () {
            Volt::route('/dashboard/system-manager/terminal-command', 'admin.dashboard.terminal-command-manager')->name('dashboard.system-manager.terminal-command');
            Volt::route('/dashboard/system-manager/database-monitor', 'admin.dashboard.database-monitor')->name('dashboard.system-manager.database-monitor');
        });

        Route::middleware(['can:google indexing request'])->group(function () {
            Volt::route('/dashboard/google-indexing', 'admin.dashboard.google-indexing-request')->name('dashboard.google-indexing');
        });
    });

    Route::middleware(['can:view-analytics'])->group(function () {
        Route::livewire('analytics/page-performance', 'admin.analytics.page-performance')
            ->name('analytics.page-performance');
    });

    // 2. User, Role & App Management
    Route::middleware(['can:manage-users'])->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Volt::route('/manage', 'admin.users.users-manage')->name('manage');
            Volt::route('/activity/{slug?}', 'admin.users.user-activity')->name('activity');
            Volt::route('/activity', 'admin.users.all-activity')->name('activity.all');
        });
    });

    Route::middleware(['can:manage-roles'])->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Volt::route('/role-manage', 'admin.users.role-manage')->name('role.manage');
            Volt::route('/permission-manage', 'admin.users.permission-manage')->name('permission.manage');
        });
    });

    Route::prefix('apps')->middleware(['can:manage-apps'])->name('apps.')->group(function () {
        Volt::route('/manage', 'admin.app.apps-manage')->name('manage');
    });

    // 3. Bangladesh, Location & Person Content
    Route::prefix('ai-generator')->middleware(['can:manage-ai-generator'])->name('ai-generator.')->group(function () {
        Volt::route('/all-models', 'admin.ai-generator.all-models')->name('all-models');
    });

    Route::prefix('bangladesh')->middleware(['can:manage-bangladesh'])->name('bangladesh.')->group(function () {
        Volt::route('/introduction', 'admin.bangladesh.intro-manage')->name('introduction');
        Volt::route('/tourism', 'admin.bangladesh.tourism-manage')->name('tourism');
        Volt::route('/history', 'admin.bangladesh.historybd-manage')->name('history');
        Volt::route('/establishment', 'admin.bangladesh.establishmentbd-manage')->name('establishment');
        Volt::route('/holiday', 'admin.bangladesh.holiday-manage')->name('holiday');
        Volt::route('/minister', 'admin.bangladesh.minister-manage')->name('minister');
    });

    Route::prefix('location')->middleware(['can:manage-location'])->name('location.')->group(function () {
        Volt::route('/division', 'admin.location.division-manage')->name('division');
        Volt::route('/district', 'admin.location.district-manage')->name('district');
        Volt::route('/thana', 'admin.location.thana-manage')->name('thana');
    });

    Route::prefix('person')->middleware(['can:manage-person'])->name('person.')->group(function () {
        Volt::route('/', 'admin.person.person-manage')->name('manage');
        Volt::route('/category', 'admin.person.person-category-manage')->name('category');
        Volt::route('/position', 'admin.person.position-manage')->name('position');
        Volt::route('/role-history', 'admin.person.role-history-manage')->name('role-history');
    });

    // 4. Content Modules (Islam, Health, Education, etc.)
    Route::prefix('islam')->middleware(['can:manage-islam'])->name('islam.')->group(function () {
        Volt::route('basicislam', 'admin.islam.basicislam-manage')->name('basicislam');
        Volt::route('dowa', 'admin.islam.dowa-manage')->name('dowa');
        Volt::route('para', 'admin.islam.para-manage')->name('para');
        Volt::route('surah', 'admin.islam.surah-manage')->name('surah');
        Volt::route('quran', 'admin.islam.quran-manage')->name('quran');
    });

    Route::prefix('health')->middleware(['can:manage-health'])->name('health.')->group(function () {
        Volt::route('/food/category', 'admin.health.food-category-manage')->name('food.category');
        Volt::route('/food', 'admin.health.food-manage')->name('food');
        Volt::route('/nutrient', 'admin.health.nutrient-manage')->name('nutrient');
        Volt::route('/vitamins', 'admin.health.vitamins-manage')->name('vitamins');
        Volt::route('/food/nutrient', 'admin.health.food-nutrient-manage')->name('food.nutrient');
        Volt::route('/basic-health', 'admin.health.basic-health-manage')->name('basic-health');
    });

    // 5. Utility & External Modules
    Route::prefix('contact')->middleware(['can:manage-contacts'])->name('contact.')->group(function () {
        Volt::route('/contact-category', 'admin.contact.contact-category-manage')->name('contact-category');
        Volt::route('/contact-number', 'admin.contact.contact-number-manage')->name('contact-number');
    });

    Route::prefix('sign')->middleware(['can:manage-signs'])->name('sign.')->group(function () {
        Volt::route('category-manage', 'admin.sign.sign-category-manage')->name('category-manage');
        Volt::route('manage', 'admin.sign.sign-manage')->name('manage');
    });

    Route::prefix('excel')->middleware(['can:manage-excel'])->name('excel.')->group(function () {
        Volt::route('formula-manage', 'admin.excel.excel-manage')->name('formula-manage');
    });

    Route::prefix('news')->middleware(['can:manage-news'])->name('news.')->group(function () {
        Volt::route('headlines-manage', 'admin.news.headlines-manage')->name('headlines-manage');
        Volt::route('headlines-dashboard', 'admin.news.headlines-dashboard')->name('headlines-dashboard');
    });

    Route::prefix('blogs')->middleware(['can:manage-blogs'])->name('blogs.')->group(function () {
        Volt::route('/live-matches', 'admin.blogs.live-match-manager')->name('live-matches');
    });

    Route::prefix('messages')->middleware(['can:manage-messages'])->name('messages.')->group(function () {
        Volt::route('/messages-manager', 'admin.messages.messages-manager')->name('messages-manager');
    });
    Route::prefix('notification')->middleware(['can:manage-notification'])->name('notification.')->group(function () {
        Volt::route('/notification-manager', 'admin.notifications.broadcasting')->name('notification-manager');
    });
});