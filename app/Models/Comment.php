<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasReactions;

class Comment extends Model
{
    use HasUuids, SoftDeletes, HasReactions;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'content',
        'user_id',
        'parent_id',
        'depth',
        'commentable_id',
        'commentable_type'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Get the parent model (BuySell, Post, etc.)
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    // User who made the comment
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Deleted User',
            'email' => 'deleted@example.com'
        ]);
    }

    // Parent comment
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Child comments (replies)
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    // Scope for root comments (not replies)
    public function scopeRootComments($query)
    {
        return $query->whereNull('parent_id');
    }

    // Helper to check if comment is a reply
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }
}
