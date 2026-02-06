<?php

use App\Models\Minister;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $partyFilter = '';
    public $designationFilter = '';
    public $currentFilter = 'all';
    public $fromDateFilter = '';
    public $toDateFilter = '';

    public $parties = [];
    public $designations = [];

    public function mount()
    {
        $this->parties = Minister::distinct()->whereNotNull('party')->orderBy('party')->pluck('party')->toArray();
        $this->designations = Minister::distinct()->whereNotNull('designation')->orderBy('designation')->pluck('designation')->toArray();
    }

    /**
     * এরর লগ অনুযায়ী এই মেথডটির নাম resetFilter করা হলো যাতে ব্লেড থেকে কল করলে পাওয়া যায়।
     */
    public function resetFilters()
    {
        $this->reset(['search', 'partyFilter', 'designationFilter', 'currentFilter', 'fromDateFilter', 'toDateFilter']);
        $this->resetPage();
    }


    public function updating($field)
    {
        $this->resetPage();
    }

    public function ministers()
    {
        return Minister::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->partyFilter, fn($q) => $q->where('party', $this->partyFilter))
            ->when($this->designationFilter, fn($q) => $q->where('designation', $this->designationFilter))
            ->when($this->currentFilter === 'current', fn($q) => $q->where('is_current', true))
            ->when($this->currentFilter === 'former', fn($q) => $q->where('is_current', false))
            ->when($this->fromDateFilter, fn($q) => $q->whereDate('from_date', '>=', $this->fromDateFilter))
            ->when($this->toDateFilter, fn($q) => $q->whereDate('from_date', '<=', $this->toDateFilter))
            ->orderBy('rank', 'asc')
            ->paginate(12);
    }

    public function incrementView($id)
    {
        Minister::where('id', $id)->increment('view_count');
    }
};

?>

