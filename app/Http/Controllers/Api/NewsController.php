<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsHeading;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
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
                Rule::in(collect(config('news_sources', []))->pluck('key')->filter()->values()->all()),
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

        // story_group and local_image_path were added after the original news
        // table. Keep the public feed available during staggered deployments;
        // enhanced coverage/local thumbnails remain enabled when columns exist.
        $columns = [
            'id',
            'title',
            'slug',
            'source_link',
            'source_name',
            'source_key',
            'category',
            'language',
            'published_at',
            'image_url',
            'created_at',
        ];

        if ($this->hasOptionalNewsColumn('story_group')) {
            $columns[] = 'story_group';
        }

        if ($this->hasOptionalNewsColumn('local_image_path')) {
            $columns[] = 'local_image_path';
        }

        $query = NewsHeading::query()
            ->select($columns)
            ->where(function ($query) {
                $query->where('source_link', 'like', 'https://%')
                    ->orWhere('source_link', 'like', 'http://%');
            });

        if (! empty($data['source'])) {
            $sourceRecord = collect(config('news_sources', []))
                ->first(fn (array $source) => ($source['key'] ?? null) === $data['source']);

            if ($sourceRecord) {
                $this->applySourceFilter($query, $sourceRecord);
            }
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

        $sourceCatalog = collect(config('news_sources', []))
            ->sortBy(fn (array $source) => sprintf('%s-%03d', $source['language'] ?? '', (int) ($source['order'] ?? 0)))
            ->keyBy('key');

        $items = collect($page->items())
            ->map(function (NewsHeading $item) use ($sourceCounts, $sourceCatalog) {
                $source = $sourceCatalog->get($item->source_key)
                    ?? $this->sourceForUrl($item->source_link, $sourceCatalog->values());

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'source_url' => $item->source_link,
                    'source_name' => $source['name'] ?? $item->source_name,
                    'source_key' => $item->source_key,
                    'source_slug' => $this->sourceSlugForKey((string) $item->source_key, $source),
                    'category' => $item->category,
                    'language' => $source['language'] ?? $item->language,
                    'published_at' => $item->published_at?->toIso8601String() ?? $item->created_at?->toIso8601String(),
                    'image_url' => $this->resolveImageUrl($item),
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
            $sources = collect(config('news_sources', []))
                ->filter(fn ($source) => is_array($source)
                    && ! empty($source['key'])
                    && ! empty($source['name'])
                    && ! empty($source['language'])
                    && ! empty($source['home_url']))
                ->sortBy(fn (array $source) => sprintf('%s-%03d', $source['language'], (int) ($source['order'] ?? 0)))
                ->values()
                ->map(fn (array $source) => [
                    'key' => (string) $source['key'],
                    'slug' => $this->sourceSlugForKey((string) $source['key'], $source),
                    'name' => (string) $source['name'],
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
     * Match legacy rows in the original news_headings table by source key,
     * stored publisher name, or publisher URL. No separate source table needed.
     */
    private function applySourceFilter(Builder $query, array $source): Builder
    {
        $host = $this->normalizeHost((string) parse_url((string) ($source['home_url'] ?? ''), PHP_URL_HOST));

        return $query->where(function (Builder $match) use ($source, $host) {
            $match->where('source_key', $source['key'])
                ->orWhere('source_name', $source['name']);

            if ($host !== '') {
                $match->orWhere('source_link', 'like', '%'.$host.'/%');
            }
        });
    }

    private function countHeadlinesForSource(array $source): int
    {
        return $this->applySourceFilter(NewsHeading::query(), $source)->count();
    }

    private function sourceForUrl(?string $url, $sources): ?array
    {
        $host = $this->normalizeHost((string) parse_url((string) $url, PHP_URL_HOST));

        if ($host === '') {
            return null;
        }

        return $sources->first(function (array $source) use ($host) {
            $sourceHost = $this->normalizeHost((string) parse_url((string) ($source['home_url'] ?? ''), PHP_URL_HOST));

            return $sourceHost !== ''
                && ($host === $sourceHost || str_ends_with($host, '.'.$sourceHost));
        });
    }

    private function sourceSlugForKey(string $sourceKey, ?array $source = null): string
    {
        if ($source === null) {
            $source = collect(config('news_sources', []))
                ->first(fn (array $configured) => ($configured['key'] ?? null) === $sourceKey);
        }

        $slug = $source['slug'] ?? $source['name'] ?? $sourceKey;

        return (string) \Illuminate\Support\Str::slug((string) $slug);
    }

    private function normalizeHost(string $host): string
    {
        $host = strtolower(rtrim($host, '.'));

        return preg_replace('/^www\\./i', '', $host) ?? $host;
    }

    public function show(string $slug)
    {
        $columns = [
            'id',
            'title',
            'slug',
            'source_link',
            'source_name',
            'source_key',
            'category',
            'language',
            'published_at',
            'image_url',
            'created_at',
        ];

        if ($this->hasOptionalNewsColumn('story_group')) {
            $columns[] = 'story_group';
        }

        if ($this->hasOptionalNewsColumn('local_image_path')) {
            $columns[] = 'local_image_path';
        }

        $item = NewsHeading::query()
            ->select($columns)
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
                    'created_at',
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
                    'source_slug' => $this->sourceSlugForKey((string) $news->source_key),
                    'category' => $news->category,
                    'language' => $news->language,
                    'published_at' => $news->published_at?->toIso8601String() ?? $news->created_at?->toIso8601String(),
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
                'source_slug' => $this->sourceSlugForKey((string) $item->source_key),
                'category' => $item->category,
                'language' => $item->language,
                'published_at' => $item->published_at?->toIso8601String(),
                'image_url' => $this->resolveImageUrl($item),
                'story_group' => $item->story_group,
                'coverage' => $coverage,
            ],
        ]);
    }

    /**
     * Enhanced news columns are optional during a rolling deployment. Cache
     * their availability briefly so every request does not query table metadata.
     */
    private function hasOptionalNewsColumn(string $column): bool
    {
        if (! in_array($column, ['story_group', 'local_image_path'], true)) {
            return false;
        }

        return Cache::remember(
            'news_headings_has_column_'.$column,
            now()->addMinutes(5),
            fn () => Schema::hasColumn('news_headings', $column)
        );
    }

    /**
     * Prefer a locally stored thumbnail when present. This avoids publisher CDN
     * hotlink restrictions and serves existing local images from our own domain.
     * Fall back to the publisher's valid remote image URL when no local copy exists.
     */
    private function resolveImageUrl(NewsHeading $item): ?string
    {
        $localPath = trim((string) $item->local_image_path);

        if ($localPath !== '') {
            try {
                $disk = Storage::disk('public');

                if ($disk->exists($localPath)) {
                    $url = $disk->url($localPath);

                    // Public disk URLs can be relative in custom filesystems. The
                    // frontend lives on a different origin, so make them absolute.
                    if (str_starts_with($url, '/')) {
                        $url = rtrim((string) config('app.url'), '/').$url;
                    }

                    return $url;
                }
            } catch (\Throwable) {
                // A storage-driver failure should not prevent using a safe remote image.
            }
        }

        return $this->safeImageUrl($item->image_url);
    }

    /**
     * Return only valid absolute HTTP(S) image URLs stored by the scraper.
     * Publishers frequently host images on separate CDN domains, so requiring
     * the article's domain here incorrectly hid otherwise valid thumbnails.
     */
    private function safeImageUrl(?string $url): ?string
    {
        if (! $url || strlen($url) > 2048) {
            return null;
        }

        $url = trim($url);

        // Normalize legacy protocol-relative and backend-relative image paths.
        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        } elseif (str_starts_with($url, '/')) {
            $url = rtrim((string) config('app.url'), '/').$url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
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
