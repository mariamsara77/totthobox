<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuySellImage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'buy_sell_post_id',
        'path',
        'disk',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relationships
     */

    public function post(): BelongsTo
    {
        return $this->belongsTo(BuySellPost::class, 'buy_sell_post_id');
    }

    /**
     * Accessors
     */

    public function getUrlAttribute(): string
    {
        return asset("storage/{$this->path}");
    }

    /**
     * Scopes
     */

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
