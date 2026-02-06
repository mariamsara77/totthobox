<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use App\Traits\HasReactions;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BuySellPost extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasReactions, InteractsWithMedia;

    protected $table = 'buy_sell_posts';

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'description',
        'note',
        'buy_sell_category_id',
        'buy_sell_item_id',
        'condition',
        'price',
        'discount_price',
        'currency',
        'is_negotiable',
        'sku',
        'stock',
        'is_available',
        'division_id',
        'district_id',
        'thana_id',
        'address',
        'latitude',
        'longitude',
        'phone',
        'whatsapp',
        'imo',
        'email',
        'images_count',
        'is_active',
        'is_featured',
        'status',
        'published_at',
        'expires_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'view_count',
        'favourite_count',
        'share_count',
        'attributes',
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'published_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_negotiable' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_available' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'attributes' => 'array',
    ];

    protected $appends = [
        'formatted_price',
        'short_description',
        'comments_count',
    ];

    /**
     * Boot Method
     */
    protected static function booted(): void
    {
        static::creating(function (self $post) {
            // শুধুমাত্র uuid কলামের জন্য স্ট্রিং জেনারেট হবে
            if (empty($post->uuid)) {
                $post->uuid = (string) Str::uuid();
            }

            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title . '-' . Str::random(6));
            }
        });
    }

    /**
     * Mutator for discount_price
     */
    public function setDiscountPriceAttribute($value)
    {
        $this->attributes['discount_price'] = $value === '' ? null : $value;
    }

    /**
     * Spatie Media Conversions
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1200, 1200)
            ->nonQueued();
    }

    /**
     * -------------------------------------------------------------------------
     * Relationships
     * -------------------------------------------------------------------------
     */
    public function category()
    {
        return $this->belongsTo(BuySellCategory::class, 'buy_sell_category_id');
    }

    public function item()
    {
        return $this->belongsTo(BuySellItem::class, 'buy_sell_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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

    // Images relationship (for BuySellImage model)
    public function images()
    {
        return $this->hasMany(BuySellImage::class, 'buy_sell_post_id');
    }

    // Comments relationship
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->with(['user', 'replies.user']);
    }

    // All comments including replies
    public function allComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'asc');
    }

    /**
     * -------------------------------------------------------------------------
     * Accessors & Mutators
     * -------------------------------------------------------------------------
     */

    // ✅ Get full formatted price string
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->price
            ? number_format($this->price, 2) . ' ' . ($this->currency ?? 'BDT')
            : 'N/A'
        );
    }

    // ✅ Short description for listing
    protected function shortDescription(): Attribute
    {
        return Attribute::make(
            get: fn() => Str::limit(strip_tags($this->description ?? ''), 120)
        );
    }

    // ✅ Automatically ensure status capitalization
    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtolower($value)
        );
    }

    // Count comments (including replies)
    public function getCommentsCountAttribute(): int
    {
        return $this->allComments()->count();
    }

    /**
     * -------------------------------------------------------------------------
     * Query Scopes
     * -------------------------------------------------------------------------
     */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term) {
            $term = '%' . strtolower($term) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(title) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(note) LIKE ?', [$term]);
            });
        }
        return $query;
    }

    public function scopeCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('buy_sell_category_id', $categoryId);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * -------------------------------------------------------------------------
     * Utility Methods
     * -------------------------------------------------------------------------
     */

    // Primary image from BuySellImage model
    public function primaryImage(): ?BuySellImage
    {
        return $this->images()->where('is_primary', true)->first();
    }

    // Primary image URL from Spatie Media Library (backward compatibility)
    public function getPrimaryImageUrl($conversion = 'thumb')
    {
        // First check if we have images via BuySellImage model
        if ($this->images()->exists()) {
            $primaryImage = $this->images()->where('is_primary', true)->first();
            if ($primaryImage) {
                return $primaryImage->path ? asset($primaryImage->path) : null;
            }

            // If no primary image, get the first image
            $firstImage = $this->images()->first();
            if ($firstImage && $firstImage->path) {
                return asset($firstImage->path);
            }
        }

        // Fallback to Spatie Media Library
        $media = $this->getFirstMedia('posts');
        if ($media) {
            return $media->getUrl($conversion);
        }

        // Return placeholder
        return asset('images/placeholder.png');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}