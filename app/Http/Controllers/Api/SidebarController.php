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
                ->withCount('headlines')
                ->orderBy('language')
                ->orderBy('position')
                ->get()
                ->map(fn (NewsSource $source) => [
                    'source_name' => $source->name,
                    'source_key' => $source->source_key,
                    'slug' => $source->slug,
                    'language' => $source->language,
                    'home_url' => $source->home_url,
                    'total' => (int) $source->headlines_count,
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
}