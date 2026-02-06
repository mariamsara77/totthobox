<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HistoryBd extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'history_bds';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'image',
        'division_id',
        'district_id',
        'thana_id',
        'slug',
        'user_id',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'updated_by',
        'deleted_by',
        'published_at',
        'published_by',
        'view_count',
        'is_featured',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status'       => 'integer',
        'view_count'   => 'integer',
        'is_featured'  => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Boot method for model event hooks.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    /**
     * Spatie Image v3 অনুযায়ী ফিক্সড থাম্বনেইল কনভার্সন
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 300, 300) // ইমেজ ক্রপ করে সুন্দর থাম্বনেইল করবে
            ->sharpen(10)
            ->nonQueued();
    }

    /* ======================================================
     |  Relationships
     ====================================================== */

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class, 'thana_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /* ======================================================
     |  Scopes
     ====================================================== */

    // Only active
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // Featured content
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Published only
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    /* ======================================================
     |  Accessors & Mutators
     ====================================================== */

    // Image full URL accessor
    public function getImageUrlAttribute()
    {
        return $this->image 
            ? asset('storage/' . $this->image)
            : asset('images/default.png');
    }

    // Short description accessor
    public function getShortDescriptionAttribute()
    {
        return Str::limit(strip_tags($this->description), 150);
    }

    // Meta title fallback
    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->title;
    }

    // Increment view count
    public function incrementViews()
    {
        $this->increment('view_count');
    }
}