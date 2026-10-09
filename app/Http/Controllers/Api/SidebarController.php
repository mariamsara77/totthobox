<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsHeading;
use App\Models\BuySellCategory;
use App\Models\ContactCategory;
use App\Models\SignCategory;
use App\Models\ExcelTutorial;
use App\Models\AppResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SidebarController extends Controller
{
    /**
     * Build the dynamic news menu from config/news_sources.php and the
     * existing news_headings table. No separate source catalogue is required.
     */
    public function newsSources()
    {
        $data = Cache::remember('news_sidebar_sources_v2', now()->addMinute(), function () {
            $sources = collect(config('news_sources', []))
                ->filter(fn ($source) => is_array($source)
                    && ! empty($source['key'])
                    && ! empty($source['name'])
                    && ! empty($source['language'])
                    && ! empty($source['home_url']))
                ->sortBy(fn (array $source) => sprintf('%s-%03d', $source['language'], (int) ($source['order'] ?? 0)))
                ->values()
                ->map(fn (array $source) => [
                    'source_name' => (string) $source['name'],
                    'source_key' => (string) $source['key'],
                    'slug' => (string) Str::slug((string) ($source['slug'] ?? $source['name'])),
                    'language' => (string) $source['language'],
                    'home_url' => (string) $source['home_url'],
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
     * Count headlines from the original news_headings table. Match legacy
     * source keys/names by the publisher host so older saved rows still count.
     */
    private function countHeadlinesForSource(array $source): int
    {
        $host = strtolower(rtrim((string) parse_url((string) ($source['home_url'] ?? ''), PHP_URL_HOST), '.'));
        $host = preg_replace('/^www\\./i', '', $host) ?? $host;

        return NewsHeading::query()
            ->where(function ($match) use ($source, $host) {
                $match->where('source_key', $source['key'])
                    ->orWhere('source_name', $source['name']);

                if ($host !== '') {
                    $match->orWhere('source_link', 'like', '%'.$host.'/%');
                }
            })
            ->count();
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
}