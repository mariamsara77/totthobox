<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Nutrient extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'nutrients';

    protected $fillable = [
        'name_bn',
        'name_en',
        'slug',
        'unit',
        'description',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'status' => 'integer',
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

    // Spatie Media Collection
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('nutrient_images')
            ->singleFile();
    }

    // Relations
    public function foods()
    {
        return $this->belongsToMany(Food::class, 'food_nutrients')
            ->withPivot('amount', 'note')
            ->withTimestamps()
            ->using(FoodNutrient::class);
    }
}
