<?php

use Livewire\Volt\Component;
use Carbon\Carbon;
use App\Models\Holiday;

new class extends Component
{
    public $selectedDate;
    public $currentBanglaDate;
    public $currentEnglishDate;
    public $calendarDays = [];
    public $currentMonth;
    public $currentYear;
    public $holidays = [];
    public $showEventModal = false;
    public $eventDate;
    public $eventTitle;
    public $eventDescription;
    public $todayBangla;
    public $todayEnglish;

    protected $listeners = ['refreshCalendar' => 'refresh'];

    public function mount()
    {
        $this->selectedDate = date('Y-m-d');
        $this->todayEnglish = date('Y-m-d');
        $this->todayBangla = $this->convertToBanglaDate($this->todayEnglish);
        $this->loadHolidays(); // Changed from initializeHolidays
        $this->updateCalendar();
    }

    public function loadHolidays()
    {
        // Load both fixed and dynamic holidays
        $fixedHolidays = [
            '04-14' => ['title' => 'Bengali New Year', 'type' => 'national', 'color' => 'bg-success'],
            '05-01' => ['title' => 'May Day', 'type' => 'national', 'color' => 'bg-info'],
            '12-16' => ['title' => 'Victory Day', 'type' => 'national', 'color' => 'bg-primary'],
            '03-26' => ['title' => 'Independence Day', 'type' => 'national', 'color' => 'bg-danger'],
            '02-21' => ['title' => 'Language Martyrs Day', 'type' => 'national', 'color' => 'bg-warning'],
            '12-25' => ['title' => 'Christmas', 'type' => 'religious', 'color' => 'bg-primary'],
            '11-07' => ['title' => 'National Revolution Day', 'type' => 'national', 'color' => 'bg-secondary']
        ];

        // Get dynamic holidays from database for current year
        $currentYear = date('Y');
        $dbHolidays = Holiday::whereYear('date', $currentYear)
            ->get()
            ->mapWithKeys(function ($holiday) {
                $dateKey = Carbon::parse($holiday->date)->format('m-d');
                return [
                    $dateKey => [
                        'title' => $holiday->title,
                        'type' => $holiday->type,
                        'color' => $this->getHolidayColor($holiday->type),
                        'custom' => true // Mark as custom holiday
                    ]
                ];
            })
            ->toArray();

        // Merge fixed and dynamic holidays
        $this->holidays = array_merge($fixedHolidays, $dbHolidays);
    }

    protected function getHolidayColor($type)
    {
        return match ($type) {
            'national' => 'bg-blue',
            'religious' => 'bg-purple',
            'government' => 'bg-teal',
            default => 'bg-secondary'
        };
    }

    public function refresh()
    {
        $this->updateCalendar();
    }

    public function updatedSelectedDate()
    {
        $this->updateCalendar();
    }

    public function updateCalendar()
    {
        $date = Carbon::parse($this->selectedDate);
        $this->currentEnglishDate = $date->format('d F Y');
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->currentBanglaDate = $this->convertToBanglaDate($this->selectedDate);
        $this->calendarDays = $this->generateCalendar($date->month, $date->year);
    }

    public function navigateMonth($direction)
    {
        $date = Carbon::parse($this->selectedDate);

        if ($direction === 'next') {
            $date->addMonth();
        } else {
            $date->subMonth();
        }

        $this->selectedDate = $date->format('Y-m-d');
        $this->updateCalendar();
    }

   public function getBanglaDateDetails($inputDate)
    {
        $months = [
            "বৈশাখ",
            "জ্যৈষ্ঠ", 
            "আষাঢ়",
            "শ্রাবণ",
            "ভাদ্র",
            "আশ্বিন",
            "কার্তিক",
            "অগ্রহায়ণ",
            "পৌষ",
            "মাঘ",
            "ফাল্গুন",
            "চৈত্র"
        ];

        $timestamp = strtotime($inputDate);
        $year = date("Y", $timestamp);

        $bangla_start = strtotime("14 April $year");
        $current_date = strtotime($inputDate);

        if ($current_date < $bangla_start) {
            $bangla_start = strtotime("14 April " . ($year - 1));
            $bangla_year = $year - 594;
        } else {
            $bangla_year = $year - 593;
        }

        $days_passed = floor(($current_date - $bangla_start) / (60 * 60 * 24));

        $month_days = [31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 30, 30];
        if (date('L', $year) == 1) {
            $month_days[11] = 31; // Chaitra has 31 days in leap year
        }

        $i = 0;
        // Add bounds checking to prevent array index overflow
        while ($i < 12 && $days_passed >= $month_days[$i]) {
            $days_passed -= $month_days[$i];
            $i++;
        }

        // If we've gone through all months, it means we're in the next year
        if ($i >= 12) {
            $i = 0;
            $bangla_year++;
        }

        return [
            'day' => $days_passed + 1,
            'month' => $months[$i],
            'month_index' => $i,
            'year' => $bangla_year,
            'month_days' => $month_days[$i]
        ];
    }

    public function generateCalendar($month, $year)
    {
        $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $lastDayOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $daysInMonth = $lastDayOfMonth->day;
        $firstDayOfWeek = $firstDayOfMonth->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        $days = [];

        // Add empty cells for days before the first day of the month
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $days[] = $this->createEmptyDayCell();
        }

        // Add actual days of the month
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($year, $month, $i)->format('Y-m-d');
            $banglaDate = $this->getBanglaDateDetails($date);
            $isToday = $date === date('Y-m-d');
            $dayOfWeek = Carbon::parse($date)->dayOfWeek;

            $holidayKey = date('m-d', strtotime($date));
            $isHoliday = isset($this->holidays[$holidayKey]) || $dayOfWeek == 5 || $dayOfWeek == 6; // Friday or Saturday

            $days[] = [
                'englishDay' => $i,
                'banglaDay' => $banglaDate['day'],
                'banglaMonth' => $banglaDate['month'],
                'isToday' => $isToday,
                'isHoliday' => $isHoliday,
                'holidayInfo' => $isHoliday ? ($this->holidays[$holidayKey] ?? ['title' => $dayOfWeek == 5 ? '' : '', 'type' => 'weekend']) : null,
                'date' => $date,
                'isCurrentMonth' => true
            ];
        }

        // Add days from next month to complete the grid
        $totalCells = count($days);
        $remainingCells = 42 - $totalCells; // 6 weeks x 7 days
        for ($i = 1; $i <= $remainingCells; $i++) {
            $days[] = $this->createEmptyDayCell();
        }

        return array_chunk($days, 7);
    }

    private function createEmptyDayCell()
    {
        return [
            'englishDay' => '',
            'banglaDay' => '',
            'banglaMonth' => '',
            'isToday' => false,
            'isHoliday' => false,
            'holidayInfo' => null,
            'date' => null,
            'isCurrentMonth' => false
        ];
    }

    public function convertToBanglaDate($inputDate)
    {
        $banglaDate = $this->getBanglaDateDetails($inputDate);
        return $this->formatBanglaDate($banglaDate);
    }

    public function formatBanglaDate($banglaDate)
    {
        return "{$banglaDate['day']} {$banglaDate['month']}, {$banglaDate['year']}";
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->updateCalendar();
    }


    public function showModal($date)
    {
        if ($date) {
            $this->eventDate = $date;
            $this->showEventModal = true;
            $this->selectedDate = $date;
        }
    }

    public function addEvent()
    {
        $this->validate([
            'eventTitle' => 'required|string|max:255',
            'eventDescription' => 'nullable|string'
        ]);

        $this->showEventModal = false;
        $this->reset(['eventTitle', 'eventDescription']);
        $this->dispatch('refreshCalendar');
    }
};?>

