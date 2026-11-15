<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class NewsHeading extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'summary',
        'slug',
        'source_link',
        'source_name',
        'source_key',
        'category',
        'story_group',
        'image_url',
        'local_image_path',
        'language',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        $clear = function () {
            Cache::forget('news_sidebar_sources_v1');
            Cache::forget('news_api_sources_v3');
            // tag support থাকলে
            try {
                Cache::tags(['news_sidebar', 'news_headlines', 'news_coverage'])->flush();
            } catch (\Throwable) {
                // tag driver না থাকলে ignore
            }
        };

        static::saved($clear);
        static::deleted($clear);
    }

    public function getEffectiveDateAttribute(): Carbon
    {
        return $this->published_at ?? $this->created_at;
    }

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

    public static function availableSources(): Collection
    {
        return Cache::remember('news_sidebar_sources_v1', now()->addMinutes(10), function () {
            return self::selectRaw('source_key, source_name, COUNT(*) as count')
                ->groupBy('source_key', 'source_name')
                ->orderByDesc('count')
                ->get()
                ->map(fn($row) => [
                    'key'   => $row->source_key,
                    'name'  => $row->source_name,
                    'count' => (int) $row->count,
                ]);
        });
    }

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