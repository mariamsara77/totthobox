<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuySellItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass Assignment Protection
     */
    protected $fillable = [
        'title',
        'slug',
        'thumbnail',
        'description',
        'note',
        'price',
        'buy_sell_category_id',
        'user_id',
        'status',
        'is_active',
        'is_featured',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'view_count',
        'created_by',
        'updated_by',
        'deleted_by',
        'published_by',
        'ip_address',
        'user_agent',
    ];

    /**
     * Attribute Casting
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    /**
     * Default Attribute Values
     */
    protected $attributes = [
        'status' => 'draft',
        'view_count' => 0,
    ];

    /**
     * ======================
     *  🔗 RELATIONSHIPS
     * ======================
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BuySellCategory::class, 'buy_sell_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * ======================
     *  ⚙️ ACCESSORS / MUTATORS
     * ======================
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucfirst($value),
            set: fn($value) => strtolower(trim($value))
        );
    }

    protected function isPublished(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'published'
        );
    }

    /**
     * ======================
     *  📊 QUERY SCOPES
     * ======================
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeRecent($query)
    {
        return $query->latest('published_at');
    }

    /**
     * ======================
     *  📈 BUSINESS LOGIC
     * ======================
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function markAsPublished(int $userId): void
    {
        $this->update([
            'status' => 'published',
            'published_by' => $userId,
            'published_at' => now(),
        ]);
    }
}
