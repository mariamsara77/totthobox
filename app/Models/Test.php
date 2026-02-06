<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'title',
        'subject_id',
        'class_level_id',
        'total_questions',
        'total_marks',
        'duration',
        'start_time',
        'end_time',
        'is_published',
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
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Relationships
     */

protected static function boot()
{
    parent::boot();

    static::creating(function ($test) {
        if (!$test->slug) {
            $test->slug = \Str::slug($test->title);
        }
    });
}



    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classLevel()
    {
        return $this->belongsTo(ClassLevel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }


    protected $dates = ['start_time', 'end_time'];


    public function questions()
    {
        return $this->belongsToMany(Question::class, 'test_questions')
            ->withPivot('order')
            ->orderBy('test_questions.order');
    }


    public function attempts()
    {
        return $this->hasMany(UserTestAttempt::class);
    }

    public function isActive()
    {
        $now = now();
        return $this->is_published &&
            (!$this->start_time || $this->start_time <= $now) &&
            (!$this->end_time || $this->end_time >= $now);
    }
}
