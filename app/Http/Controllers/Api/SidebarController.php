<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppResource;
use App\Models\BuySellCategory;
use App\Models\ContactCategory;
use App\Models\ExcelTutorial;
use App\Models\NewsHeading;
use App\Models\SignCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SidebarController extends Controller
{
    /**
     * GET /api/sidebar/news-sources
     * Redis cache — minimum 1 hour
     */
    public function newsSources(): JsonResponse
    {
        $data = Cache::remember('sidebar_news_sources_v5', now()->addHour(), function () {
            $counts = Cache::remember('news_source_counts_v1', now()->addHours(6), function () {
                return NewsHeading::query()
                    ->selectRaw('source_key, COUNT(*) as total')
                    ->groupBy('source_key')
                    ->pluck('total', 'source_key')
                    ->toArray();
            });

            $sources = collect(config('news_sources', []))
                ->filter(fn($s) => is_array($s)
                    && ! empty($s['key'])
                    && ! empty($s['name'])
                    && ! empty($s['language'])
                    && ! empty($s['home_url']))
                ->sortBy(fn(array $s) => sprintf('%s-%03d', $s['language'], (int) ($s['order'] ?? 0)))
                ->values()
                ->map(fn(array $s) => [
                    'source_name' => (string) $s['name'],
                    'source_key'  => (string) $s['key'],
                    'slug'        => (string) Str::slug((string) ($s['slug'] ?? $s['name'])),
                    'language'    => (string) $s['language'],
                    'home_url'    => (string) $s['home_url'],
                    'total'       => (int) ($counts[$s['key']] ?? 0),
                ]);

            return [
                'bn' => $sources->where('language', 'bn')->values(),
                'en' => $sources->where('language', 'en')->values(),
            ];
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=1800');
    }

    public function buysellCategories(): JsonResponse
    {
        $data = Cache::remember('sidebar_buysell_categories', now()->addHour(), function () {
            return BuySellCategory::query()->orderBy('name')->get();
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function contactCategories(): JsonResponse
    {
        $data = Cache::remember('sidebar_contact_categories', now()->addHour(), function () {
            return ContactCategory::query()->orderBy('name')->get();
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function signCategories(): JsonResponse
    {
        $data = Cache::remember('sidebar_sign_categories', now()->addHour(), function () {
            return SignCategory::query()->orderBy('name')->get();
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function excelChapters(): JsonResponse
    {
        $data = Cache::remember('sidebar_excel_chapters', now()->addHour(), function () {
            return ExcelTutorial::query()
                ->where('is_published', true)
                ->orderBy('position')
                ->get()
                ->groupBy('chapter_name');
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function softwarePlatforms(): JsonResponse
    {
        $data = Cache::remember('sidebar_software_platforms', now()->addHour(), function () {
            return AppResource::query()
                ->select('platform')
                ->distinct()
                ->pluck('platform')
                ->filter()
                ->values();
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=3600');
    }
}