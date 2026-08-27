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
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TourismBd extends Model implements HasMedia, Viewable
{
    use AutoCacheable;
    use HasFactory;
    use HasReactions;
    use InteractsWithMedia;
    use InteractsWithViews;
    use LogsActivity;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'id', 'title', 'tourism_type', 'description', 'image', 'map',
        'division_id', 'district_id', 'thana_id', 'slug', 'status', 'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'status' => 'integer',
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
}
