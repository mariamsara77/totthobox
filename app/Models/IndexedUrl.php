<?php

namespace App\Models;

use App\Enums\IndexingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndexedUrl extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'url',
        'last_crawled',
        'is_indexed',
        'status',
        'source',
        'last_pushed_at',
        'error_message',
    ];

    protected $casts = [
        'last_crawled' => 'date',
        'is_indexed' => 'boolean',
        'status' => IndexingStatus::class,
        'last_pushed_at' => 'datetime',
    ];

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', IndexingStatus::Pending);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', IndexingStatus::Failed);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', IndexingStatus::Success);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        // % ও _ কে literal হিসেবে escape করা হচ্ছে যাতে LIKE wildcard injection না হয়
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);

        return $query->where('url', 'like', "%{$escaped}%");
    }
}