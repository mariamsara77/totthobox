<x-layouts.app.header :title="__('Home')" :description="__('Welcome to the home page')"
    :image="asset('images/logo.gif')">

    <div class="max-w-2xl mx-auto p-3">

        {{-- Header Section --}}
        <div class="text-center">
            <flux:heading size="xl" level="1">ব্যবহারের শর্তাবলি (Terms of Service)
            </flux:heading>
            <flux:subheading>Totthobox প্ল্যাটফর্ম ব্যবহারের নিয়ম ও নির্দেশিকা</flux:subheading>
            <flux:badge color="zinc" class="mt-4">কার্যকরী তারিখ: {{ bn_date(now()->format('d M, Y')) }}</flux:badge>
        </div>

        <div class="space-y-10">
            {{-- 1. General Acceptance --}}
            <section>
                <div class="flex items-center gap-3 mb-4">
                    <flux:icon.document-text class="text-primary-500" />
                    <flux:heading level="2">১. সাধারণ নিয়মাবলি</flux:heading>
                </div>
                <flux:text class="leading-relaxed">
                    **Totthobox** ওয়েবসাইটে প্রবেশ করার মাধ্যমে আপনি আমাদের শর্তাবলির সাথে একমত পোষণ করছেন। এই
                    প্ল্যাটফর্মটি মূলত তথ্য প্রদান, শিক্ষা এবং বিভিন্ন অনলাইন টুলস (যেমন: কনভার্টার, ক্যালেন্ডার)
                    ব্যবহারের জন্য তৈরি করা হয়েছে। আপনি যদি এই শর্তাবলির কোনো অংশের সাথে একমত না হন, তবে অনুগ্রহ করে
                    সাইটটি ব্যবহার থেকে বিরত থাকুন।
                </flux:text>
            </section>

            {{-- 2. User Responsibilities --}}
            <section>
                <div class="flex items-center gap-3 mb-4">
                    <flux:icon.user-group class="text-primary-500" />
                    <flux:heading level="2">২. ব্যবহারকারীর দায়িত্ব</flux:heading>
                </div>
                <flux:text class="mb-4">আমাদের সেবা ব্যবহারের ক্ষেত্রে ব্যবহারকারীকে অবশ্যই নিচের বিষয়গুলো মেনে চলতে
                    হবে:</flux:text>
                <ul class="space-y-3">
                    <li class="flex gap-2">
                        <flux:icon.check class="text-zinc-400 mt-1" variant="micro" />
                        <flux:text>বিক্রয়/ক্রয় সেকশনে কোনো প্রকার অবৈধ বা বিভ্রান্তিকর বিজ্ঞাপন প্রদান করা যাবে না।
                        </flux:text>
                    </li>
                    <li class="flex gap-2">
                        <flux:icon.check class="text-zinc-400 mt-1" variant="micro" />
                        <flux:text>এমসিকিউ পরীক্ষায় কোনো ধরনের অসদুপায় অবলম্বন বা সার্ভারের ক্ষতি করার চেষ্টা করা
                            যাবে
                            না।</flux:text>
                    </li>
                    <li class="flex gap-2">
                        <flux:icon.check class="text-zinc-400 mt-1" variant="micro" />
                        <flux:text>আমাদের লোগো, ডাটা বা কন্টেন্ট অনুমতি ছাড়া বাণিজ্যিক উদ্দেশ্যে ব্যবহার নিষিদ্ধ।
                        </flux:text>
                    </li>
                </ul>
            </section>

            {{-- 3. Service Specific Terms --}}
            <section
                class="bg-primary-50 dark:bg-primary-900/10 p-6 rounded-2xl border border-primary-100 dark:border-primary-800">
                <div class="flex items-center gap-3 mb-4">
                    <flux:icon.cpu-chip class="text-primary-600" />
                    <flux:heading level="2" class="text-primary-900 dark:text-primary-100">৩. সেবা সংক্রান্ত
                        সীমাবদ্ধতা
                    </flux:heading>
                </div>
                <div class="space-y-4">
                    <div>
                        <flux:heading level="3" size="sm">এক্সেল এক্সপার্ট ও ডাটা:</flux:heading>
                        <flux:text size="sm">এখানে প্রদত্ত ফর্মুলা এবং টিপসগুলো শিক্ষার উদ্দেশ্যে দেওয়া। আপনার
                            গুরুত্বপূর্ণ ডাটা প্রসেস করার আগে অবশ্যই ব্যাকআপ রাখুন।</flux:text>
                    </div>
                    <div>
                        <flux:heading level="3" size="sm">জরুরী সেবা ও নম্বর:</flux:heading>
                        <flux:text size="sm">আমরা সঠিক নম্বর প্রদানের চেষ্টা করি, তবে টেলিকম অপারেটর বা সরকারি
                            পরিবর্তনের কারণে কোনো নম্বর কাজ না করলে আমরা দায়ী নই।</flux:text>
                        </li>
                    </div>
            </section>

            {{-- 4. Accuracy of Information --}}
            <section>
                <div class="flex items-center gap-3 mb-4">
                    <flux:icon.exclamation-triangle class="text-primary-500" />
                    <flux:heading level="2">৪. তথ্যের সঠিকতা</flux:heading>
                </div>
                <flux:text>
                    বাংলাদেশ, আন্তর্জাতিক তথ্য বা স্বাস্থ্য সংকেত মডিউলে থাকা তথ্যগুলো আমরা নির্ভরযোগ্য উৎস থেকে
                    সংগ্রহ
                    করি। তবে তথ্যের শতভাগ নির্ভুলতা নিয়ে আমরা কোনো গ্যারান্টি দিই না। ব্যবহারের আগে ক্রস-চেক করার
                    অনুরোধ
                    রইল।
                </flux:text>
            </section>

            {{-- 5. Modifications --}}
            <section>
                <div class="flex items-center gap-3 mb-4">
                    <flux:icon.arrow-path class="text-primary-500" />
                    <flux:heading level="2">৫. পরিবর্তন ও পরিমার্জন</flux:heading>
                </div>
                <flux:text>
                    Totthobox কর্তৃপক্ষ যেকোনো সময় কোনো পূর্ব নোটিশ ছাড়াই এই শর্তাবলি পরিবর্তন করার অধিকার রাখে।
                    নিয়মিত
                    এই পেজটি চেক করা ব্যবহারকারীর দায়িত্ব।
                </flux:text>
            </section>
        </div>

        {{-- Footer Call-to-action --}}
        <div class="mt-16 pt-8 border-t border-zinc-100 dark:border-zinc-700 text-center">
            <flux:text class="italic mb-6">আমাদের শর্তাবলি মেনে চলার জন্য আপনাকে ধন্যবাদ।</flux:text>
            <div class="flex flex-wrap justify-center gap-4">
                <flux:button href="/" variant="filled" size="sm">হোম পেজে ফিরে যান</flux:button>
                <flux:button href="/contact-us" variant="ghost" size="sm" icon="envelope">সহযোগিতার
                    জন্য
                    যোগাযোগ
                    করুন</flux:button>
            </div>
        </div>
    </div>
</x-layouts.app.header>