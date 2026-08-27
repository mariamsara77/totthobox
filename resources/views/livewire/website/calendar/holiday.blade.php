<?php

use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    #[Url(history: true, keep: false)]
    public string $search = '';

    #[Url(history: true)]
    public $selectedYear;

    #[Url(history: true)]
    public $selectedType = '';

    #[Url(history: true)]
    public $fromDate = '';

    #[Url(history: true)]
    public $toDate = '';

    public $perPage = 15;

    public function mount()
    {
        $this->selectedYear = $this->selectedYear ?? now()->year;
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }

    #[Computed]
    public function years()
    {
        return Holiday::selectRaw('YEAR(date) as year')->distinct()->orderBy('year', 'desc')->pluck('year');
    }

    #[Computed]
    public function holidayTypes()
    {
        return Holiday::select('type')->distinct()->whereNotNull('type')->orderBy('type', 'asc')->pluck('type');
    }

    #[Computed]
    public function holidays()
    {
        return Holiday::query()
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->selectedYear && !$this->fromDate, fn($q) => $q->whereYear('date', $this->selectedYear))
            ->when($this->selectedType, fn($q) => $q->where('type', $this->selectedType))
            ->when($this->fromDate, fn($q) => $q->whereDate('date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('date', '<=', $this->toDate))
            ->orderBy('date', 'asc')
            ->limit($this->perPage)
            ->get();
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember('holiday_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', Holiday::class)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get();
        });
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'selectedYear', 'selectedType', 'fromDate', 'toDate'])) {
            $this->perPage = 15;
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedType', 'fromDate', 'toDate']);
        $this->selectedYear = now()->year;
        $this->perPage = 15;
    }

    private function generateSeoData()
    {
        $year = $this->selectedYear ?? now()->year;
        $type = $this->selectedType ?: 'সকল ধরণের';

        $title = "{$year} সালের সরকারি ও ঐচ্ছিক ছুটির তালিকা";
        if ($this->search) {
            $title = "'{$this->search}' - ছুটির বিস্তারিত তথ্য ({$year})";
        }

        $description = "{$year} সালের বাংলাদেশের {$type} সরকারি ছুটি, ঐচ্ছিক ছুটি এবং উৎসবের পূর্ণাঙ্গ ক্যালেন্ডার। ";
        if ($this->fromDate && $this->toDate) {
            $description .= Carbon::parse($this->fromDate)->format('d M') . ' থেকে ' . Carbon::parse($this->toDate)->format('d M') . ' পর্যন্ত ছুটির তালিকা দেখুন।';
        } else {
            $description .= 'আপনার প্রয়োজনীয় ছুটির দিনগুলো আগেভাগেই দেখে নিন এবং পরিকল্পনা করুন।';
        }

        return [
            'title' => "{$title} | তথ্যবক্স",
            'description' => $description,
            'keywords' => "ছুটির তালিকা {$year}, সরকারি ছুটি, বাংলাদেশের ক্যালেন্ডার, ঐচ্ছিক ছুটি, উৎসবের দিন, {$type} ছুটি, সরকারি ছুটির তালিকা",
        ];
    }

    public function with(): array
    {
        return [
            'seo' => $this->generateSeoData(),
        ];
    }
}; ?>

