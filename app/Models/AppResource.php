<?php

namespace App\Models;

use App\Traits\AutoCacheable;
use App\Traits\HasReactions;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppResource extends Model implements HasMedia, Viewable
{
    use AutoCacheable, HasFactory, HasReactions, InteractsWithMedia, InteractsWithViews, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'version',
        'platform',
        'description',
        'download_type',
        'external_url',
        'download_password',
        'masked_extension',
        'download_count',
    ];

    // protected static function booted(): void
    // {
    //     static::saving(function ($app) {
    //         if (empty($app->slug) || $app->isDirty('name')) {
    //             $slug = Str::slug($app->name);
    //             $count = static::where('slug', 'LIKE', "{$slug}%")->where('id', '!=', $app->id ?? 0)->count();
    //             $app->slug = $count ? "{$slug}-{$count}" : $slug;
    //         }
    //     });
    // }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('app_files')->singleFile();
        $this->addMediaCollection('app_icons')->singleFile();
    }

    // থাম্বনেইল কনভার্সন যুক্ত করা হলো (পেইজ স্পিড বুস্ট করার জন্য)
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10)
            ->format('webp') // জেনুইন পারফরম্যান্সের জন্য webp বেস্ট
            ->nonQueued();   // আপলোডের সাথে সাথেই কনভার্ট হবে
    }
}
