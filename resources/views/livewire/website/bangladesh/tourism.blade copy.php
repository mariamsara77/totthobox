<?php

use App\Models\District;
use App\Models\Division;
use App\Models\Thana;
use App\Models\TourismBd;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = '';

    public $selectedDivision = null;

    public $selectedDistrict = null;

    public $selectedThana = null;

    public function updated($property)
    {
        if (in_array($property, ['selectedDivision', 'selectedDistrict', 'selectedThana', 'search'])) {
            $this->resetPage();
        }

        if ($property === 'selectedDivision') {
            $this->reset(['selectedDistrict', 'selectedThana']);
        }
        if ($property === 'selectedDistrict') {
            $this->reset('selectedThana');
        }
    }

    #[Computed]
    public function divisions()
    {
        return Cache::remember('divisions_list_opt', 86400, function () {
            return Division::orderBy('name')->get(['id', 'name']);
        });
    }

    #[Computed]
    public function districts()
    {
        if (! $this->selectedDivision) {
            return [];
        }

        return Cache::remember("districts_div_{$this->selectedDivision}", 86400, function () {
            return District::where('division_id', $this->selectedDivision)
                ->orderBy('name')
                ->get(['id', 'name', 'division_id']);
        });
    }

    #[Computed]
    public function thanas()
    {
        if (! $this->selectedDistrict) {
            return [];
        }

        return Cache::remember("thanas_dist_{$this->selectedDistrict}", 86400, function () {
            return Thana::where('district_id', $this->selectedDistrict)
                ->orderBy('name')
                ->get(['id', 'name', 'district_id']);
        });
    }

    #[Computed]
    public function tourisms()
    {
        return TourismBd::query()
            ->with([
                'division:id,name',
                'district:id,name',
                'thana:id,name',
                'media',
                'creator' => function ($query) {
                    $query->select(['id', 'name', 'avatar', 'slug', 'email_verified_at'])
                        ->with(['media']);
                },
            ])
            ->where('status', 1)
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->selectedDivision, fn ($q) => $q->where('division_id', $this->selectedDivision))
            ->when($this->selectedDistrict, fn ($q) => $q->where('district_id', $this->selectedDistrict))
            ->when($this->selectedThana, fn ($q) => $q->where('thana_id', $this->selectedThana))
            ->latest()
            ->paginate(10);
    }

    // Computed বাদ দিয়ে সরাসরি মেথড হিসেবে লিখুন
    public function getCreatorsProperty()
    {
        return collect($this->tourisms->items())
            ->pluck('creator')
            ->filter()
            ->unique('id');
    }

    public function resetFilter()
    {
        $this->reset(['selectedDivision', 'selectedDistrict', 'selectedThana', 'search']);
        $this->resetPage();
    }
}; ?>
@php
// প্রথম আইটেমের ইমেজ নেওয়া (যদি থাকে)
$firstItem = $this->tourisms->first();
$ogImage = $firstItem && $firstItem->hasMedia('default')
? $firstItem->getFirstMediaUrl('default', 'preview')
: asset('images/og-default.jpg'); // আপনার পাবলিক ফোল্ডারে একটি ডিফল্ট ইমেজ রাখুন

$pageTitle = $search ? 'খুঁজুন: ' . $search . ' | Totthobox' : 'বাংলাদেশের সকল পর্যটন কেন্দ্র ও ভ্রমণ গাইড';
$pageDesc = $search ? $search . ' সম্পর্কিত তথ্য ও পর্যটন কেন্দ্রের বিস্তারিত গাইড।' : 'বাংলাদেশের ৬৪ জেলার সেরা পর্যটন
কেন্দ্র, ঐতিহাসিক স্থান ও প্রাকৃতিক সৌন্দর্যের বিস্তারিত ভ্রমণ গাইড।';
@endphp

<x-seo :title="$pageTitle" :description="$pageDesc"
    :keywords="'বাংলাদেশ পর্যটন, ভ্রমণ গাইড, Totthobox, দর্শনীয় স্থান, ' . ($search ? $search : 'বাংলাদেশ')"
    :image="$ogImage" />