<section class="max-w-2xl mx-auto">
    <div class="mb-8">
        <flux:heading level="1" size="xl" class="text-center">বাংলাদেশের মন্ত্রী পরিষদ আর্কাইভ</flux:heading>
        <flux:text class="text-center text-base">স্বাধীনতার পর থেকে অদ্যবধি সকল মন্ত্রীগণের তথ্যাদি</flux:text>
    </div>

    <div class="flex gap-3 items-center justify-between p-2 overflow-x-auto">
        <div class="flex-1 min-w-[200px]">
            <flux:input icon='search' wire:model.live.debounce.300ms="search" placeholder="নাম দিয়ে খুজুন..." size="sm"
                clearable />
        </div>

        <flux:select wire:model.live="partyFilter" size="sm" class="min-w-[150px]">
            <option value="">সকল রাজনৈতিক দল</option>
            @foreach ($parties as $party)
                <option value="{{ $party }}">{{ $party }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="designationFilter" size="sm" class="min-w-[150px]">
            <option value="">সকল পদবী</option>
            @foreach ($designations as $designation)
                <option value="{{ $designation }}">{{ $designation }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="currentFilter" size="sm">
            <option value="all">অবস্থা (সকল)</option>
            <option value="current">বর্তমান</option>
            <option value="former">সাবেক</option>
        </flux:select>

        <div class="flex gap-2">
            <flux:input type="date" wire:model.live="fromDateFilter" size="sm" />
            <flux:input type="date" wire:model.live="toDateFilter" size="sm" />
        </div>

        @if($search)
            <flux:button wire:click="resetFilters" size="sm" variant="ghost" icon="x-mark" class="shrink-0">মুছে ফেলুন
            </flux:button>
        @endif
    </div>


    <div class="space-y-4">
        @php $ministersList = $this->ministers(); @endphp

        @forelse($ministersList as $minister)
                                    <div x-data="{ open: false }"
                                        class="group border border-slate-200 dark:border-zinc-700 rounded-2xl overflow-hidden transition-all duration-300 bg-white dark:bg-zinc-800/50"
                                        :class="open ? 'ring-1 ring-primary/30 shadow-xl' : 'hover:border-primary/50 shadow-sm'">

                                        <div @click="open = !open; if(open) $wire.incrementView({{ $minister->id }})"
                                            class="p-5 flex flex-col md:flex-row items-center gap-6 cursor-pointer">

                                            <div class="relative flex-shrink-0">
                                        {{-- Grid Gallery --}}
                                        @if($minister->hasMedia('minister_images'))
                                            <flux:media :media="$minister->getMedia('minister_images')" class="max-w-20 max-h-20"/>
                                        @endif

                                                <flux:badge variant="solid" :color="$minister->is_current ? 'green' : 'zinc'" size="sm"
                                                    class="absolute -top-2 -right-2">
                                                    {{ $minister->is_current ? 'বর্তমান' : 'সাবেক' }}
                                                </flux:badge>
                                            </div>

                                            <div class="flex-1 text-center md:text-left">
                                                <div class="flex flex-col md:flex-row md:items-center gap-2 mb-1">
                                                    <h3 class="text-xl font-bold text-slate-800 dark:text-zinc-100">{{ $minister->name }}</h3>
                                                    <span class="hidden md:block text-slate-300">|</span>
                                                    <span class="text-primary font-semibold text-sm">{{ $minister->designation }}</span>
                                                </div>

                                                <div
                                                    class="flex flex-wrap justify-center md:justify-start gap-y-1 gap-x-4 text-sm text-slate-500 dark:text-zinc-400">
                                                    <span class="flex items-center gap-1.5">
                                                        <flux:icon.briefcase size="xs" /> {{ $minister->party }}
                                                    </span>
                                                    <span class="flex items-center gap-1.5">
                                                        <flux:icon.map-pin size="xs" /> {{ $minister->district->name ?? 'জেলা নেই' }}
                                                    </span>
                                                    <span class="flex items-center gap-1.5">
                                                        <flux:icon.calendar size="xs" />
                                                        {{ $minister->from_date?->format('Y') }} -
                                                        {{ $minister->is_current ? 'বর্তমান' : ($minister->to_date?->format('Y') ?? 'অনির্দিষ্ট') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex-shrink-0">
                                                <flux:icon.chevron-down size="sm" class="transition-transform duration-300"
                                                    ::class="open ? 'rotate-180 text-primary' : 'text-slate-400'" />
                                            </div>
                                        </div>

                                        <div x-show="open" x-collapse x-cloak>
                                            <div class="px-6 pb-8 pt-2 border-t border-slate-100 dark:border-zinc-700/50">
                                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
                                                    <div class="lg:col-span-1 space-y-4">
                                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">অফিসিয়াল রেকর্ড</h4>
                                                        <div class="bg-slate-50 dark:bg-zinc-900/40 rounded-xl p-4 space-y-3">
                                                            <div class="flex justify-between text-sm">
                                                                <span class="text-slate-500">পদমর্যাদা (Rank):</span>
                                                                <span
                                                                    class="font-bold text-slate-700 dark:text-zinc-200">{{ $minister->rank }}</span>
                                                            </div>
                                                            <div class="flex justify-between text-sm">
                                                                <span class="text-slate-500">দায়িত্বভার গ্রহণ:</span>
                                                                <span
                                                                    class="font-semibold text-slate-700 dark:text-zinc-200">{{ $minister->from_date?->format('d M, Y') }}</span>
                                                            </div>
                                                            @if (!$minister->is_current)
                                                                <div class="flex justify-between text-sm">
                                                                    <span class="text-slate-500">দায়িত্ব শেষ:</span>
                                                                    <span
                                                                        class="font-semibold text-slate-700 dark:text-zinc-200">{{ $minister->to_date?->format('d M, Y') }}</span>
                                                                </div>
                                                            @endif
                                                            <div
                                                                class="pt-2 border-t border-slate-200 dark:border-zinc-700 flex justify-between text-sm italic">
                                                                <span class="text-slate-500">মোট সময়কাল:</span>
                                                                <span class="text-primary font-bold">
                                                                    @php
            $startDate = $minister->from_date;
            $endDate = $minister->is_current ? now() : $minister->to_date;
            if ($startDate && $endDate) {
                $diff = $startDate->diff($endDate);
                $years = $diff->y;
                $months = $diff->m;
            } else {
                $years = 0;
                $months = 0;
            }
                                                                    @endphp

                                                                    @if ($years > 0) {{ $years }} বছর @endif
                                                                    @if ($months > 0) {{ $months }} মাস @endif
                                                                    @if ($years == 0 && $months == 0) ১ মাসের কম @endif
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div class="p-4 border border-slate-100 dark:border-zinc-700 rounded-xl">
                                                            <h5 class="text-xs font-bold text-slate-400 uppercase mb-2">নির্বাচনী এলাকা</h5>
                                                            <p class="text-sm dark:text-zinc-300">
                                                                {{ $minister->thana->name ?? '' }}{{ $minister->thana ? ',' : '' }}
                                                                {{ $minister->district->name ?? '' }}{{ $minister->district ? ',' : '' }}
                                                                {{ $minister->division->name ?? '' }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="lg:col-span-2">
                                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">জীবন বৃত্তান্ত ও
                                                            কর্মজীবন</h4>
                                                        <div class="prose prose-sm dark:prose-invert max-w-none">
                                                            <p class="text-slate-600 dark:text-zinc-400 leading-relaxed text-justify">
                                                                @if ($minister->bio)
                                                                    {!! $minister->bio !!}
                                                                @else
                                                                    এই মন্ত্রী মহোদয়ের বিস্তারিত জীবন বৃত্তান্ত এখনো আপলোড করা হয়নি।
                                                                @endif
                                                            </p>
                                                        </div>

                                                        <div class="mt-6 flex flex-wrap gap-2">
                                                            <span
                                                                class="px-3 py-1 bg-slate-100 dark:bg-zinc-700 text-[11px] rounded-md text-slate-500">
                                                                Views: {{ number_format($minister->view_count) }}
                                                            </span>
                                                            @if ($minister->is_featured)
                                                                <span
                                                                    class="px-3 py-1 bg-amber-50 dark:bg-amber-900/20 text-[11px] rounded-md text-amber-600 font-bold">
                                                                    Featured Profile
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
        @empty
            <livewire:global.nodata-message :title="'মন্ত্রীর তথ্য'" :search="$search" />
        @endforelse
    </div>

    <div class="mt-10">
        {{ $ministersList->links() }}
    </div>
</section>