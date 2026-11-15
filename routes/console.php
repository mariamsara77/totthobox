<?php

use App\Enums\IndexingStatus;
use App\Jobs\IndexUrlJob;
use App\Models\ConversionTask;
use App\Models\IndexedUrl;
use App\Services\GoogleIndexingService;
use App\Services\NewsScraperService;
use App\Services\StreamService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Stream Refresh
|--------------------------------------------------------------------------
*/

Schedule::call(function () {
    (new StreamService)->refreshAllStreams();
})->hourly()->name('refresh-all-streams')->withoutOverlapping(10);

/*
|--------------------------------------------------------------------------
| Channel Sync Schedule
|--------------------------------------------------------------------------
*/

// Health-check — প্রতিদিন রাত 12:00
Schedule::command('channels:sync-all --type=all --health-only --chunk=30')
    ->dailyAt('00:00')
    ->withoutOverlapping(60)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/channel-health.log'));

// FIFA World Cup channels — রাত 01:00
Schedule::command('channels:sync-all --type=worldcup --force --chunk=30')
    ->dailyAt('01:00')
    ->withoutOverlapping(60)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/worldcup-sync.log'));

// Full new-channel fetch — রাত 02:00
Schedule::command('channels:sync-all --type=all --force --chunk=30')
    ->dailyAt('02:00')
    ->withoutOverlapping(60)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/channel-sync.log'));

// Nightly deep full sync — রাত 03:30
Schedule::command('channels:sync-all --type=all --chunk=50')
    ->dailyAt('03:30')
    ->withoutOverlapping(120)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/channel-full-sync.log'));

/*
|--------------------------------------------------------------------------
| Sitemap & Google Indexing
|--------------------------------------------------------------------------
*/

// Sitemap regeneration — রাত 00:15
Schedule::command('sitemap:generate')
    ->dailyAt('00:15')
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/sitemap-generate.log'));

// Google Auto Indexing — রাত 01:15 (sitemap এর পরে)
Schedule::command('google:auto-index-sitemap')
    ->dailyAt('01:15')
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/google-indexing.log'));

// Google Indexing Daily Retry — রাত 00:05
Schedule::call(function () {
    $service = app(GoogleIndexingService::class);

    if (! $service->isConfigured()) {
        return;
    }

    $remaining = $service->remainingQuota();

    if ($remaining <= 0) {
        return;
    }

    $records = IndexedUrl::query()
        ->whereIn('status', [
            IndexingStatus::QuotaExceeded,
            IndexingStatus::Failed,
        ])
        ->orderByRaw("CASE WHEN status = 'quota_exceeded' THEN 0 ELSE 1 END")
        ->orderBy('updated_at')
        ->limit($remaining)
        ->get();

    foreach ($records as $record) {
        $record->update([
            'status' => IndexingStatus::Queued,
            'error_message' => null,
        ]);

        IndexUrlJob::dispatch($record->id);
    }

    if ($records->isNotEmpty()) {
        Log::info('Google Indexing: Daily auto-retry dispatched', [
            'count' => $records->count(),
            'remaining' => $remaining,
        ]);
    }
})
    ->dailyAt('00:05')
    ->name('google-indexing-daily-retry')
    ->withoutOverlapping(30)
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| News Related Jobs
|--------------------------------------------------------------------------
*/

// Scrape all news sources every 30 minutes
Schedule::command('news:scrape')
    ->everyThirtyMinutes()
    ->withoutOverlapping(25)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/news-scraper.log'));

// Cross-source story clustering (AdSense value-add)
Schedule::command('news:cluster-stories --hours=48')
    ->everyFifteenMinutes()
    ->withoutOverlapping(12)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/news-cluster.log'));

/*
|--------------------------------------------------------------------------
| Cleanup & Maintenance
|--------------------------------------------------------------------------
*/

// Telescope Prune
Schedule::command('telescope:prune --hours=24')
    ->daily()
    ->appendOutputTo(storage_path('logs/telescope-prune.log'));

// Cleanup expired conversion tasks
Schedule::call(function (): void {
    ConversionTask::withTrashed()
        ->where('created_at', '<', now()->subHours(2))
        ->get()
        ->each(function (ConversionTask $task): void {
            $task->clearMediaCollection('original_files');
            $task->clearMediaCollection('converted_files');
            $task->forceDelete();
        });
})->hourly()->name('cleanup-expired-conversion-tasks')->withoutOverlapping()->onOneServer();


/*
|--------------------------------------------------------------------------
| Website Knowledge Base Crawl (AI এর জন্য)
|--------------------------------------------------------------------------
*/

Schedule::command('website:crawl')
    ->cron('0 4 */15 * *')
    ->withoutOverlapping(120)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/website-crawl.log'))
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| Custom Artisan Commands
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Super-clean: clears all caches then re-caches for production.
 * Usage: php artisan super:clean [--prod]
 */
Artisan::command('super:clean {--prod : Also rebuild config/route/view caches}', function () {
    $this->warn('⚡ Starting optimization process...');

    $this->info('① Clearing all caches...');
    Artisan::call('optimize:clear');
    $this->line(Artisan::output());

    if ($this->option('prod')) {
        $this->info('② Rebuilding production caches...');
        Artisan::call('optimize');
        Artisan::call('view:cache');
        Artisan::call('event:cache');
        $this->info('   ✓ All production caches optimized.');
    }

    $this->newLine();
    $this->info('✅ Project optimized successfully.');
})->purpose('Clear caches and optionally rebuild for production (--prod)');

/**
 * Scrape news on demand.
 * Usage: php artisan news:scrape [--source=prothom_alo]
 */
Artisan::command('news:scrape {--source= : Scrape a single source by key}', function () {
    $service = app(NewsScraperService::class);
    $source = $this->option('source');

    if ($source) {
        $this->info("Scraping source: {$source}");
        $service->scrapeByKey($source);
    } else {
        $this->info('Scraping all sources...');
        $service->scrapeAll();
    }

    $this->info('✅ Done.');
})->purpose('Scrape news from RSS feeds. Use --source=key to target one source.');