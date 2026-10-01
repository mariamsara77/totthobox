<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Search\GlobalSearchService;
use App\Search\SearchRegistry;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request, GlobalSearchService $searchService)
    {
        $query = trim($request->input('q', ''));
        $limit = (int) $request->input('limit', 10);

        // Limit কে নিরাপদ রাখো
        $limit = max(5, min($limit, 50));

        $result = $searchService->search($query, $limit + 1);

        $hasMore = $result->items->count() > $limit;
        $items = $result->items->take($limit);

        return response()->json([
    'items' => $items->map(function ($item) {
        return [
            '_search_title'    => $item->_search_title ?? 'No Title',
            '_search_subtitle' => $item->_search_subtitle ?? null,
            '_search_label'    => $item->_search_label ?? '',
            '_search_url'      => $item->_search_url ?? '#',
            '_search_image'    => $item->_search_image ?? null,
            '_search_icon'     => $item->_search_icon ?? null,
            '_search_color'    => $item->_search_color ?? 'zinc',
            // নতুন ফিল্ড
            '_search_type'     => $item->_search_type ?? '',
            '_search_slug'     => $item->_search_slug ?? '',
            '_search_extra'    => $item->_search_extra ?? [],
        ];
    })->values(),
    'scope'   => $result->scope,
    'hasMore' => $hasMore,
    'total'   => $items->count(),
]);
    }

    /**
     * Prefixes + Hints দেওয়ার জন্য (একবার লোড করে রাখতে পারো)
     */
    public function meta()
    {
        return response()->json([
            'prefixes' => SearchRegistry::keys(),
            'hints'    => SearchRegistry::prefixHints(),
        ]);
    }
}