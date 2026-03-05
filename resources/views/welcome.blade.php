<x-layouts.app.header>

    <x-seo title="মূল সেবা"
        description="Totthobox-এ পাবেন বাংলাদেশ জেলা তথ্য, ইসলামিক শিক্ষা, স্বাস্থ্য জ্ঞান, জরুরী নম্বর, ছুটির তালিকা এবং এক্সেল এক্সপার্ট টিপসসহ প্রয়োজনীয় সকল ডিজিটাল সেবা।"
        keywords="তথ্যবক্স হোমপেজ, বাংলাদেশ সার্ভিস পোর্টাল, অনলাইন এমসিকিউ, শিশুশিক্ষা, কারেন্সি কনভার্টার, এক্সেল টিপস, Totthobox" />

    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- ২. হেডার সেকশন (গুগলকে বোঝানোর জন্য H1 ব্যবহার) --}}
        <div class="text-center mb-10 mt-6">
            <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                মূল সেবা
            </h1>
            <p class="mt-3 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                আপনার প্রয়োজনীয় সকল তথ্য ও ডিজিটাল সেবা এক জায়গায়
            </p>
        </div>

        {{-- ৩. সার্ভিস গ্রিড --}}
        <div class="flex flex-wrap justify-center -mx-2">
            @php
                // ক্যাশ হ্যান্ডলিং (আপনার কোড অনুযায়ী)
                $firstContact = cache()->remember('home_first_contact', 3600, fn() => App\Models\ContactCategory::query()->active()->first());
                $firstSign = cache()->remember('home_first_sign', 3600, fn() => App\Models\SignCategory::query()->active()->first());
                $firstExcel = cache()->remember('home_first_excel', 3600, fn() => App\Models\ExcelTutorial::query()->first());

                $services = [
                    ['route' => 'bangladesh.introduction', 'icon' => 'bd-map', 'label' => 'বাংলাদেশ', 'details' => 'বিভাগ ও জেলা সম্পর্কিত তথ্য।'],
                    ['route' => 'international.all-country', 'icon' => 'earth', 'label' => 'আন্তর্জাতিক', 'details' => 'পতাকা, রাজধানী ও মুদ্রা।'],
                    ['route' => 'islam.basicislam', 'icon' => 'islamic', 'label' => 'ইসলামিক', 'details' => 'নামাজ, কালেমা ও দোয়া।'],
                    ['route' => 'health.calorie-chart', 'icon' => 'health', 'label' => 'স্বাস্থ্য', 'details' => 'প্রাথমিক চিকিৎসা ও পরিচর্যা।'],
                    ['route' => 'contact.number', 'slug' => $firstContact?->slug ?? 'police', 'icon' => 'contact', 'label' => 'জরুরী সেবা', 'details' => 'হেল্পলাইন ও জরুরি নম্বর।'],
                    ['route' => 'buysell.all', 'icon' => 'buysell', 'label' => 'বিক্রয়/ক্রয়', 'details' => 'খোলা বাজারে কেনাবেচা।'],
                    ['route' => 'education.child.practice', 'icon' => 'child-edu', 'label' => 'শিশুশিক্ষা', 'details' => 'বর্ণমালা ও ডিজিটাল শিক্ষা।'],
                    ['route' => 'mcq.home', 'icon' => 'mcq', 'label' => 'এমসিকিউ', 'details' => 'অনলাইন কুইজ ও পরীক্ষা।'],
                    ['route' => 'converter.currency', 'icon' => 'converter', 'label' => 'কনভার্টার', 'details' => 'মুদ্রা ও একক রূপান্তর।'],
                    ['route' => 'signs.sign', 'slug' => $firstSign?->slug ?? 'emergency', 'icon' => 'sign', 'label' => 'সংকেত', 'details' => 'স্বাস্থ্য ও ট্রাফিক সংকেত।'],
                    ['route' => 'calendar.calendar', 'icon' => 'calendar', 'label' => 'ক্যালেন্ডার', 'details' => 'ছুটি ও বিশেষ দিনসমূহ।'],
                    ['route' => 'excel.view', 'slug' => $firstExcel?->slug ?? 'excel-expert', 'icon' => 'table-cells', 'label' => 'এক্সেল এক্সপার্ট', 'details' => 'অ্যাডভান্সড এক্সেল টিপস।'],
                ];
            @endphp

            @foreach ($services as $service)
                <div class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6 p-2 mb-4">
                    <a @if (Route::has($service['route']))
                        href="{{ isset($service['slug']) ? route($service['route'], ['slug' => $service['slug']]) : route($service['route']) }}"
                    @else href="#" @endif wire:navigate.hover
                        class="group p-4 h-full flex flex-col items-center text-center rounded-3xl bg-gray-50 dark:bg-white/5 border border-transparent hover:border-indigo-500/50 hover:bg-indigo-50/50 dark:hover:bg-indigo-500/10 transition-all duration-300">

                        <div class="mb-3 transform group-hover:scale-110 transition-transform duration-300">
                            <flux:icon name="{{ $service['icon'] }}" class="size-14 text-indigo-600 dark:text-indigo-400" />
                        </div>

                        <flux:heading size="lg" class="group-hover:text-indigo-600 transition-colors">
                            {{ $service['label'] }}
                        </flux:heading>

                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                            {{ $service['details'] }}
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ৪. ফুটার লিংকস --}}
    <footer
        class="flex flex-wrap items-center justify-center gap-6 mt-16 mb-10 border-t border-gray-100 dark:border-white/5 pt-8">
        <flux:link href="/privacy-policy" variant="subtle" class="text-sm">গোপনীয়তা নীতি</flux:link>
        <flux:link href="/terms-of-service" variant="subtle" class="text-sm">ব্যবহারের শর্তাবলী</flux:link>
        <flux:link href="/contact-us" variant="subtle" class="text-sm">যোগাযোগ</flux:link>
    </footer>
</x-layouts.app.header>