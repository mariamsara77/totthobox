<?php

use Livewire\Volt\Component;
use Carbon\Carbon;
use App\Models\Holiday;

new class extends Component {
    public string $selectedDate = '';
    public string $today = '';

    // Derived display state
    public string $currentEnglishDate = '';
    public string $currentBanglaMonthRange = '';
    public int $currentBanglaYear = 0;
    public array $calendarDays = [];
    public array $holidays = [];

    // ─────────────────────────────────────────────
    //  Lifecycle
    // ─────────────────────────────────────────────

    public function mount(): void
    {
        $this->today = now()->format('Y-m-d');
        $this->selectedDate = $this->today;
        $this->updateCalendar();
    }

    public function updatedSelectedDate(): void
    {
        $this->updateCalendar();
    }

    // ─────────────────────────────────────────────
    //  Navigation
    // ─────────────────────────────────────────────

    public function navigateMonth(string $direction): void
    {
        $date = Carbon::parse($this->selectedDate)->startOfMonth();
        $direction === 'next' ? $date->addMonth() : $date->subMonth();
        $this->selectedDate = $date->format('Y-m-d');
        $this->updateCalendar();
    }

    public function goToday(): void
    {
        $this->selectedDate = $this->today;
        $this->updateCalendar();
    }

    // ─────────────────────────────────────────────
    //  Calendar build
    // ─────────────────────────────────────────────

    public function updateCalendar(): void
    {
        $date = Carbon::parse($this->selectedDate);
        $this->loadHolidays($date->year);

        $this->currentEnglishDate = $date->format('F Y');

        $firstDayBn = $this->getBanglaDate($date->copy()->startOfMonth()->format('Y-m-d'));
        $lastDayBn = $this->getBanglaDate($date->copy()->endOfMonth()->format('Y-m-d'));

        $this->currentBanglaMonthRange = $firstDayBn['month'] !== $lastDayBn['month'] ? "{$firstDayBn['month']} – {$lastDayBn['month']}" : $firstDayBn['month'];

        $this->currentBanglaYear = $firstDayBn['year'];
        $this->calendarDays = $this->buildCalendarGrid($date->month, $date->year);
    }

    // ─────────────────────────────────────────────
    //  Holiday loader
    // ─────────────────────────────────────────────

    private function loadHolidays(int $year): void
    {
        $fixed = [
            '02-21' => ['title' => 'শহীদ দিবস', 'color' => 'rose'],
            '03-17' => ['title' => 'বঙ্গবন্ধুর জন্মদিন', 'color' => 'indigo'],
            '03-26' => ['title' => 'স্বাধীনতা দিবস', 'color' => 'emerald'],
            '04-14' => ['title' => 'পহেলা বৈশাখ', 'color' => 'orange'],
            '05-01' => ['title' => 'মে দিবস', 'color' => 'sky'],
            '12-16' => ['title' => 'বিজয় দিবস', 'color' => 'red'],
            '12-25' => ['title' => 'বড়দিন', 'color' => 'purple'],
        ];

        $fromDb = Holiday::whereYear('date', $year)
            ->get()
            ->mapWithKeys(
                fn($h) => [
                    Carbon::parse($h->date)->format('m-d') => [
                        'title' => $h->title,
                        'color' => 'amber',
                    ],
                ],
            )
            ->toArray();

        $this->holidays = array_merge($fixed, $fromDb);
    }

    // ─────────────────────────────────────────────
    //  Bangla date converter
    // ─────────────────────────────────────────────

    public function getBanglaDate($inputDate): array
    {
        $date = Carbon::parse($inputDate);
        $day = $date->day;
        $month = $date->month;
        $year = $date->year;
        $isLeapYear = $date->isLeapYear();

        $months = ['বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ', 'পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র'];

        // বঙ্গাব্দ নির্ণয় (১৪ এপ্রিল থেকে নতুন বছর শুরু)
        $bnYear = $month < 4 || ($month == 4 && $day < 14) ? $year - 594 : $year - 593;

        $startDates = [
            1 => 14,
            2 => 14,
            3 => 15,
            4 => 14,
            5 => 15,
            6 => 15,
            7 => 16,
            8 => 16,
            9 => 16,
            10 => 16,
            11 => 15,
            12 => 15,
        ];

        $monthMap = [
            1 => ['prev' => 8, 'curr' => 9],
            2 => ['prev' => 9, 'curr' => 10],
            3 => ['prev' => 10, 'curr' => 11],
            4 => ['prev' => 11, 'curr' => 0],
            5 => ['prev' => 0, 'curr' => 1],
            6 => ['prev' => 1, 'curr' => 2],
            7 => ['prev' => 2, 'curr' => 3],
            8 => ['prev' => 3, 'curr' => 4],
            9 => ['prev' => 4, 'curr' => 5],
            10 => ['prev' => 5, 'curr' => 6],
            11 => ['prev' => 6, 'curr' => 7],
            12 => ['prev' => 7, 'curr' => 8],
        ];

        $startDate = $startDates[$month];

        if ($day >= $startDate) {
            $bnDay = $day - $startDate + 1;
            $bnMonthIndex = $monthMap[$month]['curr'];
        } else {
            $prevMonthDays = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 30];
            if ($isLeapYear) {
                $prevMonthDays[10] = 31; // ফাল্গুন লিপ ইয়ারে ৩১
            }

            $bnMonthIndex = $monthMap[$month]['prev'];
            $daysInPrevBnMonth = $prevMonthDays[$bnMonthIndex];
            $bnDay = $daysInPrevBnMonth - ($startDate - $day) + 1;
        }

        return [
            'day' => $bnDay,
            'month' => $months[$bnMonthIndex],
            'year' => $bnYear,
        ];
    }

    // ─────────────────────────────────────────────
    //  Grid builder
    // ─────────────────────────────────────────────

    private function buildCalendarGrid(int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1);
        $endDay = $start->copy()->endOfMonth()->day;
        $padding = $start->dayOfWeek; // 0 = Sunday

        $days = array_fill(0, $padding, null);

        for ($d = 1; $d <= $endDay; $d++) {
            $current = Carbon::create($year, $month, $d);
            $formatted = $current->format('Y-m-d');
            $holidayKey = $current->format('m-d');
            $bn = $this->getBanglaDate($formatted);

            $days[] = [
                'date' => $formatted,
                'engDay' => $d,
                'bnDay' => $bn['day'],
                'isToday' => $formatted === $this->today,
                'isSelected' => $formatted === $this->selectedDate,
                'isFriday' => $current->dayOfWeek === 5,
                'isSaturday' => $current->dayOfWeek === 6,
                'isWeekend' => in_array($current->dayOfWeek, [5, 6]),
                'holiday' => $this->holidays[$holidayKey] ?? null,
                'dayOfWeek' => $current->dayOfWeek,
            ];
        }

        return array_chunk($days, 7);
    }

    // ─────────────────────────────────────────────
    //  SEO Data (100% optimized)
    // ─────────────────────────────────────────────

    private function generateSeoData(): array
    {
        $date = Carbon::parse($this->selectedDate);
        $bn = $this->getBanglaDate($this->selectedDate);
        $holiday = $this->holidays[$date->format('m-d')] ?? null;

        $bnDay = bn_num($bn['day']);
        $bnYear = bn_num($bn['year']);

        $title = "আজকের বাংলা তারিখ {$bnDay} {$bn['month']} {$bnYear} | বাংলা ক্যালেন্ডার " . date('Y');

        $description = "আজকের বাংলা তারিখ {$bnDay} {$bn['month']} {$bnYear} বঙ্গাব্দ। ইংরেজি তারিখ {$date->format('d F Y')} ({$date->format('l')})। ";

        if ($holiday) {
            $description .= "আজ {$holiday['title']} উপলক্ষে সরকারি ছুটি। ";
        }

        $description .= 'বাংলা ক্যালেন্ডার, সরকারি ছুটির তালিকা, বাংলা মাস ও বছরের সম্পূর্ণ তথ্য এক জায়গায়।';

        $keywords = implode(', ', ['আজকের বাংলা তারিখ', 'বাংলা ক্যালেন্ডার', 'বাংলা তারিখ আজ', 'ajker bangla tarikh', 'today bengali date', 'bangla calendar', $bn['month'], 'সরকারি ছুটি', 'বাংলা সন', 'বঙ্গাব্দ', 'বাংলা মাস', date('Y') . ' বাংলা ক্যালেন্ডার']);

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
        ];
    }

    // ─────────────────────────────────────────────
    //  Current month holidays helper
    // ─────────────────────────────────────────────

    public function getCurrentMonthHolidays(): array
    {
        $month = Carbon::parse($this->selectedDate)->format('m');

        return collect($this->holidays)
            ->filter(fn($h, $key) => str_starts_with($key, $month . '-'))
            ->map(function ($h, $key) {
                return [
                    'date' => $key,
                    'title' => $h['title'],
                    'color' => $h['color'] ?? 'amber',
                ];
            })
            ->values()
            ->toArray();
    }

    public function with(): array
    {
        $selDate = Carbon::parse($this->selectedDate);
        $selBn = $this->getBanglaDate($this->selectedDate);
        $selHoliday = $this->holidays[$selDate->format('m-d')] ?? null;

        return [
            'seo' => $this->generateSeoData(),
            'selDate' => $selDate,
            'selBn' => $selBn,
            'selHoliday' => $selHoliday,
            'currentMonthHolidays' => $this->getCurrentMonthHolidays(),
        ];
    }
}; ?>

