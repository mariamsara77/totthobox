<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LiveChannel extends Model
{
    use SoftDeletes;

    // ── Fillable ─────────────────────────────────────────────────────────
    protected $fillable = [
        'title',
        'slug',
        'stream_url',
        'embed_url',
        'category',
        'country_code',
        'language',
        'broadcaster',
        'logo_url',
        'is_live',
        'is_featured',
        'sort_order',
        'last_checked_at',
        'last_live_at',
        'consecutive_failures',
        'health_score',
        'source_label',
        'm3u_meta',
    ];

    protected $casts = [
        'is_live' => 'boolean',
        'is_featured' => 'boolean',
        'last_checked_at' => 'datetime',
        'last_live_at' => 'datetime',
        'consecutive_failures' => 'integer',
        'health_score' => 'integer',
        'sort_order' => 'integer',
        'm3u_meta' => 'array',
    ];

    // ── Categories ───────────────────────────────────────────────────────
    const CATEGORY_WORLDCUP = 'worldcup';

    const CATEGORY_BANGLADESH = 'bangladesh';

    const CATEGORY_FOOTBALL = 'football';

    const CATEGORIES = [
        self::CATEGORY_BANGLADESH => 'Bangladeshi TV',
        // self::CATEGORY_WORLDCUP => '🏆 FIFA World Cup 2026',
        // self::CATEGORY_FOOTBALL => '⚽ Football / Sports',
    ];

    // ── Boot ─────────────────────────────────────────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title).'-'.Str::random(5);
            }
        });
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    public function scopeWorldCup(Builder $q): Builder
    {
        return $q->where('category', self::CATEGORY_WORLDCUP);
    }

    public function scopeBangladesh(Builder $q): Builder
    {
        return $q->where('category', self::CATEGORY_BANGLADESH);
    }

    public function scopeFootball(Builder $q): Builder
    {
        return $q->where('category', self::CATEGORY_FOOTBALL);
    }

    public function scopeLive(Builder $q): Builder
    {
        return $q->where('is_live', true);
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    public function scopeHealthy(Builder $q): Builder
    {
        return $q->where('consecutive_failures', '<', 5);
    }

    /** Order: featured first → live first → sort_order → latest */
    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderByDesc('is_featured')
            ->orderByDesc('is_live')
            ->orderBy('sort_order')
            ->orderByDesc('updated_at');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getHealthBadgeAttribute(): string
    {
        return match (true) {
            $this->health_score >= 80 => 'excellent',
            $this->health_score >= 50 => 'good',
            $this->health_score >= 20 => 'poor',
            default => 'dead',
        };
    }

    /**
     * Mark channel as live and update tracking fields.
     */
    public function markLive(): bool
    {
        return $this->update([
            'is_live' => true,
            'last_checked_at' => now(),
            'last_live_at' => now(),
            'consecutive_failures' => 0,
            'health_score' => min(100, $this->health_score + 10),
        ]);
    }

    /**
     * Mark channel as offline and increment failure counter.
     */
    public function markOffline(): bool
    {
        $failures = $this->consecutive_failures + 1;
        $score = max(0, $this->health_score - 15);

        return $this->update([
            'is_live' => false,
            'last_checked_at' => now(),
            'consecutive_failures' => $failures,
            'health_score' => $score,
        ]);
    }
}
