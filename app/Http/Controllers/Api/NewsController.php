<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsHeading;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'source' => [
                'nullable',
                'string',
                'max:64',
                Rule::in(collect(config('news_sources', []))->pluck('key')->all()),
            ],
            'language' => ['nullable', Rule::in(['bn', 'en'])],
            'category' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:160'],
            'hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'diverse' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $perPage = (int) ($data['per_page'] ?? 18);

        $query = NewsHeading::query()
            ->select([
                'id',
                'title',
                'summary',
                'slug',
                'source_link',
                'source_name',
                'source_key',
                'category',
                'image_url',
                'language',
                'published_at',
                'story_group',
            ])
            ->where(function ($query) {
                $query->where('source_link', 'like', 'https://%')
                    ->orWhere('source_link', 'like', 'http://%');
            });

        if (! empty($data['source'])) {
            $query->where('source_key', $data['source']);
        }

        if (! empty($data['language'])) {
            $query->where('language', $data['language']);
        }

        if (! empty($data['category'])) {
            $query->where('category', $data['category']);
        }

        if (! empty($data['search'])) {
            $term = trim($data['search']);
            $query->where('title', 'like', '%'.$term.'%');
        }

        if (! empty($data['hours'])) {
            $query->recent((int) $data['hours']);
        }

        if ($request->boolean('diverse')) {
            $query->diversified(5);
        }

        $page = $query
            ->latestPublished()
            ->paginate($perPage)
            ->withQueryString();

        $storyGroups = collect($page->items())
            ->pluck('story_group')
            ->filter()
            ->unique()
            ->values();

        $sourceCounts = $storyGroups->isEmpty()
            ? collect()
            : NewsHeading::query()
                ->whereIn('story_group', $storyGroups)
                ->get(['story_group', 'source_key'])
                ->groupBy('story_group')
                ->map(fn ($items) => $items->pluck('source_key')->unique()->count());

        $items = collect($page->items())
            ->map(function (NewsHeading $item) use ($sourceCounts) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'summary' => $item->summary,
                    'slug' => $item->slug,
                    'source_url' => $item->source_link,
                    'source_name' => $item->source_name,
                    'source_key' => $item->source_key,
                    'category' => $item->category,
                    'image_url' => $item->image_url,
                    'language' => $item->language,
                    'published_at' => $item->published_at?->toIso8601String(),
                    'story_group' => $item->story_group,
                    'coverage_count' => $item->story_group
                        ? ($sourceCounts->get($item->story_group) ?? 1)
                        : 1,
                ];
            })
            ->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'from' => $page->firstItem(),
                'to' => $page->lastItem(),
                'has_more' => $page->hasMorePages(),
            ],
        ]);
    }

    public function sources()
    {
        $configured = collect(config('news_sources', []))
            ->sortBy(fn (array $source) => sprintf('%s-%03d', $source['language'], $source['order']))
            ->values();

        $counts = NewsHeading::query()
            ->selectRaw('source_key, COUNT(*) as total')
            ->groupBy('source_key')
            ->pluck('total', 'source_key');

        $result = $configured->map(function (array $source) use ($counts) {
            return [
                'key' => $source['key'],
                'name' => $source['name'],
                'language' => $source['language'],
                'home_url' => $source['home_url'],
                'total' => (int) ($counts[$source['key']] ?? 0),
            ];
        });

        return response()->json($result->groupBy('language'));
    }

    public function show(string $slug)
    {
        $item = NewsHeading::query()
            ->select([
                'id',
                'title',
                'summary',
                'slug',
                'source_link',
                'source_name',
                'source_key',
                'category',
                'image_url',
                'language',
                'published_at',
                'story_group',
            ])
            ->where('slug', $slug)
            ->first();

        if (! $item) {
            return response()->json(['message' => 'News item not found.'], 404);
        }

        $coverage = collect();

        if ($item->story_group) {
            $coverage = NewsHeading::query()
                ->select([
                    'id',
                    'title',
                    'slug',
                    'source_link',
                    'source_name',
                    'source_key',
                    'category',
                    'language',
                    'published_at',
                ])
                ->where('story_group', $item->story_group)
                ->where('id', '!=', $item->id)
                ->latestPublished()
                ->limit(8)
                ->get()
                ->map(fn (NewsHeading $news) => [
                    'id' => $news->id,
                    'title' => $news->title,
                    'slug' => $news->slug,
                    'source_url' => $news->source_link,
                    'source_name' => $news->source_name,
                    'source_key' => $news->source_key,
                    'category' => $news->category,
                    'language' => $news->language,
                    'published_at' => $news->published_at?->toIso8601String(),
                ])
                ->values();
        }

        return response()->json([
            'data' => [
                'id' => $item->id,
                'title' => $item->title,
                'summary' => $item->summary,
                'slug' => $item->slug,
                'source_url' => $item->source_link,
                'source_name' => $item->source_name,
                'source_key' => $item->source_key,
                'category' => $item->category,
                'image_url' => $item->image_url,
                'language' => $item->language,
                'published_at' => $item->published_at?->toIso8601String(),
                'story_group' => $item->story_group,
                'coverage' => $coverage,
            ],
        ]);
    }
}
