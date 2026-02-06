<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sign extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'signs';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'sign_category_id',
        'image',
        'name_bn',
        'name_en',
        'description_bn',
        'description_en',
        'details',
        'others',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Default attributes.
     */
    protected $attributes = [
        'status' => 1,
    ];

    /**
     * Relationships
     */

    // Category that this sign belongs to
    public function category()
    {
        return $this->belongsTo(SignCategory::class, 'sign_category_id');
    }


    // Audit users
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

    /**
     * Scopes
     */

    // Scope for only active signs
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // Scope for only inactive signs
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }
}
