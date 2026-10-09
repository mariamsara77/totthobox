<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsHeading;
use App\Models\NewsSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
                Rule::in(NewsSource::query()->active()->pluck('source_key')->all()),
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
        $hours = (int) ($data['hours'] ?? 168);

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
                'local_image_path',
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
            $query->whereRaw('LOWER(category) = ?', [mb_strtolower(trim($data['category']))]);
        }

        if (! empty($data['search'])) {
            $term = trim($data['search']);
            $query->where('title', 'like', '%'.$term.'%');
        }

        $query->recent($hours);

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

        $sourceKeys = collect($page->items())
            ->pluck('source_key')
            ->filter()
            ->unique()
            ->values();

        $sourceSlugs = $sourceKeys->isEmpty()
            ? collect()
            : NewsSource::query()
                ->whereIn('source_key', $sourceKeys)
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
                    'source_slug' => $sourceSlugs->get($item->source_key) ?? str_replace('_', '-', $item->source_key),
                    'category' => $item->category,
                    'language' => $item->language,
                    'published_at' => $item->published_at?->toIso8601String(),
                    ...$this->resolveImagePayload($item),
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
        $data = Cache::remember('news_api_sources_v1', now()->addMinutes(5), function () {
            $sources = NewsSource::query()
                ->active()
                ->ordered()
                ->get();

            $counts = NewsHeading::query()
                ->selectRaw('source_key, COUNT(*) as total')
                ->groupBy('source_key')
                ->pluck('total', 'source_key');

            $result = $sources->map(fn (NewsSource $source) => [
                'key' => $source->source_key,
                'slug' => $source->slug,
                'name' => $source->name,
                'language' => $source->language,
                'home_url' => $source->home_url,
                'total' => (int) ($counts[$source->source_key] ?? 0),
            ]);

            return $result->groupBy('language');
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
                'image_url',
                'local_image_path',
            ])
            ->where('slug', $slug)
            ->first();

        if (! $item) {
            return response()->json(['message' => 'News item not found.'], 404);
        }

        $coverageItems = collect();

        if ($item->story_group) {
            $coverageItems = NewsHeading::query()
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
                    'image_url',
                    'local_image_path',
                ])
                ->where('story_group', $item->story_group)
                ->where('id', '!=', $item->id)
                ->latestPublished()
                ->limit(8)
                ->get();
        }

        $sourceKeys = collect([$item->source_key])
            ->merge($coverageItems->pluck('source_key'))
            ->filter()
            ->unique()
            ->values();

        $sourceSlugs = NewsSource::query()
            ->whereIn('source_key', $sourceKeys)
            ->pluck('slug', 'source_key');

        $coverage = $coverageItems
            ->map(fn (NewsHeading $news) => [
                'id' => $news->id,
                'title' => $news->title,
                'slug' => $news->slug,
                'source_url' => $news->source_link,
                'source_name' => $news->source_name,
                'source_key' => $news->source_key,
                'source_slug' => $sourceSlugs->get($news->source_key)
                    ?? str_replace('_', '-', $news->source_key),
                'category' => $news->category,
                'language' => $news->language,
                'published_at' => $news->published_at?->toIso8601String(),
                ...$this->resolveImagePayload($news),
            ])
            ->values();

        return response()->json([
            'data' => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'source_url' => $item->source_link,
                'source_name' => $item->source_name,
                'source_key' => $item->source_key,
                'source_slug' => $sourceSlugs->get($item->source_key)
                    ?? str_replace('_', '-', $item->source_key),
                'category' => $item->category,
                'language' => $item->language,
                'published_at' => $item->published_at?->toIso8601String(),
                ...$this->resolveImagePayload($item),
                'story_group' => $item->story_group,
                'coverage' => $coverage,
            ],
        ]);
    }

    /**
     * Prefer a managed local thumbnail when it exists, then use the publisher
     * URL. When both exist, send the publisher URL as a client-side fallback so
     * a stale local file does not leave a blank card. Filesystem paths stay private.
     *
     * @return array{image_url: ?string, image_fallback_url: ?string}
     */
    private function resolveImagePayload(NewsHeading $item): array
    {
        $remoteUrl = $this->validHttpUrl($item->image_url);
        $localCandidates = [
            trim((string) $item->local_image_path),
            $remoteUrl === null ? trim((string) $item->image_url) : '',
        ];

        foreach (array_unique($localCandidates) as $path) {
            if ($path === '') {
                continue;
            }

            $localUrl = $this->resolvePublicStoragePath($path);
            if ($localUrl !== null) {
                return [
                    'image_url' => $localUrl,
                    'image_fallback_url' => $remoteUrl !== null && $remoteUrl !== $localUrl
                        ? $remoteUrl
                        : null,
                ];
            }
        }

        return [
            'image_url' => $remoteUrl,
            'image_fallback_url' => null,
        ];
    }

    private function validHttpUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '//')) {
            $value = 'https:'.$value;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $value;
    }

    private function resolvePublicStoragePath(string $path): ?string
    {
        $path = str_replace(chr(92), '/', trim($path));
        $path = ltrim($path, '/');

        foreach (['storage/app/public/', 'public/storage/', 'storage/', 'public/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }

        if ($path === '' || str_contains($path, '../')) {
            return null;
        }

        if (Storage::disk('public')->exists($path)) {
            $url = Storage::disk('public')->url($path);

            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                return $url;
            }

            return url('/'.ltrim($url, '/'));
        }

        // Support legacy files stored directly in Laravel's public directory
        // while still returning a public URL, never an absolute server path.
        $publicPath = ltrim($path, '/');
        if (str_starts_with($publicPath, 'public/')) {
            $publicPath = substr($publicPath, strlen('public/'));
        }

        if ($publicPath !== '' && file_exists(public_path($publicPath))) {
            return url('/'.ltrim($publicPath, '/'));
        }

        return null;
    }
}
