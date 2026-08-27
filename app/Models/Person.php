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

    /* -----------------------------------------------------------------
      |  Activity Log
      | -----------------------------------------------------------------
      */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /* -----------------------------------------------------------------
     |  Media Library
     | -----------------------------------------------------------------
     */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10)
            ->format('webp')
            ->nonQueued();
    }

    public function peopleCategories()
    {
        return $this->belongsToMany(PeopleCategory::class, 'category_people', 'person_id', 'category_id');
    }

    public function roleHistories()
    {
        return $this->hasMany(RoleHistory::class);
    }

    // app/Models/Person.php

    public function histories()
    {
        return $this->hasMany(RoleHistory::class);
    }

    // বর্তমানে কোন পদে আছেন তা সহজে পাওয়ার জন্য
    public function currentRole()
    {
        return $this->hasOne(RoleHistory::class)->where('is_current', true)->withDefault([
            'custom_role' => 'কোন পদ নেই',
        ]);
    }

    public function role()
    {
        return $this->hasOne(RoleHistory::class);
    }

    // ── Meilisearch ───────────────────────────────────────────────────────────

    /**
     * What gets indexed in Meilisearch.
     *
     * Include both Bangla and English fields so search works in both scripts.
     * Also include relation names so "Dhaka division" searches find items
     * even when division data is denormalized here.
     */
    public function toSearchableArray(): array
    {
        // Person মডেলের প্রাসঙ্গিক রিলেশনগুলো লোড করুন
        $this->loadMissing(['currentRole', 'peopleCategories', 'histories']);

        return [
            'id' => (int) $this->id,
            'title' => $this->name, // এখানে 'title' কি হিসেবে 'name' কে ম্যাপ করা হয়েছে সার্চ ইনডেক্সের সুবিধার জন্য
            'slug' => $this->slug,
            'description' => strip_tags($this->bio ?? ''),
            // Person মডেলে status না থাকলে এটি বাদ দিন বা ডিফল্ট ১ দিন
            'status' => 1,

            // রিলেশনাল ডেটা ইনডেক্স করা (সার্চ রেজাল্ট উন্নত করতে)
            'current_role' => optional($this->currentRole)->custom_role ?? '',
            'categories' => $this->peopleCategories->pluck('name')->toArray(),
        ];
    }

    /**
     * যদি status কলাম না থাকে, তবে সরাসরি true রিটার্ন করুন অথবা
     * আপনার প্রয়োজন অনুযায়ী লজিক লিখুন।
     */
    public function shouldBeSearchable(): bool
    {
        return true;
    }
}
