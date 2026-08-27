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
use Illuminate\Support\Facades\DB;

class SidebarController extends Controller
{
    /**
     * News Sources (Livewire-এর newsSources এর exact copy)
     */
    public function newsSources()
    {
        $data = Cache::remember('news_sidebar_grouped_v4', now()->addMinutes(30), function () {
            return NewsHeading::query()
                ->select('source_name', 'source_key', 'language', DB::raw('COUNT(*) as total'))
                ->groupBy('source_key', 'source_name', 'language')
                ->get()
                ->sortBy('source_name')
                ->groupBy('language');
        });

        return response()->json($data);
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