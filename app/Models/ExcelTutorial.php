<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\SoftDeletes;

class ExcelTutorial extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'chapter_name',
        'position',
        'description',
        'excel_formula',
        'meta_title',
        'meta_description',
        'is_published'
    ];

    // অটোমেটিক স্লাগ তৈরি (SEO এর জন্য)
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }

    /**
     * Spatie Media Collections
     */
    public function registerMediaCollections(): void
    {
        // টিউটোরিয়ালের ভেতরের মেইন স্ক্রিনশট বা ব্যানার
        $this->addMediaCollection('lesson_image')->singleFile();

        // ইউজারদের জন্য ডাউনলোডযোগ্য এক্সেল ফাইল
        $this->addMediaCollection('downloadable_files');
    }
}