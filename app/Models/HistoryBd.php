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
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HistoryBd extends Model implements HasMedia, Viewable
{
    use AutoCacheable;
    use HasFactory;
    use HasReactions;
    use InteractsWithMedia;
    use InteractsWithViews;
    use LogsActivity;
    use Searchable;
    use SoftDeletes;

    protected $table = 'history_bds';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'division_id',
        'district_id',
        'thana_id',
        'status',
        'is_featured',
        'era',
        'sort_order',
        'start_year',
        'end_year',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        // 'start_year' => 'integer',
        // 'end_year' => 'integer',
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
            ->singleFile();
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
            'status' => $this->status,
            'is_featured' => $this->is_featured,
        ];
    }

    protected function convertToEnglishPhonetic(?string $text): string
    {
        if (blank($text)) {
            return '';
        }

        $englishFromSlug = str_replace('-', ' ', $this->slug ?? '');

        return trim($text.' '.$englishFromSlug);
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);

        if (blank($baseSlug)) {
            $baseSlug = 'history-'.Str::random(8);
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

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /* ======================================================
     |  Accessors
     ====================================================== */

    public function getShortDescriptionAttribute()
    {
        return Str::limit(strip_tags($this->description), 150);
    }

    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->title;
    }
}
