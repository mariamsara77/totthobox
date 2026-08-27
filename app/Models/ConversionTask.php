<?php

// app/Models/ConversionTask.php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConversionStatus;
use App\Enums\ConversionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class ConversionTask extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'source_format',
        'target_format',
        'status',
        'original_filename',
        'error_message',
    ];

    protected $casts = [
        'type' => ConversionType::class,
        'status' => ConversionStatus::class,
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('original_files')->singleFile();
        $this->addMediaCollection('converted_files')->singleFile();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'type', 'source_format', 'target_format', 'original_filename', 'error_message'])
            ->logOnlyDirty()
            ->useLogName('conversion_task')
            ->setDescriptionForEvent(fn (string $eventName): string => "Conversion task {$eventName}");
    }
}
