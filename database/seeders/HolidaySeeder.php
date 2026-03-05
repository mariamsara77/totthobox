<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    public function run()
    {
        \App\Models\Holiday::truncate();
        
        $holidays = [
            // --- সাধারণ সরকারি ছুটি (Public Holidays) ---
            ['title' => 'শহীদ দিবস ও আন্তর্জাতিক মাতৃভাষা দিবস', 'date' => '2026-02-21', 'type' => 'সাধারণ ছুটি', 'featured' => true],
            ['title' => 'জাতির পিতা বঙ্গবন্ধু শেখ মুজিবুর রহমান-এর জন্ম দিবস ও জাতীয় শিশু দিবস', 'date' => '2026-03-17', 'type' => 'সাধারণ ছুটি', 'featured' => true],
            ['title' => 'স্বাধীনতা ও জাতীয় দিবস', 'date' => '2026-03-26', 'type' => 'সাধারণ ছুটি', 'featured' => true],
            ['title' => 'ঈদুল ফিতর (সম্ভাব্য তারিখ)', 'date' => '2026-03-20', 'type' => 'সাধারণ ছুটি', 'featured' => true],
            ['title' => 'ঈদুল ফিতর (পরের দিন)', 'date' => '2026-03-21', 'type' => 'সাধারণ ছুটি', 'featured' => false],
            ['title' => 'মে দিবস', 'date' => '2026-05-01', 'type' => 'সাধারণ ছুটি', 'featured' => false],
            ['title' => 'বুদ্ধ পূর্ণিমা (বৈশাখী পূর্ণিমা)', 'date' => '2026-05-01', 'type' => 'সাধারণ ছুটি', 'featured' => false],
            ['title' => 'ঈদুল আযহা (সম্ভাব্য তারিখ)', 'date' => '2026-05-27', 'type' => 'সাধারণ ছুটি', 'featured' => true],
            ['title' => 'ঈদুল আযহা (পরের দিন)', 'date' => '2026-05-28', 'type' => 'সাধারণ ছুটি', 'featured' => false],
            ['title' => 'জাতীয় শোক দিবস', 'date' => '2026-08-15', 'type' => 'সাধারণ ছুটি', 'featured' => true],
            ['title' => 'শ্রী কৃষ্ণের জন্মাষ্টমী', 'date' => '2026-09-03', 'type' => 'সাধারণ ছুটি', 'featured' => false],
            ['title' => 'ঈদে মিলাদুন্নবী (সা.) (সম্ভাব্য তারিখ)', 'date' => '2026-08-25', 'type' => 'সাধারণ ছুটি', 'featured' => true],
            ['title' => 'শারদীয় দুর্গাপূজা (বিজয়া দশমী)', 'date' => '2026-10-20', 'type' => 'সাধারণ ছুটি', 'featured' => true],
            ['title' => 'বিজয় দিবস', 'date' => '2026-12-16', 'type' => 'সাধারণ ছুটি', 'featured' => true],
            ['title' => 'যিশু খ্রিস্টের জন্মদিন (বড় দিন)', 'date' => '2026-12-25', 'type' => 'সাধারণ ছুটি', 'featured' => true],

            // --- নির্বাহী আদেশে সরকারি ছুটি (Executive Order Holidays) ---
            ['title' => 'শবে বরাত (সম্ভাব্য তারিখ)', 'date' => '2026-03-03', 'type' => 'নির্বাহী আদেশে ছুটি', 'featured' => false],
            ['title' => 'শবে কদর (সম্ভাব্য তারিখ)', 'date' => '2026-03-15', 'type' => 'নির্বাহী আদেশে ছুটি', 'featured' => false],
            ['title' => 'ঈদুল ফিতরের আগের দিন', 'date' => '2026-03-19', 'type' => 'নির্বাহী আদেশে ছুটি', 'featured' => false],
            ['title' => 'পহেলা বৈশাখ (বাংলা নববর্ষ)', 'date' => '2026-04-14', 'type' => 'নির্বাহী আ আদেশ ছুটি', 'featured' => true],
            ['title' => 'ঈদুল আযহার আগের দিন', 'date' => '2026-05-26', 'type' => 'নির্বাহী আদেশে ছুটি', 'featured' => false],
            ['title' => 'আশুরা (সম্ভাব্য তারিখ)', 'date' => '2026-07-26', 'type' => 'নির্বাহী আদেশে ছুটি', 'featured' => false],

            // --- ঐচ্ছিক ছুটি: মুসলিম (Optional Muslim Holidays) ---
            ['title' => 'শবে মেরাজ (সম্ভাব্য তারিখ)', 'date' => '2026-01-16', 'type' => 'ঐচ্ছিক ছুটি (মুসলিম)', 'featured' => false],
            ['title' => 'ঈদুল ফিতরের ৩য় দিন', 'date' => '2026-03-22', 'type' => 'ঐচ্ছিক ছুটি (মুসলিম)', 'featured' => false],
            ['title' => 'ঈদুল আযহার ৩য় দিন', 'date' => '2026-05-29', 'type' => 'ঐচ্ছিক ছুটি (মুসলিম)', 'featured' => false],
            ['title' => 'ফাতেহা-ই-ইয়াজদাহম', 'date' => '2026-09-23', 'type' => 'ঐচ্ছিক ছুটি (মুসলিম)', 'featured' => false],
            ['title' => 'আখেরী চাহার সোম্বা', 'date' => '2026-09-09', 'type' => 'ঐচ্ছিক ছুটি (মুসলিম)', 'featured' => false],

            // --- ঐচ্ছিক ছুটি: হিন্দু (Optional Hindu Holidays) ---
            ['title' => 'সরস্বতী পূজা', 'date' => '2026-01-24', 'type' => 'ঐচ্ছিক ছুটি (হিন্দু)', 'featured' => false],
            ['title' => 'শিবরাত্রি ব্রত', 'date' => '2026-02-15', 'type' => 'ঐচ্ছিক ছুটি (হিন্দু)', 'featured' => false],
            ['title' => 'দোলযাত্রা', 'date' => '2026-03-04', 'type' => 'ঐচ্ছিক ছুটি (হিন্দু)', 'featured' => false],
            ['title' => 'হরিচাঁদ ঠাকুরের আবির্ভাব দিবস', 'date' => '2026-03-17', 'type' => 'ঐচ্ছিক ছুটি (হিন্দু)', 'featured' => false],
            ['title' => 'মহালয়া', 'date' => '2026-10-10', 'type' => 'ঐচ্ছিক ছুটি (হিন্দু)', 'featured' => false],
            ['title' => 'শারদীয় দুর্গাপূজা (অষ্টমী ও নবমী)', 'date' => '2026-10-19', 'type' => 'ঐচ্ছিক ছুটি (হিন্দু)', 'featured' => false],
            ['title' => 'লক্ষ্মী পূজা', 'date' => '2026-10-25', 'type' => 'ঐচ্ছিক ছুটি (হিন্দু)', 'featured' => false],
            ['title' => 'শ্যামা পূজা', 'date' => '2026-11-08', 'type' => 'ঐচ্ছিক ছুটি (হিন্দু)', 'featured' => false],

            // --- ঐচ্ছিক ছুটি: খ্রিস্টান (Optional Christian Holidays) ---
            ['title' => 'ইংরেজি নববর্ষ', 'date' => '2026-01-01', 'type' => 'ঐচ্ছিক ছুটি (খ্রিস্টান)', 'featured' => false],
            ['title' => 'ভস্ম বুধবার', 'date' => '2026-02-18', 'type' => 'ঐচ্ছিক ছুটি (খ্রিস্টান)', 'featured' => false],
            ['title' => 'পুণ্য বৃহস্পতিবার', 'date' => '2026-04-02', 'type' => 'ঐচ্ছিক ছুটি (খ্রিস্টান)', 'featured' => false],
            ['title' => 'পুণ্য শুক্রবার', 'date' => '2026-04-03', 'type' => 'ঐচ্ছিক ছুটি (খ্রিস্টান)', 'featured' => false],
            ['title' => 'পুণ্য শনিবার', 'date' => '2026-04-04', 'type' => 'ঐচ্ছিক ছুটি (খ্রিস্টান)', 'featured' => false],
            ['title' => 'ইস্টার সানডে', 'date' => '2026-04-05', 'type' => 'ঐচ্ছিক ছুটি (খ্রিস্টান)', 'featured' => false],
            ['title' => 'বড় দিনের আগের ও পরের দিন (২৪ ও ২৬ ডিসেম্বর)', 'date' => '2026-12-24', 'type' => 'ঐচ্ছিক ছুটি (খ্রিস্টান)', 'featured' => false],

            // --- ঐচ্ছিক ছুটি: বৌদ্ধ (Optional Buddhist Holidays) ---
            ['title' => 'মাঘী পূর্ণিমা', 'date' => '2026-02-01', 'type' => 'ঐচ্ছিক ছুটি (বৌদ্ধ)', 'featured' => false],
            ['title' => 'চৈত্র সংক্রান্তি', 'date' => '2026-04-13', 'type' => 'ঐচ্ছিক ছুটি (বৌদ্ধ)', 'featured' => false],
            ['title' => 'আষাঢ়ী পূর্ণিমা', 'date' => '2026-07-28', 'type' => 'ঐচ্ছিক ছুটি (বৌদ্ধ)', 'featured' => false],
            ['title' => 'মধু পূর্ণিমা', 'date' => '2026-09-25', 'type' => 'ঐচ্ছিক ছুটি (বৌদ্ধ)', 'featured' => false],
            ['title' => 'প্রবারণা পূর্ণিমা (আশ্বিনী পূর্ণিমা)', 'date' => '2026-10-25', 'type' => 'ঐচ্ছিক ছুটি (বৌদ্ধ)', 'featured' => false],

            // --- পার্বত্য চট্টগ্রাম ও ক্ষুদ্র নৃ-গোষ্ঠী (Tribal Holidays) ---
            ['title' => 'বৈসাবি/বিঝু/সাংগ্রাই (ক্ষুদ্র নৃ-গোষ্ঠীর উৎসব)', 'date' => '2026-04-13', 'type' => 'আঞ্চলিক ছুটি', 'featured' => false],
            ['title' => 'বৈসাবি উৎসবের দ্বিতীয় দিন', 'date' => '2026-04-15', 'type' => 'আঞ্চলিক ছুটি', 'featured' => false],
        ];

        foreach ($holidays as $holiday) {
            // আধুনিক স্লাগ জেনারেশন (তারিখ + ইউনিক স্ট্রিং)
            $slug = 'chuti-' . $holiday['date'] . '-' . Str::random(5);

            Holiday::updateOrCreate(
                ['date' => $holiday['date'], 'title' => $holiday['title']],
                [
                    'type' => $holiday['type'],
                    'slug' => $slug,
                    'details' => $holiday['title'] . ' উপলক্ষে ২০২৬ সালের নির্ধারিত সরকারি ছুটি।',
                    'status' => 1,
                    'is_featured' => $holiday['featured'],
                    'user_id' => 1,
                    'created_by' => 1,
                    'meta_title' => $holiday['title'] . ' - ২০২৬ সালের ছুটির তালিকা',
                    'meta_description' => $holiday['title'] . ' উপলক্ষে ছুটির বিস্তারিত তথ্য।',
                    'published_at' => Carbon::now(),
                    'published_by' => 1,
                    'view_count' => 0,
                ]
            );
        }
    }
}