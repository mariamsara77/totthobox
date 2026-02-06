<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ClassLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'order',
        'is_active',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'user_id',
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

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Auto-generate slug on create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($classLevel) {
            if (empty($classLevel->slug)) {
                $classLevel->slug = Str::slug($classLevel->name) . '-' . Str::random(5);
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'class_level_id');
    }
}
