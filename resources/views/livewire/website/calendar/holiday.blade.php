<?php

use Livewire\Volt\Component;
use App\Models\Holiday;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $selectedYear;
    public $selectedType = '';
    public $selectedHoliday = null;
    public $years = [];

    public function mount()
    {
        $this->years = Holiday::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $this->selectedYear = now()->year;
    }

    public function showDetails($id)
    {
        $this->selectedHoliday = Holiday::find($id);
    }

    public function closeDetails()
    {
        $this->selectedHoliday = null;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedType', 'selectedYear','perPage']);
        $this->resetPage();
    }
}; ?>

@php
$holidays = Holiday::query()
->when($search, fn($q) => $q->where(function ($q2) use ($search) {
$q2->where('title', 'like', "%{$search}%")
->orWhere('details', 'like', "%{$search}%");
}))
->when($selectedYear, fn($q) => $q->whereYear('date', $selectedYear))
->when($selectedType, fn($q) => $q->where('type', $selectedType))
->orderBy('date')
->paginate($perPage);
@endphp

<section>
    <div class="">
        <!-- Header Section -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold">বাংলাদেশের ছুটির দিন</h1>
            <h2 class="text-gray-500 dark:text-gray-300 text-sm">সরকারি ছুটি ও বিশেষ দিবসের তালিকা</h2>
        </div>

        <!-- Filters -->
        <div class="w-full overflow-x-auto">
            <div class="flex md:grid md:grid-cols-12 gap-3 mb-4 p-3">
                <!-- Search -->
                <div class=" md:col-span-4 flex-shrink-0">
                    <flux:input icon="search" placeholder="ছুটির নাম বা বিবরণ লিখুন..." wire:model.live.debounce.300ms="search" size="sm" />
                </div>

                <!-- Year -->
                <div class="md:col-span-3 flex-shrink-0">
                    <flux:select wire:model.live="selectedYear" size="sm">
                        <option value="">সব বছর</option>
                        @foreach($years as $year)
                        <option value="{{ $year }}" @selected($year==$selectedYear)>
                            {{ $year }} ইং
                        </option>
                        @endforeach
                    </flux:select>
                </div>

                <!-- Type -->
                <div class=" md:col-span-3 flex-shrink-0">
                    <flux:select wire:model.live="selectedType" size="sm">
                        <option value="">সব প্রকার</option>
                        <option value="Public">সরকারি ছুটি</option>
                        <option value="National">জাতীয় দিবস</option>
                        <option value="Religious">ধর্মীয় ছুটি</option>
                    </flux:select>
                </div>

                <!-- Per Page -->
                <div class=" md:col-span-1 flex-shrink-0">
                    <flux:select wire:model.live="perPage" size="sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </flux:select>
                </div>

                <!-- Reset -->
                <div class="md:col-span-1 flex-shrink-0">
                    <flux:button wire:click="resetFilters" class="w-full" size="sm">
                        Reset
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Count -->
        <div class="text-sm px-3 py-2 mb-4">
            <i class="fas fa-info-circle"></i>
            মোট {{ bn_num($holidays->total()) }} টি ছুটি পাওয়া গেছে
            @if($selectedYear) {{ bn_num($selectedYear) }} সালে @endif
        </div>

        <!-- Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($holidays as $holiday)
            <div class="bg-zinc-100 dark:bg-zinc-700/25 rounded-xl overflow-hidden">
                <div class="p-4 flex items-center gap-4">
                    <!-- Date and Day -->
                    <div class="bg-zinc-500/10 text-blue-600 rounded-lg p-2 text-center flex-shrink-0 w-20">
                        <h5 class="text-lg font-bold">
                            {{ bn_date(\Carbon\Carbon::parse($holiday->date)->format('d')) }}
                        </h5>
                        <small class="text-xs">{{ bn_month(\Carbon\Carbon::parse($holiday->date)->format('F')) }}</small>
                        <small class="block text-xs text-gray-500 mt-1">{{ bn_day(\Carbon\Carbon::parse($holiday->date)->format('l')) }}</small>
                    </div>

                    <!-- Holiday Info -->
                    <div class="flex-grow">
                        <h6 class="font-semibold text-lg">{{ $holiday->title }}</h6>
                        <small class="text-gray-500 block">{{ $holiday->title_en }}</small>

                        <!-- Type and Annual Badge -->
                        <div class="mt-2 flex items-center gap-2 text-xs">
                            <span class="px-2 py-1 rounded-md
                                @if($holiday->type === 'Public') bg-green-100 text-green-700
                                @elseif($holiday->type === 'Religious') bg-yellow-100 text-yellow-700
                                @elseif($holiday->type === 'National') bg-red-100 text-red-700
                                @else bg-gray-200 text-gray-700 @endif">
                                {{ ucfirst($holiday->type ?? 'General') }}
                            </span>
                            @if($holiday->is_annual)
                            <span class="px-2 py-1 rounded-md bg-blue-100 text-blue-700">বার্ষিক</span>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Details Button -->
                <div class="p-4 text-right">
                    <button class="px-3 py-1.5 rounded-md text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-zinc-600 transition-colors" wire:click="showDetails({{ $holiday->id }})">
                        <i class="fas fa-eye mr-1"></i> বিস্তারিত
                    </button>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <div class="text-center text-yellow-700 px-4 py-6 rounded-xl border border-zinc-400/25">
                    <h6 class="font-semibold">কোন ছুটি পাওয়া যায়নি</h6>
                    <small>অনুগ্রহ করে ভিন্ন ফিল্টার ব্যবহার করুন।</small>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-3 mt-4">
            <div>
                {{ $holidays->links() }}
            </div>
        </div>

        <!-- Modal -->
        @if($selectedHoliday)
        <div class="fixed bg-zinc-300/25 inset-0 flex items-center justify-center z-50" wire:click="closeDetails()">
            <div class="bg-zinc-200 dark:bg-zinc-700 rounded-xl max-w-lg w-full mx-2" @click.stop>
                <div class="flex justify-between items-center px-4 py-3 border-b border-zinc-400/25">
                    <h5 class="text-lg font-semibold text-center flex-grow">{{ $selectedHoliday->title }}</h5>
                    <button class="text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100" wire:click="closeDetails()">✕</button>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p><strong>তারিখ:</strong><br>
                                {{ bn_day(\Carbon\Carbon::parse($selectedHoliday->date)->format('l')) }},<br>
                                {{ bn_date(\Carbon\Carbon::parse($selectedHoliday->date)->format('d F Y')) }}
                            </p>
                            <p class="mt-2"><strong>ইংরেজি তারিখ:</strong><br>
                                {{ \Carbon\Carbon::parse($selectedHoliday->date)->format('l, d F Y') }}
                            </p>
                        </div>
                        <div>
                            <p><strong>প্রকার:</strong>
                                <span class="px-2 py-1 text-xs rounded-md
                                    @if($selectedHoliday->type === 'Public') bg-green-100 text-green-700
                                    @elseif($selectedHoliday->type === 'Religious') bg-yellow-100 text-yellow-700
                                    @elseif($selectedHoliday->type === 'National') bg-red-100 text-red-700
                                    @else bg-gray-200 text-gray-700 @endif">
                                    {{ ucfirst($selectedHoliday->type ?? 'General') }}
                                </span>
                            </p>
                            @if($selectedHoliday->is_annual)
                            <p class="mt-2"><strong>বার্ষিক ছুটি:</strong>
                                <span class="px-2 py-1 text-xs rounded-md bg-blue-100 text-blue-700">হ্যাঁ</span>
                            </p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="font-semibold">বিস্তারিত:</h6>
                        <p>{{ $selectedHoliday->details }}</p>
                        @if($selectedHoliday->details_en)
                        <h6 class="font-semibold mt-2">Details:</h6>
                        <p>{{ $selectedHoliday->details_en }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
