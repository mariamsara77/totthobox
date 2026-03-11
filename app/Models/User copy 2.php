<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasFactory, HasPushSubscriptions, HasRoles, InteractsWithMedia, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'password',
        'phone',
        'is_student',
        'class_level_id',
        'google_id',
        'avatar',
        'location',
        'division_id',
        'district_id',
        'thana_id',
        'address',
        'profession',
        'occupation',
        'education',
        'bio',
        'note',
        'status',
        'last_active_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $with = ['roles', 'permissions', 'media'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_active_at' => 'datetime',
        'is_student' => 'boolean',
    ];

    // User.php
    // User.php
    public function getAvatarUrlAttribute(): string
    {
        // ১. মিডিয়া কালেকশন মেমোরি থেকে চেক করা (hasMedia এর বদলে)
        $media = $this->getMedia('avatars');

        if ($media->isNotEmpty()) {
            // থাম্বনেইল চেক
            $url = $this->getFirstMediaUrl('avatars', 'thumb');

            return $url ?: $this->getFirstMediaUrl('avatars');
        }

        // ২. কলাম চেক
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        // ৩. ডিফল্ট
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    // --- Relationships ---
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function thana(): BelongsTo
    {
        return $this->belongsTo(Thana::class);
    }

    public function classLevel(): BelongsTo
    {
        return $this->belongsTo(ClassLevel::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function buysellposts(): HasMany
    {
        return $this->hasMany(Buysellpost::class);
    }

    // --- Messaging Relationships ---
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function unreadMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id')->unread();
    }

    // --- Blocking System ---
    public function blockedBy()
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocked_user_id', 'user_id');
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocks', 'user_id', 'blocked_user_id');
    }

    // --- Helpers & Logic ---
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            if (empty($user->slug)) {
                $user->slug = Str::slug($user->name).'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')->take(2)->map(fn ($w) => Str::substr($w, 0, 1))->implode('');
    }

    public function isOnline(): bool
    {
        return (bool) ($this->last_active_at?->gt(now()->subMinutes(5)));
    }

    // --- Scopes ---
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
    }

    // --- Media Library Config ---
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // টেবিল বা ছোট ভিউয়ের জন্য থাম্বনেইল
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 100, 100)
            ->format('webp')
            ->nonQueued();

        // প্রোফাইল পেজ বা বড় ভিউয়ের জন্য অপ্টিমাইজড ইমেজ
        $this->addMediaConversion('optimized')
            ->fit(Fit::Crop, 400, 400)
            ->format('webp') // WebP ফরম্যাট
            ->quality(80)    // ৮০% কোয়ালিটি (ব্যালেন্সড)
            ->sharpen(10)
            ->nonQueued();
    }
}