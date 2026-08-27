<?php

namespace App\Models;

use App\Traits\AutoCacheable;
use App\Traits\HasReactions;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EstablishmentBd extends Model implements HasMedia, Viewable
{
    use AutoCacheable;
    use HasFactory;
    use HasReactions;
    use InteractsWithMedia;
    use InteractsWithViews;
    use LogsActivity;
    use Searchable;
    use SoftDeletes;

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
        'division_id',
        'district_id',
        'thana_id',
        'slug',
        'status',
        'is_featured',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => 'integer',
        'is_featured' => 'boolean',
        'founding_year' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            cache()->flush();
        });

        static::deleted(function () {
            cache()->flush();
        });
    }

    /* -----------------------------------------------------------------
      |  Activity Log
      | -----------------------------------------------------------------
      */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /* -----------------------------------------------------------------
     |  Media Library
     | -----------------------------------------------------------------
     */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->singleFile(); // optional: only one image if you want
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

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function thana(): BelongsTo
    {
        return $this->belongsTo(Thana::class);
    }

    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */

    /**
     * Dynamic URL for the introduction page.
     */
    // protected function url(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn () => route('bangladesh.tourism.show', ['slug' => $this->slug]),
    //     );
    // }

    /* -----------------------------------------------------------------
     |  Scout / Search
     | -----------------------------------------------------------------
     */

    public function searchableAs(): string
    {
        return 'intro_bds';
    }

    public function toSearchableArray(): array
    {
        $phoneticTitle = $this->convertToEnglishPhonetic($this->title);

        return [
            'id' => (int) $this->id,
            'title' => $this->title,
            'phonetic_title' => $phoneticTitle,
            'slug' => $this->slug,
            'url' => $this->url,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
        ];
    }

    /**
     * Basic Bengali → English phonetic helper.
     * You can later replace this with a proper transliteration library.
     */
    protected function convertToEnglishPhonetic(?string $text): string
    {
        if (blank($text)) {
            return '';
        }

        // Prefer the already generated slug (clean English form)
        $englishFromSlug = str_replace('-', ' ', $this->slug ?? '');

        return trim($text.' '.$englishFromSlug);
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */

    /**
     * Generate a unique slug from the given title.
     */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);

        // Fallback if title is pure Bengali and Str::slug returns empty
        if (blank($baseSlug)) {
            $baseSlug = 'intro-'.Str::random(8);
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
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
}
