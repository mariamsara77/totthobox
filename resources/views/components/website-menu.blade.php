{{-- সেটিংস মেনু --}}
@if (Request::is('profile*'))
    <flux:sidebar.item icon="cog" class="text-center mb-4 text-base">সেটিংস</flux:sidebar.item>

    <flux:sidebar.item icon="user" :href="route('profile.view')" :current="request()->routeIs('profile.view')"
        wire:navigate.hover>
        প্রোফাইল
    </flux:sidebar.item>

    <flux:sidebar.item icon="cog" :href="route('profile.settings')" :current="request()->routeIs('profile.settings')"
        wire:navigate.hover>
        প্রোফাইল সেটিংস
    </flux:sidebar.item>
    <flux:sidebar.item icon="briefcase" :href="route('profile.activity')"
        :current="request()->routeIs('profile.activity')" wire:navigate.hover>
        আমার কার্যক্রম
    </flux:sidebar.item>

    <flux:sidebar.item icon="key" :href="route('profile.password')" :current="request()->routeIs('profile.password')"
        wire:navigate.hover>
        পাসওয়ার্ড সেটিংস
    </flux:sidebar.item>

    <flux:sidebar.item icon="trash" :href="route('profile.remove')" :current="request()->routeIs('profile.remove')"
        wire:navigate.hover>
        প্রোফাইল মুছুন
    </flux:sidebar.item>

    <flux:sidebar.item icon="eye" :href="route('profile.appearance')"
        :current="request()->routeIs('profile.appearance')" wire:navigate.hover>
        প্রদর্শন ব্যবস্থা
    </flux:sidebar.item>
@endif

