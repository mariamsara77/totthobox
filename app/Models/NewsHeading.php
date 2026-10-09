<?php

namespace App\\Models;

use Carbon\\Carbon;
use Illuminate\\Database\\Eloquent\\Builder;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;
use Illuminate\\Support\\Collection;
use Illuminate\\Support\\Facades\\Cache;

class NewsHeading extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'summary', // Optional editorial summary, written and reviewed by the editorial team.
        'slug',
        'source_link',
        'source_hash',
        'source_name',
        'source_key',
        'category',
        'story_group', // Cross-source coverage cluster.
        'image_url',
        'language',
        'published_at',
        'local_image_path',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        $clearNewsCaches = static function (): void {
            cache()->forget('news_sidebar_sources_v1');
            cache()->forget('news_sidebar_counts_v1');
            cache()->forget('news_api_sources_v1');
            Cache::tags(['news_headlines', 'news_coverage'])->flush();
        };

        static::saved($clearNewsCaches);
        static::deleted($clearNewsCaches);
        static::restored($clearNewsCaches);
    }

    /**
     * Best available timestamp for display purposes.
     */
    public function getEffectiveDateAttribute(): Carbon
    {
        return $this->published_at ?? $this->created_at;
    }

    /**
     * Published-time ordering prevents edits from promoting old stories.
     */
    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->orderByRaw('COALESCE(published_at, created_at) DESC');
    }

    public function scopeOnDate(Builder $query, mixed $date): Builder
    {
        return $query->where(function (Builder $q) use ($date) {
            $q->whereDate('published_at', $date)
                ->orWhereDate('created_at', $date);
        });
    }

    public function scopeFromSource(Builder $query, string $sourceKey): Builder
    {
        return $query->where('source_key', $sourceKey);
    }

    public function scopeInLanguage(Builder $query, string $lang): Builder
    {
        return $query->where('language', $lang);
    }

    public function scopeInCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where(function (Builder $q) use ($hours) {
            $cutoff = now()->subHours($hours);
            $q->where('published_at', '>=', $cutoff)
                ->orWhere(function (Builder $inner) use ($cutoff) {
                    $inner->whereNull('published_at')
                        ->where('created_at', '>=', $cutoff);
                });
        });
    }

    /**
     * Limit each source in a diversified feed. Requires MySQL 8+ window support.
     */
    public function scopeDiversified(Builder $query, int $perSource = 5): Builder
    {
        $sub = self::selectRaw('
                id,
                ROW_NUMBER() OVER (
                    PARTITION BY source_key
                    ORDER BY COALESCE(published_at, created_at) DESC
                ) AS rn
            ')
            ->whereNull('deleted_at');

        return $query->joinSub($sub, 'ranked', 'news_headings.id', '=', 'ranked.id')
            ->where('ranked.rn', '<=', $perSource)
            ->select('news_headings.*');
    }

    /**
     * Sources available for sidebar/filter UI.
     *
     * @return Collection<int, array{key: string, name: string, count: int}>
     */
    public static function availableSources(): Collection
    {
        return Cache::remember('news_sidebar_sources_v1', now()->addMinutes(10), function () {
            $counts = self::selectRaw('source_key, COUNT(*) as count')
                ->groupBy('source_key')
                ->pluck('count', 'source_key');

            return collect(config('news_sources', []))
                ->sortBy(fn (array $source) => sprintf('%s-%03d', $source['language'], $source['order']))
                ->values()
                ->map(fn (array $source) => [
                    'key' => $source['key'],
                    'name' => $source['name'],
                    'count' => (int) ($counts[$source['key']] ?? 0),
                ]);
        });
    }

    /**
     * Articles from other sources covering the same grouped story.
     */
    public function relatedCoverage(): Collection
    {
        if (empty($this->story_group)) {
            return collect();
        }

        return static::query()
            ->where('story_group', $this->story_group)
            ->where('id', '!=', $this->id)
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->get();
    }

    /**
     * Load related coverage for a group of stories without N+1 queries.
     *
     * @param  Collection<int, self>  $headlines
     * @return array<int, Collection<int, self>>
     */
    public static function loadCoverageMap(Collection $headlines): array
    {
        $groups = $headlines->pluck('story_group')->filter()->unique()->values();

        if ($groups->isEmpty()) {
            return [];
        }

        $all = static::query()
            ->whereIn('story_group', $groups)
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->get()
            ->groupBy('story_group');

        $map = [];
        foreach ($headlines as $news) {
            if (empty($news->story_group)) {
                continue;
            }
            $map[$news->id] = $all->get($news->story_group, collect())
                ->where('id', '!=', $news->id)
                ->values();
        }

        return $map;
    }
}
