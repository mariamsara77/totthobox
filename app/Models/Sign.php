<?php

namespace App\Models;

use App\Traits\AutoCacheable;
use App\Traits\HasReactions;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Sign extends Model implements HasMedia, Viewable
{
    use AutoCacheable, HasFactory, HasReactions, InteractsWithMedia, InteractsWithViews, LogsActivity, SoftDeletes;

    protected $table = 'signs';

    protected $fillable = [
        'sign_category_id',
        'name',
        'description',
        'details',
        'others',
        'slug',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_featured' => 'boolean',
    ];

    protected $attributes = [
        'status' => 1,
        'is_featured' => false,
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            cache()->forget('basic_islam_v1');
        });

        static::deleted(function () {
            cache()->forget('basic_islam_v1');
        });
    }

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'sign_category_id', 'is_featured', 'status'])
            ->logOnlyDirty()
            ->useLogName('sign');
    }

    /**
     * Relationships
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SignCategory::class, 'sign_category_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 0);
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

        // রিয়েল-টাইমে ইমেজের সব EXIF/Copyright ডেটা ডিলিট করে WebP করা হবে
        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(85)
            ->optimize()
            ->nonQueued();
    }

    public function incrementViews(): void
    {
        $this->view_count++;
        $this->save();
    }
}
