<?php

use Livewire\Volt\Component;
use Carbon\Carbon;
use App\Models\Holiday;

new class extends Component {
    public $selectedDate;
    public $currentEnglishDate;
    public $currentBanglaMonthRange;
    public $currentBanglaYear;
    public $calendarDays = [];
    public $holidays = [];
    public $today;

    public function mount()
    {
        $this->today = date('Y-m-d');
        $this->selectedDate = $this->today;
        $this->updateCalendar();
    }

    // তারিখ পরিবর্তন হলে স্বয়ংক্রিয়ভাবে ক্যালেন্ডার আপডেট হবে
    public function updatedSelectedDate($value)
    {
        $this->updateCalendar();
    }

    public function loadHolidays($year)
    {
        $fixedHolidays = [
            '02-21' => ['title' => 'শহীদ দিবস', 'color' => 'bg-rose-500'],
            '03-17' => ['title' => 'বঙ্গবন্ধুর জন্মদিন', 'color' => 'bg-indigo-500'],
            '03-26' => ['title' => 'স্বাধীনতা দিবস', 'color' => 'bg-emerald-600'],
            '04-14' => ['title' => 'পহেলা বৈশাখ', 'color' => 'bg-orange-500'],
            '05-01' => ['title' => 'মে দিবস', 'color' => 'bg-sky-500'],
            '12-16' => ['title' => 'বিজয় দিবস', 'color' => 'bg-red-600'],
            '12-25' => ['title' => 'বড় দিন', 'color' => 'bg-purple-500'],
        ];

        $dbHolidays = Holiday::whereYear('date', $year)
            ->get()
            ->mapWithKeys(function ($h) {
                return [
                    Carbon::parse($h->date)->format('m-d') => [
                        'title' => $h->title,
                        'color' => 'bg-amber-500',
                    ],
                ];
            })
            ->toArray();

        $this->holidays = array_merge($fixedHolidays, $dbHolidays);
    }

    public function updateCalendar()
    {
        $date = Carbon::parse($this->selectedDate);
        $this->loadHolidays($date->year);

        $this->currentEnglishDate = $date->format('F Y');

        // হেডার-এ ২ মাসের নাম দেখানোর লজিক
        $firstDayBn = $this->getBanglaDateDetails($date->copy()->startOfMonth()->format('Y-m-d'));
        $lastDayBn = $this->getBanglaDateDetails($date->copy()->endOfMonth()->format('Y-m-d'));

        if ($firstDayBn['month'] !== $lastDayBn['month']) {
            $this->currentBanglaMonthRange = "{$firstDayBn['month']} - {$lastDayBn['month']}";
        } else {
            $this->currentBanglaMonthRange = $firstDayBn['month'];
        }

        $this->currentBanglaYear = $firstDayBn['year'];
        $this->calendarDays = $this->generateCalendar($date->month, $date->year);
    }

    public function navigateMonth($direction)
    {
        $date = Carbon::parse($this->selectedDate)->startOfMonth();
        $direction === 'next' ? $date->addMonth() : $date->subMonth();
        $this->selectedDate = $date->format('Y-m-d');
        $this->updateCalendar();
    }

    public function getBanglaDateDetails($inputDate)
    {
        $months = ['বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ', 'পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র'];
        $timestamp = strtotime($inputDate);
        $year = date('Y', $timestamp);
        $current_date = strtotime($inputDate);
        $bangla_start = strtotime("$year-04-14");

        if ($current_date < $bangla_start) {
            $bangla_year = $year - 594;
            $start_date = strtotime($year - 1 . '-04-14');
        } else {
            $bangla_year = $year - 593;
            $start_date = $bangla_start;
        }

        $days_passed = floor(($current_date - $start_date) / (60 * 60 * 24));
        $month_days = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 30];

        $isLeap = date('L', $current_date);
        if ($isLeap) {
            $month_days[10] = 31;
        }

        $i = 0;
        while ($i < 12 && $days_passed >= $month_days[$i]) {
            $days_passed -= $month_days[$i];
            $i++;
        }

        return [
            'day' => (int) $days_passed + 1,
            'month' => $months[$i],
            'year' => $bangla_year,
        ];
    }

    public function generateCalendar($month, $year)
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();
        $padding = $start->dayOfWeek;

        $days = [];
        for ($i = 0; $i < $padding; $i++) {
            $days[] = null;
        }

        for ($i = 1; $i <= $end->day; $i++) {
            $current = Carbon::create($year, $month, $i);
            $formattedDate = $current->format('Y-m-d');
            $holidayKey = $current->format('m-d');
            $bn = $this->getBanglaDateDetails($formattedDate);

            $days[] = [
                'date' => $formattedDate,
                'engDay' => $i,
                'bnDay' => $bn['day'],
                'isToday' => $formattedDate === $this->today,
                'isWeekend' => in_array($current->dayOfWeek, [5, 6]),
                'holiday' => $this->holidays[$holidayKey] ?? null,
            ];
        }
        return array_chunk($days, 7);
    }
}; ?>

