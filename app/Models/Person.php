<?php

namespace App\Models;

use App\Traits\AutoCacheable;
use App\Traits\HasReactions;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Person extends Model implements HasMedia, Viewable
{
    use AutoCacheable;
    use HasFactory;
    use HasReactions;
    use InteractsWithMedia;
    use InteractsWithViews;
    use LogsActivity;
    use Searchable;
    use SoftDeletes;

    protected $table = 'people';

    protected $fillable = [
        'name',
        'slug',
        'bio',
        'date_of_birth',
        'date_of_death',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_death' => 'date',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            cache()->flush();
        });
        static::deleted(function () {
            cache()->flush();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /* -----------------------------------------------------------------
     |  Media Library (Multiple Image Support)
     | -----------------------------------------------------------------
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
        $this->addMediaCollection('default');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->format('webp')
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(800)
            ->sharpen(10)
            ->format('webp')
            ->nonQueued();
    }

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */
    public function peopleCategories()
    {
        return $this->belongsToMany(PeopleCategory::class, 'category_people', 'person_id', 'category_id');
    }

    public function histories()
    {
        return $this->hasMany(RoleHistory::class);
    }

    public function currentRole()
    {
        return $this->hasOne(RoleHistory::class)->where('is_current', true);
    }

    /* -----------------------------------------------------------------
     |  Scout
     | -----------------------------------------------------------------
     */
    public function searchableAs(): string
    {
        return 'people';
    }

    public function toSearchableArray(): array
{
    $this->loadMissing(['currentRole.position', 'peopleCategories']);

    $role = $this->currentRole;

    $currentRoleLabel = '';
    if ($role) {
        $currentRoleLabel = $role->custom_role
            ?? $role->position?->title
            ?? '';
    }

    return [
        'id' => (int) $this->id,
        'name' => $this->name,
        'slug' => $this->slug,
        'bio' => strip_tags($this->bio ?? ''),
        'current_role' => $currentRoleLabel,
        'categories' => $this->peopleCategories->pluck('name')->toArray(),
    ];
}

    public function shouldBeSearchable(): bool
    {
        return true;
    }
}