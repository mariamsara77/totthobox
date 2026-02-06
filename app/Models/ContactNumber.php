<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactNumber extends Model
{
    use SoftDeletes;

    protected $table = 'contact_numbers';

    // Mass assignable fields
    protected $fillable = [
        'contact_category_id',
        'division_id',
        'district_id',
        'thana_id',
        'unit_name',
        'area',
        'zone',
        'location',
        'name',
        'phone',
        'type',
        'designation',
        'alt_phone',
        'email',
        'address',
        'user_id',
        'status',
        'is_active',
        'is_featured',
        'extra_attributes',
        'created_by',
        'updated_by',
        'deleted_by',
        'published_at',
        'published_by',
        'view_count',
        'ip_address',
        'user_agent',
    ];

    // Casts
    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'extra_attributes' => 'array',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(ContactCategory::class, 'contact_category_id');
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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

    // Accessor example for extra attributes
    public function getExtraAttribute($key)
    {
        return $this->extra_attributes[$key] ?? null;
    }

    // Mutator example for extra attributes
    public function setExtraAttribute($key, $value)
    {
        $extra = $this->extra_attributes ?? [];
        $extra[$key] = $value;
        $this->extra_attributes = $extra;
    }
}
