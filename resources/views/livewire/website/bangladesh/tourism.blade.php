<?php

use Livewire\Volt\Component;
use App\Models\{TourismBd, Division, District, Thana};
use Livewire\{WithPagination, Attributes\Computed};
use Illuminate\Support\Facades\Cache;

new class extends Component {
    use WithPagination;

    public $search = '',
    $selectedDivision = null,
    $selectedDistrict = null,
    $selectedThana = null,
    $selectedType = null;

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

        if ($property === 'selectedType') {
            $this->resetPage();
        }
    }

    #[Computed]
    public function divisions()
    {
        return Cache::remember('divisions_list_opt', 86400, fn() => Division::orderBy('name')->get(['id', 'name']));
    }

    #[Computed]
    public function districts()
    {
        if (!$this->selectedDivision) {
            return [];
        }
        return Cache::remember(
            "districts_div_{$this->selectedDivision}",
            86400,
            fn() => District::where('division_id', $this->selectedDivision)
                ->orderBy('name')
                ->get(['id', 'name', 'division_id']),
        );
    }

    #[Computed]
    public function thanas()
    {
        if (!$this->selectedDistrict) {
            return [];
        }
        return Cache::remember(
            "thanas_dist_{$this->selectedDistrict}",
            86400,
            fn() => Thana::where('district_id', $this->selectedDistrict)
                ->orderBy('name')
                ->get(['id', 'name', 'district_id']),
        );
    }

    #[Computed]
    public function tourisms()
    {
        return TourismBd::query()
            ->with(['division:id,name', 'district:id,name', 'thana:id,name', 'media'])
            ->where('status', 1)
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->selectedType, fn($q) => $q->where('tourism_type', $this->selectedType))
            ->when($this->selectedDivision, fn($q) => $q->where('division_id', $this->selectedDivision))
            ->when($this->selectedDistrict, fn($q) => $q->where('district_id', $this->selectedDistrict))
            ->when($this->selectedThana, fn($q) => $q->where('thana_id', $this->selectedThana))
            ->latest()
            ->paginate(12); // কার্ডের জন্য ১২টি আইটেম ভালো দেখায় (৩ কলামে ৪ সারি)
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
    $ogImage =
        $firstItem && $firstItem->hasMedia('default')
        ? $firstItem->getFirstMediaUrl('default', 'preview')
        : asset('images/og-default.jpg'); // আপনার পাবলিক ফোল্ডারে একটি ডিফল্ট ইমেজ রাখুন

    $pageTitle = $search ? 'খুঁজুন: ' . $search . ' | Totthobox' : 'বাংলাদেশের সকল পর্যটন কেন্দ্র ও ভ্রমণ গাইড';
    $pageDesc = $search
        ? $search . ' সম্পর্কিত তথ্য ও পর্যটন কেন্দ্রের বিস্তারিত গাইড।'
        : 'বাংলাদেশের ৬৪ জেলার সেরা পর্যটন
                                                                                                                                                                                                                    কেন্দ্র, ঐতিহাসিক স্থান ও প্রাকৃতিক সৌন্দর্যের বিস্তারিত ভ্রমণ গাইড।';
@endphp

<x-seo :title="$pageTitle" :description="$pageDesc" :keywords="'বাংলাদেশ পর্যটন, ভ্রমণ গাইড, Totthobox, দর্শনীয় স্থান, ' . ($search ? $search : 'বাংলাদেশ')" :image="$ogImage" />

