<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory, SoftDeletes;

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
        'date' => 'date',
        'published_at' => 'datetime',
        'tags' => 'array',
        'meta_keywords' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'view_count' => 'integer',
    ];

    /**
     * Holiday types
     */
    public const TYPES = [
        'national' => 'National',
        'religious' => 'Religious',
        'international' => 'International',
        'observance' => 'Observance',
        'seasonal' => 'Seasonal',
    ];

    /**
     * Get the division that owns the holiday.
     */
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
     * Get the user that owns the holiday.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created the holiday.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the holiday.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who published the holiday.
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
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
