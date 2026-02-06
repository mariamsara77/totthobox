<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class EstablishmentBd extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * The table associated with the model.
     */
    protected $table = 'establishment_bds';

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
        'status' => 'integer',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'founding_year' => 'integer',
    ];

    /**
     * Boot method for model event hooks.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from title if not provided
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title) . '-' . Str::random(5);
            }
        });
    }

    /* ======================================================
     |  Relationships
     ====================================================== */

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

    // Filter by establishment type
    public function scopeType($query, $type)
    {
        return $query->where('establishment_type', $type);
    }

    /* ======================================================
     |  Accessors & Mutators
     ====================================================== */

    // Image full URL accessor
    public function getImageUrlAttribute()
    {
        return $this->image 
            ? asset('storage/' . $this->image)
            : asset('images/default-establishment.png');
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

    // Formatted founding year
    public function getFormattedFoundingYearAttribute()
    {
        return $this->founding_year ?: 'Unknown';
    }

    // Increment view count
    public function incrementViews()
    {
        $this->increment('view_count');
    }
}