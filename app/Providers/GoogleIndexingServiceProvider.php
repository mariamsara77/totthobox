<?php

namespace App\Providers;

use App\Services\GoogleIndexingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class GoogleIndexingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /**
         * IndexUrlJob-এর RateLimited middleware এই নাম ব্যবহার করে।
         *
         * দুই লেয়ারে থ্রটল করা হচ্ছে:
         * 1) perMinute(10)  — Google-এর undocumented burst/QPS লিমিট থেকে বাঁচার জন্য,
         *    যাতে CSV থেকে একসাথে অনেক job dispatch হলেও queue worker গুলো এক নিমিষে
         *    ৪২৯ এরর খেয়ে জ্যাম না বেঁধে যায়।
         * 2) perDay(190)    — Google-এর দৈনিক ২০০ কোটার নিচে ১০-এর সেফটি বাফার রেখে।
         *
         * লিমিট শেষ হলে Laravel নিজে থেকেই জব release করে দেয় সঠিক সময় পরে —
         * ম্যানুয়ালি "কালকের জন্য নতুন জব dispatch করো" লজিক আর দরকার নেই।
         */
        RateLimiter::for(GoogleIndexingService::RATE_LIMITER_NAME, function () {
            $dailyQuota = max(1, (int) config('services.google.indexing_daily_quota', 200) - 10);

            return [
                Limit::perMinute(10)->by(GoogleIndexingService::RATE_LIMITER_BY_KEY),
                Limit::perDay($dailyQuota)->by(GoogleIndexingService::RATE_LIMITER_BY_KEY),
            ];
        });
    }
}
