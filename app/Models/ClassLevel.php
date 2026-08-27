<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ClassLevel extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'order',
        'is_active',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // Auto-generate slug on create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($classLevel) {
            if (empty($classLevel->slug)) {
                $classLevel->slug = Str::slug($classLevel->name).'-'.Str::random(5);
            }
        });

        static::saved(fn () => Cache::forget('active_class_levels'));
        static::deleted(fn () => Cache::forget('active_class_levels'));
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'class_level_id');
    }
}
