<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_session_id',
        'role',
        'image_path',
        'content',
    ];

    // ✅ fix: Laravel-এর default foreign key 'chat_session_id' কিন্তু
    // ChatSession::messages() hasMany করার সময় এটা match করতে হবে
    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
}