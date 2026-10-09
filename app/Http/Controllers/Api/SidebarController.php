<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsHeading;
use App\Models\NewsSource;
use App\Models\BuySellCategory;
use App\Models\ContactCategory;
use App\Models\SignCategory;
use App\Models\ExcelTutorial;
use App\Models\AppResource;
use Illuminate\Support\Facades\Cache;

class SidebarController extends Controller
{
    /**
     * News Sources (Livewire-এর newsSources এর exact copy)
     */
    public function newsSources()
    {
        $data = Cache::remember('news_sidebar_sources_v2', now()->addMinute(), function () {
            $sources = NewsSource::query()
                ->where('is_active', true)
                ->orderBy('language')
                ->orderBy('position')
                ->get()
                ->map(fn (NewsSource $source) => [
                    'source_name' => $source->name,
                    'source_key' => $source->source_key,
                    'slug' => $source->slug,
                    'language' => $source->language,
                    'home_url' => $source->home_url,
                    'total' => $this->countHeadlinesForSource($source),
                ]);

            return [
                'bn' => $sources->where('language', 'bn')->values(),
                'en' => $sources->where('language', 'en')->values(),
            ];
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
    }

    /**
     * BuySell Categories
     */
    public function buysellCategories()
    {
        $categories = BuySellCategory::all();
        return response()->json($categories);
    }

    /**
     * Contact Categories
     */
    public function contactCategories()
    {
        $categories = ContactCategory::all();
        return response()->json($categories);
    }

    /**
     * Sign Categories
     */
    public function signCategories()
    {
        $categories = SignCategory::all();
        return response()->json($categories);
    }

    /**
     * Excel Chapters (grouped by chapter_name)
     */
    public function excelChapters()
    {
        $chapters = ExcelTutorial::query()
            ->where('is_published', true)
            ->orderBy('position', 'asc')
            ->get()
            ->groupBy('chapter_name');

        return response()->json($chapters);
    }

    /**
     * Software Platforms
     */
    public function softwarePlatforms()
    {
        $platforms = AppResource::query()
            ->select('platform')
            ->distinct()
            ->pluck('platform')
            ->filter()
            ->values();

        return response()->json($platforms);
    }
    /**
     * Count saved headlines against the DB source key, canonical name, and
     * publisher host. The URL fallback keeps legacy rows visible even when an
     * older scraper saved a stale source_key/source_name.
     */
    private function countHeadlinesForSource(NewsSource $source): int
    {
        $host = $this->normalizeNewsHost((string) parse_url($source->home_url, PHP_URL_HOST));

        return NewsHeading::query()
            ->where(function ($match) use ($source, $host) {
                $match->where('source_key', $source->source_key)
                    ->orWhere('source_name', $source->name);

                if ($host !== '') {
                    $match->orWhere('source_link', 'like', '%'.$host.'/%');
                }
            })
            ->count();
    }

    private function normalizeNewsHost(string $host): string
    {
        $host = strtolower(rtrim($host, '.'));

        return preg_replace('/^www\\./i', '', $host) ?? $host;
    }
}