<?php

use Livewire\Volt\Component;
use App\Models\Holiday;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Carbon\Carbon;

new class extends Component {
    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $selectedYear;

    #[Url(history: true)]
    public $selectedType = '';

    // নতুন ডেট রেঞ্জ প্রপার্টিজ
    #[Url(history: true)]
    public $fromDate = '';

    #[Url(history: true)]
    public $toDate = '';

    public $perPage = 15;
    public $selectedHolidayId = null;

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
        return Holiday::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }

    #[Computed]
    public function holidays()
    {
        return Holiday::query()
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->selectedYear && !$this->fromDate, fn($q) => $q->whereYear('date', $this->selectedYear))
            ->when($this->selectedType, fn($q) => $q->where('type', $this->selectedType))
            // ডেট রেঞ্জ ফিল্টার
            ->when($this->fromDate, fn($q) => $q->whereDate('date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('date', '<=', $this->toDate))
            ->orderBy('date', 'asc')
            ->limit($this->perPage)
            ->get();
    }

    public function showDetails($id)
    {
        $this->selectedHolidayId = $id;
        $this->dispatch('modal-opened', name: 'holiday-details');
    }

    #[Computed]
    public function selectedHoliday()
    {
        return $this->selectedHolidayId ? Holiday::find($this->selectedHolidayId) : null;
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
}; ?>

<section class="max-w-2xl mx-auto py-12 px-6">
    {{-- Branding & Header --}}
    <div class="mb-10 space-y-1">
        <flux:heading size="xl" level="1" class="font-bold tracking-tight">ছুটির ক্যালেন্ডার</flux:heading>
        <flux:subheading class="text-zinc-500">সরকারি ও নির্ধারিত ছুটির বিস্তারিত তথ্য</flux:subheading>
    </div>

    {{-- Horizontal Scrollable Filter Bar --}}
    <div class="flex items-center gap-3 mb-10 overflow-x-auto pb-4 no-scrollbar -mx-6 px-6">
        {{-- Search Input - fixed width for consistency in scroll --}}
        <div class="min-w-[200px]">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="ছুটির নাম..."
                variant="filled" />
        </div>

        {{-- Year Select --}}
        <div class="min-w-[120px]">
            <flux:select wire:model.live="selectedYear" variant="listbox" placeholder="বছর">
                @foreach($this->years as $year)
                    <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Type Select --}}
        <div class="min-w-[140px]">
            <flux:select wire:model.live="selectedType" variant="listbox">
                <flux:select.option value="">সকল ধরণ</flux:select.option>
                <flux:select.option value="Public">সরকারি ছুটি</flux:select.option>
                <flux:select.option value="National">জাতীয় দিবস</flux:select.option>
                <flux:select.option value="Religious">ধর্মীয় ছুটি</flux:select.option>
            </flux:select>
        </div>

        {{-- Date From --}}
        <div class="min-w-[160px]">
            <flux:input type="date" wire:model.live="fromDate" variant="filled" />
        </div>

        {{-- Date To --}}
        <div class="min-w-[160px]">
            <flux:input type="date" wire:model.live="toDate" variant="filled" />
        </div>

        {{-- Reset Button --}}
        <div class="flex-none">
            <flux:button wire:click="resetFilters" icon="arrow-path" variant="subtle" tooltip="রিসেট" />
        </div>
    </div>

    {{-- List Container --}}
    <div class="space-y-1">
        @forelse ($this->holidays as $holiday)
            <div x-on:click="$wire.showDetails({{ $holiday->id }}); $flux.modal('holiday-details').show()"
                class="group flex items-center justify-between p-4 rounded-2xl hover:bg-zinc-50 dark:hover:bg-white/5 transition-all cursor-pointer">

                <div class="flex items-center gap-5">
                    <div
                        class="flex flex-col items-center justify-center w-12 h-12 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-sm">
                        <span class="text-[9px] uppercase font-black text-indigo-600 dark:text-indigo-400">
                            {{ \Carbon\Carbon::parse($holiday->date)->format('M') }}
                        </span>
                        <span class="text-lg font-bold leading-none text-zinc-800 dark:text-zinc-100">
                            {{ \Carbon\Carbon::parse($holiday->date)->format('d') }}
                        </span>
                    </div>

                    <div>
                        <div
                            class="font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            {{ $holiday->title }}
                        </div>
                        <div class="text-xs text-zinc-500 flex items-center gap-2">
                            <span>{{ bn_day(\Carbon\Carbon::parse($holiday->date)->format('l')) }}</span>
                            <span class="size-1 bg-zinc-300 rounded-full"></span>
                            <span class="capitalize">{{ $holiday->type }}</span>
                        </div>
                    </div>
                </div>

                <flux:icon.chevron-right size="sm" variant="micro"
                    class="text-zinc-300 group-hover:translate-x-1 transition-transform" />
            </div>
        @empty
            <div class="py-20 text-center">
                <flux:icon.magnifying-glass class="mx-auto size-10 text-zinc-300 mb-4" />
                <flux:heading>কিছু পাওয়া যায়নি</flux:heading>
                <flux:subheading>অনুগ্রহ করে অন্য কোনো শব্দ বা ডেট রেঞ্জ দিয়ে চেষ্টা করুন</flux:subheading>
            </div>
        @endforelse
    </div>

    {{-- Infinite Scroll Observer --}}
    @if($this->holidays->count() >= 10)
        <div x-data x-intersect="$wire.loadMore()" class="py-10 flex justify-center">
            <div wire:loading wire:target="loadMore" class="flex flex-col items-center gap-2">
                <span class="text-xs text-zinc-400 animate-pulse">আরও লোড হচ্ছে...</span>
            </div>
        </div>
    @endif

    {{-- Modal Details (Same as before) --}}
    <flux:modal name="holiday-details" class="space-y-4">
        @if($this->selectedHoliday)
            <div class="space-y-2">
                <flux:heading size="xl">{{ $this->selectedHoliday->title }}</flux:heading>
                <flux:subheading class="text-lg">{{ $this->selectedHoliday->title_en }}</flux:subheading>
            </div>
            <div class="grid grid-cols-1 gap-6">
                <div class="flex items-start gap-3">
                    <flux:icon.calendar class="size-5 text-zinc-400" />
                    <div>
                        <flux:label>তারিখ ও বার</flux:label>
                        <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ \Carbon\Carbon::parse($this->selectedHoliday->date)->format('d F, Y') }}
                            ({{ bn_day(\Carbon\Carbon::parse($this->selectedHoliday->date)->format('l')) }})
                        </flux:text>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <flux:icon.tag class="size-5 text-zinc-400" />
                    <div>
                        <flux:label>ছুটির ধরণ</flux:label>
                        <div class="mt-1">
                            <flux:badge size="sm" color="indigo" inset="top bottom">{{ $this->selectedHoliday->type }}
                            </flux:badge>
                        </div>
                    </div>
                </div>
                <div class="space-y-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:label>বিস্তারিত বিবরণ</flux:label>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed italic">
                        "{{ $this->selectedHoliday->details ?? 'এই ছুটির দিন সম্পর্কে অতিরিক্ত কোনো তথ্য পাওয়া যায়নি।' }}"
                    </p>
                </div>
            </div>
            <div class="pt-6">
                <flux:button x-on:click="$dispatch('modal-close')" variant="filled" class="w-full">বন্ধ করুন</flux:button>
            </div>
        @endif
    </flux:modal>
</section>