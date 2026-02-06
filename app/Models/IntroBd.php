<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;


class IntroBd extends Model implements HasMedia
{
    use SoftDeletes, HasFactory, InteractsWithMedia;
    
    protected $fillable = [
        'title',
        'intro_category',
        'description',
        'image',
        'user_id',
        'slug',
        'status',
        // Ordering columns
        'sort_order',
        'featured_order',
        'category_order',
        // SEO fields
        'meta_title',
        'meta_description',
        'meta_keywords',
        // Audit fields
        'created_by',
        'updated_by',
        'deleted_by',
        'published_at',
        'published_by',
        'view_count',
        'is_featured',
        'ip_address',
        'user_agent',
        // Location fields
        'division_id',
        'district_id',
        'thana_id'
    ];

    protected $dates = [
        'deleted_at',
        'published_at'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'status' => 'integer',
        'view_count' => 'integer',
        'sort_order' => 'integer',
        'featured_order' => 'integer',
        'category_order' => 'integer',
        'published_at' => 'datetime',
    ];


    // protected function image(): \Illuminate\Database\Eloquent\Casts\Attribute
    // {
    //     return \Illuminate\Database\Eloquent\Casts\Attribute::make(
    //         get: fn($value) => $value ? \Illuminate\Support\Facades\Storage::url($value) : null,
    //     );
    // }

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
    
    // Constants for status
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

    // Constants for categories (customize as needed)
    const CATEGORY_BUSINESS = 'business';
    const CATEGORY_EDUCATION = 'education';
    const CATEGORY_HEALTHCARE = 'healthcare';
    const CATEGORY_TECHNOLOGY = 'technology';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate slug if not provided
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }

            // Ensure unique slug
            $originalSlug = $model->slug;
            $count = 1;
            while (static::where('slug', $model->slug)->withTrashed()->exists()) {
                $model->slug = $originalSlug . '-' . $count++;
            }

            // Set created_by if not set
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            // Set updated_by if not set
            if (empty($model->updated_by) && auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            // Set deleted_by if not set
            if (empty($model->deleted_by) && auth()->check()) {
                $model->deleted_by = auth()->id();
            }
        });
    }

    // Define relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('intro_category', $category);
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')
                    ->orderBy('created_at', 'desc');
    }

    public function scopeFeaturedSorted($query)
    {
        return $query->where('is_featured', true)
                    ->orderBy('featured_order')
                    ->orderBy('sort_order');
    }

    public function scopeCategorySorted($query, $category = null)
    {
        $query = $query->orderBy('category_order')
                      ->orderBy('sort_order');

        if ($category) {
            $query->where('intro_category', $category);
        }

        return $query;
    }

    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    // Accessors & Mutators
    public function getExcerptAttribute()
    {
        return Str::limit(strip_tags($this->description), 150);
    }

    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->description));
        return ceil($wordCount / 200); // Assuming 200 words per minute
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED => 'Archived',
            default => 'Unknown',
        };
    }

    public function getIsPublishedAttribute()
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at
            && $this->published_at <= now();
    }

    // Methods
    public function publish()
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => now(),
            'published_by' => auth()->id() ?? $this->created_by
        ]);
    }

    public function unpublish()
    {
        $this->update([
            'status' => self::STATUS_DRAFT,
            'published_at' => null
        ]);
    }

    public function markAsFeatured()
    {
        $this->update(['is_featured' => true]);
    }

    public function unmarkAsFeatured()
    {
        $this->update(['is_featured' => false]);
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function getLocationAttribute()
    {
        $location = [];

        if ($this->thana) $location[] = $this->thana->name;
        if ($this->district) $location[] = $this->district->name;
        if ($this->division) $location[] = $this->division->name;

        return implode(', ', $location);
    }

    // Static methods
    public static function getCategories()
    {
        return [
            self::CATEGORY_BUSINESS => 'Business',
            self::CATEGORY_EDUCATION => 'Education',
            self::CATEGORY_HEALTHCARE => 'Healthcare',
            self::CATEGORY_TECHNOLOGY => 'Technology',
        ];
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public function scopeActive($query)
{
    return $query->where('status', 1);
}
}