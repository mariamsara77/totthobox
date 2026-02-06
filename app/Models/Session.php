<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; // User মডেলটি ইমপোর্ট করুন
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $table = 'sessions';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // এই অংশটুকু যোগ করুন
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
