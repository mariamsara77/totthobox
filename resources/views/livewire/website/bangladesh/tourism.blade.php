<?php

use Livewire\Volt\Component;
use App\Models\{TourismBd, Division, District, Thana};
use Livewire\{WithPagination, Attributes\Computed};

new class extends Component {
    use WithPagination;

    // selectedThana যুক্ত করা হয়েছে
    public $search = '', $selectedDivision = null, $selectedDistrict = null, $selectedThana = null;

    public function updated($property)
    {
        // নতুন ফিল্টারগুলোর জন্য পেজ রিসেট
        if (in_array($property, ['selectedDivision', 'selectedDistrict', 'selectedThana', 'search']))
            $this->resetPage();

        // হায়ারার্কি অনুযায়ী রিসেট (বিভাগ পাল্টালে জেলা ও থানা রিসেট হবে)
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
        return Division::orderBy('name')->get();
    }

    #[Computed]
    public function districts()
    {
        return $this->selectedDivision ? District::where('division_id', $this->selectedDivision)->orderBy('name')->get() : [];
    }

    // নতুন: থানার লিস্টের জন্য Computed Property
    #[Computed]
    public function thanas()
    {
        return $this->selectedDistrict ? Thana::where('district_id', $this->selectedDistrict)->orderBy('name')->get() : [];
    }

    #[Computed]
    public function tourisms()
    {
        return TourismBd::query()
            ->with(['division', 'district', 'thana', 'media'])
            ->where('status', 1)
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->selectedDivision, fn($q) => $q->where('division_id', $this->selectedDivision))
            ->when($this->selectedDistrict, fn($q) => $q->where('district_id', $this->selectedDistrict))
            ->when($this->selectedThana, fn($q) => $q->where('thana_id', $this->selectedThana)) // থানা ফিল্টার
            ->latest()->paginate(10);
    }

    public function resetFilter()
    {
        $this->reset(['selectedDivision', 'selectedDistrict', 'selectedThana', 'search']);
        $this->resetPage();
    }
}; ?>

<section class="max-w-2xl mx-auto">
    {{-- Search & Filters --}}
    <div class="mb-10 flex items-center gap-3 overflow-x-auto">
        <flux:input wire:model.live.debounce.400ms="search" size="sm" placeholder="দর্শনীয় স্থান খুঁজুন..."
            icon="magnifying-glass" variant="filled" class="!rounded-xl min-w-[200px]" />

        {{-- বিভাগ --}}
        <flux:select wire:model.live="selectedDivision" variant="listbox" size="sm" placeholder="বিভাগ"
            class="min-w-[120px]">
            <flux:select.option value="">সকল বিভাগ</flux:select.option>
            @foreach ($this->divisions as $div)
                <flux:select.option value="{{ $div->id }}">{{ $div->name }}</flux:select.option>
            @endforeach
        </flux:select>

        {{-- জেলা --}}
        <flux:select wire:model.live="selectedDistrict" variant="listbox" size="sm" placeholder="জেলা"
            class="min-w-[120px]" :disabled="!$selectedDivision">
            <flux:select.option value="">সকল জেলা</flux:select.option>
            @foreach ($this->districts as $dis)
                <flux:select.option value="{{ $dis->id }}">{{ $dis->name }}</flux:select.option>
            @endforeach
        </flux:select>

        {{-- থানা (নতুন যুক্ত করা হয়েছে) --}}
        <flux:select wire:model.live="selectedThana" variant="listbox" size="sm" placeholder="থানা"
            class="min-w-[120px]" :disabled="!$selectedDistrict">
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
    {{-- Content List --}}
    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
        @forelse ($this->tourisms as $item)
            <article class="py-10 first:pt-0 last:pb-0">
                {{-- Title & Location Header --}}
                <header class="mb-4">
                    <flux:heading size="xl">
                        {{ $item->title }}
                    </flux:heading>
                    <div class="flex items-center gap-3">

                        <span class="flex text-zinc-400 gap-1">
                            <flux:icon.map-pin variant="mini" color="zinc" />
                            <flux:text size="md">
                                {{ $item->thana?->name }}, {{ $item->district?->name }}
                            </flux:text>
                        </span>

                        <span class="w-1 h-1 rounded-full bg-zinc-300"></span>
                        <span class="text-xs uppercase tracking-wider font-medium">{{ $item->division?->name }}</span>
                    </div>
                </header>

                {{-- Grid Gallery --}}
                @if($item->hasMedia('images'))
                    <flux:media :media="$item->getMedia('images')" />
                @endif

                {{-- Description Section --}}
                <div x-data="{ expanded: false }" class="relative">
                    {{-- টেক্সট এরিয়া: এখানে ক্লিক করলে expanded টগল হবে --}}
                    <div @click="expanded = !expanded" class="" title="বিস্তারিত দেখতে ক্লিক করুন">
                        <div class="transition-all duration-300" :class="expanded ? '' : 'line-clamp-3'">
                            <flux:text size="lg">{{ $item->description }}</flux:text>
                        </div>
                        <span x-show="!expanded" class="text-xs text-zinc-400">
                            বিস্তারিত পড়ুন
                        </span>


                    </div>
                </div>
            </article>
            <flux:separator />
        @empty
            <livewire:global.nodata-message :title="'বাংলাদেশের তথ্য'" :search="$search" />
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-12 border-t border-zinc-100 dark:border-zinc-800 pt-6">
        {{ $this->tourisms->links() }}
    </div>
</section>