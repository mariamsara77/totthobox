<?php

namespace App\Console\Commands;

use App\Models\AppResource;
use App\Models\BasicIslam;
use App\Models\BuySellCategory;
use App\Models\BuySellPost;
use App\Models\ContactCategory;
use App\Models\Dowa;
use App\Models\EstablishmentBd;
use App\Models\ExcelTutorial;
use App\Models\HistoryBd;
use App\Models\Holiday;
use App\Models\IntroBd;
use App\Models\NewsHeading;
use App\Models\Person;
use App\Models\Sign;
use App\Models\SignCategory;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TourismBd;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate a highly optimized sitemap for Totthobox.';

    public function handle()
    {
        $this->info('🚀 Initializing Sitemap Generation...');

        try {
            $sitemap = Sitemap::create();

            // 0. Homepage
            $this->processHomepage($sitemap);

            // 1. Static / cornerstone pages
            $this->processStaticPages($sitemap);

            // 2. Dynamic models (simple slug)
            $this->processDynamicModels($sitemap);

            // 3. Nested Signs: /signs/{category}/{sign}
            $this->processNestedSigns($sitemap);

            // 4. International countries
            $this->processCountryRoutes($sitemap);

            // 5. News
            // $this->processNewsRoutes($sitemap);

            // 6. Fresh content
            $this->processFreshContentRoutes($sitemap);

            // 7. File converters
            $this->processFileConverterRoutes($sitemap);

            // 8. Automatic discovery (fallback)
            $this->processAutomaticRoutes($sitemap);

            $sitemap->writeToFile(public_path('sitemap.xml'));

            $totalUrls = count($sitemap->getTags());

            $this->info("✅ Totthobox Sitemap updated successfully! Total URLs: {$totalUrls}");
            Log::info("Sitemap generated successfully. Total URLs: {$totalUrls}");

        } catch (\Exception $e) {
            $this->error('❌ Sitemap Generation Failed: '.$e->getMessage());
            Log::error('Sitemap Error: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function processHomepage(Sitemap $sitemap): void
    {
        $sitemap->add(
            Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setLastModificationDate(now())
        );
    }

    private function processStaticPages(Sitemap $sitemap): void
    {
        $highPriorityPages = [
            '/ai/chat',
            // '/live-television', // route currently commented out
            '/bangla/calendar',
            '/bangla/holiday',
            '/bangladesh/introduction',
            '/bangladesh/tourism',
            '/bangladesh/history',
            '/bangladesh/establishment',
            '/bangladesh/public-figure',
            '/international/all-country',
            '/islam/basic',
            '/islam/dowan',
            '/islam/al-quran',
            '/health/calorie-chart',
            '/health/food-nutrients',
            '/health/basic-health',
            '/mcq',
            '/education/child/practice',
            '/buysell/category/all',
            '/excel-expert',
            '/software/all',
            '/converter/number-to-word',
            '/converter/adarshalipi',
            '/tools/image-resizer',
            '/tools/age-calculator',
            '/tools/word-and-character-counter',
            '/tools/zodiac-calculator',
            '/tools/percentage-calculator',
            '/tools/qrcode-generator',
            '/tools/id-card-generator',
        ];

        foreach ($highPriorityPages as $page) {
            $sitemap->add(
                Url::create($page)
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            );
        }

        $lowPriorityPages = [
            '/privacy-policy',
            '/terms-of-service',
            '/contact-us',
            '/about-us',
            '/help',
            '/status',
            '/mcq/test-result',
        ];

        foreach ($lowPriorityPages as $page) {
            $sitemap->add(
                Url::create($page)
                    ->setPriority(0.4)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            );
        }

        $converters = [
            'currency', 'length', 'weight', 'area', 'volume',
            'temperature', 'speed', 'time', 'data', 'energy', 'land',
        ];

        foreach ($converters as $type) {
            $sitemap->add(
                Url::create("/converter/{$type}")
                    ->setPriority(0.7)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        }

        $this->comment('✔ Static and converter pages added.');
    }

    private function processDynamicModels(Sitemap $sitemap): void
    {
        $models = [
            User::class => ['prefix' => '/users',                    'priority' => 0.5, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
            IntroBd::class => ['prefix' => '/bangladesh/introduction',  'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
            TourismBd::class => ['prefix' => '/bangladesh/tourism',       'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
            HistoryBd::class => ['prefix' => '/bangladesh/history',       'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
            EstablishmentBd::class => ['prefix' => '/bangladesh/establishment', 'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
            Person::class => ['prefix' => '/bangladesh/public-figure', 'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
            ContactCategory::class => ['prefix' => '/contact',                  'priority' => 0.6, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
            Subject::class => ['prefix' => '/mcq/subject',              'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
            Test::class => ['prefix' => '/mcq/test',                 'priority' => 0.6, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
            BasicIslam::class => ['prefix' => '/islam/basic',              'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
            Dowa::class => ['prefix' => '/islam/dowan',              'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
            BuySellPost::class => ['prefix' => '/buysell/prodict',          'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_DAILY],
            BuySellCategory::class => ['prefix' => '/buysell/category',         'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
            ExcelTutorial::class => ['prefix' => '/excel-expert',             'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
            SignCategory::class => ['prefix' => '/signs',                    'priority' => 0.6, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
            AppResource::class => ['prefix' => '/software',                 'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
            Holiday::class => ['prefix' => '/bangla/holiday',           'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY], // NEW
        ];

        foreach ($models as $modelClass => $config) {
            if (! class_exists($modelClass)) {
                $this->warn("⚠️ Model not found, skipped: {$modelClass}");

                continue;
            }

            $count = 0;
            $instance = new $modelClass;
            $table = $instance->getTable();

            if (! Schema::hasColumn($table, 'slug')) {
                $this->warn("⚠️ Model {$modelClass} has no slug column – skipped.");

                continue;
            }

            $hasTimestamps = Schema::hasColumn($table, 'updated_at');
            $columns = $hasTimestamps ? ['id', 'slug', 'updated_at'] : ['id', 'slug'];

            $modelClass::select($columns)
                ->whereNotNull('slug')
                ->cursor()
                ->each(function ($item) use ($sitemap, $config, &$count, $hasTimestamps) {
                    $url = Url::create("{$config['prefix']}/{$item->slug}")
                        ->setPriority($config['priority'])
                        ->setChangeFrequency($config['freq']);

                    if ($hasTimestamps && $item->updated_at) {
                        $url->setLastModificationDate($item->updated_at);
                    }

                    $sitemap->add($url);
                    $count++;
                });

            if ($count > 0) {
                $this->comment("✔ Added {$count} links from model: {$modelClass}");
            }
        }
    }

    /**
     * Nested Signs: /signs/{category}/{sign}
     * Assumes Sign model has relation to SignCategory (or category_slug + slug)
     */
    private function processNestedSigns(Sitemap $sitemap): void
    {
        if (! class_exists(Sign::class) || ! class_exists(SignCategory::class)) {
            $this->warn('⚠️ Sign or SignCategory model not found. Skipping nested signs.');

            return;
        }

        $count = 0;

        // Preferred way: using relationship
        if (method_exists(Sign::class, 'category') || method_exists(new Sign, 'category')) {
            Sign::with('category:id,slug')
                ->whereNotNull('slug')
                ->cursor()
                ->each(function ($sign) use ($sitemap, &$count) {
                    if (! $sign->category || ! $sign->category->slug) {
                        return;
                    }

                    $url = Url::create("/signs/{$sign->category->slug}/{$sign->slug}")
                        ->setPriority(0.6)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY);

                    if ($sign->updated_at) {
                        $url->setLastModificationDate($sign->updated_at);
                    }

                    $sitemap->add($url);
                    $count++;
                });
        } else {
            // Fallback: if Sign has category_slug column
            $table = (new Sign)->getTable();
            if (Schema::hasColumn($table, 'category_slug')) {
                Sign::select(['id', 'slug', 'category_slug', 'updated_at'])
                    ->whereNotNull('slug')
                    ->whereNotNull('category_slug')
                    ->cursor()
                    ->each(function ($sign) use ($sitemap, &$count) {
                        $url = Url::create("/signs/{$sign->category_slug}/{$sign->slug}")
                            ->setPriority(0.6)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY);

                        if ($sign->updated_at) {
                            $url->setLastModificationDate($sign->updated_at);
                        }

                        $sitemap->add($url);
                        $count++;
                    });
            } else {
                $this->warn('⚠️ Sign model has no category relation or category_slug column. Nested signs skipped.');

                return;
            }
        }

        if ($count > 0) {
            $this->comment("✔ Added {$count} nested sign links (/signs/{category}/{sign}).");
        }
    }

    private function processCountryRoutes(Sitemap $sitemap): void
    {
        $this->comment('Processing: International Countries...');

        try {
            $filePath = storage_path('app/countries.json');

            if (! file_exists($filePath)) {
                $this->warn('⚠️ Local countries.json not found. Skipping.');

                return;
            }

            $countries = json_decode(file_get_contents($filePath), true);

            if (! is_array($countries)) {
                return;
            }

            $count = 0;
            foreach ($countries as $c) {
                $commonName = $c['name']['common'] ?? null;
                if ($commonName) {
                    $slug = Str::slug($commonName);
                    $sitemap->add(
                        Url::create(route('international.country', ['slug' => $slug]))
                            ->setPriority(0.7)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    );
                    $count++;
                }
            }
            $this->comment("✔ Added {$count} international country routes.");
        } catch (\Exception $e) {
            $this->warn('⚠️ Country sitemap failed: '.$e->getMessage());
        }
    }

    private function processFileConverterRoutes(Sitemap $sitemap): void
    {
        $pages = ['image', 'document', 'media', 'file-data'];

        foreach ($pages as $page) {
            $sitemap->add(
                Url::create("/converter/{$page}")
                    ->setPriority(0.75)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        }

        $this->comment('✔ File-converter tool pages added.');
    }

    private function processFreshContentRoutes(Sitemap $sitemap): void
    {
        // Live television route is currently commented out in routes/web.php
        /*
        if (Route::has('live.television')) {
            $sitemap->add(
                Url::create(route('live.television'))
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
                    ->setLastModificationDate(now())
            );
            $this->comment('✔ Live television page added with high priority.');
        }
        */
    }

    private function processNewsRoutes(Sitemap $sitemap): void
    {
        $this->comment('Processing: News Headlines & Sources...');

        if (Route::has('news.headlines')) {
            $sitemap->add(
                Url::create(route('news.headlines'))
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
                    ->setLastModificationDate(now())
            );
        }

        $count = 0;
        NewsHeading::query()
            ->select('source_key', 'updated_at')
            ->distinct('source_key')
            ->whereNotNull('source_key')
            ->cursor()
            ->each(function ($news) use ($sitemap, &$count) {
                if (Route::has('news.source')) {
                    $url = Url::create(route('news.source', $news->source_key))
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY);

                    if ($news->updated_at) {
                        $url->setLastModificationDate($news->updated_at);
                    }

                    $sitemap->add($url);
                    $count++;
                }
            });

        if ($count > 0) {
            $this->comment("✔ Added {$count} dynamic news source routes.");
        }
    }

    private function processAutomaticRoutes(Sitemap $sitemap): void
    {
        $blacklist = [
            'admin', 'api', 'livewire', 'flux', '_debugbar', 'pulse',
            'auth', 'login', 'register', 'password', 'verify',
            'profile', 'notifications', 'sanctum', 'broadcasting',
            'up', 'clean', 'test-result', 'offline', 'quick-login',
            'messages', 'push-',
        ];

        $exactExclusions = [
            'test',
        ];

        $alreadyAdded = [
            '/',
            'ai/chat',
            'privacy-policy',
            'terms-of-service',
            'contact-us',
            'about-us',
            'help',
            'status',
            'bangla/calendar',
            'bangla/holiday',
            'bangladesh/introduction',
            'bangladesh/tourism',
            'bangladesh/history',
            'bangladesh/establishment',
            'bangladesh/public-figure',
            'international/all-country',
            'islam/basic',
            'islam/dowan',
            'islam/al-quran',
            'health/calorie-chart',
            'health/food-nutrients',
            'health/basic-health',
            'mcq',
            'mcq/test-result',
            'education/child/practice',
            'buysell/category/all',
            'excel-expert',
            'software/all',
            'converter/number-to-word',
            'converter/adarshalipi',
            'converter/image',
            'converter/document',
            'converter/media',
            'converter/file-data',
            'tools/image-resizer',
            'tools/age-calculator',
            'tools/word-and-character-counter',
            'tools/zodiac-calculator',
            'tools/percentage-calculator',
            'tools/qrcode-generator',
            'tools/id-card-generator',
        ];

        $count = 0;
        foreach (Route::getRoutes() as $route) {
            $uri = ltrim($route->uri(), '/');

            if (! in_array('GET', $route->methods()) || str_contains($uri, '{')) {
                continue;
            }

            if (in_array('auth', $route->gatherMiddleware())) {
                continue;
            }

            if (
                $this->shouldSkipRoute($uri, $blacklist) ||
                in_array($uri, $exactExclusions) ||
                in_array($uri, $alreadyAdded) ||
                str_starts_with($uri, 'converter/')
            ) {
                continue;
            }

            $sitemap->add(Url::create('/'.$uri)->setPriority(0.5));
            $count++;
        }

        if ($count > 0) {
            $this->comment("✔ Added {$count} automatic static routes.");
        }
    }

    private function shouldSkipRoute(string $uri, array $blacklist): bool
    {
        foreach ($blacklist as $term) {
            if (str_contains(strtolower($uri), $term)) {
                return true;
            }
        }

        return (bool) preg_match('/\.(js|css|map|png|jpg|jpeg|gif|svg|ico|txt|xml)$/i', $uri);
    }
}
