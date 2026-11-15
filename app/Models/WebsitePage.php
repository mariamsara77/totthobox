<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class WebsitePage extends Model
{
    protected $fillable = [
        'url',
        'title',
        'content',
        'images',
        'meta',
        'content_hash',
        'last_crawled_at',
        'is_indexed',
    ];

    protected $casts = [
        'images'          => 'array',
        'meta'            => 'array',
        'last_crawled_at' => 'datetime',
        'is_indexed'      => 'boolean',
    ];

    /**
     * ভবিষ্যতে Laravel Scout (Meilisearch/Typesense) যোগ করলে
     * শুধু এই treit টা আনকমেন্ট করলেই হবে।
     */
    // use \Laravel\Scout\Searchable;

    /**
     * Scout ব্যবহার করলে কোন কোন ফিল্ড ইনডেক্স হবে
     * (এখন কমেন্ট করে রাখা হয়েছে)
     */
    // public function toSearchableArray(): array
    // {
    //     return [
    //         'id'      => $this->id,
    //         'url'     => $this->url,
    //         'title'   => $this->title,
    //         'content' => Str::limit($this->content, 8000),
    //         'images'  => $this->images,
    //     ];
    // }

    /**
     * সহজ সার্চ স্কোপ (এখনকার জন্য)
     * পরে Scout এ গেলে এটা আর লাগবে না
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return $query;
        }

        return $query->whereFullText(['title', 'content'], $keyword)
            ->orWhere('title', 'like', "%{$keyword}%")
            ->orWhere('url', 'like', "%{$keyword}%");
    }

    /**
     * শুধু সাম্প্রতিক ক্রল করা পেজ
     */
    public function scopeRecentlyCrawled(Builder $query, int $days = 20): Builder
    {
        return $query->where('last_crawled_at', '>=', now()->subDays($days));
    }

    /**
     * ইমেজ আছে কিনা
     */
    public function scopeHasImages(Builder $query): Builder
    {
        return $query->whereNotNull('images')
            ->whereJsonLength('images', '>', 0);
    }

    /**
     * কনটেন্টের সংক্ষিপ্ত অংশ (AI context এর জন্য সুবিধাজনক)
     */
    public function getShortContentAttribute(): string
    {
        return Str::limit(strip_tags($this->content ?? ''), 1500);
    }

    /**
     * প্রথম ইমেজ URL (থাম্বনেইল হিসেবে ব্যবহার করা যায়)
     */
    public function getThumbnailAttribute(): ?string
    {
        return $this->images[0]['url'] ?? null;
    }
}