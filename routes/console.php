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

Schedule::call(function () {
    (new StreamService)->refreshAllStreams();
})->hourly();

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//  CHANNEL SYNC SCHEDULE
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

// 🔴 Health-check — দিনে ১ বার রাত ১২:০০ টায় হবে
Schedule::command('channels:sync-all --type=all --health-only --chunk=30')
    ->dailyAt('00:00')
    ->withoutOverlapping(60)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/channel-health.log'));

// 🏆 FIFA World Cup channels — দিনে ১ বার রাত ০১:০০ টায় হবে
Schedule::command('channels:sync-all --type=worldcup --force --chunk=30')
    ->dailyAt('01:00')
    ->withoutOverlapping(60)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/worldcup-sync.log'));

// ⚽ Full new-channel fetch — দিনে ১ বার রাত ০২:০০ টায় হবে
Schedule::command('channels:sync-all --type=all --force --chunk=30')
    ->dailyAt('02:00')
    ->withoutOverlapping(60)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/channel-sync.log'));

// 🌙 Nightly deep full sync — দিনে ১ বার রাত ০৩:৩০ AM এ হবে
Schedule::command('channels:sync-all --type=all --chunk=50')
    ->dailyAt('03:30')
    ->withoutOverlapping(120)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/channel-full-sync.log'));

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//  OTHER SCHEDULED JOBS (NOW PROPERLY LOGGED)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

// Sitemap regeneration — once daily at midnight
Schedule::command('sitemap:generate')
    ->dailyAt('00:10')
    ->appendOutputTo(storage_path('logs/sitemap-generate.log'));

// Google Auto Indexing — Daily at 01:00 AM (1 hour after sitemap)
Schedule::command('google:auto-index-sitemap')
    ->dailyAt('01:00')
    ->appendOutputTo(storage_path('logs/google-indexing.log'));

Schedule::call(function () {
    $service = app(GoogleIndexingService::class);

    if (! $service->isConfigured()) {
        return;
    }

    $remaining = $service->remainingQuota();

    if ($remaining <= 0) {
        return;
    }

    // আগে QuotaExceeded, তারপর পুরনো Failed (সর্বোচ্চ remaining সংখ্যক)
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

// AI Content Auto-Improve — Daily at 04:00 AM
// Schedule::command('content:auto-improve')
//     ->dailyAt('04:00')
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->appendOutputTo(storage_path('logs/auto-improve.log'));

// AI Content Stats — Weekly (Dry run)
// Schedule::command('content:auto-improve --dry-run')
//     ->weekly()
//     ->appendOutputTo(storage_path('logs/auto-improve-stats.log'));

// Scrape all news sources every 30 minutes
Schedule::command('news:scrape')
    ->everyThirtyMinutes()
    ->withoutOverlapping(25)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/news-scraper.log'));

// Telescope Prune Logs — Daily
Schedule::command('telescope:prune --hours=24')
    ->daily()
    ->appendOutputTo(storage_path('logs/telescope-prune.log'));

// Pulse Prune Logs — Daily (pulse_entries ক্লিয়ার রাখার জন্য)
// Schedule::command('pulse:clear')
//     ->daily()
//     ->appendOutputTo(storage_path('logs/pulse-trim.log'));

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//  ARTISAN COMMANDS
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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

// Cross-source story clustering (AdSense value-add)
Schedule::command('news:cluster-stories --hours=48')
    ->everyFifteenMinutes()
    ->withoutOverlapping(12)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/news-cluster.log'));

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
