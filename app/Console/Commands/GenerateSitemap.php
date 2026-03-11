<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
// আপনার মডেলগুলো এখানে ইমপোর্ট করুন
use Spatie\Sitemap\Tags\Url;

// উদাহরণস্বরূপ অন্য মডেলগুলো যেমন: Product, McqSubject ইত্যাদি

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the sitemap for Totthobox.';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // ১. স্ট্যাটিক পেজগুলো যুক্ত করা
        $sitemap->add(Url::create('/')->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/privacy-policy'))
            ->add(Url::create('/terms-of-service'))
            ->add(Url::create('/contact-us'))
            ->add(Url::create('/calendar'))
            ->add(Url::create('/calendar/holiday'));

        // ২. বাংলাদেশ কন্টেন্ট (যেহেতু এগুলো ফিক্সড রুট)
        $sitemap->add(Url::create('/bangladesh/introduction'))
            ->add(Url::create('/bangladesh/tourism'))
            ->add(Url::create('/bangladesh/history'))
            ->add(Url::create('/bangladesh/establishment'))
            ->add(Url::create('/bangladesh/minister'));

        // ৩. কনভার্টার পেজগুলো
        $converters = ['currency', 'length', 'weight', 'area', 'volume', 'temperature', 'speed', 'time', 'data', 'energy', 'land'];
        foreach ($converters as $type) {
            $sitemap->add(Url::create("/converter/{$type}"));
        }

        // ৪. ডাইনামিক ইউজার প্রোফাইল (আপনার রুটে /users/{slug} আছে)
        // ধরুন User মডেলে slug ফিল্ড আছে
        User::all()->each(function (User $user) use ($sitemap) {
            if ($user->slug) {
                $sitemap->add(Url::create("/users/{$user->slug}"));
            }
        });

        /* ৫. একইভাবে আপনার অন্যান্য ডাইনামিক মডেলগুলো যোগ করুন। উদাহরণ:

        Product::all()->each(function ($product) use ($sitemap) {
            $sitemap->add(Url::create("/buysell/prodict/{$product->slug}"));
        });

        McqSubject::all()->each(function ($subject) use ($sitemap) {
            $sitemap->add(Url::create("/mcq/subject/{$subject->slug}"));
        });
        */

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Totthobox Sitemap generated successfully!');
    }
}
