<?php

namespace App\Models;

use App\Traits\AutoCacheable;
use App\Traits\HasReactions;
use Carbon\Carbon;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\ResponseCache\Facades\ResponseCache;

class BasicIslam extends Model implements HasMedia, Viewable
{
    use AutoCacheable, HasFactory, HasReactions, InteractsWithMedia, InteractsWithViews, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'slug',
        'status',
        'is_featured',
    ];

   protected $casts = [
    'date' => 'date',
    'tags' => 'array',
    'is_featured' => 'boolean',
    'status' => 'boolean', // is_active এর জায়গায় status
];



    public const TYPES = [
        'national' => 'National',
        'religious' => 'Religious',
        'international' => 'International',
        'observance' => 'Observance',
        'seasonal' => 'Seasonal',
    ];

    protected static function booted(): void
    {
        $clearCache = function () {
            if (class_exists(ResponseCache::class)) {
                ResponseCache::clear();
            }
            cache()->forget('basic_islams_list');
        };

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
        static::restored($clearCache);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10)
            ->format('webp')
            ->nonQueued();
    }

    /**
     * Scope a query to only include active items.
     */
  public function scopeFeatured($query)
{
    return $query->where('is_featured', true)
        ->where('status', true); // is_active এর জায়গায় status
}

public function scopeActive($query)
{
    return $query->where('status', true); // is_active এর জায়গায় status
}

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('date', '>=', Carbon::today())
            ->where('date', '<=', Carbon::today()->addDays($days))
            ->orderBy('date');
    }

    public function isPublished(): bool
    {
        return $this->published_at && $this->published_at <= Carbon::now();
    }

    /**
     * Increment the view count quietly without firing model events/clearing cache.
     */
    public function incrementViews(): void
    {
        $this->timestamps = false;
        $this->increment('view_count');
        $this->timestamps = true;
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}