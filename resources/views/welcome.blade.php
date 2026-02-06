<x-layouts.app.header :title="__('Home')" :description="__('Welcome to the home page')" :image="asset('images/logo.gif')">

    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-6 mt-4">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">মূল সেবা</h1>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">আপনার প্রয়োজনীয় সকল সেবা এক জায়গায়</p>
        </div>
        <div class="flex flex-wrap justify-center text-center">


            @php
$firstContact = App\Models\ContactCategory::query()->active()->first();
$firstSign = App\Models\SignCategory::query()->active()->first();
$firstExcel = App\Models\ExcelTutorial::query()->first();

$services = [
    [
        'route' => 'bangladesh.introduction',
        'icon_name' => 'bd-map',
        'label' => 'বাংলাদেশ',
        'details' => 'বাংলাদেশ সকল সম্পর্কিত তথ্য, বিভাগ ও জেলা।',
    ],
    [
        'route' => 'international.all-country',
        'icon_name' => 'earth',
        'label' => 'আন্তর্জাতিক',
        'details' => 'বিভিন্ন দেশের পতাকা, রাজধানী, মুদ্রা ইত্যাদি।',
    ],
    [
        'route' => 'islam.basicislam',
        'icon_name' => 'islamic',
        'label' => 'ইসলামিক',
        'details' => 'ইসলামের মূল শিক্ষা, নামাজ, কালেমা ও দোয়া।',
    ],
    [
        'route' => 'health.calorie-chart',
        'icon_name' => 'health',
        'label' => 'স্বাস্থ',
        'details' => 'সাধারণ স্বাস্থ্য জ্ঞান, প্রাথমিক চিকিৎসা ও পরিচর্যা।',
    ],
    [
        'route' => 'contact.number',
        'slug' => $firstContact?->slug ?? 'police',
        'icon_name' => 'contact',
        'label' => 'জরুরী সেবা',
        'details' => 'জরুরি হেল্পলাইন নম্বর, পিলিশ, অ্যাম্বুলেন্স ও ফায়ার সার্ভিস।',
    ],
    [
        'route' => 'buysell.all',
        'icon_name' => 'buysell',
        'label' => 'বিক্রয়/ক্রয়',
        'details' => 'বিক্রয় ও ক্রয়ের খোলা বাজার।',
    ],
    [
        'route' => 'education.child.practice',
        'icon_name' => 'child-edu',
        'label' => 'শিশুশিক্ষা',
        'details' => 'শিশুদের জন্য বাংলা বর্ণমালা, সংখ্যা ও শিক্ষামূলক উপকরণ।',
    ],
    [
        'route' => 'mcq.home',
        'icon_name' => 'mcq',
        'label' => 'এমসিকিউ',
        'details' => 'বিভিন্ন বিষয়ের উপর অনলাইন এমসিকিউ পরীক্ষা।',
    ],
    [
        'route' => 'converter.currency',
        'icon_name' => 'converter',
        'label' => 'কনভার্টার',
        'details' => 'মুদ্রা, দৈর্ঘ্য, ওজন, সময় ইত্যাদির রূপান্তরকরণ।',
    ],
    [
        'route' => 'signs.sign',
        'slug' => $firstSign?->slug ?? 'emergency',
        'icon_name' => 'sign',
        'label' => 'সংকেত',
        'details' => 'বিভিন্ন স্বাস্থ্য সংকেত এবং তাদের অর্থ।',
    ],
    [
        'route' => 'calendar.calendar',
        'icon_name' => 'calendar',
        'label' => 'ক্যালেন্ডার',
        'details' => 'বাংলাদেশের ক্যালেন্ডার, ছুটির তালিকা ও বিশেষ দিন।',
    ],
    [
        'route' => 'excel.view',
        'slug' => $firstExcel?->slug ?? 'excel-expert',
        'icon_name' => 'table-cells', // এক্সেল বোঝাতে আইকনটি পরিবর্তন করা হয়েছে
        'label' => 'এক্সেল এক্সপার্ট',
        'details' => 'অ্যাডভান্সড এক্সেল ফর্মুলা, ডাটা অ্যানালাইসিস এবং রিপোর্ট তৈরির টিপস।',
    ],
];
            @endphp

            @foreach ($services as $service)
                <div class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6 p-2 mb-4">
                    <a @if (Route::has($service['route'])) href="{{ isset($service['slug']) ? route($service['route'], ['slug' => $service['slug']]) : route($service['route']) }}" @else href="#" @endif
                        wire:navigate.hover
                        class="p-2 h-full flex flex-col items-center text-decoration-none hover:bg-gray-400/10 rounded-4xl bg-gray-500/10 border-gray-400/50 transition-colors duration-200">
                        @if (!empty($service['icon_name']))
                            <flux:icon name="{{ $service['icon_name'] }}" class="size-18 text-gray-500" />
                        @elseif(!empty($service['icon']))
                            <flux:icon icon=" {!! $service['icon'] !!}" class="size-18 text-gray-500" />
                        @endif
                        <flux:heading size="xl">{{ $service['label'] }}</flux:heading>
                        <span class="text-gray-500 mt-1 text-sm text-center">{{ $service['details'] }}</span>
                    </a>
                </div>
            @endforeach

        </div>
    </div>

</x-layouts.app.header>
