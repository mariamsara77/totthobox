<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsHeading;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class NewsController extends Controller
{
    /**
     * GET /api/news
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'source'   => [
                'nullable',
                'string',
                'max:64',
                Rule::in(
                    collect(config('news_sources', []))
                        ->pluck('key')
                        ->filter()
                        ->values()
                        ->all()
                ),
            ],
            'language' => ['nullable', Rule::in(['bn', 'en'])],
            'category' => ['nullable', 'string', 'max:50'],
            'search'   => ['nullable', 'string', 'max:160'],
            'hours'    => ['nullable', 'integer', 'min:1', 'max:168'],
            'date'     => ['nullable', 'date_format:Y-m-d'],          // single day
            'from'     => ['nullable', 'date_format:Y-m-d'],          // range start
            'to'       => ['nullable', 'date_format:Y-m-d'],          // range end
            'diverse'  => ['nullable', 'boolean'],
            'page'     => ['nullable', 'integer', 'min:1', 'max:100000'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $cacheKey = 'news_index_v2:' . md5(json_encode([
            'source'   => $data['source']   ?? null,
            'language' => $data['language'] ?? null,
            'category' => $data['category'] ?? null,
            'search'   => $data['search']   ?? null,
            'hours'    => $data['hours']    ?? null,
            'date'     => $data['date']     ?? null,
            'from'     => $data['from']     ?? null,
            'to'       => $data['to']       ?? null,
            'diverse'  => $request->boolean('diverse'),
            'page'     => (int) ($data['page'] ?? 1),
            'per_page' => (int) ($data['per_page'] ?? 18),
        ]));

        $payload = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($request, $data) {
            return $this->buildNewsIndexPayload($request, $data);
        });

        return response()->json($payload)
            ->header('Cache-Control', 'public, max-age=900, stale-while-revalidate=300');
    }

    /**
     * GET /api/news/sources
     */
    public function sources(): JsonResponse
    {
        $data = Cache::remember('news_api_sources_v5', now()->addMinutes(30), function () {
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
                    'key'      => (string) $s['key'],
                    'slug'     => $this->sourceSlugForKey((string) $s['key'], $s),
                    'name'     => (string) $s['name'],
                    'language' => (string) $s['language'],
                    'home_url' => (string) $s['home_url'],
                    'total'    => (int) ($counts[$s['key']] ?? 0),
                ]);

            return [
                'bn' => $sources->where('language', 'bn')->values(),
                'en' => $sources->where('language', 'en')->values(),
            ];
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=600');
    }

    /**
     * GET /api/news/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $cacheKey = 'news_show_v1:' . md5($slug);

        $payload = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($slug) {
            $item = NewsHeading::query()
                ->select($this->selectableColumns())
                ->where('slug', $slug)
                ->first();

            if (! $item) {
                return null;
            }

            $storyGroup = $item->getRawOriginal('story_group');
            $coverage   = collect();

            if ($storyGroup) {
                $coverage = NewsHeading::query()
                    ->select($this->selectableColumns())
                    ->where('story_group', $storyGroup)
                    ->where('id', '!=', $item->id)
                    ->latestPublished()
                    ->limit(8)
                    ->get()
                    ->map(fn(NewsHeading $news) => [
                        'id'           => $news->id,
                        'title'        => $news->title,
                        'slug'         => $news->slug,
                        'source_url'   => $news->source_link,
                        'source_name'  => $news->source_name,
                        'source_key'   => $news->source_key,
                        'source_slug'  => $this->sourceSlugForKey((string) $news->source_key),
                        'category'     => $news->category,
                        'language'     => $news->language,
                        'published_at' => $news->published_at?->toIso8601String()
                            ?? $news->created_at?->toIso8601String(),
                        'image_url'    => $this->resolveImageUrl($news),
                    ])
                    ->values();
            }

            return [
                'data' => [
                    'id'           => $item->id,
                    'title'        => $item->title,
                    'slug'         => $item->slug,
                    'source_url'   => $item->source_link,
                    'source_name'  => $item->source_name,
                    'source_key'   => $item->source_key,
                    'source_slug'  => $this->sourceSlugForKey((string) $item->source_key),
                    'category'     => $item->category,
                    'language'     => $item->language,
                    'published_at' => $item->published_at?->toIso8601String()
                        ?? $item->created_at?->toIso8601String(),
                    'image_url'    => $this->resolveImageUrl($item),
                    'story_group'  => $storyGroup,
                    'coverage'     => $coverage,
                ],
            ];
        });

        if ($payload === null) {
            return response()->json(['message' => 'News item not found.'], 404);
        }

        return response()->json($payload)
            ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=120');
    }

    // ─── Core index builder ───────────────────────────────────────────────────

    private function buildNewsIndexPayload(Request $request, array $data): array
    {
        $perPage = (int) ($data['per_page'] ?? 18);

        $query = NewsHeading::query()
            ->select($this->selectableColumns())
            ->where(function (Builder $q) {
                $q->where('source_link', 'like', 'https://%')
                    ->orWhere('source_link', 'like', 'http://%');
            });

        // Source filter
        if (! empty($data['source'])) {
            $sourceRecord = collect(config('news_sources', []))
                ->first(fn(array $s) => ($s['key'] ?? null) === $data['source']);

            if ($sourceRecord) {
                $this->applySourceFilter($query, $sourceRecord);
            }
        }

        // Language
        if (! empty($data['language'])) {
            $query->inLanguage($data['language']);
        }

        // Category
        if (! empty($data['category'])) {
            $query->whereRaw('LOWER(category) = ?', [mb_strtolower(trim($data['category']))]);
        }

        // Search
        if (! empty($data['search'])) {
            $term = trim($data['search']);
            $query->where('title', 'like', '%' . $term . '%');
        }

        // Hours filter (relative)
        if (! empty($data['hours'])) {
            $query->recent((int) $data['hours']);
        }

        // Exact date or date range (most useful for "that day I liked")
        if (! empty($data['date'])) {
            $day = Carbon::createFromFormat('Y-m-d', $data['date'])->startOfDay();
            $query->whereBetween('published_at', [
                $day->copy()->startOfDay(),
                $day->copy()->endOfDay(),
            ]);
        } elseif (! empty($data['from']) || ! empty($data['to'])) {
            $from = ! empty($data['from'])
                ? Carbon::createFromFormat('Y-m-d', $data['from'])->startOfDay()
                : null;
            $to = ! empty($data['to'])
                ? Carbon::createFromFormat('Y-m-d', $data['to'])->endOfDay()
                : null;

            if ($from && $to) {
                $query->whereBetween('published_at', [$from, $to]);
            } elseif ($from) {
                $query->where('published_at', '>=', $from);
            } elseif ($to) {
                $query->where('published_at', '<=', $to);
            }
        }

        // Diversified only when explicitly requested
        if ($request->boolean('diverse')) {
            $query->diversified(5);
        }

        $page = $query
            ->latestPublished()
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        // Coverage counts
        $storyGroups = collect($page->items())
            ->map(fn(NewsHeading $item) => $item->getRawOriginal('story_group'))
            ->filter()
            ->unique()
            ->values();

        $sourceCounts = $storyGroups->isEmpty()
            ? collect()
            : NewsHeading::query()
            ->whereIn('story_group', $storyGroups)
            ->get(['story_group', 'source_key'])
            ->groupBy('story_group')
            ->map(fn($items) => $items->pluck('source_key')->unique()->count());

        $sourceCatalog = collect(config('news_sources', []))
            ->sortBy(fn(array $s) => sprintf('%s-%03d', $s['language'] ?? '', (int) ($s['order'] ?? 0)))
            ->keyBy('key');

        $items = collect($page->items())
            ->map(function (NewsHeading $item) use ($sourceCounts, $sourceCatalog) {
                $storyGroup = $item->getRawOriginal('story_group');
                $source     = $sourceCatalog->get($item->source_key)
                    ?? $this->sourceForUrl($item->source_link, $sourceCatalog->values());

                return [
                    'id'             => $item->id,
                    'title'          => $item->title,
                    'slug'           => $item->slug,
                    'source_url'     => $item->source_link,
                    'source_name'    => $source['name'] ?? $item->source_name,
                    'source_key'     => $item->source_key,
                    'source_slug'    => $this->sourceSlugForKey((string) $item->source_key, $source),
                    'category'       => $item->category,
                    'language'       => $source['language'] ?? $item->language,
                    'published_at'   => $item->published_at?->toIso8601String()
                        ?? $item->created_at?->toIso8601String(),
                    'image_url'      => $this->resolveImageUrl($item),
                    'story_group'    => $storyGroup,
                    'coverage_count' => $storyGroup
                        ? ($sourceCounts->get($storyGroup) ?? 1)
                        : 1,
                ];
            })
            ->values();

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
                'from'         => $page->firstItem(),
                'to'           => $page->lastItem(),
                'has_more'     => $page->hasMorePages(),
            ],
        ];
    }

    // ─── Private helpers (unchanged logic, just cleaned) ─────────────────────

    private function selectableColumns(): array
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
        if ($this->hasOptionalNewsColumn('summary')) {
            $columns[] = 'summary';
        }

        return $columns;
    }

    private function applySourceFilter(Builder $query, array $source): Builder
    {
        $host = $this->normalizeHost((string) parse_url((string) ($source['home_url'] ?? ''), PHP_URL_HOST));

        return $query->where(function (Builder $match) use ($source, $host) {
            $match->where('source_key', $source['key'])
                ->orWhere('source_name', $source['name']);

            if ($host !== '') {
                $match->orWhere('source_link', 'like', '%' . $host . '/%');
            }
        });
    }

    private function sourceForUrl(?string $url, $sources): ?array
    {
        $host = $this->normalizeHost((string) parse_url((string) $url, PHP_URL_HOST));
        if ($host === '') {
            return null;
        }

        return $sources->first(function (array $source) use ($host) {
            $sourceHost = $this->normalizeHost(
                (string) parse_url((string) ($source['home_url'] ?? ''), PHP_URL_HOST)
            );

            return $sourceHost !== ''
                && ($host === $sourceHost || str_ends_with($host, '.' . $sourceHost));
        });
    }

    private function sourceSlugForKey(string $sourceKey, ?array $source = null): string
    {
        if ($source === null) {
            $source = collect(config('news_sources', []))
                ->first(fn(array $c) => ($c['key'] ?? null) === $sourceKey);
        }

        $slug = $source['slug'] ?? $source['name'] ?? $sourceKey;

        return (string) Str::slug((string) $slug);
    }

    private function normalizeHost(string $host): string
    {
        $host = strtolower(rtrim($host, '.'));
        return preg_replace('/^www\./i', '', $host) ?? $host;
    }

    private function hasOptionalNewsColumn(string $column): bool
    {
        if (! in_array($column, ['story_group', 'local_image_path', 'summary'], true)) {
            return false;
        }

        return Cache::remember(
            'news_headings_has_column_' . $column,
            now()->addHour(),
            fn() => Schema::hasColumn('news_headings', $column)
        );
    }

    private function resolveImageUrl(NewsHeading $item): ?string
    {
        $localPath = trim((string) $item->getRawOriginal('local_image_path'));

        if ($localPath !== '') {
            try {
                $disk = Storage::disk('public');
                if ($disk->exists($localPath)) {
                    $url = $disk->url($localPath);
                    if (str_starts_with($url, '/')) {
                        $url = rtrim((string) config('app.url'), '/') . $url;
                    }
                    return $url;
                }
            } catch (\Throwable) {
                // ignore
            }
        }

        return $this->safeImageUrl($item->image_url);
    }

    private function safeImageUrl(?string $url): ?string
    {
        if (! $url || strlen($url) > 2048) {
            return null;
        }

        $url = trim($url);

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        } elseif (str_starts_with($url, '/')) {
            $url = rtrim((string) config('app.url'), '/') . $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $image  = parse_url($url);
        $scheme = strtolower((string) ($image['scheme'] ?? ''));
        $host   = strtolower(rtrim((string) ($image['host'] ?? ''), '.'));

        if (
            ! in_array($scheme, ['http', 'https'], true)
            || $host === ''
            || isset($image['user'])
            || isset($image['pass'])
        ) {
            return null;
        }

        if (
            filter_var($host, FILTER_VALIDATE_IP)
            && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
        ) {
            return null;
        }

        return $url;
    }
}