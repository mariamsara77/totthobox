<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'class_level_id',
        'name',
        'description',
        'is_active',
        'slug',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // Relationships
    public function classLevel()
    {
        return $this->belongsTo(ClassLevel::class);
    }

    public function tests()
    {
        return $this->hasMany(Test::class, 'subject_id');
    }
}
