<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class BasicHealth extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'summary',
        'image',
        'tags',
        'slug',
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
     * Cast attributes to specific types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'view_count' => 'integer',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('published_at', [$startDate, $endDate]);
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('published_at', '>=', Carbon::today())
            ->where('published_at', '<=', Carbon::today()->addDays($days))
            ->orderBy('published_at');
    }

    /**
     * Helpers
     */
    public function isPublished(): bool
    {
        return $this->published_at && $this->published_at <= Carbon::now();
    }

    public function incrementViews(): void
    {
        $this->view_count++;
        $this->save();
    }

    public function getCategoryNameAttribute(): string
    {
        return ucfirst($this->category);
    }

    public function getTypeNameAttribute(): string
    {
        return ucfirst($this->type);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
