<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Food extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'foods';

    protected $fillable = [
        'name_bn', 'name_en', 'slug', 'description', 'calorie',
        'carb', 'protein', 'fat', 'fiber', 'serving_size',
        'food_category_id', 'status', 'is_featured',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    // Spatie Activitylog Options
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relations
    public function category()
    {
        return $this->belongsTo(FoodCategory::class, 'food_category_id');
    }

    public function nutrients()
    {
        return $this->belongsToMany(Nutrient::class, 'food_nutrients')
            ->using(FoodNutrient::class)
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function vitamins()
    {
        return $this->belongsToMany(Vitamin::class, 'food_vitamins')
            ->using(FoodVitamin::class)
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function scopeSearch($query, $term)
    {
        $term = "%$term%";

        return $query->where('name_bn', 'like', $term)
            ->orWhere('name_en', 'like', $term)
            ->orWhere('description', 'like', $term);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('food_images')
            ->singleFile(); // যদি একাধিক ছবি চান, তাহলে singleFile() রিমুভ করবেন
    }
}
