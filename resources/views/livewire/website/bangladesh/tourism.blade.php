<?php

use Livewire\Volt\Component;
use App\Models\{TourismBd, Division, District, Thana};
use Livewire\{WithPagination, Attributes\Computed};
use Illuminate\Support\Facades\Cache;

new class extends Component {
    use WithPagination;

    public $search = '', $selectedDivision = null, $selectedDistrict = null, $selectedThana = null;

    public function updated($property)
    {
        if (in_array($property, ['selectedDivision', 'selectedDistrict', 'selectedThana', 'search']))
            $this->resetPage();

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
        // বিভাগগুলো ১ ঘণ্টার জন্য ক্যাশ করা হলো এবং শুধু id ও name নেওয়া হলো
        return Cache::remember('divisions_list_opt', 3600, function () {
            return Division::orderBy('name')->get(['id', 'name']);
        });
    }

    #[Computed]
    public function districts()
    {
        if (!$this->selectedDivision)
            return [];

        // নির্দিষ্ট বিভাগের জেলাগুলো ক্যাশ করা
        return Cache::remember("districts_div_{$this->selectedDivision}", 3600, function () {
            return District::where('division_id', $this->selectedDivision)
                ->orderBy('name')
                ->get(['id', 'name', 'division_id']);
        });
    }

    #[Computed]
    public function thanas()
    {
        if (!$this->selectedDistrict)
            return [];

        // নির্দিষ্ট জেলার থানাগুলো ক্যাশ করা
        return Cache::remember("thanas_dist_{$this->selectedDistrict}", 3600, function () {
            return Thana::where('district_id', $this->selectedDistrict)
                ->orderBy('name')
                ->get(['id', 'name', 'district_id']);
        });
    }

    #[Computed]
    public function tourisms()
    {
        return TourismBd::query()
            // রিলেশনের শুধু প্রয়োজনীয় কলাম ইগার লোড করা এবং মিডিয়া লাইব্রেরি অপ্টিমাইজ করা
            ->with([
                'division:id,name',
                'district:id,name',
                'thana:id,name',
                'media'
            ])
            ->where('status', 1)
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->selectedDivision, fn($q) => $q->where('division_id', $this->selectedDivision))
            ->when($this->selectedDistrict, fn($q) => $q->where('district_id', $this->selectedDistrict))
            ->when($this->selectedThana, fn($q) => $q->where('thana_id', $this->selectedThana))
            ->latest()
            ->paginate(10);
    }

    public function resetFilter()
    {
        $this->reset(['selectedDivision', 'selectedDistrict', 'selectedThana', 'search']);
        $this->resetPage();
    }
}; ?>

<section class="max-w-2xl mx-auto">

    <flux:heading size="xl" class="text-center">বাংলাদেশের সকল পর্যটন কেন্দ্র</flux:heading>

    <div class="mt-4 space-y-6">

        {{-- Search & Filters --}}
        <div class="mb-10 flex items-center gap-3 overflow-x-auto">
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
            <flux:select wire:model.live="selectedDistrict" variant="listbox" size="sm" placeholder="জেলা"
                class="min-w-30" :disabled="!$selectedDivision">
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

        {{-- Content List --}}
        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
            @forelse ($this->tourisms as $item)
                <article class="py-10 first:pt-0 last:pb-0" wire:key="tourism-{{ $item->id }}">
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
                            <span
                                class="text-xs uppercase tracking-wider font-medium">{{ $item->division->name ?? '' }}</span>
                        </div>
                    </header>

                    @if($item->media->isNotEmpty())
                        <flux:media :media="$item->media" />
                    @endif

                    <div x-data="{ expanded: false }" class="relative mt-4">
                        <div @click="expanded = !expanded" class="cursor-pointer" title="বিস্তারিত দেখতে ক্লিক করুন">
                            <div class="transition-all duration-300" :class="expanded ? '' : 'line-clamp-3'">
                                <flux:text size="lg">{{ $item->description }}</flux:text>
                            </div>
                            @if(mb_strlen($item->description) > 160)
                                <span x-show="!expanded" class="text-xs text-zinc-400">বিস্তারিত পড়ুন</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <livewire:global.nodata-message :title="'বাংলাদেশের তথ্য'" :search="$search" />
            @endforelse
        </div>

        <div class="mt-12 border-t border-zinc-100 dark:border-zinc-800 pt-6">
            {{ $this->tourisms->links() }}
        </div>
    </div>
</section>