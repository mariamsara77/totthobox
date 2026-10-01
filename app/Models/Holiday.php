<?php

namespace App\Models;

use App\Traits\AutoCacheable;
use App\Traits\HasReactions;
use Carbon\Carbon;
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

class Holiday extends Model implements HasMedia, Viewable
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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'date',
        'type',
        'details',
        'details_en',
        'is_annual',
        'image',
        'tags',
        'slug',
        'division_id',
        'district_id',
        'thana_id',
        'user_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'updated_by',
        'deleted_by',
        'published_by',
        'published_at',
        'view_count',
        'is_featured',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_annual' => 'boolean',
        'date' => 'date',
        'published_at' => 'datetime',
        'tags' => 'array',
        'meta_keywords' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'view_count' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the district that owns the holiday.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the thana that owns the holiday.
     */
    public function thana(): BelongsTo
    {
        return $this->belongsTo(Thana::class);
    }

    /**
     * Scope a query to only include published holidays.
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->where('is_active', true);
    }

    /**
     * Scope a query to only include featured holidays.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->where('is_active', true);
    }

    /**
     * Scope a query to only include active holidays.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include holidays of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include holidays in a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include upcoming holidays.
     */
    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('date', '>=', Carbon::today())
            ->where('date', '<=', Carbon::today()->addDays($days))
            ->orderBy('date');
    }

    /**
     * Check if the holiday is published.
     */
    public function isPublished(): bool
    {
        return $this->published_at && $this->published_at <= Carbon::now();
    }

    /**
     * Increment the view count.
     */
    public function incrementViews(): void
    {
        $this->view_count++;
        $this->save();
    }

   public function registerMediaCollections(): void
{
    $this->addMediaCollection('holiday_images')
        ->useDisk('public'); // বা আপনার disk

    // fallback হিসেবে default ও রাখা হলো
    $this->addMediaCollection('default');
}

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(100)
        ->height(100)
        ->sharpen(10)
        ->format('webp')
        ->nonQueued();

    $this->addMediaConversion('preview')
        ->width(800)
        ->height(600)
        ->format('webp')
        ->nonQueued();
}

    /**
     * Get the holiday type as a readable string.
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
