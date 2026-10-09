<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsHeading;
use App\Models\NewsSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
                Rule::in(NewsSource::query()->where('is_active', true)->pluck('source_key')->all()),
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
        $hours = isset($data['hours']) ? (int) $data['hours'] : null;

        $query = NewsHeading::query()
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
                'story_group',
                'image_url',
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

        // By default show the complete database feed, newest first. A time window
        // is applied only when the visitor explicitly selects one in the UI.
        if ($hours !== null) {
            $query->recent($hours);
        }

        if ($request->boolean('diverse')) {
            $query->diversified(5);
        }

        $page = $query
            ->latestPublished()
            ->orderByDesc('id')
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

        $sourceSlugs = NewsSource::query()
            ->where('is_active', true)
            ->pluck('slug', 'source_key');

        $items = collect($page->items())
            ->map(function (NewsHeading $item) use ($sourceCounts, $sourceSlugs) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'source_url' => $item->source_link,
                    'source_name' => $item->source_name,
                    'source_key' => $item->source_key,
                    'source_slug' => $sourceSlugs->get($item->source_key),
                    'category' => $item->category,
                    'language' => $item->language,
                    'published_at' => $item->published_at?->toIso8601String(),
                    'image_url' => $this->safeImageUrl($item->image_url),
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
        $data = Cache::remember('news_api_sources_v2', now()->addMinute(), function () {
            $sources = NewsSource::query()
                ->where('is_active', true)
                ->withCount('headlines')
                ->orderBy('language')
                ->orderBy('position')
                ->get()
                ->map(fn (NewsSource $source) => [
                    'key' => $source->source_key,
                    'slug' => $source->slug,
                    'name' => $source->name,
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

    public function show(string $slug)
    {
        $item = NewsHeading::query()
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
                    'source_slug' => NewsSource::query()->where('source_key', $news->source_key)->value('slug'),
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
                'slug' => $item->slug,
                'source_url' => $item->source_link,
                'source_name' => $item->source_name,
                'source_key' => $item->source_key,
                'source_slug' => NewsSource::query()->where('source_key', $item->source_key)->value('slug'),
                'category' => $item->category,
                'language' => $item->language,
                'published_at' => $item->published_at?->toIso8601String(),
                'image_url' => $this->safeImageUrl($item->image_url),
                'story_group' => $item->story_group,
                'coverage' => $coverage,
            ],
        ]);
    }

    /**
     * Return only valid absolute HTTP(S) image URLs stored by the scraper.
     * Publishers frequently host images on separate CDN domains, so requiring
     * the article's domain here incorrectly hid otherwise valid thumbnails.
     */
    private function safeImageUrl(?string $url): ?string
    {
        if (! $url || strlen($url) > 2048 || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $image = parse_url($url);
        $scheme = strtolower((string) ($image['scheme'] ?? ''));
        $host = strtolower(rtrim((string) ($image['host'] ?? ''), '.'));

        if (! in_array($scheme, ['http', 'https'], true)
            || $host === ''
            || isset($image['user'])
            || isset($image['pass'])) {
            return null;
        }

        // Do not emit local/private IP addresses as image destinations.
        if (filter_var($host, FILTER_VALIDATE_IP)
            && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        return $url;
    }
}
