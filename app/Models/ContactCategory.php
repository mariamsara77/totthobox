<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// যদি schemaless attributes ব্যবহার করতে চান
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

class ContactCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contact_categories';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'is_active',
        'is_featured',
        'status',
        'extra_attributes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'extra_attributes' => SchemalessAttributes::class, // অথবা 'array'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Mutators
    |--------------------------------------------------------------------------
    */

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon ? asset('storage/icons/' . $this->icon) : null;
    }
}
