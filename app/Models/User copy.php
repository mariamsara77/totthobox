<?php

namespace App\Models;

use App\Traits\UserRelationships;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasFactory, HasPushSubscriptions, HasRoles, InteractsWithMedia, LogsActivity, Notifiable, SoftDeletes, UserRelationships;

    protected $fillable = [
        'name', 'slug', 'email', 'password', 'phone', 'is_student', 'class_level_id',
        'google_id', 'avatar', 'location', 'division_id', 'district_id', 'thana_id',
        'address', 'profession', 'occupation', 'education', 'bio', 'note', 'status',
        'last_active_at', 'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $with = ['roles', 'permissions', 'media'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_active_at' => 'datetime',
        'is_student' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($user) => $user->slug = $user->slug ?: Str::slug($user->name).'-'.Str::lower(Str::random(6)));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->hasMedia('avatars')) {
            return $this->getFirstMediaUrl('avatars', 'thumb') ?: $this->getFirstMediaUrl('avatars');
        }

        return $this->avatar ? asset('storage/'.$this->avatar) :
               'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit(Fit::Crop, 100, 100)->format('webp')->nonQueued();
        $this->addMediaConversion('optimized')->fit(Fit::Crop, 400, 400)->format('webp')->quality(80)->sharpen(10)->nonQueued();
    }
}
