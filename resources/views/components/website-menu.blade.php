{{-- সেটিংস মেনু --}}
@if (Request::is('settings*'))
    <flux:sidebar.item icon="cog" class="text-center mb-4 text-base">সেটিংস</flux:sidebar.item>
    <flux:sidebar.item icon="cog" :href="route('settings.profile.view', ['slug' => auth()->user()->slug])"
        :current="request()->routeIs('settings.profile.view')" wire:navigate.hover>
        প্রোফাইল
    </flux:sidebar.item>
    <flux:sidebar.item icon="cog" :href="route('settings.profile')" :current="request()->routeIs('settings.profile')"
        wire:navigate.hover>
        প্রোফাইল সেটিংস
    </flux:sidebar.item>
    <flux:sidebar.item icon="key" :href="route('settings.password')" :current="request()->routeIs('settings.password')"
        wire:navigate.hover>
        পাসওয়ার্ড সেটিংস
    </flux:sidebar.item>
    <flux:sidebar.item icon="eye" :href="route('settings.appearance')" :current="request()->routeIs('settings.appearance')"
        wire:navigate.hover>
        প্রদর্শন ব্যবস্থা
    </flux:sidebar.item>
@endif

{{-- বাংলাদেশ সম্পর্কিত মেনু --}}
@if (Request::is('bangladesh*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">সেটিংস</flux:sidebar.item>
    {{-- <flux:sidebar.item class="!text-center mb-4 text-base">বাংলাদেশ</flux:sidebar.item> --}}
    <flux:sidebar.item icon="flag" :href="route('bangladesh.introduction')"
        :current="request()->routeIs('bangladesh.introduction')" wire:navigate.hover>
        পরিচিতি
    </flux:sidebar.item>
    <flux:sidebar.item icon="map" :href="route('bangladesh.tourism')" :current="request()->routeIs('bangladesh.tourism')"
        wire:navigate.hover>
        পর্যটন
    </flux:sidebar.item>
    <flux:sidebar.item icon="book-open" :href="route('bangladesh.history')"
        :current="request()->routeIs('bangladesh.history')" wire:navigate.hover>
        ইতিহাস
    </flux:sidebar.item>
    <flux:sidebar.item icon="building-library" :href="route('bangladesh.establishment')"
        :current="request()->routeIs('bangladesh.establishment')" wire:navigate.hover>
        প্রতিষ্ঠা
    </flux:sidebar.item>
    <flux:sidebar.item icon="user-group" :href="route('bangladesh.minister')"
        :current="request()->routeIs('bangladesh.minister')" wire:navigate.hover>
        মন্ত্রী
    </flux:sidebar.item>
@endif

{{-- আন্তর্জাতিক মেনু --}}
@if (Request::is('international*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">আন্তর্জাতিক</flux:sidebar.item>
    <flux:sidebar.item icon="home" :href="route('international.all-country')"
        :current="request()->routeIs('international.all-country')" wire:navigate.hover>
        সব দেশ
    </flux:sidebar.item>
@endif

{{-- ইসলাম সম্পর্কিত মেনু --}}
@if (Request::is('islam*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">ইসলাম</flux:sidebar.item>
    <flux:sidebar.item icon="moon" :href="route('islam.basicislam')" :current="request()->routeIs('islam.basicislam')"
        wire:navigate.hover>
        ইসলামের মূলনীতি
    </flux:sidebar.item>
    <flux:sidebar.item icon="home" :href="route('islam.dowa')" :current="request()->routeIs('islam.dowa')"
        wire:navigate.hover>
        দোয়া
    </flux:sidebar.item>
    <flux:sidebar.item icon="book-open" :href="route('islam.al-quran')" :current="request()->routeIs('islam.al-quran')"
        wire:navigate.hover>
        কুরআন
    </flux:sidebar.item>
@endif

{{-- স্বাস্থ্য সম্পর্কিত মেনু --}}
@if (Request::is('health*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">স্বাস্থ্য</flux:sidebar.item>
    <flux:sidebar.item icon="chart-bar" :href="route('health.calorie-chart')"
        :current="request()->routeIs('health.calorie-chart')" wire:navigate.hover>
        ক্যালোরী চার্ট
    </flux:sidebar.item>
    <flux:sidebar.item icon="home" :href="route('health.food-nutrients')"
        :current="request()->routeIs('health.food-nutrients')" wire:navigate.hover>
        খাদ্য পুষ্টি
    </flux:sidebar.item>
    <flux:sidebar.item icon="heart" :href="route('health.basic-health')"
        :current="request()->routeIs('health.basic-health')" wire:navigate.hover>
        মৌলিক স্বাস্থ্য
    </flux:sidebar.item>
@endif

{{-- ক্রয়/বিক্রয় মেনু --}}
@if (Request::is('buysell*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">ক্রয়/বিক্রয়</flux:sidebar.item>

    <flux:sidebar.item icon="shopping-cart" :href="route('buysell.all')" :current="request()->routeIs('buysell.all')"
        wire:navigate.hover>
        সব ক্যাটাগরি
    </flux:sidebar.item>

    <flux:sidebar.item icon="plus" :href="route('buysell.post-ad')" :current="request()->routeIs('buysell.post-ad')"
        wire:navigate.hover>
        পোস্ট যোগ করুন
    </flux:sidebar.item>

    @php
        $buysellCategories = \App\Models\BuySellCategory::all();
    @endphp

    @forelse ($buysellCategories as $category)
        <flux:sidebar.item icon="{{ $category->icon }}" :href="route('buysell.category', $category->slug)"
            :current="request()->routeIs('buysell.category') && request()->route('categorySlug') === $category->slug"
            wire:navigate.hover>
            {{ $category->name }}
        </flux:sidebar.item>
    @empty
        <flux:sidebar.item icon="exclamation-circle">কোনো পরিচিতি ক্যাটাগরি পাওয়া যায়নি</flux:sidebar.item>
    @endforelse
@endif


{{-- ক্যালেন্ডার মেনু --}}
@if (Request::is('calendar*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">ক্যালেন্ডার</flux:sidebar.item>
    <flux:sidebar.item icon="calendar" :href="route('calendar.calendar')" :current="request()->routeIs('calendar.calendar')"
        wire:navigate.hover>
        ক্যালেন্ডার
    </flux:sidebar.item>
    <flux:sidebar.item icon="sun" :href="route('calendar.holiday')" :current="request()->routeIs('calendar.holiday')"
        wire:navigate.hover>
        ছুটির দিন
    </flux:sidebar.item>
@endif

{{-- শিশু শিক্ষা মেনু --}}
@if (Request::is('education/child*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">শিশু শিক্ষা</flux:sidebar.item>
    <flux:sidebar.item icon="pencil" :href="route('education.child.practice')"
        :current="request()->routeIs('education.child.practice')" wire:navigate.hover>
        অনুশীলন
    </flux:sidebar.item>
@endif

{{-- এমসিকিউ মেনু --}}
@if (Request::is('mcq*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">এমসিকিউ</flux:sidebar.item>
    <flux:sidebar.item icon="document-text" :href="route('mcq.home')" :current="request()->routeIs('mcq.home')"
        wire:navigate.hover>
        এমসিকিউ সূচি
    </flux:sidebar.item>
    <flux:sidebar.item icon="chart-pie" :href="route('mcq.test-result')" :current="request()->routeIs('mcq.test-result')"
        wire:navigate.hover>
        এমসিকিউ ফলাফল
    </flux:sidebar.item>
@endif

{{-- রূপান্তর মেনু --}}
@if (Request::is('converter*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">রূপান্তর</flux:sidebar.item>
    <flux:sidebar.item icon="currency-dollar" :href="route('converter.currency')"
        :current="request()->routeIs('converter.currency')" wire:navigate.hover>
        মুদ্রা রূপান্তর
    </flux:sidebar.item>
    <flux:sidebar.item icon="bars-2" :href="route('converter.land')" :current="request()->routeIs('converter.land')"
        wire:navigate.hover> জমি রূপান্তর
    </flux:sidebar.item>
    <flux:sidebar.item icon="bars-2" :href="route('converter.length')" :current="request()->routeIs('converter.length')"
        wire:navigate.hover>
        দৈর্ঘ্য রূপান্তর
    </flux:sidebar.item>
    <flux:sidebar.item icon="scale" :href="route('converter.weight')" :current="request()->routeIs('converter.weight')"
        wire:navigate.hover>
        ওজন রূপান্তর
    </flux:sidebar.item>
    <flux:sidebar.item icon="map" :href="route('converter.area')" :current="request()->routeIs('converter.area')"
        wire:navigate.hover>
        এলাকা রূপান্তর
    </flux:sidebar.item>
    <flux:sidebar.item icon="view-columns" :href="route('converter.volume')"
        :current="request()->routeIs('converter.volume')" wire:navigate.hover>
        পরিমাণ রূপান্তর
    </flux:sidebar.item>
    <flux:sidebar.item icon="adjustments-vertical" :href="route('converter.temperature')"
        :current="request()->routeIs('converter.temperature')" wire:navigate.hover>
        তাপমাত্রা রূপান্তর
    </flux:sidebar.item>
    <flux:sidebar.item icon="home" :href="route('converter.speed')" :current="request()->routeIs('converter.speed')"
        wire:navigate.hover>
        গতিবেগ রূপান্তর
    </flux:sidebar.item>
    <flux:sidebar.item icon="clock" :href="route('converter.time')" :current="request()->routeIs('converter.time')"
        wire:navigate.hover>
        সময় রূপান্তর
    </flux:sidebar.item>
    <flux:sidebar.item icon="circle-stack" :href="route('converter.data')" :current="request()->routeIs('converter.data')"
        wire:navigate.hover>
        ডেটা স্টোরেজ রূপান্তর
    </flux:sidebar.item>
    <flux:sidebar.item icon="bolt" :href="route('converter.energy')" :current="request()->routeIs('converter.energy')"
        wire:navigate.hover>
        শক্তি/পাওয়ার রূপান্তর
    </flux:sidebar.item>
@endif

{{-- ডায়নামিক মেনু --}}

@if (Request::is('contact*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">জরুরী নাম্বার</flux:sidebar.item>
    @php
        $contactCategories = \App\Models\ContactCategory::all();
    @endphp
    @forelse ($contactCategories as $category)
        <flux:sidebar.item icon="{{ $category->icon }}" :href="route('contact.number', $category->slug)"
            :current="request()->routeIs('contact.number') && request()->route('slug') === $category->slug" wire:navigate.hover>
            {{ $category->name }}
        </flux:sidebar.item>
    @empty
        <flux:sidebar.item icon="exclamation-circle">কোনো পরিচিতি ক্যাটাগরি পাওয়া যায়নি</flux:sidebar.item>
    @endforelse
@endif

@if (Request::is('signs*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">বিভিন্ন সংকেত</flux:sidebar.item>
    @php
        $signCategories = \App\Models\SignCategory::all();
    @endphp
    @forelse ($signCategories as $category)
        <flux:sidebar.item :icon="$category->icon" :href="route('signs.sign', $category->slug)"
            :current="request()->routeIs('signs.sign') && request()->route('slug') === $category->slug" wire:navigate.hover>
            {{ $category->name }}
        </flux:sidebar.item>
    @empty
        <flux:sidebar.item icon="exclamation-circle">কোনো সাইন ক্যাটাগরি পাওয়া যায়নি</flux:sidebar.item>
    @endforelse
@endif


@if (Request::is('excel-expert*'))
    <flux:sidebar.item class="!text-center mb-4 text-lg font-bold text-green-600 border-b pb-2">
        Excel টিউটোরিয়াল
    </flux:sidebar.item>

    @php
        // চ্যাপ্টার অনুযায়ী লেসনগুলো গ্রুপ করে নিয়ে আসা
        $excelChapters = \App\Models\ExcelTutorial::where('is_published', true)
            ->orderBy('position', 'asc')
            ->get()
            ->groupBy('chapter_name');
    @endphp

    @forelse ($excelChapters as $chapterName => $lessons)
        {{-- চ্যাপ্টারের নাম --}}
        <div class="px-3 py-2 mt-4 text-xs font-bold text-zinc-400 uppercase tracking-widest">
            {{ $chapterName }}
        </div>

        {{-- ওই চ্যাপ্টারের আন্ডারে থাকা লেসনগুলো --}}
        @foreach ($lessons as $lesson)
            <flux:sidebar.item icon="document-text" :href="route('excel.view', $lesson->slug)"
                :current="request()->route('slug') === $lesson->slug" wire:navigate.hover>
                {{ $lesson->title }}
            </flux:sidebar.item>
        @endforeach

    @empty
        <flux:sidebar.item icon="exclamation-circle">কোনো লেসন পাওয়া যায়নি</flux:sidebar.item>
    @endforelse
@endif