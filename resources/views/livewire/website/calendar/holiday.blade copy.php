<?php

use Livewire\Volt\Component;
use App\Models\Holiday;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $perPage = 12;
    public $selectedYear;
    public $selectedType = '';
    public $selectedHolidayId = null;

    public function mount()
    {
        $this->selectedYear = now()->year;
    }

    // বছরগুলোর লিস্ট পাওয়ার জন্য Computed Property
    #[Computed]
    public function years()
    {
        return Holiday::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }

    // মেইন হলিডে ডেটা কুয়েরি
    #[Computed]
    public function holidays()
    {
        return Holiday::query()
            ->when($this->search, function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('details', 'like', "%{$this->search}%");
            })
            ->when($this->selectedYear, fn($q) => $q->whereYear('date', $this->selectedYear))
            ->when($this->selectedType, fn($q) => $q->where('type', $this->selectedType))
            ->orderBy('date', 'asc')
            ->paginate($this->perPage);
    }

    // ডিটেইলস দেখানোর জন্য
    #[Computed]
    public function selectedHoliday()
    {
        return $this->selectedHolidayId ? Holiday::find($this->selectedHolidayId) : null;
    }

    public function showDetails($id)
    {
        $this->selectedHolidayId = $id;
        $this->dispatch('modal-opened', name: 'holiday-details');
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'selectedYear', 'selectedType', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedType', 'selectedYear', 'perPage']);
        $this->resetPage();
    }
}; ?>

<section class="max-w-2xl mx-auto space-y-4">
    <div class="flex flex-col items-center text-center space-y-2">
        <flux:heading size="xl" level="1">বাংলাদেশের ছুটির
            দিন</flux:heading>
        <flux:subheading>সরকারি ছুটি ও বিশেষ দিবসের একটি পূর্ণাঙ্গ তালিকা</flux:subheading>
    </div>
    <div class="flex items-center gap-4 overflow-x-auto p-2">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm" class="min-w-50"
            placeholder="নাম বা বিবরণ..." />
        <flux:select wire:model.live="selectedYear" size="sm" class="min-w-30">
            <option value="">সব বছর</option>
            @foreach($this->years as $year)
                <option value="{{ $year }}">{{ $year }} ইং</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="selectedType" size="sm" class="min-w-30">
            <option value="">সব প্রকার ছুটি</option>
            <option value="Public">সরকারি ছুটি</option>
            <option value="National">জাতীয় দিবস</option>
            <option value="Religious">ধর্মীয় ছুটি</option>
        </flux:select>
        <flux:select wire:model.live="perPage" class="w-20" size="sm" class="min-w-30">
            <option value="12">12</option>
            <option value="24">24</option>
            <option value="48">48</option>
        </flux:select>
        <flux:button icon="arrow-path" wire:click="resetFilters" variant="ghost" size="sm" class="flex-1">রিসেট
        </flux:button>
    </div>

    @forelse ($this->holidays as $holiday)
        <flux:card
            class="group hover:shadow-md transition-shadow duration-300 overflow-hidden border-none bg-zinc-50 dark:bg-zinc-900/50">
            <div class="flex gap-4">
                <div
                    class="flex flex-col items-center justify-center w-16 h-20 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm group-hover:border-indigo-500 transition-colors">
                    <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">
                        {{ bn_num(\Carbon\Carbon::parse($holiday->date)->format('d')) }}
                    </span>
                    <span class="text-[10px] uppercase font-bold text-zinc-500">
                        {{ bn_month(\Carbon\Carbon::parse($holiday->date)->format('F')) }}
                    </span>
                </div>

                <div class="flex-1 space-y-1">
                    <div class="flex justify-between items-start">
                        @php
                            $badgeColor = match ($holiday->type) {
                                'Public' => 'green',
                                'National' => 'red',
                                'Religious' => 'amber',
                                default => 'zinc'
                            };
                        @endphp
                        <flux:badge size="sm" :color="$badgeColor" inset="top bottom">{{ $holiday->type }}</flux:badge>
                        @if($holiday->is_annual)
                            <flux:badge size="sm" color="indigo" variant="outline">বার্ষিক</flux:badge>
                        @endif
                    </div>

                    <h3 class="font-bold text-zinc-800 dark:text-zinc-100 line-clamp-1 mt-2">{{ $holiday->title }}</h3>
                    <p class="text-xs text-zinc-500">{{ bn_day(\Carbon\Carbon::parse($holiday->date)->format('l')) }}
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
                <span class="text-xs text-zinc-400 font-mono">{{ $holiday->date }}</span>
                <flux:button wire:click="showDetails({{ $holiday->id }})" size="sm" variant="ghost"
                    icon-trailing="chevron-right">বিস্তারিত</flux:button>
            </div>
        </flux:card>
    @empty
        <livewire:global.nodata-message :title="'ছুটির দিন'" :search="$search" />
    @endforelse

    <div class="mt-8">
        {{ $this->holidays->links() }}
    </div>

    <flux:modal name="holiday-details" class="md:w-[500px]">
        @if($this->selectedHoliday)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $this->selectedHoliday->title }}</flux:heading>
                    <flux:subheading>{{ $this->selectedHoliday->title_en }}</flux:subheading>
                </div>

                <div class="grid grid-cols-2 gap-4 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl">
                    <flux:text size="sm" class="flex flex-col">
                        <span class="font-semibold text-zinc-400 text-[10px] uppercase">বাংলা তারিখ</span>
                        {{ bn_date(\Carbon\Carbon::parse($this->selectedHoliday->date)->format('d F, Y')) }}
                    </flux:text>
                    <flux:text size="sm" class="flex flex-col">
                        <span class="font-semibold text-zinc-400 text-[10px] uppercase">ইংরেজি তারিখ</span>
                        {{ \Carbon\Carbon::parse($this->selectedHoliday->date)->format('d M, Y') }}
                    </flux:text>
                </div>

                <div class="space-y-2">
                    <flux:label>বিস্তারিত বিবরণ</flux:label>
                    <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed text-sm">
                        {{ $this->selectedHoliday->details ?? 'কোন অতিরিক্ত তথ্য নেই।' }}
                    </p>
                </div>

                <flux:button class="w-full" variant="filled" x-on:click="$dispatch('modal-close')">বন্ধ করুন</flux:button>
            </div>
        @endif
    </flux:modal>
</section>