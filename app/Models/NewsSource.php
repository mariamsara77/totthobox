<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        $forgetSourceCaches = static function (NewsSource $source): void {
            cache()->forget('news_api_sources_v2');
            cache()->forget('news_sidebar_sources_v2');
        };

        static::saved($forgetSourceCaches);
        static::deleted($forgetSourceCaches);
    }

    public function headlines(): HasMany
    {
        return $this->hasMany(NewsHeading::class, 'source_key', 'source_key');
    }
}
