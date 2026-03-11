<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

abstract class BaseModel extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $user = auth()->id();

                // চেক করে কলাম থাকলে ভ্যালু সেট করবে
                if (Schema::hasColumn($model->getTable(), 'created_by')) {
                    $model->created_by = $user;
                }
                if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                    $model->updated_by = $user;
                }
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                    $model->updated_by = auth()->id();
                }
            }
        });

        // SoftDeletes থাকলে deleted_by সেট করার জন্য
        static::deleting(function ($model) {
            if (auth()->check() && Schema::hasColumn($model->getTable(), 'deleted_by')) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept(['view_count'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
