<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait AutoCacheable
{
    // মডেলের নাম বা টেবিলের নাম থেকে ডায়নামিক কী তৈরি করবে
    public static function getCacheKey()
    {
        return (new static)->getTable() . '_all_data';
    }

    public static function forgetCache()
    {
        Cache::forget(self::getCacheKey());
    }

    public static function bootAutoCacheable()
    {
        // যখনই কোনো ডাটা সেভ, আপডেট বা ডিলিট হবে, 
        // পুরনো ক্যাশ ফাইলটি ডিলিট হয়ে যাবে।
        static::saved(fn($model) => Cache::forget($model->getTable() . '_all_data'));
        static::deleted(fn($model) => Cache::forget($model->getTable() . '_all_data'));
    }
}