<section class="max-w-2xl mx-auto space-y-4">
    <header>
        {{-- SEO Friendly H1 Header --}}
        <flux:heading size="xl" level="1" class="font-extrabold tracking-tight text-zinc-900 dark:text-white">
            @if ($search)
                '{{ $search }}' এর দর্শনীয় স্থানসমূহ
            @else
                বাংলাদেশের সকল পর্যটন কেন্দ্র
            @endif
        </flux:heading>

        {{-- Subheading: Indirect travel guidance --}}
        <flux:subheading>
            আমাদের যাচাইকৃত তথ্যভাণ্ডার থেকে আপনার পছন্দের গন্তব্যের খুঁটিনাটি জেনে নিন। ঐতিহাসিক নিদর্শন থেকে প্রাকৃতিক
            বৈচিত্র্য—সবকিছুর বিস্তারিত তথ্য এখন এক জায়গায়।
        </flux:subheading>
    </header>

    {{-- Horizontal Scrollable Filters --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <div>
            <flux:input wire:model.live.debounce.400ms="search" placeholder="নামে খুঁজুন..." icon="magnifying-glass"
                variant="filled" class="min-w-40 rounded-xl!" />
        </div>
        <div>
            <flux:select wire:model.live="selectedType" variant="listbox" placeholder="সব ধরন" class="min-w-40">
                <flux:select.option value="">সকল ধরন</flux:select.option>
                <flux:select.option value="historical">ঐতিহাসিক ও প্রত্নতাত্ত্বিক</flux:select.option>
                <flux:select.option value="heritage">রাজপ্রাসাদ ও জমিদার বাড়ি</flux:select.option>
                <flux:select.option value="natural">প্রাকৃতিক সৌন্দর্য</flux:select.option>
                <flux:select.option value="waterfall">ঝর্ণা ও জলপ্রপাত</flux:select.option>
                <flux:select.option value="beach">সমুদ্র সৈকত</flux:select.option>
                <flux:select.option value="hill_station">পাহাড় ও পার্বত্য এলাকা</flux:select.option>
                <flux:select.option value="forest">বন ও বন্যপ্রাণী</flux:select.option>
                <flux:select.option value="religious">ধর্মীয় ও পবিত্র স্থান</flux:select.option>
                <flux:select.option value="cultural">সাংস্কৃতিক ও জাদুঘর</flux:select.option>
                <flux:select.option value="adventure">অ্যাডভেঞ্চার ও ট্র্যাকিং</flux:select.option>
                <flux:select.option value="resort">রিসোর্ট ও বিনোদন কেন্দ্র</flux:select.option>
                <flux:select.option value="riverine">হাওর ও নদীকেন্দ্রিক</flux:select.option>
                <flux:select.option value="picnic">পিকনিক স্পট</flux:select.option>
            </flux:select>
        </div>
        <div>
            <flux:select wire:model.live="selectedDivision" variant="listbox" placeholder="বিভাগ" class="min-w-30">
                <flux:select.option value="">সকল বিভাগ</flux:select.option>
                @foreach ($this->divisions as $div)
                    <flux:select.option value="{{ $div->id }}">{{ $div->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <flux:select wire:model.live="selectedDistrict" variant="listbox" placeholder="জেলা" class="min-w-30"
                :disabled="!$selectedDivision">
                <flux:select.option value="">সকল জেলা</flux:select.option>
                @foreach ($this->districts as $dis)
                    <flux:select.option value="{{ $dis->id }}">{{ $dis->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <flux:select wire:model.live="selectedThana" variant="listbox" placeholder="থানা" class="min-w-30"
                :disabled="!$selectedDistrict">
                <flux:select.option value="">সকল থানা</flux:select.option>
                @foreach ($this->thanas as $thana)
                    <flux:select.option value="{{ $thana->id }}">{{ $thana->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    @php
        $typeLabels = [
            'historical' => 'ঐতিহাসিক ও প্রত্নতাত্ত্বিক',
            'heritage' => 'রাজপ্রাসাদ ও জমিদার বাড়ি',
            'natural' => 'প্রাকৃতিক সৌন্দর্য',
            'waterfall' => 'ঝর্ণা ও জলপ্রপাত',
            'beach' => 'সমুদ্র সৈকত',
            'hill_station' => 'পাহাড় ও পার্বত্য এলাকা',
            'forest' => 'বন ও বন্যপ্রাণী',
            'religious' => 'ধর্মীয় ও পবিত্র স্থান',
            'cultural' => 'সাংস্কৃতিক ও জাদুঘর',
            'adventure' => 'অ্যাডভেঞ্চার ও ট্র্যাকিং',
            'resort' => 'রিসোর্ট ও বিনোদন কেন্দ্র',
            'riverine' => 'হাওর ও নদীকেন্দ্রিক',
            'picnic' => 'পিকনিক স্পট',
        ];
    @endphp

    {{-- List View --}}
    <div class="space-y-4">
        @forelse ($this->tourisms as $item)
            <a href="{{ route('bangladesh.tourism.show', $item->slug) }}" aria-label="Latest on our blog">
                <flux:card class="flex gap-6 mb-4 items-center justify-between cursor-pointer">
                    <div>
                        {{-- Avatar Group --}}
                        <flux:avatar.group class="size-16">
                            @foreach ($item->getMedia('tourism_images')->take(1) as $media)
                                <flux:avatar src="{{ $media->getUrl() }}" />
                            @endforeach
                            @if ($item->getMedia('tourism_images')->count() > 1)
                                <flux:avatar initials="+{{ bn_num($item->getMedia('tourism_images')->count() - 1) }}" />
                            @endif
                        </flux:avatar.group>
                    </div>

                    {{-- Card Details --}}
                    <div>

                        <flux:heading size="lg" class="font-bold">{{ $item->title }}
                        </flux:heading>

                        @if($item->tourism_type && isset($typeLabels[$item->tourism_type]))
                            <flux:badge size="sm" color="zinc" variant="outline" class="text-[10px] md:text-xs font-medium">
                                {{ $typeLabels[$item->tourism_type] }}
                            </flux:badge>
                        @endif

                        <div class="text-xs text-zinc-500 flex items-center gap-1 mt-0.5">
                            <flux:icon.map-pin class="size-3" />
                            {{ $item->thana->name ?? '...' }} • {{ $item->district->name ?? '...' }}
                        </div>

                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2 line-clamp-1">
                            {{ Str::limit(strip_tags($item->description), 60) }}
                        </p>

                    </div>
                    <flux:icon.chevron-right class="" />
                </flux:card>
            </a>
        @empty
            <livewire:global.nodata-message :title="'বাংলাদেশের সকল পর্যটন কেন্দ্র'" :search="$search" />
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center pt-2">{{ $this->tourisms->links() }}</div>
</section>