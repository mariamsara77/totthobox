<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NewsSource extends Model
{
    protected $fillable = [
        'source_key',
        'slug',
        'name',
        'language',
        'home_url',
        'position',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $clearSourceCaches = static function (): void {
            Cache::forget('news_api_sources_v1');
            Cache::forget('news_sidebar_counts_v1');
            Cache::forget('news_sidebar_sources_v1');
        };

        static::saved($clearSourceCaches);
        static::deleted($clearSourceCaches);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('language')
            ->orderBy('position')
            ->orderBy('name');
    }
}