<div class="bilingual-calendar-container space-y-4">
    <!-- Header -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <div class="rounded">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            English Calendar with Bangla Dates
        </h3>
        <div class="flex items-center mt-3 space-x-2">
            <flux:input type="date" wire:model.live="selectedDate" class="overflow-hidden rounded-lg" size="sm" />
            <flux:button wire:click="$set('selectedDate', '{{ $todayEnglish }}')" size="sm">
                Today
            </flux:button>
        </div>
    </div>

    <!-- Calendar -->
    <div class="borde rounded overflow-hidden ">
        <!-- Current Date Display -->
        <div class="py-3 flex justify-between items-center ">
            <flux:button size="sm" wire:click="navigateMonth('previous')" variant="subtle">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="m3.86 8.753 5.482 4.796c.646.566 1.658.106 1.658-.753V3.204a1 1 0 0 0-1.659-.753l-5.48 4.796a1 1 0 0 0 0 1.506z" />
                </svg>
            </flux:button>
            <div class="flex flex-col md:flex-row items-center gap-x-4 text-center">
                <h3 class="font-bold text-lg p-0">{{ $currentEnglishDate }}</h3>
                <h3 class="font-noto text-lg p-0 text-green-500">{{ bn_num($currentBanglaDate) }}</h3>
            </div>
            <flux:button size="sm" wire:click="navigateMonth('next')" variant="subtle">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z" />
                </svg>
            </flux:button>
        </div>

        <!-- Weekday Headers -->
        <div class="grid grid-cols-7 text-center  text-sm rounded-xl mb-1">
            <div class="py-2">Sun<br><span class="text-xs">রবি</span></div>
            <div class="py-2">Mon<br><span class="text-xs">সোম</span></div>
            <div class="py-2">Tue<br><span class="text-xs">মঙ্গল</span></div>
            <div class="py-2">Wed<br><span class="text-xs">বুধ</span></div>
            <div class="py-2">Thu<br><span class="text-xs">বৃহস্পতি</span></div>
            <div class="py-2 text-red-500">Fri<br><span class="text-xs text-red-500">শুক্র</span></div>
            <div class="py-2 text-red-500">Sat<br><span class="text-xs text-red-500">শনি</span></div>
        </div>

        <!-- Calendar Grid -->
        <div class="divide-">
            @foreach($calendarDays as $week)
            <div class="grid grid-cols-7">
                @foreach($week as $day)
                <div wire:click="selectDate('{{ $day['date'] }}')" class="rounded-xl m-0.5 border-0  border-gray-600 cursor-pointer p-1 text-center flex flex-col justify-start items-center 
                                {{ $day['isToday'] ? ' bg-green-300/25' : '' }}
                                {{ $day['isHoliday'] ? 'text-red-600' : '' }}
                                {{ !$day['isCurrentMonth'] ? 'text-gray-400 ' : '' }}
                                {{ $day['date'] === $selectedDate ? ' bg-zinc-500/10' : '' }}">

                    <!-- English Date -->
                    <div class="text-base font-bold">{{ $day['englishDay'] }}</div>

                    <!-- Bangla Date -->
                    <div class="text-green-600 font-noto text-sm">
                        @if($day['banglaDay'] == 1 && $day['isCurrentMonth'])
                        {{ bn_num($day['banglaDay']) }} <small class="text-[8px]">{{ $day['banglaMonth'] }}</small>
                        @else
                        {{ bn_num($day['banglaDay']) }}
                        @endif
                    </div>

                    <!-- Holiday Badge -->
                    @if($day['isHoliday'])
                    <div class="mt-1 text-yellow-500 rounded text-[8px] truncat">
                        {{ $day['holidayInfo']['title'] }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>

    <!-- Footer -->
    <div class="flex justify-between items-center text-xs text-gray-500">
        <div>
            © {{ date('Y') }} Calendar | All rights reserved
        </div>
    </div>
</div>
