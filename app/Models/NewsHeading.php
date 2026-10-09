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
        'summary', // ★ নতুন — এডিটোরিয়াল সারাংশ (ঐচ্ছিক, নিজে লিখে/রিভিউ করে ভরতে হবে)
        'slug',
        'source_link',
        'source_hash',
        'source_name',
        'source_key',
        'category',
        'story_group', // ★ নতুন — cross-source coverage ক্লাস্টারিং-এর জন্য
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

        static::saved(function () {
            cache()->forget('news_sidebar_sources_v1');
            cache()->forget('news_sidebar_sources_v2');
            cache()->forget('news_sidebar_counts_v1');
            cache()->forget('news_api_sources_v1');
            cache()->forget('news_api_sources_v2');
            Cache::tags(['news_headlines', 'news_coverage'])->flush();
        });

        static::deleted(function () {
            cache()->forget('news_sidebar_sources_v1');
            cache()->forget('news_sidebar_sources_v2');
            cache()->forget('news_sidebar_counts_v1');
            cache()->forget('news_api_sources_v1');
            cache()->forget('news_api_sources_v2');
            Cache::tags(['news_headlines', 'news_coverage'])->flush();
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
    // ─── ★ Cross-source coverage (AdSense-এর জন্য "real value-add" ফিচার) ──────

    /**
     * এই headline-টা যে story_group-এ আছে, সেই একই গল্প কভার করা
     * অন্যান্য সোর্সের headline। কোনো story_group না থাকলে খালি কালেকশন।
     *
     * সিঙ্গেল রেকর্ডে ব্যবহারের জন্য (যেমন সিঙ্গেল-নিউজ পেজে)। লিস্ট পেজে
     * একাধিক রেকর্ডের জন্য নিচের loadCoverageMap() ব্যবহার করুন — N+1 এড়াতে।
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
     * একাধিক headline-এর জন্য coverage একবারেই লোড করে — N+1 query এড়ানোর জন্য।
     *
     * @param  Collection<int, self>  $headlines
     * @return array<int, Collection<int, self>> [news_id => other headlines in same story_group]
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
