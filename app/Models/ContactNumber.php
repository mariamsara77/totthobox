<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ContactNumber extends BaseModel
{
    use SoftDeletes;

    protected $table = 'contact_numbers';

    // Mass assignable fields
    protected $fillable = [
        'id',
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
        'status',
        'is_active',
        'is_featured',
    ];

    // Casts
    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
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
}
