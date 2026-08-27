<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\NewsHeading;
use App\Models\BuySellCategory;
use App\Models\ContactCategory;
use App\Models\SignCategory;
use App\Models\ExcelTutorial;
use App\Models\AppResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

new class extends Component {
    #[Computed]
    public function newsSources()
    {
        if (!request()->is('news*')) {
            return collect(['bn' => collect(), 'en' => collect()]);
        }

        return Cache::remember('news_sidebar_grouped_v4', now()->addMinutes(30), function () {
            return NewsHeading::query()->select('source_name', 'source_key', 'language', DB::raw('COUNT(*) as total'))->groupBy('source_key', 'source_name', 'language')->get()->sortBy('source_name')->groupBy('language');
        });
    }

    public function sortNews($items, $order)
    {
        return collect($items)->sortBy(function ($s) use ($order) {
            $pos = array_search($s->source_key, $order);
            return $pos !== false ? $pos : 99;
        });
    }

    public function resolveFluxIcon($sourceKey)
    {
        $hasIcon = view()->exists("flux::icon.{$sourceKey}") || view()->exists("flux.icon.{$sourceKey}") || class_exists('Livewire\\Flux\\Components\\Icon\\' . Str::studly($sourceKey));

        return $hasIcon ? $sourceKey : 'newspaper';
    }

    #[Computed]
    public function buysellCategories()
    {
        return request()->is('buysell*') ? BuySellCategory::all() : collect();
    }

    #[Computed]
    public function contactCategories()
    {
        return request()->is('contact*') ? ContactCategory::all() : collect();
    }

    #[Computed]
    public function signCategories()
    {
        return request()->is('signs*') ? SignCategory::all() : collect();
    }

    #[Computed]
    public function excelChapters()
    {
        if (!request()->is('excel-expert*')) {
            return collect();
        }

        return ExcelTutorial::query()->where('is_published', true)->orderBy('position', 'asc')->get()->groupBy('chapter_name');
    }

    #[Computed]
    public function softwarePlatforms()
    {
        if (!request()->is('software*')) {
            return collect();
        }
        // ডাটাবেজ থেকে ইউনিক প্ল্যাটফর্মগুলো আনা হচ্ছে
        return AppResource::query()->select('platform')->distinct()->pluck('platform')->filter();
    }
}; ?>

