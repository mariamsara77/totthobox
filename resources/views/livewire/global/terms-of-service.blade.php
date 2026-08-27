<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app.header')] class extends Component {
    //
}; ?>

<x-seo title="ব্যবহারের শর্তাবলি (Terms of Service)"
    description="Totthobox প্ল্যাটফর্ম ব্যবহারের সম্পূর্ণ নিয়ম ও শর্তাবলি। ব্যবহারকারীর দায়িত্ব, সেবার সীমাবদ্ধতা এবং তথ্যের সঠিকতা সম্পর্কে বিস্তারিত জানুন।"
    keywords="ব্যবহারের শর্তাবলি, terms of service, Totthobox terms, নিয়মাবলী, তথ্যবক্স শর্তাবলি" />

<section>
    <div class="max-w-2xl mx-auto space-y-8 py-6">

        {{-- Header --}}
        <div class="text-center space-y-3">
            <flux:heading size="xl" level="1">ব্যবহারের শর্তাবলি</flux:heading>
            <flux:subheading class="max-w-xl mx-auto">
                Totthobox প্ল্যাটফর্ম ব্যবহারের নিয়ম, শর্ত ও নির্দেশিকা
            </flux:subheading>
            <div class="pt-1">
                <flux:badge color="zinc" size="sm">
                    সর্বশেষ আপডেট: {{ now()->format('d F, Y') }}
                </flux:badge>
            </div>
            <div class="pt-2">
                <flux:separator variant="subtle" />
            </div>
        </div>

        {{-- 1. Acceptance --}}
        <section class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-2 rounded-lg bg-zinc-400/10">
                    <flux:icon name="document-text" class="size-5 text-primary-600 dark:text-primary-400" />
                </div>
                <flux:heading level="2">১. সাধারণ নিয়মাবলি ও গ্রহণযোগ্যতা</flux:heading>
            </div>
            <flux:text class="leading-relaxed">
                <strong>Totthobox</strong> (<a href="https://totthobox.com"
                    class="-600 hover:underline">totthobox.com</a>) ওয়েবসাইট এবং এর সকল সেবা ব্যবহার করার
                মাধ্যমে আপনি এই ব্যবহারের শর্তাবলির সাথে সম্পূর্ণভাবে একমত পোষণ করছেন।
                এই প্ল্যাটফর্মটি তথ্য প্রদান, শিক্ষামূলক কনটেন্ট এবং বিভিন্ন অনলাইন টুলস (কনভার্টার, ক্যালকুলেটর,
                ক্যালেন্ডার ইত্যাদি) ব্যবহারের জন্য তৈরি করা হয়েছে।
            </flux:text>
            <flux:text class="leading-relaxed">
                আপনি যদি এই শর্তাবলির কোনো অংশের সাথে একমত না হন, তাহলে অনুগ্রহ করে আমাদের ওয়েবসাইট ও সেবা ব্যবহার থেকে
                বিরত থাকুন।
            </flux:text>
        </section>

        {{-- 2. Description of Services --}}
        <section class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-2 rounded-lg bg-zinc-400/10">
                    <flux:icon name="squares-2x2" class="size-5 text-primary-600 dark:text-primary-400" />
                </div>
                <flux:heading level="2">২. আমাদের সেবাসমূহ</flux:heading>
            </div>
            <flux:text>
                Totthobox নিম্নলিখিত ধরনের সেবা প্রদান করে:
            </flux:text>
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 list-none">
                <li class="flex items-center gap-4">
                    <flux:icon name="check-circle" variant="micro" class="text-green-500 shrink-0" />
                    <flux:text>বাংলা ক্যালেন্ডার ও ছুটির তালিকা</flux:text>
                </li>
                <li class="flex items-center gap-4">
                    <flux:icon name="check-circle" variant="micro" class="text-green-500 shrink-0" />
                    <flux:text>বিভিন্ন কনভার্টার টুলস</flux:text>
                </li>
                <li class="flex items-center gap-4">
                    <flux:icon name="check-circle" variant="micro" class="text-green-500 shrink-0" />
                    <flux:text>ইউটিলিটি টুলস (Image Resizer, QR Code ইত্যাদি)</flux:text>
                </li>
                <li class="flex items-center gap-4">
                    <flux:icon name="check-circle" variant="micro" class="text-green-500 shrink-0" />
                    <flux:text>জরুরি সেবা ও হেল্পলাইন নম্বর</flux:text>
                </li>
                <li class="flex items-center gap-4">
                    <flux:icon name="check-circle" variant="micro" class="text-green-500 shrink-0" />
                    <flux:text>বাংলাদেশ ও বিশ্বকোষ বিষয়ক তথ্য</flux:text>
                </li>
                <li class="flex items-center gap-4">
                    <flux:icon name="check-circle" variant="micro" class="text-green-500 shrink-0" />
                    <flux:text>ইসলামিক বিষয়বস্তু ও শিশুশিক্ষা</flux:text>
                </li>
                <li class="flex items-center gap-4">
                    <flux:icon name="check-circle" variant="micro" class="text-green-500 shrink-0" />
                    <flux:text>সর্বশেষ সংবাদ</flux:text>
                </li>
                <li class="flex items-center gap-4">
                    <flux:icon name="check-circle" variant="micro" class="text-green-500 shrink-0" />
                    <flux:text>Totthobox AI চ্যাটবট</flux:text>
                </li>
            </ul>
        </section>

        {{-- 3. User Responsibilities --}}
        <section class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-2 rounded-lg bg-zinc-400/10">
                    <flux:icon name="user-group" class="size-5 text-primary-600 dark:text-primary-400" />
                </div>
                <flux:heading level="2">৩. ব্যবহারকারীর দায়িত্ব</flux:heading>
            </div>
            <flux:text>আমাদের সেবা ব্যবহার করার সময় আপনাকে নিম্নলিখিত নিয়মগুলো মেনে চলতে হবে:</flux:text>
            <ul class="space-y-3">
                <li class="flex gap-4">
                    <flux:icon name="check" variant="micro" class="text-zinc-400 mt-1 shrink-0" />
                    <flux:text>সাইটের কোনো অংশে অবৈধ, ক্ষতিকর, বিভ্রান্তিকর বা অশ্লীল কনটেন্ট প্রদান করা যাবে না।
                    </flux:text>
                </li>
                <li class="flex gap-4">
                    <flux:icon name="check" variant="micro" class="text-zinc-400 mt-1 shrink-0" />
                    <flux:text>সার্ভার, সিস্টেম বা অন্য ব্যবহারকারীর ক্ষতি করার চেষ্টা করা সম্পূর্ণ নিষিদ্ধ।</flux:text>
                </li>
                <li class="flex gap-4">
                    <flux:icon name="check" variant="micro" class="text-zinc-400 mt-1 shrink-0" />
                    <flux:text>আমাদের লোগো, কনটেন্ট বা ডাটা অনুমতি ছাড়া বাণিজ্যিক কাজে ব্যবহার করা যাবে না।</flux:text>
                </li>
                <li class="flex gap-4">
                    <flux:icon name="check" variant="micro" class="text-zinc-400 mt-1 shrink-0" />
                    <flux:text>অন্যের ব্যক্তিগত তথ্য অননুমোদিতভাবে সংগ্রহ বা প্রকাশ করা যাবে না।</flux:text>
                </li>
                <li class="flex gap-4">
                    <flux:icon name="check" variant="micro" class="text-zinc-400 mt-1 shrink-0" />
                    <flux:text>সাইটের নিরাপত্তা ব্যবস্থা ভেদ করার কোনো প্রচেষ্টা করা যাবে না।</flux:text>
                </li>
            </ul>
        </section>

        {{-- 4. Service Limitations --}}
        <section class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/30">
                    <flux:icon name="exclamation-triangle" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <flux:heading level="2">৪. সেবা সংক্রান্ত সীমাবদ্ধতা ও দায়মুক্তি</flux:heading>
            </div>

            <div class="space-y-5">
                <div>
                    <flux:heading level="3" size="sm" class="mb-2">টুলস ও কনভার্টার</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        আমাদের প্রদত্ত সকল টুলস (Image Resizer, QR Generator, কনভার্টার ইত্যাদি) শিক্ষামূলক ও সহায়ক
                        উদ্দেশ্যে দেওয়া হয়েছে। গুরুত্বপূর্ণ কাজের আগে ফলাফল যাচাই করে নেওয়ার পরামর্শ দেওয়া হচ্ছে।
                    </flux:text>
                </div>

                <div>
                    <flux:heading level="3" size="sm" class="mb-2">জরুরি সেবা ও হেল্পলাইন</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        আমরা সঠিক নম্বর প্রদানের সর্বোচ্চ চেষ্টা করি। তবে টেলিকম অপারেটর বা সরকারি পরিবর্তনের কারণে কোনো
                        নম্বর কাজ না করলে Totthobox তার জন্য দায়ী থাকবে না।
                    </flux:text>
                </div>

                <div>
                    <flux:heading level="3" size="sm" class="mb-2">সংবাদ ও তথ্য</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        সংবাদ ও তথ্য বিভিন্ন উৎস থেকে সংগ্রহ করা হয়। আমরা তথ্যের শতভাগ নির্ভুলতার গ্যারান্টি দিই না।
                        গুরুত্বপূর্ণ সিদ্ধান্ত নেওয়ার আগে মূল উৎস থেকে যাচাই করে নিন।
                    </flux:text>
                </div>

                <div>
                    <flux:heading level="3" size="sm" class="mb-2">Totthobox AI</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        AI চ্যাটবট সাধারণ তথ্য সহায়তার জন্য তৈরি। এটি পেশাদার পরামর্শ (চিকিৎসা, আইনগত ইত্যাদি) হিসেবে
                        ব্যবহার করা উচিত নয়।
                    </flux:text>
                </div>
            </div>
        </section>

        {{-- 5. Intellectual Property --}}
        <section class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-2 rounded-lg bg-zinc-400/10">
                    <flux:icon name="shield-check" class="size-5 text-primary-600 dark:text-primary-400" />
                </div>
                <flux:heading level="2">৫. মেধা সম্পত্তি অধিকার</flux:heading>
            </div>
            <flux:text class="leading-relaxed">
                এই ওয়েবসাইটের সকল কনটেন্ট, লোগো, ডিজাইন, কোড এবং উপকরণ Totthobox-এর মেধা সম্পত্তি।
                আমাদের লিখিত অনুমতি ছাড়া এগুলো কপি, পরিবর্তন, বিতরণ বা বাণিজ্যিক কাজে ব্যবহার করা সম্পূর্ণ নিষিদ্ধ।
            </flux:text>
        </section>

        {{-- 6. Third Party Links --}}
        <section class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-2 rounded-lg bg-zinc-400/10">
                    <flux:icon name="link" class="size-5 text-primary-600 dark:text-primary-400" />
                </div>
                <flux:heading level="2">৬. তৃতীয় পক্ষের লিংক</flux:heading>
            </div>
            <flux:text class="leading-relaxed">
                আমাদের সাইটে বাহ্যিক ওয়েবসাইট, সংবাদমাধ্যম বা সরকারি সার্ভিসের লিংক থাকতে পারে।
                এই সাইটগুলোর বিষয়বস্তু বা সেবার জন্য Totthobox কোনো দায়িত্ব নেয় না। সেগুলো ব্যবহারের আগে তাদের নিজস্ব
                শর্তাবলি ও গোপনীয়তা নীতি পড়ে নিন।
            </flux:text>
        </section>

        {{-- 7. Limitation of Liability --}}
        <section class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-2 rounded-lg bg-zinc-400/10">
                    <flux:icon name="scale" class="size-5 text-primary-600 dark:text-primary-400" />
                </div>
                <flux:heading level="2">৭. দায়বদ্ধতার সীমাবদ্ধতা</flux:heading>
            </div>
            <flux:text class="leading-relaxed">
                Totthobox “যেমন আছে” (as is) ভিত্তিতে সেবা প্রদান করে। সাইট ব্যবহারের ফলে সৃষ্ট কোনো প্রত্যক্ষ বা পরোক্ষ
                ক্ষতির জন্য আমরা দায়ী থাকব না।
                ব্যবহারকারী নিজ দায়িত্বে সাইট ব্যবহার করবেন।
            </flux:text>
        </section>

        {{-- 8. Changes to Terms --}}
        <section class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-2 rounded-lg bg-zinc-400/10">
                    <flux:icon name="arrow-path" class="size-5 text-primary-600 dark:text-primary-400" />
                </div>
                <flux:heading level="2">৮. শর্তাবলির পরিবর্তন</flux:heading>
            </div>
            <flux:text class="leading-relaxed">
                Totthobox কর্তৃপক্ষ যেকোনো সময় এই শর্তাবলি আপডেট বা পরিবর্তন করার অধিকার রাখে।
                পরিবর্তন এই পৃষ্ঠায় প্রকাশিত হওয়ার সাথে সাথেই কার্যকর হবে। নিয়মিত এই পৃষ্ঠা দেখা ব্যবহারকারীর দায়িত্ব।
                পরিবর্তনের পরও সাইট ব্যবহার অব্যাহত রাখলে আপনি নতুন শর্তাবলির সাথে একমত বলে গণ্য হবেন।
            </flux:text>
        </section>

        {{-- 9. Governing Law --}}
        <section class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="p-2 rounded-lg bg-zinc-400/10">
                    <flux:icon name="building-library" class="size-5 text-primary-600 dark:text-primary-400" />
                </div>
                <flux:heading level="2">৯. প্রযোজ্য আইন</flux:heading>
            </div>
            <flux:text class="leading-relaxed">
                এই শর্তাবলি বাংলাদেশের প্রচলিত আইন অনুসারে পরিচালিত ও ব্যাখ্যা করা হবে।
                যেকোনো বিরোধের ক্ষেত্রে বাংলাদেশের আদালতের এখতিয়ার প্রযোজ্য হবে।
            </flux:text>
        </section>
</section>
