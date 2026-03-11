<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class IntroBd extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    const CACHE_KEY = 'intro_bds_all_data';

    protected $fillable = [
        'title', 'intro_category', 'description', 'user_id', 'slug', 'status',
        'sort_order', 'featured_order', 'category_order', 'meta_title',
        'meta_description', 'meta_keywords', 'created_by', 'updated_by',
        'deleted_by', 'published_at', 'published_by', 'view_count',
        'is_featured', 'ip_address', 'user_agent', 'division_id',
        'district_id', 'thana_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'status' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = $model->slug ?: Str::slug($model->title);
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        // Clear cache on save/delete
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault(['name' => 'Admin']);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 300, 300)
            ->sharpen(10)
            ->nonQueued();
    }
}
