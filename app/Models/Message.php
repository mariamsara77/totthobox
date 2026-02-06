<?php

namespace App\Models;

use App\Events\MessageSent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Message extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $guarded = ['id']; // fillable এর বদলে guarded ব্যবহার করা সহজ

    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
        'meta' => 'array',
    ];

    // ডিফল্টভাবে রিলেশন লোড করা (সাবধানে ব্যবহার করুন)
    // protected $with = ['sender']; 

    // --- Scopes (আপনার চ্যাট লোড করার সময় এটি ব্যবহার করবেন) ---

    public function scopeBetweenUsers($query, $user1, $user2)
    {
        // ইনডেক্স ব্যবহার নিশ্চিত করতে এই কুয়েরিটি সবচেয়ে কার্যকর
        return $query->where(function ($q) use ($user1, $user2) {
            $q->where('sender_id', $user1)->where('receiver_id', $user2);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('sender_id', $user2)->where('receiver_id', $user1);
        })->latest(); // সবসময় লেটেস্ট মেসেজ আগে আসবে
    }

    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    // --- Relationships ---

    public function sender()
    {
        // select(['id', 'name', 'avatar']) দিলে মেমোরি অনেক বেঁচে যায়
        return $this->belongsTo(User::class, 'sender_id')->withDefault([
            'name' => 'System User'
        ]);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // --- Helper Logic ---

    /**
     * মেসেজ এডিট করা হয়েছে কিনা
     */
    public function isEdited(): bool
    {
        return isset($this->meta['edited_at']);
    }

    /**
     * অ্যাটাচমেন্টের ধরন চেক করা
     */
    public function hasImage(): bool
    {
        return str_contains($this->attachment_type, 'image');
    }


    // Message.php মডেলের ভেতরে এটি যোগ করুন

    /**
     * রিপ্লাই মেসেজের ক্ষেত্রে মেইন মেসেজটি পাওয়ার জন্য
     */
    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    /**
     * একটি মেসেজের আন্ডারে কতগুলো রিপ্লাই আছে তা দেখার জন্য (ঐচ্ছিক)
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }


    // ইমেজ কনভার্সন (রিসাইজ করার জন্য)
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->nonQueued(); // এটি তৎক্ষণাৎ ফাইল জেনারেট করবে
    }

}