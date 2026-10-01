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
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class IntroBd extends Model implements HasMedia, Viewable
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
        'title',
        'intro_category',
        'description',
        'slug',
        'status',
        'sort_order',
        'featured_order',
        'category_order',
        'is_featured',
        'division_id',
        'district_id',
        'thana_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'status' => 'integer',
    ];

    protected $appends = ['url'];

    protected static function booted(): void
    {
        static::saved(function () {
            cache()->flush();
        });

        static::deleted(function () {
            cache()->flush();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

public function registerMediaCollections(): void
{
    $this->addMediaCollection('intro_images');   // Multiple image
    $this->addMediaCollection('default');
}

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(300)
        ->height(200)
        ->sharpen(10)
        ->format('webp')
        ->nonQueued();

    $this->addMediaConversion('preview')
        ->width(800)
        ->height(500)
        ->sharpen(10)
        ->format('webp')
        ->nonQueued();
}

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

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => route('bangladesh.introduction.show', ['slug' => $this->slug]),
        );
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

    protected function convertToEnglishPhonetic(?string $text): string
    {
        if (blank($text)) {
            return '';
        }

        $englishFromSlug = str_replace('-', ' ', $this->slug ?? '');

        return trim($text . ' ' . $englishFromSlug);
    }
}