<div class="max-w-md mx-auto antialiased space-y-4">
    <div class="bg-zinc-400/10 p-6 pb-4 rounded-4xl">
        <div class="flex justify-between items-center mb-6">
            <flux:button wire:click="navigateMonth('prev')" variant="subtle" icon="chevron-left" circular size="sm" />
            <div class="text-center">
                <h2 class="text-xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">
                    {{ $currentEnglishDate }}
                </h2>
                <p class="text-emerald-500 font-bold text-sm">
                    {{ $currentBanglaMonthRange }}, {{ bn_num($currentBanglaYear) }} বঙ্গাব্দ
                </p>
            </div>
            <flux:button wire:click="navigateMonth('next')" variant="subtle" icon="chevron-right" circular size="sm" />
        </div>

        <div class="flex gap-2 items-center">
            <flux:input type="date" wire:model.live="selectedDate" size="sm" class="flex-1" />
            <flux:button wire:click="$set('selectedDate', '{{ $today }}')" size="sm" variant="filled"
                class="rounded-2xl px-5">আজ</flux:button>
        </div>
    </div>

    <div class="">
        <div class="grid grid-cols-7 mb-4">
            @php
                $days = [
                    ['en' => 'Sun', 'bn' => 'রবি'],
                    ['en' => 'Mon', 'bn' => 'সোম'],
                    ['en' => 'Tue', 'bn' => 'মঙ্গল'],
                    ['en' => 'Wed', 'bn' => 'বুধ'],
                    ['en' => 'Thu', 'bn' => 'বৃহঃ'],
                    ['en' => 'Fri', 'bn' => 'শুক্র'],
                    ['en' => 'Sat', 'bn' => 'শনি'],
                ];
            @endphp

            @foreach ($days as $index => $day)
                <div class="text-center flex flex-col">
                    <span
                        class="text-[10px] font-black uppercase tracking-widest {{ $index >= 5 ? 'text-rose-500' : 'text-zinc-400' }}">
                        {{ $day['en'] }}
                    </span>
                    <span class="text-[9px] font-bold {{ $index >= 5 ? 'text-rose-400/80' : 'text-zinc-400/70' }}">
                        {{ $day['bn'] }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-2 px-1">
            @foreach (collect($calendarDays)->flatten(1) as $day)
                <div @if ($day) wire:click="$set('selectedDate', '{{ $day['date'] }}')" @endif
                    class="aspect-square relative rounded-xl transition-all duration-300
                                                                                                            {{ !$day ? 'opacity-0' : 'cursor-pointer' }}
                                                                                                            {{ $day && $day['date'] === $selectedDate ? 'bg-emerald-500 shadow-lg shadow-emerald-200 dark:shadow-none scale-110 z-10' : '' }}
                                                                                                            {{ $day && $day['date'] !== $selectedDate ? 'hover:bg-zinc-100 dark:hover:bg-zinc-800' : '' }}">

                    @if ($day)
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span
                                class="text-lg font-black leading-none
                                                                                                                                                                                                                {{ $day['date'] === $selectedDate ? 'text-white' : ($day['isToday'] ? 'text-emerald-500' : ($day['isWeekend'] ? 'text-rose-500' : 'text-zinc-800 dark:text-zinc-200')) }}">
                                {{ $day['engDay'] }}
                            </span>

                            <span
                                class="text-xs font-bold mt-1 {{ $day['date'] === $selectedDate ? 'text-emerald-100' : 'text-emerald-600/60' }}">
                                {{ bn_num($day['bnDay']) }}
                            </span>

                            <div class="absolute flex gap-0.5">
                                @if ($day['holiday'])
                                    <div
                                        class="w-1 h-1 rounded-full {{ $day['date'] === $selectedDate ? 'bg-white' : $day['holiday']['color'] }}">
                                    </div>
                                @endif
                                @if ($day['isToday'] && $day['date'] !== $selectedDate)
                                    <div class="w-1 h-1 rounded-full bg-emerald-500"></div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="px-6 py-2 flex flex-wrap justify-center gap-4 border-t border-zinc-100 dark:border-zinc-800 pt-4">
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-[10px] font-bold text-zinc-500 uppercase">আজ</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            <span class="text-[10px] font-bold text-zinc-500 uppercase">সরকারি ছুটি</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span class="text-[10px] font-bold text-zinc-500 uppercase">ঐচ্ছিক ছুটি</span>
        </div>
    </div>

    @if ($selectedDate)
        @php
            $selDateObj = Carbon::parse($selectedDate);
            $selHoliday = $holidays[$selDateObj->format('m-d')] ?? null;
            $selBn = $this->getBanglaDateDetails($selectedDate);
        @endphp
        <div class="">
            <div
                class="bg-zinc-400/10 rounded-4xl p-4 border border-zinc-100 dark:border-zinc-800 flex items-center gap-4 shadow-sm transition-all animate-in fade-in">
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center text-white font-black text-xl shadow-inner">
                    {{ $selDateObj->day }}
                </div>
                <div class="flex-1">
                    <h4 class="font-black text-zinc-900 dark:text-white leading-tight">
                        {{ $selDateObj->format('l, d F Y') }}
                    </h4>
                    <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider">
                        {{ bn_num($selBn['day']) }} {{ $selBn['month'] }}, {{ bn_num($selBn['year']) }} বঙ্গাব্দ
                    </p>
                </div>
                @if ($selHoliday)
                    <span
                        class="px-3 py-1 rounded-full text-[8px] font-black text-white {{ $selHoliday['color'] }} uppercase tracking-tighter shadow-sm">
                        {{ $selHoliday['title'] }}
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>