{{-- ═══════════════════════════════════════════════════════════════
ছুটির ক্যালেন্ডার – ALL DATA LIST
SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo :title="$seo['title']" :description="$seo['description']" :keywords="$seo['keywords']" />

    {{-- Page Header --}}
    <header class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-4">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                <flux:icon icon="calendar" class="text-amber-600 dark:text-amber-500" />
                ছুটির ক্যালেন্ডার
            </flux:heading>

            <flux:text variant="subtle" class="mt-1">
                বাংলাদেশের সরকারি ও নির্ধারিত ছুটির সম্পূর্ণ তালিকা — {{ $selectedYear ?? now()->year }} সাল
            </flux:text>
        </div>

        <div>
            <flux:tooltip toggleable>
                <flux:button icon="users" size="sm" variant="subtle"
                    aria-label="তথ্য প্রদানকারী ও কন্ট্রিবিউটরদের তালিকা দেখুন" />

                <flux:tooltip.content class="w-80 space-y-4 p-4">
                    <div class="space-y-1">
                        <flux:heading size="lg">
                            তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                        </flux:heading>
                        <flux:text size="sm" variant="subtle">
                            এই ছুটির ক্যালেন্ডার পেজের কন্টেন্ট তৈরি ও যাচাইকরণে যারা অবদান রেখেছেন
                        </flux:text>
                    </div>

                    <div class="max-h-80 overflow-y-auto space-y-3">
                        @forelse ($this->creators as $creator)
                            <flux:card class="space-y-2">
                                <div class="flex items-start gap-4">
                                    <flux:avatar src="{{ $creator->avatar_url }}" size="md" badge
                                        badge:color="{{ $creator->isOnline() ? 'green' : 'zinc' }}"
                                        alt="{{ $creator->name }} এর প্রোফাইল ছবি" />

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <flux:text variant="strong" class="truncate">
                                                {{ $creator->name }}
                                            </flux:text>

                                            @if ($creator->email_verified_at)
                                                <flux:icon.check-badge class="size-4 text-emerald-500" variant="solid"
                                                    aria-label="ভেরিফাইড অ্যাকাউন্ট" />
                                            @endif
                                        </div>

                                        <flux:text size="sm" variant="subtle" class="truncate">
                                            {{ $creator->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর' }}
                                        </flux:text>
                                    </div>
                                </div>

                                <flux:separator class="opacity-50" />

                                <div class="flex items-center justify-between">
                                    <flux:text size="sm" variant="subtle">
                                        সর্বশেষ সক্রিয়:
                                        {{ $creator->last_active_at ? bn_num($creator->last_active_at->diffForHumans()) : 'অজানা' }}
                                    </flux:text>

                                    <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost"
                                        size="xs" icon="arrow-right"
                                        aria-label="{{ $creator->name }} এর সম্পূর্ণ প্রোফাইল দেখুন" />
                                </div>
                            </flux:card>
                        @empty
                            <flux:text size="sm" variant="subtle" class="py-2 text-center">
                                এখনো কোনো কন্ট্রিবিউটর পাওয়া যায়নি।
                            </flux:text>
                        @endforelse
                    </div>

                    <flux:separator class="opacity-50" />

                    <flux:text size="sm" variant="subtle" class="text-center">
                        আমাদের সকল তথ্য ভেরিফাইড, নির্ভরযোগ্য এবং নিয়মিত আপডেটেড।
                    </flux:text>
                </flux:tooltip.content>
            </flux:tooltip>
        </div>
    </header>

    {{-- Search + Filters --}}
    <nav class="space-y-3" aria-label="ছুটির ফিল্টার">
        {{-- Search --}}
        <div class="flex items-center gap-4">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="ছুটির নাম দিয়ে খুঁজুন..."
                icon="magnifying-glass" variant="filled" class="rounded-xl flex-1"
                aria-label="ছুটির নাম অনুসন্ধান করুন" />

            @if ($search || $selectedType || $fromDate || $toDate)
                <flux:button wire:click="resetFilters" variant="ghost" icon="x-mark" size="sm"
                    aria-label="ফিল্টার মুছুন" />
            @endif
        </div>

        {{-- Filters: horizontally scrollable on mobile --}}
        <div class="flex items-center gap-2 overflow-x-auto overflow-y-hidden pb-1 -mx-1 px-1 scrollbar-thin">
            {{-- Year --}}
            <div class="min-w-[7.5rem] shrink-0">
                <flux:select wire:model.live="selectedYear" variant="listbox" placeholder="বছর"
                    aria-label="বছর নির্বাচন করুন">
                    @foreach ($this->years as $year)
                        <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Type --}}
            <div class="min-w-[8.5rem] shrink-0">
                <flux:select wire:model.live="selectedType" variant="listbox" placeholder="সকল ধরণ"
                    aria-label="ছুটির ধরণ নির্বাচন করুন">
                    <flux:select.option value="">সকল ধরণ</flux:select.option>
                    @foreach ($this->holidayTypes as $type)
                        <flux:select.option value="{{ $type }}">{{ $type }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- From Date (Flux Date Picker) --}}
            <div class="min-w-[9.5rem] shrink-0">
                <flux:date-picker wire:model.live="fromDate" type="input" placeholder="শুরুর তারিখ"
                    aria-label="শুরুর তারিখ" />
            </div>

            {{-- To Date (Flux Date Picker) --}}
            <div class="min-w-[9.5rem] shrink-0">
                <flux:date-picker wire:model.live="toDate" type="input" placeholder="শেষের তারিখ"
                    aria-label="শেষের তারিখ" />
            </div>
        </div>
    </nav>
    {{-- Results count --}}
    @if ($search || $selectedType || $fromDate || $toDate)
        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400" role="status" aria-live="polite">
            {{ bn_num($this->holidays->count()) }}টি ফলাফল পাওয়া গেছে
        </p>
    @endif

    {{-- Holiday List --}}
    <section class="space-y-3" aria-labelledby="holiday-list-heading">
        <h2 id="holiday-list-heading" class="sr-only">
            {{ $selectedYear ?? now()->year }} সালের ছুটির তালিকা
        </h2>

        @forelse ($this->holidays as $holiday)
            <flux:card>

                <div class="flex gap-4 items-start">
                    {{-- Date Box --}}
                    <div class="shrink-0">
                        <div class="flex flex-col items-center justify-center w-14 h-14 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-400/25 shadow-sm"
                            aria-hidden="true">
                            <span class="text-[9px] uppercase font-black text-amber-600 dark:text-amber-500">
                                {{ Carbon::parse($holiday->date)->format('M') }}
                            </span>
                            <span class="text-xl font-bold leading-none text-zinc-800 dark:text-zinc-100">
                                {{ Carbon::parse($holiday->date)->format('d') }}
                            </span>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 space-y-2">
                        <flux:heading level="2" size="lg">
                            <flux:link variant="ghost"
                                href="{{ route('calendar.holiday.show', $holiday->slug ?? $holiday->id) }}"
                                wire:navigate>
                                {{ $holiday->title ?? 'অজানা ছুটি' }}
                            </flux:link>
                        </flux:heading>

                        <flux:text class="flex items-center gap-2 text-xs">
                            <span>{{ bn_day(Carbon::parse($holiday->date)->format('l')) }}</span>
                            <span class="size-1 bg-zinc-300 dark:bg-zinc-600 rounded-full" aria-hidden="true"></span>
                            <span class="capitalize">{{ $holiday->type }}</span>
                        </flux:text>

                        @if ($holiday->details)
                            <flux:text class="line-clamp-2 overflow-hidden text-base">
                                {{ strip_tags($holiday->details) }}
                            </flux:text>
                        @endif
                    </div>
                </div>

                {{-- Footer Action --}}
                <flux:separator class="opacity-50 my-2" />

                {{-- Footer Action --}}
                <div>
                    <flux:button icon="arrow-right" variant="subtle" size="xs"
                        href="{{ route('calendar.holiday.show', $holiday->slug ?? $holiday->id) }}" wire:navigate>
                        বিস্তারিত
                        পড়ুন
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <livewire:global.nodata-message :title="'ছুটির ক্যালেন্ডার'" :search="$search" />
        @endforelse
    </section>

    {{-- Infinite Scroll --}}
    @if ($this->holidays->count() >= 10)
        <div x-data x-intersect="$wire.loadMore()" class="py-8 flex justify-center" aria-live="polite">
            <div wire:loading wire:target="loadMore">
                <flux:icon.loading />
            </div>
        </div>
    @endif

    <flux:separator />

    {{-- About Section --}}
    <section aria-labelledby="about-holidays" class="space-y-4">
        <flux:heading id="about-holidays" level="2" size="lg" class="flex items-center gap-4">
            <flux:icon icon="information-circle" color="orange" />
            বাংলাদেশের সরকারি ছুটি সম্পর্কে
        </flux:heading>

        <div class="space-y-3">
            <flux:text>
                বাংলাদেশ সরকার প্রতি বছর সরকারি, ঐচ্ছিক ও ধর্মীয় ছুটির তালিকা প্রকাশ করে।
                এই পেজে আপনি <strong>{{ $selectedYear ?? now()->year }} সালের</strong> সম্পূর্ণ ছুটির ক্যালেন্ডার দেখতে
                পারবেন।
                এখানে জাতীয় দিবস, ধর্মীয় উৎসব এবং অন্যান্য গুরুত্বপূর্ণ ছুটির তারিখ একত্রিত করা হয়েছে।
            </flux:text>

            <flux:text>
                নাম, বছর, ধরণ বা তারিখ অনুযায়ী সহজেই খুঁজে নিতে পারবেন।
                “বিস্তারিত পড়ুন” বাটনে ক্লিক করে পুরো তথ্য, ছুটির ধরন এবং প্রাসঙ্গিক বিবরণ জানতে পারবেন।
                সরকারি ও ঐচ্ছিক ছুটি আলাদাভাবে ফিল্টার করে দেখা যায়।
            </flux:text>

            <flux:text>
                এই তালিকা নিয়মিত আপডেট করা হয় যাতে সরকার ঘোষিত সর্বশেষ ছুটির তথ্য যুক্ত থাকে।
                চাকরিজীবী, শিক্ষার্থী, ব্যবসায়ী এবং সাধারণ মানুষ সবাই এই তথ্য থেকে উপকৃত হতে পারেন।
                সঠিক ও নির্ভরযোগ্য ছুটির ক্যালেন্ডার পাওয়ার জন্য এটি একটি সহজ ও কার্যকর উৎস।
            </flux:text>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section aria-labelledby="faq-heading" class="space-y-4">
        <flux:heading id="faq-heading" level="2" size="lg">
            প্রায়শই জিজ্ঞাসিত প্রশ্ন
        </flux:heading>

        <flux:accordion transition exclusive>
            <flux:accordion.item>
                <flux:accordion.heading>{{ $selectedYear ?? now()->year }} সালে কয়টি সরকারি ছুটি আছে?
                </flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        প্রতি বছর সরকারি ছুটির সংখ্যা কিছুটা পরিবর্তন হতে পারে।
                        উপরের তালিকায় বর্তমান বছরের সকল সরকারি ও ঐচ্ছিক ছুটি দেখানো হয়েছে।
                        জাতীয় দিবস, ধর্মীয় উৎসব এবং অন্যান্য গুরুত্বপূর্ণ ছুটি এই তালিকায় অন্তর্ভুক্ত।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>ঐচ্ছিক ছুটি কী?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        ঐচ্ছিক ছুটি হলো সেই ছুটি যা সরকার ঘোষণা করে, কিন্তু সবাই বাধ্যতামূলকভাবে ছুটি পায় না।
                        বিভিন্ন ধর্মীয় সম্প্রদায়ের মানুষ তাদের নিজ নিজ ধর্মীয় উৎসবে এই ছুটি নিতে পারেন।
                        অফিস বা প্রতিষ্ঠান নিজেদের নীতিমালা অনুযায়ী ঐচ্ছিক ছুটি মঞ্জুর করতে পারে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>ছুটির তালিকা কীভাবে খুঁজব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        উপরের সার্চ বক্সে ছুটির নাম লিখুন, অথবা বছর ও ধরণ সিলেক্ট করুন।
                        তারিখ রেঞ্জ দিয়েও নির্দিষ্ট সময়ের ছুটি ফিল্টার করা যায়।
                        সরকারি বা ঐচ্ছিক ছুটি আলাদাভাবে দেখতে চাইলে ধরন ফিল্টার ব্যবহার করুন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>ছুটির তালিকা কি নিয়মিত আপডেট হয়?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        হ্যাঁ। সরকার নতুন ছুটি ঘোষণা করলে বা তারিখ পরিবর্তন হলে তা নিয়মিত আপডেট করা হয়।
                        তবে কোনো তথ্য ভুল বা পুরনো মনে হলে আমাদের জানাতে পারেন, যাতে দ্রুত সংশোধন করা যায়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </section>

</div>
