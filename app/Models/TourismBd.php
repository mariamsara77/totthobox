<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TourismBd extends BaseModel implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title', 'tourism_type', 'description', 'image', 'map', 'division_id', 'district_id', 'thana_id',
        'slug', 'user_id', 'status', 'meta_title', 'meta_description', 'meta_keywords',
        'created_by', 'updated_by', 'deleted_by', 'published_at', 'published_by',
        'view_count', 'is_featured', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'status' => 'integer',
        'view_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // ১. ডাটা তৈরির সময় অটো-সেটআপ
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->user_id = Auth::id();
            }

            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }

            // রিকোয়েস্ট থেকে IP এবং User Agent নেওয়া
            $model->ip_address = request()->ip();
            $model->user_agent = request()->userAgent();
        });

        // ২. ডাটা আপডেট করার সময় অটো-সেটআপ
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            // Slug যেন সারাজীবনেও পরিবর্তন না হয়
            if ($model->isDirty('slug')) {
                $model->slug = $model->getOriginal('slug');
            }
        });

        // ৩. সফট ডিলিট করার সময় কে ডিলিট করলো তা ট্র্যাক করা
        static::deleting(function ($model) {
            // যদি এটি ফোর্স ডিলিট না হয়, তবে কে ডিলিট করলো তা সেভ করো
            if (Auth::check() && ! $model->isForceDeleting()) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }

    // --- Media Library Settings ---
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 300, 300)
            ->sharpen(10)
            ->nonQueued();
    }

    // --- Relationships (পরিপূর্ণ রিলেশনস) ---

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }

    // মেইন ইউজার (মালিক)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // অডিট রিলেশনস
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault(['name' => 'Unknown']);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}