<div>
    {{-- সেটিংস মেনু --}}
    @if (Request::is('profile*'))
    <flux:sidebar.item icon="cog" class="text-center mb-4 text-base">সেটিংস</flux:sidebar.item>
    <flux:sidebar.item icon="user" :href="route('profile.view')" :current="request()->routeIs('profile.view')"
        wire:navigate>প্রোফাইল</flux:sidebar.item>
    <flux:sidebar.item icon="cog" :href="route('profile.settings')" :current="request()->routeIs('profile.settings')"
        wire:navigate>প্রোফাইল সেটিংস</flux:sidebar.item>
    <flux:sidebar.item icon="briefcase" :href="route('profile.activity')"
        :current="request()->routeIs('profile.activity')" wire:navigate>আমার কার্যক্রম</flux:sidebar.item>
    <flux:sidebar.item icon="key" :href="route('profile.password')" :current="request()->routeIs('profile.password')"
        wire:navigate>পাসওয়ার্ড সেটিংস</flux:sidebar.item>
    <flux:sidebar.item icon="trash" :href="route('profile.remove')" :current="request()->routeIs('profile.remove')"
        wire:navigate>প্রোফাইল মুছুন</flux:sidebar.item>
    <flux:sidebar.item icon="eye" :href="route('profile.appearance')"
        :current="request()->routeIs('profile.appearance')" wire:navigate>প্রদর্শন ব্যবস্থা
    </flux:sidebar.item>
    @endif

    {{-- বাংলাদেশ সম্পর্কিত মেনু --}}
    @if (Request::is('bangladesh*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">বাংলাদেশ</flux:sidebar.item>
    <flux:sidebar.item icon="flag" :href="route('bangladesh.introduction')"
        :current="request()->routeIs('bangladesh.introduction*')" wire:navigate>পরিচিতি</flux:sidebar.item>
    <flux:sidebar.item icon="map" :href="route('bangladesh.tourism')"
        :current="request()->routeIs('bangladesh.tourism*')" wire:navigate>পর্যটন</flux:sidebar.item>
    <flux:sidebar.item icon="book-open" :href="route('bangladesh.history')"
        :current="request()->routeIs('bangladesh.history*')" wire:navigate>ইতিহাস</flux:sidebar.item>
    <flux:sidebar.item icon="building-library" :href="route('bangladesh.establishment')"
        :current="request()->routeIs('bangladesh.establishment*')" wire:navigate>স্থাপনা</flux:sidebar.item>
    <flux:sidebar.item icon="user-group" :href="route('bangladesh.public-figure')"
        :current="request()->routeIs('bangladesh.public-figure*')" wire:navigate>পাবলিক ফিগার
    </flux:sidebar.item>
    @endif

    {{-- আন্তর্জাতিক মেনু --}}
    @if (Request::is('international*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">আন্তর্জাতিক</flux:sidebar.item>
    <flux:sidebar.item icon="home" :href="route('international.all-country')"
        :current="request()->routeIs('international.*')" wire:navigate>বিশ্বকোষ</flux:sidebar.item>
    @endif

    {{-- সফটওয়্যার মেনু --}}
    @if (Request::is('software*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">সফটওয়্যার</flux:sidebar.item>

    {{-- সব সফটওয়্যার (প্যারামিটার ছাড়া) --}}
    {{-- সব সফটওয়্যার --}}
    <flux:sidebar.item icon="presentation-chart-bar" :href="route('software.all')"
        :current="request()->routeIs('software.all') && empty(request()->route('platform'))" wire:navigate>সব
        সফটওয়্যার</flux:sidebar.item>

    {{-- ডায়নামিক প্ল্যাটফর্ম ক্যাটাগরি --}}
    @foreach ($this->softwarePlatforms as $platform)
    <flux:sidebar.item icon="squares-2x2" :href="route('software.all', ['platform' => $platform])"
        :current="request()->route('platform') === $platform" wire:navigate>
        {{ $platform }}
    </flux:sidebar.item>
    @endforeach
    @endif

    {{-- সংবাদ আর্কাইভ মেনু --}}
    @if (Request::is('news*'))
    <flux:sidebar.item class="text-center! mb-2 text-sm font-bold tracking-wide uppercase text-zinc-500">সংবাদ
        আর্কাইভ
    </flux:sidebar.item>
    <flux:sidebar.item icon="newspaper" :href="route('news.headlines')" :current="request()->routeIs('news.headlines')"
        wire:navigate>সব খবর</flux:sidebar.item>

    {{-- বাংলা সোর্স --}}
    @if (isset($this->newsSources['bn']) && $this->newsSources['bn']->isNotEmpty())
    <flux:sidebar.item class="text-xs font-bold text-zinc-400 uppercase tracking-widest mt-4 mb-2 pointer-events-none">
        বাংলা
        সংবাদ মাধ্যম</flux:sidebar.item>
    @php $bnOrder = ['prothom_alo', 'kalerkantho', 'samakal', 'jugantor', 'ittefaq', 'manabzamin', 'somoy_news'];
    @endphp

    @foreach ($this->sortNews($this->newsSources['bn'], $bnOrder) as $source)
    <flux:sidebar.item :href="route('news.source', $source->source_key)"
        :icon="$this->resolveFluxIcon($source->source_key)"
        :current="request()->route('source_slug') === $source->source_key" :badge="number_format($source->total)"
        wire:navigate>
        {{ $source->source_name }}
    </flux:sidebar.item>
    @endforeach
    @endif

    {{-- ইংরেজি সোর্স --}}
    @if (isset($this->newsSources['en']) && $this->newsSources['en']->isNotEmpty())
    <flux:sidebar.item class="text-xs font-bold text-zinc-400 uppercase tracking-widest mt-4 mb-2 pointer-events-none">
        English
        Media</flux:sidebar.item>
    @php $enOrder = ['daily_star', 'bdnews24', 'financial_express', 'new_age']; @endphp

    @foreach ($this->sortNews($this->newsSources['en'], $enOrder) as $source)
    <flux:sidebar.item :href="route('news.source', $source->source_key)"
        :icon="$this->resolveFluxIcon($source->source_key)"
        :current="request()->route('source_slug') === $source->source_key" :badge="number_format($source->total)"
        wire:navigate>
        {{ $source->source_name }}
    </flux:sidebar.item>
    @endforeach
    @endif
    @endif

    {{-- স্বাস্থ্য সম্পর্কিত মেনু --}}
    @if (Request::is('health*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">স্বাস্থ্য</flux:sidebar.item>
    <flux:sidebar.item icon="chart-bar" :href="route('health.calorie-chart')"
        :current="request()->routeIs('health.calorie-chart')" wire:navigate>ক্যালোরী চার্ট
    </flux:sidebar.item>
    <flux:sidebar.item icon="home" :href="route('health.food-nutrients')"
        :current="request()->routeIs('health.food-nutrients')" wire:navigate>খাদ্য পুষ্টি</flux:sidebar.item>
    <flux:sidebar.item icon="heart" :href="route('health.basic-health')"
        :current="request()->routeIs('health.basic-health')" wire:navigate>মৌলিক স্বাস্থ্য
    </flux:sidebar.item>
    @endif

    {{-- ইসলাম মেনু --}}
    @if (Request::is('islam*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">ইসলাম</flux:sidebar.item>
    <flux:sidebar.item icon="moon" :href="route('islam.basicislam')" :current="request()->routeIs('islam.basicislam')"
        wire:navigate>ইসলামের মৌলিক জ্ঞান
    </flux:sidebar.item>
    <flux:sidebar.item icon="chart-bar" :href="route('islam.dowan')" :current="request()->routeIs('islam.dowan*')"
        wire:navigate>দোয়া</flux:sidebar.item>
    @endif

    {{-- ক্রয়/বিক্রয় মেনু --}}
    @if (Request::is('buysell*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">ক্রয়/বিক্রয়</flux:sidebar.item>
    <flux:sidebar.item icon="shopping-cart" :href="route('buysell.all')" :current="request()->routeIs('buysell.all')"
        wire:navigate>সব ক্যাটাগরি</flux:sidebar.item>
    <flux:sidebar.item icon="plus" :href="route('buysell.post-ad')" :current="request()->routeIs('buysell.post-ad')"
        wire:navigate>পোস্ট যোগ করুন</flux:sidebar.item>

    @forelse ($this->buysellCategories as $category)
    <flux:sidebar.item icon="{{ $category->icon }}" :href="route('buysell.category', $category->slug)"
        :current="request()->routeIs('buysell.category') && request()->route('categorySlug') === $category->slug"
        wire:navigate>
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
        :current="request()->routeIs('calendar.calendar')" wire:navigate>ক্যালেন্ডার</flux:sidebar.item>
    <flux:sidebar.item icon="sun" :href="route('calendar.holiday')" :current="request()->routeIs('calendar.holiday')"
        wire:navigate>ছুটির দিন</flux:sidebar.item>
    @endif

    {{-- শিশু শিক্ষা মেনু --}}
    @if (Request::is('education/child*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">শিশু শিক্ষা</flux:sidebar.item>
    <flux:sidebar.item icon="pencil" :href="route('education.child.practice')"
        :current="request()->routeIs('education.child.practice')" wire:navigate>অনুশীলন</flux:sidebar.item>
    @endif

    {{-- এমসিকিউ মেনু --}}
    @if (Request::is('mcq*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">এমসিকিউ</flux:sidebar.item>
    <flux:sidebar.item icon="document-text" :href="route('mcq.home')" :current="request()->routeIs('mcq.home')"
        wire:navigate>এমসিকিউ সূচি</flux:sidebar.item>
    <flux:sidebar.item icon="chart-pie" :href="route('mcq.test-result')"
        :current="request()->routeIs('mcq.test-result')" wire:navigate>এমসিকিউ ফলাফল</flux:sidebar.item>
    @endif

    {{-- কনভার্টার মেনু --}}
    @if (Request::is('converter*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">কনভার্টার</flux:sidebar.item>
    <flux:sidebar.item icon="numbered-list" :href="route('converter.number-to-word')"
        :current="request()->routeIs('converter.number-to-word')" wire:navigate>Number to Word
    </flux:sidebar.item>
    <flux:sidebar.item icon="arrows-up-down" :href="route('converter.adarshalipi')"
        :current="request()->routeIs('converter.adarshalipi')" wire:navigate>Adorsholipi Conveter
    </flux:sidebar.item>
    <flux:sidebar.item icon="photo" :href="route('converter.image')" :current="request()->routeIs('converter.image')"
        wire:navigate>Image Converter
    </flux:sidebar.item>

    <flux:sidebar.item icon="document-text" :href="route('converter.document')"
        :current="request()->routeIs('converter.document')" wire:navigate>Documents Converter
    </flux:sidebar.item>

    <flux:sidebar.item icon="video-camera" :href="route('converter.media')"
        :current="request()->routeIs('converter.media')" wire:navigate>Media COnveter(Audio/Video)
    </flux:sidebar.item>

    <flux:sidebar.item icon="document-duplicate" :href="route('converter.file-data')"
        :current="request()->routeIs('converter.file-data')" wire:navigate>Deta File Converter
        (CSV/JSON/XML)
    </flux:sidebar.item>
    <flux:sidebar.item icon="currency-dollar" :href="route('converter.currency')"
        :current="request()->routeIs('converter.currency')" wire:navigate>মুদ্রা কনভার্টার
    </flux:sidebar.item>
    <flux:sidebar.item icon="bars-2" :href="route('converter.length')" :current="request()->routeIs('converter.length')"
        wire:navigate>দৈর্ঘ্য কনভার্টার</flux:sidebar.item>
    <flux:sidebar.item icon="scale" :href="route('converter.weight')" :current="request()->routeIs('converter.weight')"
        wire:navigate>ওজন কনভার্টার</flux:sidebar.item>
    <flux:sidebar.item icon="view-columns" :href="route('converter.volume')"
        :current="request()->routeIs('converter.volume')" wire:navigate>পরিমাণ কনভার্টার</flux:sidebar.item>

    <flux:sidebar.group expandable icon="star" class="grid" heading="অন্যান্য কনভার্টার" :expanded="Request::is('converter/land*', 'converter/time*', 'converter/area*', 'converter/temperature*',
                        'converter/speed*', 'converter/data*', 'converter/energy*')">
        <flux:sidebar.item icon="bars-2" :href="route('converter.land')" :current="request()->routeIs('converter.land')"
            wire:navigate>জমি কনভার্টার</flux:sidebar.item>
        <flux:sidebar.item icon="clock" :href="route('converter.time')" :current="request()->routeIs('converter.time')"
            wire:navigate>সময় কনভার্টার</flux:sidebar.item>
        <flux:sidebar.item icon="map" :href="route('converter.area')" :current="request()->routeIs('converter.area')"
            wire:navigate>এলাকা কনভার্টার</flux:sidebar.item>
        <flux:sidebar.item icon="adjustments-vertical" :href="route('converter.temperature')"
            :current="request()->routeIs('converter.temperature')" wire:navigate>তাপমাত্রা কনভার্টার
        </flux:sidebar.item>
        <flux:sidebar.item icon="home" :href="route('converter.speed')" :current="request()->routeIs('converter.speed')"
            wire:navigate>গতিবেগ কনভার্টার
        </flux:sidebar.item>
        <flux:sidebar.item icon="circle-stack" :href="route('converter.unit-data')"
            :current="request()->routeIs('converter.unit-data')" wire:navigate>ডেটা স্টোরেজ কনভার্টার
        </flux:sidebar.item>
        <flux:sidebar.item icon="bolt" :href="route('converter.energy')"
            :current="request()->routeIs('converter.energy')" wire:navigate>শক্তি/পাওয়ার কনভার্টার
        </flux:sidebar.item>
    </flux:sidebar.group>
    @endif

    {{-- ফাইল কনভার্টার মেনু --}}
    {{-- ফাইল কনভার্টার মেনু --}}
    @if (Request::is('tools*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">বিভিন্ন টুলস</flux:sidebar.item>

    <flux:sidebar.item icon="cursor-arrow-ripple" :href="route('tools.image-resizer')"
        :current="request()->routeIs('tools.image-resizer')" wire:navigate>Image Resizer
    </flux:sidebar.item>

    <flux:sidebar.item icon="cake" :href="route('tools.age-calculator')"
        :current="request()->routeIs('tools.age-calculator')" wire:navigate>Age Calculator
    </flux:sidebar.item>

    <flux:sidebar.item icon="document-text" :href="route('tools.word-counter')"
        :current="request()->routeIs('tools.word-counter')" wire:navigate>Word & Character Counter
    </flux:sidebar.item>

    <flux:sidebar.item icon="sparkles" :href="route('tools.zodiac-calculator')"
        :current="request()->routeIs('tools.zodiac-calculator')" wire:navigate>Zodiac(রাশি) Calculator
    </flux:sidebar.item>
    <flux:sidebar.item icon="percent-badge" :href="route('tools.percentage-calculator')"
        :current="request()->routeIs('tools.percentage-calculator')" wire:navigate>Percentage(%) Calculator
    </flux:sidebar.item>
    <flux:sidebar.item icon="qr-code" :href="route('tools.qrcode-generator')"
        :current="request()->routeIs('tools.qrcode-generator')" wire:navigate>QR Code Generator
    </flux:sidebar.item>
    @endif

    {{-- জরুরী নাম্বার --}}
    @if (Request::is('contact*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">জরুরী নাম্বার</flux:sidebar.item>
    @forelse ($this->contactCategories as $category)
    <flux:sidebar.item icon="{{ $category->icon }}" :href="route('contact.number', $category->slug)"
        :current="request()->routeIs('contact.number') && request()->route('slug') === $category->slug" wire:navigate>
        {{ $category->name }}
    </flux:sidebar.item>
    @empty
    <flux:sidebar.item icon="exclamation-circle">কোনো পরিচিতি ক্যাটাগরি পাওয়া যায়নি</flux:sidebar.item>
    @endforelse
    @endif

    {{-- বিভিন্ন সংকেত --}}
    @if (Request::is('signs*'))
    <flux:sidebar.item class="!text-center mb-4 text-base">বিভিন্ন সংকেত</flux:sidebar.item>
    <flux:sidebar.item class="" icon="sign" :href="route('signs.sign.all')"
        :current="request()->routeIs('signs.sign.all')" wire:navigate>সব সংকেত
    </flux:sidebar.item>
    @forelse ($this->signCategories as $category)
    <flux:sidebar.item :icon="$category->icon" :href="route('signs.sign', $category->slug)"
        :current="request()->routeIs('signs.sign') && request()->route('slug') === $category->slug" wire:navigate>
        {{ $category->name }}
    </flux:sidebar.item>
    @empty
    <flux:sidebar.item icon="exclamation-circle">কোনো সাইন ক্যাটাগরি পাওয়া যায়নি</flux:sidebar.item>
    @endforelse
    @endif

    {{-- Excel টিউটোরিয়াল --}}
    @if (Request::is('excel-expert*'))
    <flux:sidebar.item class="!text-center mb-4 text-lg font-bold text-green-600 border-b pb-2">Excel টিউটোরিয়াল
    </flux:sidebar.item>
    @forelse ($this->excelChapters as $chapterName => $lessons)
    <div class="px-3 py-2 mt-4 text-xs font-bold text-zinc-400 uppercase tracking-widest">{{ $chapterName }}
    </div>
    @foreach ($lessons as $lesson)
    <flux:sidebar.item icon="document-text" :href="route('excel.view', $lesson->slug)"
        :current="request()->route('slug') === $lesson->slug" wire:navigate>
        {{ $lesson->title }}
    </flux:sidebar.item>
    @endforeach
    @empty
    <flux:sidebar.item icon="exclamation-circle">কোনো লেসন পাওয়া যায়নি</flux:sidebar.item>
    @endforelse
    @endif

    {{-- AI চ্যাট --}}
    @if (Request::is('ai/chat*'))
    <livewire:ai.sidebar-history />
    @endif

    @stack('sidebar')


</div>