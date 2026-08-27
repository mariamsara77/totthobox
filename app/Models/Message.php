<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\ResponseCache\Facades\ResponseCache; // এরর সমাধানের জন্য এটি যুক্ত করা হয়েছে

class Message extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
        'updated_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(function ($model) {
            ResponseCache::clear();
        });

        static::deleted(function ($model) {
            ResponseCache::clear();
        });
    }

    // --- Spatie Activitylog ---
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // --- Scopes ---
    public function scopeBetweenUsers($query, $user1, $user2)
    {
        return $query->where(function ($q) use ($user1, $user2) {
            $q->where('sender_id', $user1)->where('receiver_id', $user2);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('sender_id', $user2)->where('receiver_id', $user1);
        })->latest();
    }

    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    // --- Relationships ---
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id')->withDefault([
            'name' => 'System User',
        ]);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    // --- Helper Logic ---
    public function isEdited(): bool
    {
        return $this->updated_at->gt($this->created_at);
    }

    public function hasImage(): bool
    {
        return str_contains($this->attachment_type ?? '', 'image');
    }

    // --- Media Library ---
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->nonQueued();
    }
}
