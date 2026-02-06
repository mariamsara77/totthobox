<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SignCategory extends Model
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
        'image',
        'icon',
        'slug',
        'user_id',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'updated_by',
        'deleted_by',
        'published_at',
        'published_by',
        'view_count',
        'is_featured',
        'ip_address',
        'user_agent',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'status' => 'integer',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * Default attributes.
     */
    protected $attributes = [
        'status' => 0,
        'view_count' => 0,
        'is_featured' => false,
    ];

    /**
     * Relationships
     */

    public function signs()
    {
        return $this->hasMany(Sign::class, 'sign_category_id');
    }


    // Owner of the category
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Audit fields
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
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
