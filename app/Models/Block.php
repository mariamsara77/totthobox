<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * যে ফিল্ডগুলো মাস অ্যাসাইনমেন্ট করা যাবে।
     */
    protected $fillable = [
        'user_id',
        'blocked_user_id',
    ];

    /**
     * যে ইউজার ব্লকটি করেছে (The blocker)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * যাকে ব্লক করা হয়েছে (The blocked user)
     */
    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }
}