{{-- ═══════════════════════════════════════════════════════════════
BANGLA CALENDAR – Livewire Volt Component
SEO Optimized · AdSense Policy Compliant · Tailwind + Flux UI
═══════════════════════════════════════════════════════════════ --}}

<x-seo :title="$seo['title']" :description="$seo['description']" :keywords="$seo['keywords']" />

<div class="max-w-xl mx-auto space-y-6">

    {{-- ══════════════════════════════════════
    H1 – Primary Keyword (Only one H1)
    ══════════════════════════════════════ --}}
    <header class="space-y-2">
        <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
            আজকের বাংলা তারিখ:
            <span class="text-green-600">
                {{ bn_num($selBn['day']) }} {{ $selBn['month'] }} {{ bn_num($selBn['year']) }}
            </span>
        </h1>

        <p class="text-base text-zinc-600 dark:text-zinc-400 leading-relaxed">
            ইংরেজি তারিখ <strong>{{ $selDate->format('d F Y') }}</strong>
            ({{ $selDate->format('l') }}) — বাংলা সন {{ bn_num($currentBanglaYear) }} বঙ্গাব্দ।
            বাংলা ক্যালেন্ডার ও সরকারি ছুটির সম্পূর্ণ তথ্য নিচে দেখুন।
        </p>
    </header>

    {{-- ══════════════════════════════════════
    Secondary Info (H2)
    ══════════════════════════════════════ --}}
    <div class="flex flex-wrap items-center gap-4 text-sm">
        <h2 class="flex items-center gap-2 font-semibold text-green-600">
            <flux:icon.calendar variant="micro" color="green" />
            {{ $currentBanglaMonthRange }} {{ bn_num($currentBanglaYear) }} বঙ্গাব্দ
        </h2>

        <span class="hidden sm:inline w-1 h-1 rounded-full bg-zinc-400"></span>

        <h2 class="flex items-center gap-2 font-medium text-zinc-500 dark:text-zinc-400">
            <flux:icon.clock variant="micro" class="text-zinc-400" />
            {{ $selDate->format('d F, Y') }}
        </h2>
    </div>

    {{-- ══════════════════════════════════════
    Selected Date Detail Card
    ══════════════════════════════════════ --}}
    <section class="rounded-2xl border border-zinc-400/25 p-3" aria-labelledby="selected-date-heading">
        <h2 id="selected-date-heading" class="sr-only">নির্বাচিত তারিখের বিস্তারিত</h2>

        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">বাংলা তারিখ</p>
                <p class="text-xl font-extrabold text-green-600">
                    {{ bn_num($selBn['day']) }} {{ $selBn['month'] }}
                </p>
                <p class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">
                    {{ bn_num($selBn['year']) }} বঙ্গাব্দ
                </p>
            </div>

            <div class="sm:text-right">
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">ইংরেজি তারিখ</p>
                <p class="text-xl font-bold text-zinc-800 dark:text-zinc-200">
                    {{ $selDate->format('d F Y') }}
                </p>
                <p class="text-sm text-zinc-500">{{ $selDate->translatedFormat('l') }}</p>
            </div>
        </div>

        @if ($selHoliday)
            <div class="flex item-center justify-center mt-2">
                <flux:badge color="red" icon="sparkles">{{ $selHoliday['title'] }} উপলক্ষে সরকারি ছুটি</flux:badge>
            </div>
        @endif
    </section>

    {{-- ══════════════════════════════════════
    Calendar Controls
    ══════════════════════════════════════ --}}
    <section class="space-y-4" aria-labelledby="calendar-controls-heading">
        <h2 id="calendar-controls-heading" class="sr-only">ক্যালেন্ডার নিয়ন্ত্রণ</h2>

        <div class="flex items-center justify-between rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 px-3 py-2">
            <flux:button wire:click="navigateMonth('prev')" variant="subtle" icon="chevron-left" circular size="sm"
                aria-label="আগের মাস" />

            <div class="text-center font-semibold text-zinc-800 dark:text-zinc-200 tracking-tight">
                {{ $currentEnglishDate }}
            </div>

            <flux:button wire:click="navigateMonth('next')" variant="subtle" icon="chevron-right" circular
                size="sm" aria-label="পরের মাস" />
        </div>

        <div class="flex gap-2 items-center">
            <div class="">
                <flux:date-picker wire:model.live="selectedDate" selectable-header with-today
                    aria-label="তারিখ নির্বাচন করুন" />
            </div>
            <flux:button wire:click="goToday" variant="filled" class="rounded-xl px-5 font-bold whitespace-nowrap">
                আজ
            </flux:button>
        </div>
    </section>

    {{-- ══════════════════════════════════════
    Calendar Grid
    ══════════════════════════════════════ --}}
    <section aria-labelledby="calendar-grid-heading">
        <h2 id="calendar-grid-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200 mb-3">
            {{ $currentEnglishDate }} — বাংলা ক্যালেন্ডার
        </h2>

        {{-- Weekday Headers --}}
        <div class="grid grid-cols-7 mb-2" role="row">
            @php
                $weekDays = [
                    ['en' => 'Sun', 'bn' => 'রবি'],
                    ['en' => 'Mon', 'bn' => 'সোম'],
                    ['en' => 'Tue', 'bn' => 'মঙ্গল'],
                    ['en' => 'Wed', 'bn' => 'বুধ'],
                    ['en' => 'Thu', 'bn' => 'বৃহঃ'],
                    ['en' => 'Fri', 'bn' => 'শুক্র'],
                    ['en' => 'Sat', 'bn' => 'শনি'],
                ];
            @endphp
            @foreach ($weekDays as $i => $wd)
                <div class="text-center py-1" role="columnheader">
                    <span
                        class="block text-xs sm:text-sm font-semibold tracking-wider {{ $i >= 5 ? 'text-rose-500' : 'text-zinc-500 dark:text-zinc-400' }}">
                        {{ $wd['en'] }}
                    </span>
                    <span
                        class="text-xs sm:text-xs {{ $i >= 5 ? 'text-rose-400/80' : 'text-zinc-400 dark:text-zinc-500' }}">
                        {{ $wd['bn'] }}
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Days --}}
        <div class="grid grid-cols-7 gap-1" role="grid">
            @foreach (collect($calendarDays)->flatten(1) as $day)
                @if (!$day)
                    <div class="aspect-square" role="gridcell"></div>
                @else
                    @php
                        $isSelected = $day['isSelected'];
                        $isToday = $day['isToday'];
                        $holiday = $day['holiday'];

                        $cellBg = match (true) {
                            $isSelected
                                => 'bg-emerald-600 shadow-lg shadow-emerald-200/60 dark:shadow-emerald-900/40 scale-[1.03] z-10',
                            $isToday => 'bg-emerald-50 dark:bg-emerald-900/25 ring-2 ring-emerald-500',
                            default => 'hover:bg-zinc-100 dark:hover:bg-zinc-800',
                        };
                    @endphp

                    <button wire:click="$set('selectedDate', '{{ $day['date'] }}')"
                        class="aspect-square relative rounded-xl transition-all duration-200 focus: focus-visible:ring-2 focus-visible:ring-emerald-500 {{ $cellBg }}"
                        aria-label="{{ $day['date'] }}{{ $holiday ? ' - ' . $holiday['title'] : '' }}"
                        @if ($isSelected) aria-current="date" @endif>
                        <div class="flex flex-col items-center justify-center h-full">
                            <span
                                class="text-sm font-black leading-none {{ $isSelected ? 'text-white' : ($day['isWeekend'] ? 'text-rose-500' : 'text-zinc-700 dark:text-zinc-300') }}">
                                {{ $day['engDay'] }}
                            </span>
                            <span
                                class="text-[11px] font-bold mt-0.5 {{ $isSelected ? 'text-emerald-100' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ bn_num($day['bnDay']) }}
                            </span>

                            @if ($holiday)
                                <span class="absolute bottom-1 w-1.5 h-1.5 rounded-full bg-rose-500"
                                    aria-hidden="true"></span>
                            @endif
                        </div>
                    </button>
                @endif
            @endforeach
        </div>
    </section>

    <flux:separator class="opacity-50" />

    {{-- ══════════════════════════════════════
    This Month's Official Holidays
    ══════════════════════════════════════ --}}
    @if (count($currentMonthHolidays) > 0)
        <div>
            <flux:heading size="lg" class="flex gap-4 mb-2">
                <flux:icon name="calendar-days" variant="micro" />
                এই মাসের সরকারি ছুটি
            </flux:heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>ছুটির নাম</flux:table.column>
                    <flux:table.column>তারিখ</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($currentMonthHolidays as $h)
                        <flux:table.row>
                            <flux:table.cell variant="strong">{{ $h['title'] }}</flux:table.cell>
                            <flux:table.cell>{{ $h['date'] }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endif

    {{-- ══════════════════════════════════════
    Informative Content (AdSense requirement)
    ══════════════════════════════════════ --}}
    <flux:card>
        <flux:heading size="lg">
            বাংলা ক্যালেন্ডার সম্পর্কে
        </flux:heading>

        <flux:text>
            বাংলা ক্যালেন্ডার বা বঙ্গাব্দ বাংলাদেশ ও পশ্চিমবঙ্গে ব্যবহৃত একটি সৌর বর্ষপঞ্জি।
            বাংলা সনের নতুন বছর শুরু হয় সাধারণত ১৪ এপ্রিল (পহেলা বৈশাখ) থেকে।
            বর্তমানে চলছে <strong>{{ bn_num($currentBanglaYear) }} বঙ্গাব্দ</strong>।
        </flux:text>

        <flux:text>
            এই পেজে আপনি সহজেই <strong>আজকের বাংলা তারিখ</strong>, ইংরেজি তারিখের সাথে তুলনা,
            মাসভিত্তিক ক্যালেন্ডার এবং সরকারি ছুটির তালিকা দেখতে পারবেন।
            তারিখ সিলেক্ট করে যেকোনো দিনের বাংলা তারিখ জানা যায়।
        </flux:text>

        <flux:text>
            বাংলা মাসগুলো হলো: বৈশাখ, জ্যৈষ্ঠ, আষাঢ়, শ্রাবণ, ভাদ্র, আশ্বিন,
            কার্তিক, অগ্রহায়ণ, পৌষ, মাঘ, ফাল্গুন ও চৈত্র।
        </flux:text>
    </flux:card>

    {{-- ══════════════════════════════════════
    FAQ Section (Strong for SEO + AdSense)
    ══════════════════════════════════════ --}}

    <flux:heading size="lg">
        প্রায়শই জিজ্ঞাসিত প্রশ্ন (FAQ)
    </flux:heading>

    <flux:accordion transition exclusive>
        <flux:accordion.item heading="আজকের বাংলা তারিখ কত?">
            <flux:text>
                আজকের বাংলা তারিখ হলো <strong>{{ bn_num($selBn['day']) }} {{ $selBn['month'] }}
                    {{ bn_num($selBn['year']) }}</strong> বঙ্গাব্দ।
                ইংরেজি তারিখ {{ $selDate->format('d F Y') }}।
            </flux:text>
        </flux:accordion.item>

        <flux:accordion.item heading="বাংলা সন কীভাবে হিসাব করা হয়?">
            <flux:text>
                বাংলা সন সাধারণত ১৪ এপ্রিল থেকে শুরু হয়। ইংরেজি বছর থেকে ৫৯৩ বা ৫৯৪ বিয়োগ করে বাংলা সন পাওয়া যায়।
                এপ্রিলের ১৪ তারিখের আগে হলে ৫৯৪ এবং পরে হলে ৫৯৩ বিয়োগ করা হয়।
            </flux:text>
        </flux:accordion.item>

        <flux:accordion.item heading="সরকারি ছুটির তালিকা কোথায় পাব?">
            <flux:text>
                এই পেজেই চলতি মাসের সকল সরকারি ছুটি দেখানো হয়। নির্দিষ্ট তারিখ সিলেক্ট করলে সেই দিনের ছুটির নামও
                দেখা যাবে।
                শহীদ দিবস, স্বাধীনতা দিবস, পহেলা বৈশাখ, বিজয় দিবসসহ প্রধান ছুটিগুলো হাইলাইট করা আছে।
            </flux:text>
        </flux:accordion.item>

        <flux:accordion.item heading="বাংলা মাস কয়টি ও কী কী?">
            <flux:text>
                বাংলা সনে মোট ১২টি মাস আছে। সেগুলো হলো: বৈশাখ, জ্যৈষ্ঠ, আষাঢ়, শ্রাবণ, ভাদ্র, আশ্বিন,
                কার্তিক, অগ্রহায়ণ, পৌষ, মাঘ, ফাল্গুন এবং চৈত্র।
            </flux:text>
        </flux:accordion.item>

        <flux:accordion.item heading="এই ক্যালেন্ডার কি মোবাইলে কাজ করে?">
            <flux:text>
                হ্যাঁ, এই বাংলা ক্যালেন্ডার সম্পূর্ণ মোবাইল-ফ্রেন্ডলি। স্মার্টফোন, ট্যাবলেট ও কম্পিউটারে একইভাবে
                ব্যবহার করা যায়।
            </flux:text>
        </flux:accordion.item>
    </flux:accordion>
    {{-- ══════════════════════════════════════
    Extra helpful note
    ══════════════════════════════════════ --}}
    <flux:text variant="subtle">
        এই পেজটি নিয়মিত আপডেট করা হয় যাতে আপনি সঠিক <strong>আজকের বাংলা তারিখ</strong>,
        বাংলা ক্যালেন্ডার এবং সরকারি ছুটির তথ্য পান। যেকোনো তারিখ সিলেক্ট করে বাংলা ও ইংরেজি তারিখ একসাথে দেখুন।
    </flux:text>

</div>
