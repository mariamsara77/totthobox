<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SignCategory extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'sign_categories';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'title',
        'short_title',
        'short_description',
        'long_description',
        'description',
        'icon',
        'slug',
        'status',
        'is_featured',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'status' => 'integer',
        'is_featured' => 'boolean',
    ];

    /**
     * Default attributes.
     */
    protected $attributes = [
        'status' => 0,
        'is_featured' => false,
    ];

    protected static function booted()
    {
        static::saved(fn ($cat) => cache()->forget("sign_page_{$cat->slug}"));
    }

    /**
     * Relationships
     */
    public function signs()
    {
        return $this->hasMany(Sign::class);
    }

    /**
     * Scopes
     */

    // Scope for published categories
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    // Scope for featured categories
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Scope for active categories (status = 1)
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
