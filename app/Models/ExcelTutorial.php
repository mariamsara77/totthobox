<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ExcelTutorial extends BaseModel implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'chapter_name',
        'position',
        'description',
        'excel_formula',
        'is_published',
    ];

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
