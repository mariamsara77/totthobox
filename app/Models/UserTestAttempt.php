<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class UserTestAttempt extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'user_id',
        'test_id',
        'started_at',
        'completed_at',
        'score',
        'correct_answers',
        'wrong_answers',
        'answers',
        'slug',
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
        'answers' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'score' => 'integer',
        'correct_answers' => 'integer',
        'wrong_answers' => 'integer',
        'view_count' => 'integer',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
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
     * Helper methods
     */
    public function isCompleted()
    {
        return !is_null($this->completed_at);
    }

    public function percentage()
    {
        return $this->test && $this->test->total_marks > 0
            ? round(($this->score / $this->test->total_marks) * 100, 2)
            : 0;
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = self::generateUniqueSlug($model);
            }
        });
    }

    protected static function generateUniqueSlug($model)
    {
        // Create a base slug from user_id and test_id (or any field)
        $baseSlug = Str::slug("attempt-{$model->user_id}-{$model->test_id}-" . now()->timestamp);
        $slug = $baseSlug;
        $count = 1;

        // Ensure uniqueness in DB
        while (self::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
