<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app.header')] class extends Component {}; ?>

<x-seo title="গোপনীয়তা নীতি (Privacy Policy)"
    description="Totthobox-এর সম্পূর্ণ গোপনীয়তা নীতি। আমরা কীভাবে আপনার ব্যক্তিগত তথ্য, কুকিজ এবং Google AdSense-এর মাধ্যমে তথ্য সংগ্রহ, ব্যবহার ও সুরক্ষা করি তা বিস্তারিত জানুন।"
    keywords="গোপনীয়তা নীতি, privacy policy, Totthobox privacy, Google AdSense privacy, কুকিজ নীতি, তথ্য সুরক্ষা, data protection Bangladesh" />

<section>
    <div class="max-w-3xl mx-auto space-y-6 py-6">

        {{-- Header --}}
        <div class="text-center mb-20">
            <flux:heading size="xl" level="1" class="mb-3">গোপনীয়তা নীতি (Privacy Policy)</flux:heading>
            <flux:subheading class="text-base">
                Totthobox - আপনার ব্যক্তিগত তথ্যের সুরক্ষা আমাদের সর্বোচ্চ অগ্রাধিকার
            </flux:subheading>
            <div class="mt-4">
                <flux:separator variant="subtle" />
            </div>
            <flux:text class="mt-4 text-sm text-zinc-500">
                সর্বশেষ আপডেট: {{ now()->format('d F, Y') }}
            </flux:text>
        </div>

        {{-- Introduction --}}
        <section class="space-y-4">
            <flux:heading level="2">ভূমিকা</flux:heading>
            <flux:text class="leading-relaxed">
                <strong>Totthobox</strong>-এ আপনাকে স্বাগতম। আমরা (<a href="https://totthobox.com"
                    class="-600 hover:underline">totthobox.com</a>) আমাদের ব্যবহারকারীদের গোপনীয়তা রক্ষায়
                পুরোপুরি প্রতিশ্রুতিবদ্ধ। এই গোপনীয়তা নীতিতে আমরা ব্যাখ্যা করেছি যে আমরা কী ধরনের তথ্য সংগ্রহ করি,
                কীভাবে তা ব্যবহার করি, কাকে শেয়ার করি এবং কীভাবে আপনার তথ্য সুরক্ষিত রাখি।
            </flux:text>
            <flux:text class="leading-relaxed">
                আমাদের ওয়েবসাইট বা যেকোনো সেবা ব্যবহার করার মাধ্যমে আপনি এই গোপনীয়তা নীতির সাথে একমত পোষণ করছেন। যদি
                আপনি এই নীতির কোনো অংশের সাথে একমত না হন, তাহলে অনুগ্রহ করে আমাদের সেবা ব্যবহার করবেন না।
            </flux:text>
        </section>

        {{-- Services Overview --}}
        <flux:card class="p-6 bg-zinc-50 dark:bg-zinc-800/50">
            <flux:heading level="3" class="mb-4 text-primary-600">আমাদের মূল সেবাসমূহ</flux:heading>
            <flux:text class="mb-5">
                Totthobox আপনার দৈনন্দিন প্রয়োজনীয় তথ্য, টুলস ও ডিজিটাল সেবা এক জায়গায় প্রদান করে। আমাদের প্রধান
                সেবাগুলো হলো:
            </flux:text>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>বাংলা ক্যালেন্ডার</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>কনভার্টার টুলস</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>বিভিন্ন ইউটিলিটি টুলস</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>সফটওয়্যার পরিচিতি</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>জরুরি সেবা ও হেল্পলাইন</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>বাংলাদেশ বিষয়ক তথ্য</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>বিশ্বকোষ</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>ইসলামিক বিষয়বস্তু</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>শিশুশিক্ষা</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>স্বাস্থ্য ও ট্রাফিক সংকেত</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>সর্বশেষ সংবাদ</flux:text>
                </div>
                <div class="flex items-start gap-4">
                    <flux:icon.check-circle variant="micro" class="text-green-500 mt-0.5 shrink-0" />
                    <flux:text>Totthobox AI চ্যাটবট</flux:text>
                </div>
            </div>
        </flux:card>

        {{-- Tools & Converters --}}
        <flux:card class="p-6">
            <flux:heading level="3" class="mb-4">টুলস ও কনভার্টারসমূহ</flux:heading>

            <flux:text class="font-medium mb-2">ইউটিলিটি টুলস:</flux:text>
            <div class="flex flex-wrap gap-2 mb-5">
                <flux:badge>Image Resizer</flux:badge>
                <flux:badge>Age Calculator</flux:badge>
                <flux:badge>Word & Character Counter</flux:badge>
                <flux:badge>Zodiac (রাশি) Calculator</flux:badge>
                <flux:badge>Percentage Calculator</flux:badge>
                <flux:badge>QR Code Generator</flux:badge>
            </div>

            <flux:text class="font-medium mb-2">কনভার্টারসমূহ:</flux:text>
            <div class="flex flex-wrap gap-4">
                <flux:badge>Number to Word</flux:badge>
                <flux:badge>অদর্শলিপি কনভার্টার</flux:badge>
                <flux:badge>Image Converter</flux:badge>
                <flux:badge>Documents Converter</flux:badge>
                <flux:badge>Media Converter</flux:badge>
                <flux:badge>Data File Converter</flux:badge>
                <flux:badge>মুদ্রা কনভার্টার</flux:badge>
                <flux:badge>দৈর্ঘ্য কনভার্টার</flux:badge>
                <flux:badge>ওজন কনভার্টার</flux:badge>
                <flux:badge>পরিমাণ কনভার্টার</flux:badge>
            </div>
        </flux:card>

        {{-- 1. Information We Collect --}}
        <section class="space-y-4">
            <flux:heading level="2">১. আমরা কী ধরনের তথ্য সংগ্রহ করি</flux:heading>

            <flux:heading level="3" class="mt-4">ক. আপনার দেওয়া তথ্য</flux:heading>
            <flux:text>
                আপনি যখন যোগাযোগ ফর্ম পূরণ করেন, Totthobox AI ব্যবহার করেন বা কোনো ফিডব্যাক দেন, তখন আমরা নিম্নলিখিত
                তথ্য সংগ্রহ করতে পারি:
            </flux:text>
            <ul class="list-disc ml-6 space-y-1.5 text-zinc-600 dark:text-zinc-400">
                <li>নাম</li>
                <li>ইমেইল ঠিকানা</li>
                <li>মোবাইল নম্বর (যদি প্রদান করা হয়)</li>
                <li>বার্তা বা মন্তব্য</li>
            </ul>

            <flux:heading level="3" class="mt-4">খ. স্বয়ংক্রিয়ভাবে সংগৃহীত তথ্য</flux:heading>
            <flux:text>
                আপনি আমাদের সাইট ভিজিট করলে আমরা নিম্নলিখিত তথ্য স্বয়ংক্রিয়ভাবে সংগ্রহ করতে পারি:
            </flux:text>
            <ul class="list-disc ml-6 space-y-1.5 text-zinc-600 dark:text-zinc-400">
                <li>আইপি ঠিকানা</li>
                <li>ব্রাউজারের ধরন ও ভার্সন</li>
                <li>ডিভাইসের তথ্য (মোবাইল/ডেস্কটপ)</li>
                <li>অপারেটিং সিস্টেম</li>
                <li>রেফারার ইউআরএল</li>
                <li>ভিজিটের সময় ও পৃষ্ঠা দেখার তথ্য</li>
            </ul>

            <flux:heading level="3" class="mt-4">গ. কুকিজ এবং ট্র্যাকিং প্রযুক্তি</flux:heading>
            <flux:text>
                আমরা এবং আমাদের তৃতীয় পক্ষের অংশীদাররা (যেমন Google) কুকিজ, ওয়েব বীকন এবং অনুরূপ প্রযুক্তি ব্যবহার করি।
                কুকিজ হলো ছোট টেক্সট ফাইল যা আপনার ডিভাইসে সংরক্ষিত হয়। এগুলো সাইটের কার্যকারিতা উন্নত করতে,
                ব্যবহারকারীর অভিজ্ঞতা ব্যক্তিগত করতে এবং বিজ্ঞাপন দেখাতে সাহায্য করে।
            </flux:text>
        </section>

        {{-- 2. How We Use Information --}}
        <section class="space-y-4">
            <flux:heading level="2">২. তথ্য কীভাবে ব্যবহার করি</flux:heading>
            <flux:text>আমরা সংগৃহীত তথ্য নিম্নলিখিত উদ্দেশ্যে ব্যবহার করি:</flux:text>
            <ul class="list-disc ml-6 space-y-1.5 text-zinc-600 dark:text-zinc-400">
                <li>আমাদের সেবা পরিচালনা ও উন্নত করা</li>
                <li>ব্যবহারকারীর অভিজ্ঞতা ব্যক্তিগত করা</li>
                <li>Totthobox AI এবং অন্যান্য টুলসের কার্যকারিতা নিশ্চিত করা</li>
                <li>আপনার প্রশ্নের উত্তর দেওয়া এবং সাপোর্ট প্রদান</li>
                <li>সাইটের নিরাপত্তা বজায় রাখা এবং প্রতারণা প্রতিরোধ</li>
                <li>আইনগত বাধ্যবাধকতা পূরণ</li>
                <li>বিজ্ঞাপন প্রদর্শন ও বিশ্লেষণ (Google AdSense সহ)</li>
            </ul>
        </section>

        {{-- 3. Google AdSense --}}
        <section class="space-y-4">
            <flux:heading level="2">৩. Google AdSense এবং তৃতীয় পক্ষের বিজ্ঞাপন</flux:heading>
            <flux:text>
                আমাদের ওয়েবসাইটে <strong>Google AdSense</strong> এর মাধ্যমে তৃতীয় পক্ষের বিজ্ঞাপন প্রদর্শিত হয়। Google
                এবং অন্যান্য বিজ্ঞাপন অংশীদাররা কুকিজ ব্যবহার করে আপনার আগের ভিজিটের ভিত্তিতে বিজ্ঞাপন দেখাতে পারে।
            </flux:text>
            <flux:text>
                Google AdSense কুকিজ ব্যবহার করে আপনার আগ্রহ অনুযায়ী বিজ্ঞাপন দেখায়। আপনি চাইলে
                <a href="https://www.google.com/settings/ads" target="_blank" rel="noopener"
                    class="-600 hover:underline">Google Ads Settings</a>
                থেকে ব্যক্তিগতকৃত বিজ্ঞাপন নিয়ন্ত্রণ করতে পারেন।
            </flux:text>
            <flux:text>
                তৃতীয় পক্ষের বিজ্ঞাপনদাতারা তাদের নিজস্ব গোপনীয়তা নীতি অনুসরণ করে। আমরা তাদের তথ্য সংগ্রহের পদ্ধতির
                জন্য দায়ী নই। বিস্তারিত জানতে Google-এর গোপনীয়তা নীতি দেখুন:
                <a href="https://policies.google.com/privacy" target="_blank" rel="noopener"
                    class="-600 hover:underline">https://policies.google.com/privacy</a>
            </flux:text>
        </section>

        {{-- 4. Cookies --}}
        <section class="space-y-4">
            <flux:heading level="2">৪. কুকিজ নীতি</flux:heading>
            <flux:text>আমরা নিম্নলিখিত ধরনের কুকিজ ব্যবহার করি:</flux:text>
            <ul class="list-disc ml-6 space-y-1.5 text-zinc-600 dark:text-zinc-400">
                <li><strong>প্রয়োজনীয় কুকিজ:</strong> সাইটের মৌলিক কার্যকারিতার জন্য</li>
                <li><strong>অ্যানালিটিক্স কুকিজ:</strong> সাইটের ব্যবহার বিশ্লেষণের জন্য (যেমন Google Analytics)</li>
                <li><strong>বিজ্ঞাপন কুকিজ:</strong> প্রাসঙ্গিক বিজ্ঞাপন দেখানোর জন্য (Google AdSense)</li>
            </ul>
            <flux:text class="mt-3">
                আপনি আপনার ব্রাউজার সেটিংস থেকে কুকিজ নিয়ন্ত্রণ বা মুছে ফেলতে পারেন। তবে কিছু কুকিজ বন্ধ করলে সাইটের
                কিছু ফিচার সঠিকভাবে কাজ নাও করতে পারে।
            </flux:text>
        </section>

        {{-- 5. Data Sharing --}}
        <section class="space-y-4">
            <flux:heading level="2">৫. তথ্য শেয়ারিং</flux:heading>
            <flux:text>
                আমরা আপনার ব্যক্তিগত তথ্য বিক্রি বা ভাড়া দিই না। তবে নিম্নলিখিত ক্ষেত্রে তথ্য শেয়ার করা হতে পারে:
            </flux:text>
            <ul class="list-disc ml-6 space-y-1.5 text-zinc-600 dark:text-zinc-400">
                <li>সেবা প্রদানকারীদের সাথে (হোস্টিং, অ্যানালিটিক্স, ইমেইল সার্ভিস)</li>
                <li>আইনি বাধ্যবাধকতা পূরণের জন্য</li>
                <li>সাইটের নিরাপত্তা ও প্রতারণা প্রতিরোধের প্রয়োজনে</li>
                <li>আপনার সম্মতিতে</li>
            </ul>
        </section>

        {{-- 6. Data Security --}}
        <section class="space-y-4">
            <flux:heading level="2">৬. তথ্যের নিরাপত্তা</flux:heading>
            <flux:text>
                আমরা আপনার ব্যক্তিগত তথ্য সুরক্ষিত রাখতে যুক্তিসঙ্গত প্রযুক্তিগত ও সাংগঠনিক ব্যবস্থা গ্রহণ করি। তবে
                ইন্টারনেটের মাধ্যমে কোনো তথ্য প্রেরণ ১০০% নিরাপদ নয় বলে আমরা সম্পূর্ণ নিরাপত্তার নিশ্চয়তা দিতে পারি না।
            </flux:text>
        </section>

        {{-- 7. Children's Privacy --}}
        <section class="space-y-4">
            <flux:heading level="2">৭. শিশুদের গোপনীয়তা</flux:heading>
            <flux:text>
                আমাদের সেবা ১৩ বছরের কম বয়সী শিশুদের জন্য নির্দিষ্টভাবে তৈরি নয়। আমরা জেনেশুনে ১৩ বছরের কম বয়সী কোনো
                শিশুর ব্যক্তিগত তথ্য সংগ্রহ করি না। যদি আপনি মনে করেন কোনো শিশুর তথ্য আমাদের কাছে আছে, তাহলে অনুগ্রহ করে
                আমাদের সাথে যোগাযোগ করুন, আমরা তা অবিলম্বে মুছে ফেলব।
            </flux:text>
        </section>

        {{-- 8. Your Rights --}}
        <section class="space-y-4">
            <flux:heading level="2">৮. আপনার অধিকার</flux:heading>
            <flux:text>আপনার নিম্নলিখিত অধিকার রয়েছে:</flux:text>
            <ul class="list-disc ml-6 space-y-1.5 text-zinc-600 dark:text-zinc-400">
                <li>আপনার সম্পর্কে আমাদের কাছে থাকা তথ্য জানার অধিকার</li>
                <li>ভুল তথ্য সংশোধনের অনুরোধ করার অধিকার</li>
                <li>আপনার তথ্য মুছে ফেলার অনুরোধ করার অধিকার</li>
                <li>কুকিজ এবং ব্যক্তিগতকৃত বিজ্ঞাপন নিয়ন্ত্রণ করার অধিকার</li>
            </ul>
            <flux:text class="mt-3">
                এই অধিকারগুলো প্রয়োগ করতে চাইলে আমাদের সাথে যোগাযোগ করুন।
            </flux:text>
        </section>

        {{-- 9. Third Party Links --}}
        <section class="space-y-4">
            <flux:heading level="2">৯. তৃতীয় পক্ষের লিঙ্ক</flux:heading>
            <flux:text>
                আমাদের সাইটে জরুরি সেবা, সংবাদমাধ্যম, সরকারি ওয়েবসাইট বা অন্যান্য বহিঃস্থ লিঙ্ক থাকতে পারে। এই সাইটগুলোর
                নিজস্ব গোপনীয়তা নীতি রয়েছে। আমরা সেই সাইটগুলোর বিষয়বস্তু বা গোপনীয়তা অনুশীলনের জন্য দায়ী নই।
            </flux:text>
        </section>

        {{-- 10. Changes --}}
        <section class="space-y-4">
            <flux:heading level="2">১০. নীতির পরিবর্তন</flux:heading>
            <flux:text>
                আমরা সময় সময় এই গোপনীয়তা নীতি আপডেট করতে পারি। কোনো পরিবর্তন হলে আমরা এই পৃষ্ঠায় নতুন তারিখ উল্লেখ করব।
                নিয়মিত এই পৃষ্ঠা দেখার পরামর্শ দিচ্ছি। পরিবর্তনের পরও সাইট ব্যবহার অব্যাহত রাখলে আপনি আপডেটকৃত নীতির
                সাথে একমত বলে গণ্য হবেন।
            </flux:text>
        </section>

        <flux:separator class="my-10" />

        {{-- Contact --}}
        <div class="bg-zinc-100 dark:bg-zinc-800 p-8 rounded-2xl text-center space-y-4">
            <flux:heading level="3">যোগাযোগ</flux:heading>
            <flux:text>
                এই গোপনীয়তা নীতি সম্পর্কে কোনো প্রশ্ন, অভিযোগ বা অনুরোধ থাকলে নির্দ্বিধায় আমাদের সাথে যোগাযোগ করুন।
            </flux:text>
            <div class="pt-2">
                <flux:button variant="primary" icon="envelope" href="/contact-us" wire:navigate>
                    সাপোর্ট সেন্টারে মেসেজ দিন
                </flux:button>
            </div>
        </div>

        {{-- Footer Note --}}
        <div class="pt-8 text-center text-zinc-500 text-sm">
            <flux:text>
                এই গোপনীয়তা নীতি Totthobox ওয়েবসাইট এবং এর সকল সাবডোমেইনের জন্য প্রযোজ্য।
            </flux:text>
        </div>

    </div>
</section>
