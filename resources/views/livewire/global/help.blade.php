<x-seo title="হেল্প ও সাপোর্ট সেন্টার | Totthobox"
    description="Totthobox-এর সাহায্যকারী পেজ। লাইভ ম্যাচ স্ট্রিমিং, কনভার্টার টুলস, ইসলাম শিক্ষা, অফলাইন সাপোর্ট এবং ব্যবহারকারী নির্দেশিকাসহ আপনার যেকোনো জিজ্ঞাসার দ্রুত সমাধান খুঁজে নিন।"
    keywords="হেল্প সেন্টার, সাপোর্ট সেন্টার, এফএকিউ, Totthobox help, লাইভ ফুটবল সাহায্য, সাধারণ জিজ্ঞাসা, সাপোর্ট টিকিট, টথবক্স" />

<x-layouts.app.header>
    <div class="max-w-2xl mx-auto p-4">

        <!-- Hero Section & Search -->
        <div class="text-center max-w-2xl mx-auto mb-26">
            <flux:badge color="indigo" inset="top bottom" class="font-semibold uppercase tracking-wider">
                Totthobox সাপোর্ট পোর্টাল
            </flux:badge>

            <h1 class="text-4xl md:text-5xl font-black tracking-tight text-zinc-900 dark:text-white mt-4 mb-4">
                আমরা কীভাবে সাহায্য করতে পারি?
            </h1>

            <p class="text-zinc-500 dark:text-zinc-400 text-base ">
                কনভার্টার, আইনি পলিসি, লাইভ স্ট্রিমিং ও এআই টিউটরসহ Totthobox-এর সকল ফিচার সংক্রান্ত সমাধান এখানে পাবেন।
            </p>

            <!-- Live Search Input -->
            <div class="mt-8 max-w-xl mx-auto">
                <flux:input type="search"
                    placeholder="আপনার সমস্যা বা বিষয় লিখে সার্চ করুন (যেমন: ক্যালেন্ডার, এআই, লাইভ ম্যাচ)..."
                    icon="magnifying-glass" size="lg" class="shadow-sm" />
            </div>
        </div>

        <!-- Category Grid (8 Advanced Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-20">

            <!-- Card 1: Live Streaming -->
            <div
                class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl flex flex-col justify-between transition-all duration-200 hover:border-zinc-400/25  group">
                <div>
                    <div
                        class="w-10 h-10 flex items-center justify-center bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl mb-4">
                        <flux:icon name="play" variant="outline" class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base">লাইভ ম্যাচ সাপোর্ট</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">
                        ফুটবল ম্যাচের লাইভ স্ট্রিমিং বাফারিং এবং প্লেয়ার ট্রাবলশুটিং গাইড।
                    </p>
                </div>
                <div class="mt-6">
                    <flux:button variant="subtle" size="sm" icon-trailing="chevron-right" x-data="{ target: 'aHR0cHM6Ly90b3R0aG9ib3guY29tL2xpdmUtdGVsZXZpc2lvbg==' }"
                        x-on:click.prevent="window.location.href = atob(target)"
                        class="text-rose-600 dark:text-rose-400 group-hover:bg-rose-50 dark:group-hover:bg-rose-950/30">
                        লাইভ ম্যাচ পেজ
                    </flux:button>
                </div>
            </div>

            <!-- Card 2: Tools & Converter -->
            <div
                class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl flex flex-col justify-between transition-all duration-200 hover:border-zinc-400/25  group">
                <div>
                    <div
                        class="w-10 h-10 flex items-center justify-center bg-zinc-400/10 text-emerald-600 dark:text-emerald-400 rounded-xl mb-4">
                        <flux:icon name="calculator" variant="outline" class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base">কনভার্টার ও ক্যালকুলেটর</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">
                        টাকা পরিবর্তন, পরিমাপ রূপান্তর এবং সংখ্যা থেকে কথায় লেখার নিয়মাবলী।
                    </p>
                </div>
                <div class="mt-6">
                    <flux:button href="{{ route('converter.currency') }}" variant="subtle" size="sm"
                        icon-trailing="chevron-right"
                        class="text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-50 dark:group-hover:bg-emerald-950/30">
                        কারেন্সি কনভার্টার
                    </flux:button>
                </div>
            </div>

            <!-- Card 3: AI Tutor & Education -->
            <div
                class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl flex flex-col justify-between transition-all duration-200 hover:border-zinc-400/25  group">
                <div>
                    <div
                        class="w-10 h-10 flex items-center justify-center bg-violet-50 dark:bg-violet-950/40 text-violet-600 dark:text-violet-400 rounded-xl mb-4">
                        <flux:icon name="cpu-chip" variant="outline" class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base">এআই টিউটর ও স্টাডি</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">
                        এআই চ্যাটবট দিয়ে পড়াশোনা এবং MCQ মক টেস্টে অংশগ্রহণের বিস্তারিত গাইড।
                    </p>
                </div>
                <div class="mt-6">
                    <flux:button href="{{ route('ai.chat.show') }}" variant="subtle" size="sm"
                        icon-trailing="chevron-right"
                        class="text-violet-600 dark:text-violet-400 group-hover:bg-violet-50 dark:group-hover:bg-violet-950/30">
                        এআই চ্যাট ওপেন করুন
                    </flux:button>
                </div>
            </div>

            <!-- Card 4: Buy & Sell Marketplace -->
            <div
                class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl flex flex-col justify-between transition-all duration-200 hover:border-zinc-400/25  group">
                <div>
                    <div
                        class="w-10 h-10 flex items-center justify-center bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-xl mb-4">
                        <flux:icon name="shopping-bag" variant="outline" class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base">কেনাবেচা ও বিজ্ঞাপন</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">
                        মার্কেটপ্লেসে ফ্রিতে আপনার বিজ্ঞাপন পোস্ট করা ও কাস্টমারের সাথে মেসেজ করার নিয়ম।
                    </p>
                </div>
                <div class="mt-6">
                    <flux:button href="{{ route('buysell.all') }}" variant="subtle" size="sm"
                        icon-trailing="chevron-right"
                        class="text-blue-600 dark:text-blue-400 group-hover:bg-blue-50 dark:group-hover:bg-blue-950/30">
                        কেনাবেচা বাজার
                    </flux:button>
                </div>
            </div>

            <!-- Card 5: Islam / Quran Section -->
            <div
                class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl flex flex-col justify-between transition-all duration-200 hover:border-zinc-400/25  group">
                <div>
                    <div
                        class="w-10 h-10 flex items-center justify-center bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-xl mb-4">
                        <flux:icon name="book-open" variant="outline" class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base">ইসলাম ও আল-কুরআন</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">
                        কুরআনের অনুবাদ, ইসলামিক দোয়া ও প্রয়োজনীয় মাসআলা ট্র্যাকিং নির্দেশিকা।
                    </p>
                </div>
                <div class="mt-6">
                    <flux:button href="{{ route('islam.al-quran') }}" variant="subtle" size="sm"
                        icon-trailing="chevron-right"
                        class="text-teal-600 dark:text-teal-400 group-hover:bg-teal-50 dark:group-hover:bg-teal-950/30">
                        আল-কুরআন রিডার
                    </flux:button>
                </div>
            </div>

            <!-- Card 6: Child Education Practice -->
            <div
                class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl flex flex-col justify-between transition-all duration-200 hover:border-zinc-400/25  group">
                <div>
                    <div
                        class="w-10 h-10 flex items-center justify-center bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-xl mb-4">
                        <flux:icon name="academic-cap" variant="outline" class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base">শিশু শিক্ষা ও প্র্যাকটিস</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">
                        ছোট সোনামণিদের আদর্শলিপি ও মজার কুইজ প্র্যাকটিস মডিউল ব্যবহার নির্দেশিকা।
                    </p>
                </div>
                <div class="mt-6">
                    <flux:button href="{{ route('education.child.practice') }}" variant="subtle" size="sm"
                        icon-trailing="chevron-right"
                        class="text-amber-600 dark:text-amber-400 group-hover:bg-amber-50 dark:group-hover:bg-amber-950/30">
                        প্র্যাকটিস পেজ
                    </flux:button>
                </div>
            </div>

            <!-- Card 7: Offline App Support -->
            <div
                class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl flex flex-col justify-between transition-all duration-200 hover:border-zinc-400/25  group">
                <div>
                    <div
                        class="w-10 h-10 flex items-center justify-center bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400 rounded-xl mb-4">
                        <flux:icon name="wifi" variant="outline" class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base">অফলাইন রিকভারি</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">
                        ইন্টারনেট কানেকশন দুর্বল বা না থাকলে Totthobox কীভাবে অফলাইনে ব্যবহার করবেন।
                    </p>
                </div>
                <div class="mt-6">
                    <flux:button href="{{ route('offline') }}" variant="subtle" size="sm"
                        icon-trailing="chevron-right"
                        class="text-cyan-600 dark:text-cyan-400 group-hover:bg-cyan-50 dark:group-hover:bg-cyan-950/30">
                        অফলাইন সমাধান
                    </flux:button>
                </div>
            </div>

            <!-- Card 8: Terms & Safety -->
            <div
                class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl flex flex-col justify-between transition-all duration-200 hover:border-zinc-400/25  group">
                <div>
                    <div
                        class="w-10 h-10 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-xl mb-4">
                        <flux:icon name="shield-check" variant="outline" class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base">আইনি নীতিমালা</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">
                        আমাদের প্ল্যাটফর্ম ব্যবহারের শর্তাবলী এবং কীভাবে আপনার ব্যক্তিগত ডেটা সুরক্ষিত থাকে।
                    </p>
                </div>
                <div class="mt-6">
                    <flux:button href="{{ route('privacy.policy') }}" variant="subtle" size="sm"
                        icon-trailing="chevron-right"
                        class="text-zinc-600 dark:text-zinc-400 group-hover:bg-zinc-100 dark:group-hover:bg-zinc-800/60">
                        প্রাইভেসি ও শর্তাবলী
                    </flux:button>
                </div>
            </div>

        </div>

        <flux:separator class="my-16" />

        <!-- FAQ Accordion Section -->
        <div class="max-w-3xl mx-auto">
            <h2
                class="text-2xl md:text-3xl font-black tracking-tight text-zinc-900 dark:text-white mb-8 text-center sm:text-left">
                সচরাচর জিজ্ঞাসিত প্রশ্নাবলী (FAQ)
            </h2>

            <div x-data="{ active: null }" class="space-y-3">

                <!-- FAQ 1: Live Football -->
                <div
                    class="border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 transition-colors duration-200">
                    <button @click="active = (active === 1 ? null : 1)"
                        class="w-full flex justify-between items-center p-5 text-left font-semibold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition">
                        <span>১. লাইভ ম্যাচের স্কোর বা ভিডিও প্লে হচ্ছে না, কী করব?</span>
                        <flux:icon name="chevron-down" class="text-zinc-400 transition-transform duration-200"
                            ::class="{ 'rotate-180': active === 1 }" />
                    </button>
                    <div x-show="active === 1" x-collapse style="display: none;">
                        <div
                            class="p-5 border-t border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed bg-zinc-50/50 dark:bg-zinc-950/20">
                            লাইভ স্ট্রিমিং সার্ভারের লোডের কারণে অনেক সময় ভিডিও বাফারিং করতে পারে। আপনার ইন্টারনেট
                            সংযোগটি পরীক্ষা করুন এবং প্লেয়ার লোড হতে কয়েক সেকেন্ড সময় দিন। সমস্যা সমাধান না হলে
                            সরাসরি
                            <a href="javascript:void(0)" x-data="{ target: 'aHR0cHM6Ly90b3R0aG9ib3guY29tL2xpdmUtdGVsZXZpc2lvbg==' }"
                                x-on:click.prevent="window.location.href = atob(target)"
                                class="text-rose-500 font-medium hover:underline">লাইভ ম্যাচ পোর্টাল</a>-এ গিয়ে পেজটি
                            রিফ্রেশ করুন।
                        </div>
                    </div>
                </div>

                <!-- FAQ 2: AI Tutor Chatbot -->
                <div
                    class="border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 transition-colors duration-200">
                    <button @click="active = (active === 2 ? null : 2)"
                        class="w-full flex justify-between items-center p-5 text-left font-semibold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition">
                        <span>২. Totthobox AI চ্যাটবট ব্যবহারের কি কোনো লিমিটেশন আছে?</span>
                        <flux:icon name="chevron-down" class="text-zinc-400 transition-transform duration-200"
                            ::class="{ 'rotate-180': active === 2 }" />
                    </button>
                    <div x-show="active === 2" x-collapse style="display: none;">
                        <div
                            class="p-5 border-t border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed bg-zinc-50/50 dark:bg-zinc-950/20">
                            আমাদের এআই টিউটর চ্যাট সম্পূর্ণ ফ্রি এবং উন্মুক্ত। তবে অতিরিক্ত স্প্যামিং এড়াতে প্রতি
                            মিনিটে একটি নির্দিষ্ট রিকোয়েস্ট লিমিট রয়েছে। যদি এআই চ্যাট লোড না হয়, তবে অনুগ্রহ করে
                            কিছুক্ষণ অপেক্ষা করে আবার <a href="{{ route('ai.chat.show') }}"
                                class="text-violet-500 font-medium hover:underline">এআই চ্যাটবট</a> ব্যবহার করতে পারেন।
                        </div>
                    </div>
                </div>

                <!-- FAQ 3: Account and Profile Security -->
                <div
                    class="border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 transition-colors duration-200">
                    <button @click="active = (active === 3 ? null : 3)"
                        class="w-full flex justify-between items-center p-5 text-left font-semibold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition">
                        <span>৩. আমি কীভাবে আমার প্রোফাইল ইনফরমেশন বা পাসওয়ার্ড আপডেট করব?</span>
                        <flux:icon name="chevron-down" class="text-zinc-400 transition-transform duration-200"
                            ::class="{ 'rotate-180': active === 3 }" />
                    </button>
                    <div x-show="active === 3" x-collapse style="display: none;">
                        <div
                            class="p-5 border-t border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed bg-zinc-50/50 dark:bg-zinc-950/20">
                            আপনি অ্যাকাউন্টে লগইন থাকা অবস্থায় আপনার প্রোফাইল সেটিংস পরিবর্তন করতে পারবেন। পাসওয়ার্ড
                            রিসেট করতে বা ডার্ক মোড/লাইট মোড সেটিংস পরিবর্তন করতে চলে যান <a
                                href="{{ route('profile.settings') }}"
                                class="text-blue-500 font-medium hover:underline">প্রোফাইল সেটিংস</a> অথবা <a
                                href="{{ route('profile.password') }}"
                                class="text-blue-500 font-medium hover:underline">পাসওয়ার্ড পরিবর্তন</a> পেজে।
                        </div>
                    </div>
                </div>

                <!-- FAQ 4: Buy & Sell Posting -->
                <div
                    class="border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 transition-colors duration-200">
                    <button @click="active = (active === 4 ? null : 4)"
                        class="w-full flex justify-between items-center p-5 text-left font-semibold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition">
                        <span>৪. আমি কি কেনাবেচা বিভাগে ফ্রিতে বিজ্ঞাপন দিতে পারব?</span>
                        <flux:icon name="chevron-down" class="text-zinc-400 transition-transform duration-200"
                            ::class="{ 'rotate-180': active === 4 }" />
                    </button>
                    <div x-show="active === 4" x-collapse style="display: none;">
                        <div
                            class="p-5 border-t border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed bg-zinc-50/50 dark:bg-zinc-950/20">
                            হ্যাঁ! যেকোনো রেজিস্টার্ড ইউজার Totthobox-এ সম্পূর্ণ ফ্রিতে কাস্টম বিজ্ঞাপন পোস্ট করতে
                            পারবেন। বিজ্ঞাপন দিতে সরাসরি <a href="{{ route('buysell.post-ad') }}"
                                class="text-indigo-500 font-medium hover:underline">বিজ্ঞাপন পোস্ট করুন</a> সেকশনে
                            গিয়ে ফর্মটি পূরণ করুন।
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Support CTA Section -->
        <div
            class="mt-24 bg-zinc-400/10 border border-zinc-200/60 dark:border-zinc-800 rounded-3xl p-8 md:p-12 text-center">
            <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">
                আপনার কাঙ্ক্ষিত সমাধান খুঁজে পাননি?
            </h3>

            <p class="mt-3 text-sm md:text-base text-zinc-500 dark:text-zinc-400 max-w-xl mx-auto">
                আমাদের সাপোর্ট টিম ২৪ ঘণ্টার মধ্যে আপনার সমস্যার সমাধান দিতে প্রতিশ্রুতিবদ্ধ। এখনই আমাদের মেসেজ পাঠান।
            </p>

            <div class="mt-8 flex justify-center items-center gap-4">
                <flux:button href="{{ route('contact.us') }}" variant="filled" color="indigo"
                    class="w-full sm:w-auto px-6 rounded-xl">
                    সরাসরি যোগাযোগ করুন
                </flux:button>
                <flux:button href="mailto:support@totthobox.com" variant="outline"
                    class="w-full sm:w-auto px-6 rounded-xl">
                    ইমেইল পাঠান
                </flux:button>
            </div>

            <div class="mt-8 text-xs text-zinc-400">
                আইনি নির্দেশিকা:
                <a href="{{ route('privacy.policy') }}"
                    class=" dark:hover:text-zinc-200 underline underline-offset-2 ml-1">প্রাইভেসি
                    পলিসি</a> |
                <a href="{{ route('terms.service') }}"
                    class=" dark:hover:text-zinc-200 underline underline-offset-2 ml-1">ব্যবহারের
                    শর্তাবলী</a>
            </div>
        </div>

    </div>
</x-layouts.app.header>
