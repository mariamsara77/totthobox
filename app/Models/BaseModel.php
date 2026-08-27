<?php

namespace App\Models;

use App\Traits\AutoCacheable;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class BaseModel extends Model implements HasMedia, Viewable
{
    use AutoCacheable, InteractsWithMedia, InteractsWithViews, LogsActivity;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $table = $model->getTable();

            // ১. অটো স্লাগ লজিক (নাম বা টাইটেল থেকে)
            if (Schema::hasColumn($table, 'slug')) {
                if ($model->isDirty(['name', 'title']) || empty($model->slug)) {
                    $source = $model->name ?? $model->title ?? null;
                    if ($source) {
                        $model->slug = Str::slug($source);
                    }
                }

                // স্লাগ যেন একবার সেট হলে আপডেট করার সময় পরিবর্তন না হয় (ঐচ্ছিক নিরাপত্তা)
                if ($model->exists && $model->isDirty('slug')) {
                    $model->slug = $model->getOriginal('slug');
                }
            }

            // ২. ইউজার ট্র্যাকিং (Created By, Updated By)
            if (auth()->check()) {
                $userId = auth()->id();
                if (Schema::hasColumn($table, 'created_by') && !$model->exists) {
                    $model->created_by = $userId;
                }
                if (Schema::hasColumn($table, 'user_id') && !$model->exists) {
                    $model->user_id = $userId;
                }
                if (Schema::hasColumn($table, 'updated_by')) {
                    $model->updated_by = $userId;
                }
            }

            // ৩. IP ও User Agent ট্র্যাকিং
            if (Schema::hasColumn($table, 'ip_address') && !$model->exists) {
                $model->ip_address = request()->ip();
            }
            if (Schema::hasColumn($table, 'user_agent') && !$model->exists) {
                $model->user_agent = request()->userAgent();
            }
        });

        // ৪. সফট ডিলিট ট্র্যাকিং
        static::deleting(function ($model) {
            if (auth()->check() && Schema::hasColumn($model->getTable(), 'deleted_by')) {
                if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                    $model->updateQuietly(['deleted_by' => auth()->id()]);
                }
            }
        });
    }

    // --- গ্লোবাল মিডিয়া সেটিংস ---
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // থাম্বনেইল কনভার্সন
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 300, 300)
            ->sharpen(10)
            ->nonQueued();
    }

    // --- গ্লোবাল অ্যাক্টিভিটি লগ সেটিংস ---
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // --- গ্লোবাল রিলেশনস ---
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault(['name' => 'Admin']);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

}