<section class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">বাংলাদেশের সকল পর্যটন কেন্দ্র</flux:heading>

        <div class="flex items-center gap-2">
            {{-- Main Creator Info Icon --}}
            <flux:tooltip toggleable class="">
                <flux:button icon="information-circle" size="sm" variant="ghost" />

                <flux:tooltip.content
                    class="max-w-[22rem] overflow-hidden border rounded-2xl! bg-white! dark:bg-zinc-700!">
                    {{-- Header of Tooltip --}}
                    <div class="p-2">
                        <flux:heading size="sm">তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                        </flux:heading>
                        <flux:subheading size="xs">এই কন্টেন্ট তৈরিতে যারা অবদান রেখেছেন</flux:subheading>
                    </div>

                    {{-- Scrollable List Area --}}
                    <div class="max-h-[350px] overflow-y-auto space-y-3 p-2 custom-scrollbar">
                        @foreach($this->creators as $creator)
                        <div class="relative p-2 group rounded-2xl bg-zinc-400/25 transition-all">
                            <div class="flex items-start gap-3">
                                {{-- Avatar with Online Indicator --}}
                                <div class="relative">
                                    <flux:avatar src="{{ $creator->avatar_url }}" size="md" badge
                                        badge:color="{{ $creator->isOnline() ? 'green' : 'zinc' }}" />
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <flux:heading>
                                            {{ $creator->name }}
                                        </flux:heading>
                                        @if ($creator->email_verified_at)
                                        <flux:icon.check-badge class="size-4" variant="solid" />
                                        @endif
                                        {{-- <flux:badge size="xs" variant="pill" class="shrink-0">
                                                {{ $creator->district?->name ?? 'বাংলাদেশ' }}
                                        </flux:badge> --}}
                                    </div>

                                    <flux:text size="sm">
                                        {{ $creator->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর' }}
                                    </flux:text>
                                    {{--
                                        @if($creator->bio)
                                        <flux:text size="sm">
                                            {!! $creator->bio !!}
                                        </flux:text>
                                        @endif --}}
                                </div>
                            </div>
                            <flux:separator class="my-2" />
                            {{-- Hover Action (Optional: Profile Link) --}}
                            <div class="flex items-center justify-between">
                                <flux:text size="xs">
                                    একটিভ: {{ bn_num($creator->last_active_at?->diffForHumans()) ?? 'অজানা' }}
                                </flux:text>
                                <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost" size="xs"
                                    icon="arrow-right" class="group-hover:translate-x-1 transition-transform" />
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Footer --}}
                    <div class="text-center">
                        <flux:text size="xs">
                            আমাদের সকল তথ্য ভেরিফাইড এবং যাচাইকৃত।
                        </flux:text>
                    </div>
                </flux:tooltip.content>
            </flux:tooltip>
        </div>
    </div>


    {{-- Search & Filters --}}
    <div class="flex items-center gap-3 overflow-x-auto">
        <flux:input wire:model.live.debounce.400ms="search" size="sm" placeholder="দর্শনীয় স্থান খুঁজুন..."
            icon="magnifying-glass" variant="filled" class="rounded-xl! min-w-50" />

        {{-- বিভাগ --}}
        <flux:select wire:model.live="selectedDivision" variant="listbox" size="sm" placeholder="বিভাগ"
            class="min-w-30">
            <flux:select.option value="">সকল বিভাগ</flux:select.option>
            @foreach ($this->divisions as $div)
            <flux:select.option value="{{ $div->id }}">{{ $div->name }}</flux:select.option>
            @endforeach
        </flux:select>

        {{-- জেলা --}}
        <flux:select wire:model.live="selectedDistrict" variant="listbox" size="sm" placeholder="জেলা" class="min-w-30"
            :disabled="!$selectedDivision">
            <flux:select.option value="">সকল জেলা</flux:select.option>
            @foreach ($this->districts as $dis)
            <flux:select.option value="{{ $dis->id }}">{{ $dis->name }}</flux:select.option>
            @endforeach
        </flux:select>

        {{-- থানা --}}
        <flux:select wire:model.live="selectedThana" variant="listbox" size="sm" placeholder="থানা" class="min-w-30"
            :disabled="!$selectedDistrict">
            <flux:select.option value="">সকল থানা</flux:select.option>
            @foreach ($this->thanas as $thana)
            <flux:select.option value="{{ $thana->id }}">{{ $thana->name }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $selectedDivision || $selectedDistrict || $selectedThana)
        <flux:button wire:click="resetFilter" size="sm" variant="ghost" icon="x-mark" class="shrink-0">মুছে ফেলুন
        </flux:button>
        @endif
    </div>


    @forelse ($this->tourisms as $item)
    <header class="mb-4">
        <flux:heading size="xl">{{ $item->title }}</flux:heading>
        <div class="flex items-center gap-3">
            <span class="flex text-zinc-400 gap-1">
                <flux:icon.map-pin variant="mini" color="zinc" />
                <flux:text size="md">
                    {{ $item->thana->name ?? 'N/A' }}, {{ $item->district->name ?? 'N/A' }}
                </flux:text>
            </span>
            <span class="w-1 h-1 rounded-full bg-zinc-300"></span>
            <span class="text-xs uppercase tracking-wider font-medium">{{ $item->division->name ?? '' }}</span>
        </div>
    </header>

    @if($item->media->isNotEmpty())
    <flux:media :media="$item->media" />
    @endif

    <flux:longtext :content="$item->description" />
    <flux:separator variant="faint" />
    @empty
    <livewire:global.nodata-message :title="'বাংলাদেশের তথ্য'" :search="$search" />
    @endforelse

    <div class="mt-12 border-t border-zinc-100 dark:border-zinc-800 pt-6">
        {{ $this->tourisms->links() }}
    </div>
</section>