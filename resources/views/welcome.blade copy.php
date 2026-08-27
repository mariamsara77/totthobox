<x-layouts.app.header>
    <x-seo title="মূল সেবা"
        description="Totthobox-এ পাবেন বাংলাদেশ জেলা তথ্য, ইসলামিক শিক্ষা, স্বাস্থ্য জ্ঞান, জরুরী নম্বর, ছুটির তালিকা, কনভার্টার এবং প্রয়োজনীয় ডিজিটাল সেবা।"
        keywords="তথ্যবক্স, Totthobox, বাংলাদেশ সার্ভিস পোর্টাল, বাংলা ক্যালেন্ডার, জরুরি নম্বর, শিশুশিক্ষা, কনভার্টার, ইসলামিক শিক্ষা" />

    <div class="max-w-8xl mx-auto p-4 lg:px-8 space-y-8">

        {{-- হেডার --}}
        <header class="text-center space-y-3">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-zinc-900 dark:text-white tracking-tight">
                মূল সেবা
            </h1>
            <p class="text-base sm:text-lg text-zinc-500 dark:text-zinc-400 max-w-2xl mx-auto">
                আপনার দৈনন্দিন প্রয়োজনীয় তথ্য, টুলস ও ডিজিটাল সেবা এক জায়গায় — নির্ভরযোগ্য ও সহজভাবে।
            </p>
        </header>

        {{-- ড্যাশবোর্ড (যদি থাকে) --}}
        <div class="w-full">
            <livewire:website.home.dashboard />
        </div>

        {{-- সার্ভিস গ্রিড --}}
        <section aria-labelledby="services-heading">
            <h2 id="services-heading" class="sr-only">সকল সেবা</h2>
            <livewire:website.home.services-grid>
                <x-slot:placeholder>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" animate-pulse">
                        @for ($i = 0; $i < 12; $i++)
                            <div class="h-36 rounded-2xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-400/25">
                            </div>
                        @endfor
                    </div>
                </x-slot:placeholder>
            </livewire:website.home.services-grid>
        </section>

        <flux:separator class="opacity-50 my-12" />
        {{-- মূল কনটেন্ট সেকশন (AdSense thin-content fix) --}}
        <div>
            <flux:heading size="xl" level="2">
                তথ্যবক্স (Totthobox) — আপনার দৈনন্দিন ডিজিটাল সহায়ক
            </flux:heading>

            <div class="space-y-5 mt-4 prose prose-zinc dark:prose-invert max-w-none">
                <flux:text class="text-base leading-relaxed">
                    বর্তমানে সঠিক তথ্য দ্রুত পাওয়া অত্যন্ত জরুরি। <strong>Totthobox</strong> বাংলাদেশের ব্যবহারকারীদের
                    জন্য তৈরি একটি সমন্বিত ডিজিটাল সার্ভিস পোর্টাল। এখানে দৈনন্দিন জীবনের প্রয়োজনীয় তথ্য, টুলস এবং
                    শিক্ষামূলক কনটেন্ট এক প্ল্যাটফর্মে রাখা হয়েছে, যাতে আপনি সহজে নির্ভরযোগ্য তথ্য পেতে পারেন।
                </flux:text>

                <flux:heading size="lg" level="3">কী কী সেবা পাবেন</flux:heading>

                <flux:text class="text-base leading-relaxed">
                    <strong>বাংলা ক্যালেন্ডার ও ছুটির তালিকা:</strong> বাংলা ও ইংরেজি তারিখ, সরকারি ছুটি এবং বিশেষ
                    দিবসের তথ্য এক নজরে দেখুন। পরিকল্পনা করতে এবং দৈনন্দিন কাজের সময় নির্ধারণে এটি সহায়ক।
                </flux:text>

                <flux:text class="text-base leading-relaxed">
                    <strong>জরুরি সেবা ও হেল্পলাইন:</strong> পুলিশ, ফায়ার সার্ভিস, অ্যাম্বুলেন্সসহ প্রয়োজনীয় জরুরি
                    নম্বরগুলো সহজে খুঁজে পাওয়ার ব্যবস্থা রাখা হয়েছে, যাতে প্রয়োজনের মুহূর্তে দ্রুত যোগাযোগ করা যায়।
                </flux:text>

                <flux:text class="text-base leading-relaxed">
                    <strong>ইসলামিক শিক্ষা:</strong> নামাজের নিয়ম, কালেমা, দোয়া এবং মৌলিক ইসলামিক জ্ঞান সহজ ভাষায়
                    উপস্থাপন করা হয়েছে, যাতে নতুন শিক্ষার্থীরাও উপকৃত হতে পারেন।
                </flux:text>

                <flux:text class="text-base leading-relaxed">
                    <strong>শিশুশিক্ষা:</strong> বর্ণমালা, সংখ্যা এবং মৌলিক ডিজিটাল শিক্ষার অনুশীলনের সুযোগ রয়েছে, যা
                    শিশুদের শেখার প্রথম ধাপে সহায়তা করে।
                </flux:text>

                <flux:text class="text-base leading-relaxed">
                    <strong>কনভার্টার ও টুলস:</strong> মুদ্রা রূপান্তর, সংখ্যা থেকে শব্দ, ছবি রিসাইজ এবং অন্যান্য
                    দৈনন্দিন টুলস এক জায়গায় পাওয়া যায়।
                </flux:text>

                <flux:text class="text-base leading-relaxed">
                    <strong>বিশ্বকোষ ও সংবাদ:</strong> দেশ-বিদেশের মৌলিক তথ্য (পতাকা, রাজধানী, মুদ্রা) এবং সর্বশেষ
                    সংবাদের শিরোনাম দেখার সুবিধা রয়েছে।
                </flux:text>

                <flux:text class="text-base leading-relaxed">
                    <strong>স্বাস্থ্য ও সংকেত:</strong> সাধারণ স্বাস্থ্য সংকেত এবং ট্রাফিক সংকেত সম্পর্কিত তথ্য সহজভাবে
                    উপস্থাপন করা হয়েছে।
                </flux:text>

                <flux:heading size="lg" level="3">কেন Totthobox ব্যবহার করবেন</flux:heading>

                <flux:text class="text-base leading-relaxed">
                    আমরা বিশ্বাস করি প্রযুক্তি সহজ ও সবার জন্য ব্যবহারযোগ্য হওয়া উচিত। তাই Totthobox-এ জটিল মেনু বা
                    অপ্রয়োজনীয় বিভ্রান্তি এড়িয়ে পরিষ্কার নেভিগেশন এবং দ্রুত লোডিংয়ের উপর গুরুত্ব দেওয়া হয়। কনটেন্ট
                    নিয়মিত পর্যালোচনা ও আপডেট করা হয়, যাতে তথ্য যথাসম্ভব নির্ভুল থাকে।
                </flux:text>

                <flux:text class="text-base leading-relaxed">
                    ভ্রমণ, শিক্ষা, জরুরি প্রয়োজন বা দৈনন্দিন হিসাব—যে কোনো কাজে প্রয়োজনীয় তথ্য এক প্ল্যাটফর্ম থেকে
                    পাওয়ার চেষ্টা করা হয়েছে। নতুন ফিচার ও উন্নতি ধারাবাহিকভাবে যোগ করা হয়, যাতে সেবা আরও উপযোগী হয়।
                </flux:text>

                <flux:text class="text-base leading-relaxed">
                    আপনার মতামত ও পরামর্শ আমাদের জন্য গুরুত্বপূর্ণ। কোনো তথ্য আপডেট বা সংশোধনের প্রয়োজন হলে যোগাযোগ
                    পেজের মাধ্যমে জানাতে পারেন। Totthobox ব্যবহার করে আপনার দৈনন্দিন তথ্য অনুসন্ধান আরও সহজ হোক—এটাই
                    আমাদের লক্ষ্য।
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts.app.header>