<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    // নোট: লারাভেলের ডিফল্ট নোটিফিকেশন টেবিলে প্রাইমারি-কি (id) UUID হয়।
    // যদি আপনার টেবিলটি ডিফল্ট হয়, তবে নিচের ২টি লাইন আনকমেন্ট করবেন:
    // public $incrementing = false;
    // protected $keyType = 'string';

    protected $guarded = ['id']; // fillable এর বদলে guarded

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
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

    // --- Relationships & Accessors ---
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getSenderUserAttribute()
    {
        $senderId = $this->data['sender_id'] ?? null;

        // ডাটাবেসে অপ্রয়োজনীয় কুয়েরি রোধ করতে আগে চেক করে নেওয়া ভালো
        return $senderId ? User::find($senderId) : null;
    }
}
