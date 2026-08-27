<?php

namespace App\Console\Commands;

use App\Models\AppResource;
use App\Models\BuySellCategory;
use App\Models\BuySellPost;
use App\Models\ContactCategory;
use App\Models\Dowa;
use App\Models\EstablishmentBd;
use App\Models\ExcelTutorial;
use App\Models\HistoryBd;
use App\Models\NewsHeading;
use App\Models\Person;
use App\Models\SignCategory;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TourismBd;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
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

            // ১. স্ট্যাটিক পেজ প্রসেসিং
            $this->processStaticPages($sitemap);

            // ২. ডাইনামিক মডেল প্রসেসিং
            $this->processDynamicModels($sitemap);

            // ৩. কান্ট্রি রুট প্রসেসিং (ফিক্সড)
            $this->processCountryRoutes($sitemap);

            // ৪. নিউজ রুট প্রসেসিং
            $this->processNewsRoutes($sitemap);

            // ৫. অটোমেটিক রুট ডিসকভারি (ফিক্সড)
            $this->processAutomaticRoutes($sitemap);

            // ফাইল রাইটিং
            $sitemap->writeToFile(public_path('sitemap.xml'));

            // মোট ইউআরএল কাউন্ট বের করা
            $totalUrls = count($sitemap->getTags());

            $this->info("✅ Totthobox Sitemap updated successfully! Total URLs: {$totalUrls}");
            Log::info('Sitemap generated successfully.');

        } catch (\Exception $e) {
            $this->error('❌ Sitemap Generation Failed: '.$e->getMessage());
            Log::error('Sitemap Error: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function processStaticPages(Sitemap $sitemap): void
    {
        $staticPages = [
            '/',
            '/ai/chat',
            '/privacy-policy',
            '/terms-of-service',
            '/contact-us',
            '/help',
            '/status',
            '/bangla/calendar',
            '/bangla/holiday',
            '/offline',
            '/converter/number-to-word',
            '/converter/adarshalipi',
            '/bangladesh/introduction',
            '/bangladesh/tourism',
            '/bangladesh/history',
            '/bangladesh/establishment',
            '/bangladesh/public-figure',
            '/international/all-country',
            '/islam/basicislam',
            '/islam/dowan',
            '/islam/al-quran',
            '/health/calorie-chart',
            '/health/food-nutrients',
            '/health/basic-health',
            '/mcq',
            '/mcq/test-result',
            '/education/child/practice',
            '/buysell/category/all',
            '/excel-expert',
            '/software/all',
        ];

        foreach ($staticPages as $page) {
            $sitemap->add(
                Url::create($page)
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            );
        }

        $converters = ['currency', 'length', 'weight', 'area', 'volume', 'temperature', 'speed', 'time', 'data', 'energy', 'land'];
        foreach ($converters as $type) {
            $sitemap->add(Url::create("/converter/{$type}")->setPriority(0.7));
        }
        $this->comment('✔ Static and converter pages added.');
    }

    private function processDynamicModels(Sitemap $sitemap): void
    {
        $models = [
            User::class => '/users',
            TourismBd::class => '/bangladesh/tourism',
            HistoryBd::class => '/bangladesh/history',
            EstablishmentBd::class => '/bangladesh/establishment',
            ContactCategory::class => '/contact',
            Person::class => '/bangladesh/public-figure',
            Subject::class => '/mcq/subject',
            Dowa::class => '/islam/dowan',
            Test::class => '/mcq/test',
            BuySellPost::class => '/buysell/product', // Fixed typo 'prodict' -> 'product' if applicable
            BuySellCategory::class => '/buysell/category',
            ExcelTutorial::class => '/excel-expert',
            SignCategory::class => '/signs',
            AppResource::class => '/software',
        ];

        foreach ($models as $modelClass => $prefix) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $count = 0;
            $modelClass::select(['id', 'slug'])->whereNotNull('slug')->cursor()
                ->each(function ($item) use ($sitemap, $prefix, &$count) {
                    $sitemap->add(Url::create("{$prefix}/{$item->slug}")->setPriority(0.6));
                    $count++;
                });

            if ($count > 0) {
                $this->comment("✔ Added {$count} links from model: {$modelClass}");
            }
        }
    }

    private function processCountryRoutes(Sitemap $sitemap): void
    {
        $this->comment('Processing: International Countries (Local)...');

        try {
            $filePath = storage_path('app/countries.json');

            if (! file_exists($filePath)) {
                $this->warn('⚠️ Local countries.json file not found. Skipping.');

                return;
            }

            $jsonContent = file_get_contents($filePath);
            $countries = json_decode($jsonContent, true);

            if (is_array($countries)) {
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
                $this->comment("✔ Successfully added {$count} international country routes from local storage.");
            }
        } catch (\Exception $e) {
            $this->warn('⚠️ Country Sitemap Generation Failed: '.$e->getMessage());
        }
    }

    private function processAutomaticRoutes(Sitemap $sitemap): void
    {
        $blacklist = [
            'admin',
            'api',
            'livewire',
            'flux',
            '_debugbar',
            'pulse',
            'auth',
            'login',
            'register',
            'password',
            'verify',
            'profile',
            'notifications',
            'sanctum',
            'broadcasting',
            'up',
            'clean',
            'test-result',
            'offline',
        ];

        $alreadyAdded = [
            '/',
            'ai/chat',
            'privacy-policy',
            'terms-of-service',
            'contact-us',
            'bangla/calendar',
            'bangla/holiday',
            'offline',
            'converter/number-to-word',
            'converter/adarshalipi',
            'bangladesh/introduction',
            'bangladesh/tourism',
            'bangladesh/history',
            'bangladesh/establishment',
            'bangladesh/public-figure',
            'international/all-country',
            'islam/basicislam',
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
        ];

        $count = 0;
        foreach (Route::getRoutes() as $route) {
            $uri = ltrim($route->uri(), '/');

            // GET রুট চেক (Volt/Livewire এর জন্য ইন-অ্যারে ম্যাচিং করা হলো)
            if (in_array('GET', $route->methods()) && ! str_contains($uri, '{')) {

                if (in_array('auth', $route->gatherMiddleware())) {
                    continue;
                }

                if (
                    $this->shouldSkipRoute($uri, $blacklist) ||
                    in_array($uri, $alreadyAdded) ||
                    str_starts_with($uri, 'converter/')
                    // ফিক্সড: 'international/country' এর স্কিপ রুলটি এখান থেকে বাদ দেওয়া হয়েছে
                ) {
                    continue;
                }

                $sitemap->add(Url::create('/'.$uri)->setPriority(0.5));
                $count++;
            }
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

        return preg_match('/\.(js|css|map|png|jpg|jpeg|gif|svg|ico|txt|xml)$/i', $uri);
    }

    private function processNewsRoutes(Sitemap $sitemap): void
    {
        $this->comment('Processing: News Headlines & Sources...');

        if (Route::has('news.headlines')) {
            $sitemap->add(
                Url::create(route('news.headlines'))
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
            );
        }

        $count = 0;
        NewsHeading::query()
            ->select('source_key')
            ->distinct()
            ->whereNotNull('source_key')
            ->cursor()
            ->each(function ($news) use ($sitemap, &$count) {
                if (Route::has('news.source')) {
                    $sitemap->add(
                        Url::create(route('news.source', $news->source_key))
                            ->setPriority(0.7)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
                    );
                    $count++;
                }
            });

        if ($count > 0) {
            $this->comment("✔ Added {$count} dynamic news source routes.");
        }
    }
}
