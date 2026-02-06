<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'class_level_id',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'marks',
        'difficulty_level',
        'explanation',
        'is_active',
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
        'options' => 'array',
    ];
    /**
     * Relations
     */
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
        return $this->belongsTo(User::class);
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

    // question অনেক test–এ থাকবে
    public function tests()
    {
        return $this->belongsToMany(Test::class, 'test_question', 'question_id', 'test_id');
    }

    public function testQuestions()
    {
        return $this->belongsToMany(Test::class, 'test_question', 'question_id', 'test_id');
    }
}
