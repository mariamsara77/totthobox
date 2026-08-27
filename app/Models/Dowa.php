<?php

namespace App\Models;

use App\Traits\HasReactions;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\AutoCacheable;

class Dowa extends Model implements HasMedia, Viewable
{
    use HasFactory, AutoCacheable, HasReactions, InteractsWithMedia, InteractsWithViews, LogsActivity, SoftDeletes;

    protected $fillable = [
        'bangla_name',
        'arabic_name',
        'arabic_text',
        'bangla_text',
        'bangla_meaning',
        'bangla_fojilot',
        'audio',
        'others',
        'type',
        'tags',
        'slug',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'status' => 'integer',
            'tags' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('audio')->singleFile();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function incrementViews(): void
    {
        $this->view_count++;
        $this->save();
    }
}
