<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use App\\Models\\NewsHeading;
use App\\Models\\BuySellCategory;
use App\\Models\\ContactCategory;
use App\\Models\\SignCategory;
use App\\Models\\ExcelTutorial;
use App\\Models\\AppResource;
use Illuminate\\Support\\Facades\\Cache;

class SidebarController extends Controller
{
    /**
     * Configured newspaper sources for the dynamic sidebar.
     */
    public function newsSources()
    {
        $configured = collect(config('news_sources', []))
            ->sortBy(fn (array $source) => sprintf('%s-%03d', $source['language'], $source['order']))
            ->values();

        $counts = Cache::remember('news_sidebar_counts_v1', now()->addMinutes(5), function () {
            return NewsHeading::query()
                ->selectRaw('source_key, COUNT(*) as total')
                ->groupBy('source_key')
                ->pluck('total', 'source_key');
        });

        $data = $configured
            ->map(function (array $source) use ($counts) {
                return [
                    'source_name' => $source['name'],
                    'source_key' => $source['key'],
                    'slug' => str_replace('_', '-', $source['key']),
                    'language' => $source['language'],
                    'home_url' => $source['home_url'],
                    'total' => (int) ($counts[$source['key']] ?? 0),
                ];
            })
            ->groupBy('language');

        return response()->json($data)->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
    }

    public function buysellCategories()
    {
        return response()->json(BuySellCategory::all());
    }

    public function contactCategories()
    {
        return response()->json(ContactCategory::all());
    }

    public function signCategories()
    {
        return response()->json(SignCategory::all());
    }

    public function excelChapters()
    {
        $chapters = ExcelTutorial::query()
            ->where('is_published', true)
            ->orderBy('position', 'asc')
            ->get()
            ->groupBy('chapter_name');

        return response()->json($chapters);
    }

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
