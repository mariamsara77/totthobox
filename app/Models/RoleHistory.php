<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleHistory extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'person_id',
        'position_id',
        'custom_role',
        'from_date',
        'to_date',
        'is_current',
        'division_id',
        'district_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_current' => 'boolean',
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}