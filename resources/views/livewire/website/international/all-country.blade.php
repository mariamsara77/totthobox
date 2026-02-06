<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
new class extends Component {
    public $search = '';
    public $regionFilter = '';
    public $subregionFilter = '';
    public $loading = false; // মাউন্টে ক্যাশ থাকলে ট্রু রাখার দরকার নেই
    public $perPage = 12;
    public $loadedCount = 12;

    // ১. অল কান্ট্রিজ এখন আর পাবলিক প্রোপার্টি না, এটি সরাসরি ক্যাশ থেকে আসবে
    public function getAllCountriesProperty()
    {
        return Cache::remember('world_countries_v7', now()->addMonth(), function () {
            $response = Http::timeout(30)->retry(3, 200)
                ->get('https://restcountries.com/v3.1/all?fields=name,capital,region,subregion,population,area,flags,cca2');

            if ($response->successful()) {
                return collect($response->json())
                    ->map(fn($country) => [
                        'name' => $country['name']['common'] ?? 'Unknown',
                        'official_name' => $country['name']['official'] ?? 'Unknown',
                        'capital' => !empty($country['capital']) ? $country['capital'][0] : 'N/A',
                        'region' => $country['region'] ?? 'Unknown',
                        'subregion' => $country['subregion'] ?? 'Unknown',
                        'population' => $country['population'] ?? 0,
                        'area' => $country['area'] ?? 0,
                        'flag' => $country['flags']['png'] ?? '',
                        'code' => $country['cca2'] ?? '',
                    ])
                    ->sortBy('name')
                    ->values()
                    ->toArray();
            }
            return [];
        });
    }

    // ২. ফিল্টারিং লজিক (সবসময় $this->allCountries থেকে ডাটা নিবে)
    public function getFilteredCountriesProperty()
    {
        return collect($this->allCountries)
            ->filter(function ($country) {
                $searchMatch = empty($this->search) || str_contains(strtolower($country['name']), strtolower($this->search));
                $regionMatch = empty($this->regionFilter) || $country['region'] === $this->regionFilter;
                $subregionMatch = empty($this->subregionFilter) || $country['subregion'] === $this->subregionFilter;
                return $searchMatch && $regionMatch && $subregionMatch;
            });
    }

    public function getDisplayedCountriesProperty()
    {
        return $this->filteredCountries->slice(0, $this->loadedCount)->all();
    }

    // ৩. ফিল্টার পরিবর্তন হলে কাউন্ট রিসেট করা
    public function updatedSearch()
    {
        $this->loadedCount = $this->perPage;
    }
    public function updatedRegionFilter()
    {
        $this->loadedCount = $this->perPage;
        $this->subregionFilter = '';
    }

    public function loadMore()
    {
        $this->loadedCount += $this->perPage;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'regionFilter', 'subregionFilter']);
        $this->loadedCount = $this->perPage;
    }

    public function getRegionsProperty()
    {
        return collect($this->allCountries)->pluck('region')->unique()->sort();
    }

    public function getSubregionsProperty()
    {
        if (!$this->regionFilter)
            return [];
        return collect($this->allCountries)
            ->where('region', $this->regionFilter)
            ->pluck('subregion')
            ->unique()
            ->filter()
            ->sort();
    }
}; ?>

<section class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="text-center mb-10">
        <flux:heading size="xl" class="font-bold">বিশ্ব পরিভ্রমণ</flux:heading>
        <flux:text class="mt-2">পৃথিবীর সকল দেশের তথ্য এক নজরে</flux:text>
    </div>

    {{-- Sticky Filter Bar --}}
    <div class="sticky top-0 z-10 backdrop-blur-md py-4 mb-8">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-grow min-w-[200px]">
                <flux:input wire:model.live.debounce.400ms="search" placeholder="দেশের নাম..." icon="magnifying-glass"
                    clearable />
            </div>

            <div class="w-full sm:w-auto flex gap-2">
                <flux:select wire:model.live="regionFilter" placeholder="অঞ্চল" class="min-w-[130px]">
                    <flux:select.option value="">সব অঞ্চল</flux:select.option>
                    @foreach ($this->regions as $region)
                        <flux:select.option value="{{ $region }}">{{ $region }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:button wire:click="resetFilters" variant="subtle" icon="arrow-path" class="shrink-0" />
            </div>
        </div>
    </div>

    {{-- Content Area --}}
    @if ($loading)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @for ($i = 1; $i <= 4; $i++)
                @include('partials.skeleton')
            @endfor
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @forelse ($this->displayedCountries as $country)
                <div
                    class="group bg-zinc-400/10 rounded-[2rem] hover:shadow-2xl hover:-translate-y-1 transition-all duration-500 overflow-hidden">

                    {{-- Aspect Ratio Flag Container --}}
                    <div class="relative aspect-[16/9] overflow-hidden">
                        {{-- এখানে কোলন প্রোপার্টির ভেতর সরাসরি ভ্যারিয়েবল লিখুন, ডাবল কার্লি ব্র্যাকেট ছাড়া --}}
                        <flux:media :media="$country['flag']" />

                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent pointer-events-none">
                        </div>

                        <div class="absolute bottom-4 left-6 text-white pointer-events-none">
                            <span
                                class="text-[10px] font-bold tracking-widest uppercase opacity-80">{{ $country['region'] }}</span>
                            <h3 class="text-2xl font-bold leading-tight">{{ $country['name'] }}</h3>
                        </div>
                    </div>

                    {{-- Country Stats Table --}}
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-6">

                            <div class="flex flex-col gap-1">
                                <flux:heading level="3" size="sm" class="text-zinc-500 dark:text-zinc-400 font-medium">রাজধানী
                                </flux:heading>
                                <flux:text class="font-semibold">
                                    {{ $country['capital'] }}
                                </flux:text>
                            </div>

                            <div class="flex flex-col gap-1 text-right">
                                <flux:heading level="3" size="sm" class="text-zinc-500 dark:text-zinc-400 font-medium">কোড
                                </flux:heading>
                                <flux:text class="font-semibold">
                                    {{ $country['code'] }}
                                </flux:text>
                            </div>

                            <div class="flex flex-col gap-1 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                                <flux:heading level="3" size="sm" class="text-zinc-500 dark:text-zinc-400 font-medium">জনসংখ্যা
                                </flux:heading>
                                <flux:text class="font-semibold">
                                    {{ number_format($country['population'] / 1000000, 2) }} মিলিয়ন
                                </flux:text>
                            </div>

                            <div class="flex flex-col gap-1 pt-4 border-t border-zinc-100 dark:border-zinc-800 text-right">
                                <flux:heading level="3" size="sm" class="text-zinc-500 dark:text-zinc-400 font-medium">আয়তন
                                </flux:heading>
                                <flux:text class="font-semibold">
                                    {{ number_format($country['area']) }}
                                    <span class="text-[10px] align-top ml-0.5 text-zinc-400 uppercase">km²</span>
                                </flux:text>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <flux:icon.magnifying-glass size="xl" class="mx-auto text-zinc-300 mb-4" />
                    <flux:heading>কোনো দেশ পাওয়া যায়নি</flux:heading>
                    <flux:text>অনুগ্রহ করে অন্য নামে চেষ্টা করুন।</flux:text>
                </div>
            @endforelse
        </div>

        {{-- Load More Section --}}
        @if ($this->filteredCountries->count() > $loadedCount)
            <div x-intersect="$wire.loadMore()" class="mt-16 flex flex-col items-center gap-4 py-10">
                <flux:button wire:click="loadMore" variant="subtle">
                </flux:button>
            </div>
        @endif
    @endif
</section>