{{-- বাংলাদেশ সম্পর্কিত মেনু --}}
@if (Request::is('bangladesh*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">বাংলাদেশ</flux:sidebar.item>
    {{-- <flux:sidebar.item class="!text-center mb-4 text-base">বাংলাদেশ</flux:sidebar.item> --}}
    <flux:sidebar.item icon="flag" :href="route('bangladesh.introduction')"
        :current="request()->routeIs('bangladesh.introduction')" wire:navigate.hover>
        পরিচিতি
    </flux:sidebar.item>
    <flux:sidebar.item icon="map" :href="route('bangladesh.tourism')"
        :current="request()->routeIs('bangladesh.tourism')" wire:navigate.hover>
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
    <flux:sidebar.item icon="user-group" :href="route('bangladesh.public-figure')"
        :current="request()->routeIs('bangladesh.public-figure')" wire:navigate.hover>
        পাবলিক ফিগার
    </flux:sidebar.item>
@endif

{{-- আন্তর্জাতিক মেনু --}}
@if (Request::is('international*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">আন্তর্জাতিক</flux:sidebar.item>
    <flux:sidebar.item icon="home" :href="route('international.all-country')"
        :current="request()->routeIs('international.*')" wire:navigate.hover>
        বিশ্বকোষ
    </flux:sidebar.item>
@endif
{{-- সফটওয়্যার মেনু --}}
@if (Request::is('software*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">সফটওয়্যার</flux:sidebar.item>
    <flux:sidebar.item icon="home" :href="route('software.all')" :current="request()->routeIs('software.all')"
        wire:navigate.hover>
        সব সফটওয়্যার
    </flux:sidebar.item>
@endif

{{-- সংবাদ আর্কাইভ মেনু --}}
@if (Request::is('news*'))

    <flux:sidebar.item class="text-center! mb-2 text-sm font-bold tracking-wide uppercase text-zinc-500">
        সংবাদ আর্কাইভ
    </flux:sidebar.item>

    <flux:sidebar.item icon="newspaper" :href="route('news.headlines')"
        :current="request()->routeIs('news.headlines')" wire:navigate.hover>
        সব খবর
    </flux:sidebar.item>

    @php
        $groupedSources = cache()->remember('news_sidebar_grouped_v4', now()->addMinutes(30), function () {
            return \App\Models\NewsHeading::query()
                ->select('source_name', 'source_key', 'language', \DB::raw('COUNT(*) as total'))
                ->groupBy('source_key', 'source_name', 'language')
                ->get()
                ->sortBy('source_name')
                ->groupBy('language');
        });

        $bnOrder = ['prothom_alo', 'kalerkantho', 'samakal', 'jugantor', 'ittefaq', 'manabzamin', 'somoy_news'];
        $enOrder = ['daily_star', 'bdnews24', 'financial_express', 'new_age'];

        $sortByOrder = fn($items, $order) => $items->sortBy(
            fn($s) => array_search($s->source_key, $order) !== false ? array_search($s->source_key, $order) : 99,
        );
    @endphp

    {{-- বাংলা সোর্স --}}
    @if (isset($groupedSources['bn']) && $groupedSources['bn']->isNotEmpty())
        <div
            class="px-3 py-2 mt-3 text-xs font-bold text-zinc-400 uppercase tracking-widest border-t border-zinc-400/25">
            বাংলা সংবাদ মাধ্যম
        </div>

        @foreach ($sortByOrder($groupedSources['bn'], $bnOrder) as $source)
            {{-- ফ্রন্টএন্ড বেসিস সিম্পল চেক: ফ্লাক্সের আইকন ভিউ বা ফাইল ব্লেড ইঞ্জিনে রেজিস্টার্ড আছে কিনা --}}
            @php
                $hasIcon =
                    view()->exists("flux::icon.{$source->source_key}") ||
                    view()->exists("flux.icon.{$source->source_key}") ||
                    class_exists('Livewire\\Flux\\Components\\Icon\\' . Str::studly($source->source_key));

                $finalIcon = $hasIcon ? $source->source_key : 'newspaper';
            @endphp

            <flux:sidebar.item :href="route('news.source', $source->source_key)" :icon="$finalIcon"
                :current="request()->route('source_slug') === $source->source_key" wire:navigate.hover>
                <div class="flex items-center justify-between w-full">
                    <span>{{ $source->source_name }}</span>
                    <span class="text-xs text-zinc-400 tabular-nums">
                        {{ number_format($source->total) }}
                    </span>
                </div>
            </flux:sidebar.item>
        @endforeach
    @endif

    {{-- ইংরেজি সোর্স --}}
    @if (isset($groupedSources['en']) && $groupedSources['en']->isNotEmpty())
        <div
            class="px-3 py-2 mt-3 text-xs font-bold text-zinc-400 uppercase tracking-widest border-t border-zinc-400/25">
            English Media
        </div>

        @foreach ($sortByOrder($groupedSources['en'], $enOrder) as $source)
            @php
                $hasIcon =
                    view()->exists("flux::icon.{$source->source_key}") ||
                    view()->exists("flux.icon.{$source->source_key}") ||
                    class_exists('Livewire\\Flux\\Components\\Icon\\' . Str::studly($source->source_key));

                $finalIcon = $hasIcon ? $source->source_key : 'newspaper';
            @endphp

            <flux:sidebar.item :href="route('news.source', $source->source_key)" :icon="$finalIcon"
                :current="request()->route('source_slug') === $source->source_key" wire:navigate.hover>
                <div class="flex items-center justify-between w-full">
                    <span>{{ $source->source_name }}</span>
                    <span class="text-xs text-zinc-400 tabular-nums">
                        {{ number_format($source->total) }}
                    </span>
                </div>
            </flux:sidebar.item>
        @endforeach
    @endif

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

{{-- স্বাস্থ্য সম্পর্কিত মেনু --}}
@if (Request::is('islam*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">ইসলাম</flux:sidebar.item>
    <flux:sidebar.item icon="chart-bar" :href="route('islam.basicislam')"
        :current="request()->routeIs('islam.basicislam')" wire:navigate.hover>
        ইসলামের মৌলিক জ্ঞান
    </flux:sidebar.item>
    <flux:sidebar.item icon="home" :href="route('islam.dowan')" :current="request()->routeIs('islam.dowan*')"
        wire:navigate.hover>
        দোয়া
    </flux:sidebar.item>
    <flux:sidebar.item icon="heart" :href="route('islam.al-quran')" :current="request()->routeIs('islam.al-quran')"
        wire:navigate.hover>
        আল-কুরআন
    </flux:sidebar.item>
@endif

{{-- ক্রয়/বিক্রয় মেনু --}}
@if (Request::is('buysell*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">ক্রয়/বিক্রয়</flux:sidebar.item>

    <flux:sidebar.item icon="shopping-cart" :href="route('buysell.all')" :current="request()->routeIs('buysell.all')"
        wire:navigate.hover>
        সব ক্যাটাগরি
    </flux:sidebar.item>

    <flux:sidebar.item icon="plus" :href="route('buysell.post-ad')"
        :current="request()->routeIs('buysell.post-ad')" wire:navigate.hover>
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
@if (request()->routeIs('calendar.*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">ক্যালেন্ডার</flux:sidebar.item>

    <flux:sidebar.item icon="calendar" :href="route('calendar.calendar')"
        :current="request()->routeIs('calendar.calendar')" wire:navigate.hover>
        ক্যালেন্ডার
    </flux:sidebar.item>

    <flux:sidebar.item icon="sun" :href="route('calendar.holiday')"
        :current="request()->routeIs('calendar.holiday')" wire:navigate.hover>
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
    <flux:sidebar.item icon="chart-pie" :href="route('mcq.test-result')"
        :current="request()->routeIs('mcq.test-result')" wire:navigate.hover>
        এমসিকিউ ফলাফল
    </flux:sidebar.item>
@endif

{{-- কনভার্টার মেনু --}}
@if (Request::is('converter*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">কনভার্টার</flux:sidebar.item>

    {{-- গুরুত্বপূর্ণ এবং নিয়মিত ব্যবহৃত --}}
    <flux:sidebar.item icon="numbered-list" :href="route('converter.number-to-word')"
        :current="request()->routeIs('converter.number-to-word')" wire:navigate.hover>
        সংখ্যা থেকে শব্দ
    </flux:sidebar.item>
    <flux:sidebar.item icon="arrows-up-down" :href="route('converter.adarshalipi')"
        :current="request()->routeIs('converter.adarshalipi')" wire:navigate.hover>
        অদর্শলিপি কনভার্টার
    </flux:sidebar.item>

    <flux:sidebar.item icon="currency-dollar" :href="route('converter.currency')"
        :current="request()->routeIs('converter.currency')" wire:navigate.hover>
        মুদ্রা কনভার্টার
    </flux:sidebar.item>

    <flux:sidebar.item icon="bars-2" :href="route('converter.length')"
        :current="request()->routeIs('converter.length')" wire:navigate.hover>
        দৈর্ঘ্য কনভার্টার
    </flux:sidebar.item>

    <flux:sidebar.item icon="scale" :href="route('converter.weight')"
        :current="request()->routeIs('converter.weight')" wire:navigate.hover>
        ওজন কনভার্টার
    </flux:sidebar.item>

    <flux:sidebar.item icon="view-columns" :href="route('converter.volume')"
        :current="request()->routeIs('converter.volume')" wire:navigate.hover>
        পরিমাণ কনভার্টার
    </flux:sidebar.item>

    {{-- অন্যান্য কনভার্টার (Nav Group) --}}
    <flux:sidebar.group expandable icon="star" class="grid" heading="অন্যান্য কনভার্টার"
        :expanded="Request::is('converter/land*', 'converter/time*', 'converter/area*',
            'converter/temperature*', 'converter/speed*', 'converter/data*', 'converter/energy*')">



        <flux:sidebar.item icon="bars-2" :href="route('converter.land')"
            :current="request()->routeIs('converter.land')" wire:navigate.hover>
            জমি কনভার্টার
        </flux:sidebar.item>

        <flux:sidebar.item icon="clock" :href="route('converter.time')"
            :current="request()->routeIs('converter.time')" wire:navigate.hover>
            সময় কনভার্টার
        </flux:sidebar.item>

        <flux:sidebar.item icon="map" :href="route('converter.area')"
            :current="request()->routeIs('converter.area')" wire:navigate.hover>
            এলাকা কনভার্টার
        </flux:sidebar.item>


        <flux:sidebar.item icon="adjustments-vertical" :href="route('converter.temperature')"
            :current="request()->routeIs('converter.temperature')" wire:navigate.hover>
            তাপমাত্রা কনভার্টার
        </flux:sidebar.item>

        <flux:sidebar.item icon="home" :href="route('converter.speed')"
            :current="request()->routeIs('converter.speed')" wire:navigate.hover>
            গতিবেগ কনভার্টার
        </flux:sidebar.item>

        <flux:sidebar.item icon="circle-stack" :href="route('converter.data')"
            :current="request()->routeIs('converter.data')" wire:navigate.hover>
            ডেটা স্টোরেজ কনভার্টার
        </flux:sidebar.item>

        <flux:sidebar.item icon="bolt" :href="route('converter.energy')"
            :current="request()->routeIs('converter.energy')" wire:navigate.hover>
            শক্তি/পাওয়ার কনভার্টার
        </flux:sidebar.item>

    </flux:sidebar.group>
@endif

{{-- ডায়নামিক মেনু --}}

@if (Request::is('contact*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">জরুরী নাম্বার</flux:sidebar.item>
    @php
        $contactCategories = \App\Models\ContactCategory::all();
    @endphp
    @forelse ($contactCategories as $category)
        <flux:sidebar.item icon="{{ $category->icon }}" :href="route('contact.number', $category->slug)"
            :current="request()->routeIs('contact.number') && request()->route('slug') === $category->slug"
            wire:navigate.hover>
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
            :current="request()->routeIs('signs.sign') && request()->route('slug') === $category->slug"
            wire:navigate.hover>
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
@if (Request::is('ai/chat*'))
    <livewire:ai.sidebar-history />
@endif

@stack('sidebar')
