<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Inspiring;

// ডিফল্ট ইন্সপায়ার কমান্ড
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// উন্নত সুপার ক্লিন কমান্ড (Fix & Optimized)
Artisan::command('super:clean', function () {
    $this->warn('Starting optimization process...');

    // লারাভেলের স্ট্যান্ডার্ড ওয়েতে মেসেজ এবং কল করা
    $this->info('1. Clearing system cache and optimization...');
    Artisan::call('optimize:clear');
    $this->line(Artisan::output());

    // ক্যাশ করার কমান্ডগুলো রান করা (যাতে সাইট ফাস্ট হয়)
    $this->info('2. Re-building configuration cache...');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');

    // নোট: composer dump-autoload শুধুমাত্র টার্মিনালে ম্যানুয়ালি চালানো ভালো। 
    // যদি খুব দরকার হয় তবে নিচের লাইনটি আন-কমেন্ট করতে পারেন, তবে এটি সাইট স্লো করবে।
    // exec('composer dump-autoload');

    $this->info('Congratulations! Your project is fully optimized.');
})->purpose('Clean cache, re-cache files for better performance');