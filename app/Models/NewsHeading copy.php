<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsHeading extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'source_link',
        'source_name',
        'source_key',
        'category',
        'image_url',
        'language',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

   protected static function booted(): void
{
    static::saved(function () {
        cache()->forget('news_sidebar_sources_v1');
    });

    static::deleted(function () {
        cache()->forget('news_sidebar_sources_v1');
    });
}
    // ─── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Best available timestamp for display purposes.
     */
    public function getEffectiveDateAttribute(): Carbon
    {
        return $this->published_at ?? $this->created_at;
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    /**
     * Latest headlines ordered by the original publish date.
     *
     * IMPORTANT: published_at ব্যবহার করা হয় — updated_at নয়।
     * এটি নিশ্চিত করে যে পুরনো নিউজ update হলেও top-এ উঠে আসবে না।
     */
    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->orderByRaw('COALESCE(published_at, created_at) DESC');
    }

    /**
     * Filter by a specific date (published_at OR created_at).
     */
    public function scopeOnDate(Builder $query, mixed $date): Builder
    {
        return $query->where(function (Builder $q) use ($date) {
            $q->whereDate('published_at', $date)
                ->orWhereDate('created_at', $date);
        });
    }

    /**
     * Filter by source key only.
     * NOTE: Previously this scope also matched `slug` which caused incorrect
     * results — a news slug is not the same as a source key.
     */
    public function scopeFromSource(Builder $query, string $sourceKey): Builder
    {
        return $query->where('source_key', $sourceKey);
    }

    /**
     * Filter by language (e.g. 'bn', 'en').
     */
    public function scopeInLanguage(Builder $query, string $lang): Builder
    {
        return $query->where('language', $lang);
    }

    /**
     * Filter by category.
     */
    public function scopeInCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Only articles published within the last N hours.
     * Useful for "latest" feeds / widgets.
     */
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
     * Diverse feed: limits each source to N articles so no single newspaper
     * floods the results. Use before latestPublished().
     *
     * Usage:
     *   NewsHeading::diversified(5)->latestPublished()->paginate(30)
     *
     * Implementation uses a subquery window approach compatible with MySQL 8+.
     * Falls back gracefully on older MySQL (just returns all rows ordered).
     */
    public function scopeDiversified(Builder $query, int $perSource = 5): Builder
    {
        // Subquery: rank each row within its source_key partition
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
     * Quick helper — sources available for sidebar/filter UI,
     * cached for 10 minutes.
     *
     * @return \Illuminate\Support\Collection<int, array{key: string, name: string, count: int}>
     */
    public static function availableSources(): \Illuminate\Support\Collection
    {
        return cache()->remember('news_sidebar_sources_v1', now()->addMinutes(10), function () {
            return self::selectRaw('source_key, source_name, COUNT(*) as count')
                ->groupBy('source_key', 'source_name')
                ->orderByDesc('count')
                ->get()
                ->map(fn($row) => [
                    'key' => $row->source_key,
                    'name' => $row->source_name,
                    'count' => (int) $row->count,
                ]);
        });
    }
}