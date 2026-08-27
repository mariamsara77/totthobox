<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

new class extends Component {
    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $regionFilter = '';

    #[Url(history: true)]
    public string $sortBy = 'name';

    public int $perPage = 12;
    public int $loadedCount = 12;

    // ─── Data (১ মাস ক্যাশ) ───────────────────────────────────
    #[Computed(cache: true, key: 'countries_merged_v11')]
    public function allCountries(): array
    {
        return Cache::remember('countries_merged_v11', now()->addMonths(1), function () {
            try {
                $mainResp = Http::timeout(20)->retry(2, 600)->get('https://raw.githubusercontent.com/mledoze/countries/master/dist/countries.json');

                if (!$mainResp->successful()) {
                    return [];
                }

                $popMap = [];
                $popResp = Http::timeout(12)->retry(1, 400)->get('https://raw.githubusercontent.com/samayo/country-json/master/src/country-by-population.json');
                if ($popResp->successful()) {
                    foreach ($popResp->json() as $item) {
                        $popMap[$item['country']] = $item['population'] ?? 0;
                    }
                }

                $contMap = [];
                $contResp = Http::timeout(12)->retry(1, 400)->get('https://raw.githubusercontent.com/samayo/country-json/master/src/country-by-continent.json');
                if ($contResp->successful()) {
                    foreach ($contResp->json() as $item) {
                        $contMap[$item['country']] = $item['continent'] ?? null;
                    }
                }

                return collect($mainResp->json())
                    ->map(function ($c) use ($popMap, $contMap) {
                        $commonName = $c['name']['common'] ?? 'Unknown';
                        $officialName = $c['name']['official'] ?? $commonName;
                        $code = $c['cca2'] ?? '';

                        $population = $popMap[$commonName] ?? ($popMap[$officialName] ?? 0);
                        $continent = $contMap[$commonName] ?? ($contMap[$officialName] ?? ($c['region'] ?? 'N/A'));

                        $nameBengali = $c['name']['native']['ben']['common'] ?? ($c['translations']['ben']['common'] ?? null);

                        $phoneCode = 'N/A';
                        if (!empty($c['idd']['root'])) {
                            $phoneCode = $c['idd']['root'] . ($c['idd']['suffixes'][0] ?? '');
                        }

                        $lowerCode = strtolower($code ?: 'un');

                        return [
                            'slug' => Str::slug($commonName),
                            'name' => $commonName,
                            'name_bengali' => $nameBengali ?? $commonName,
                            'independent' => !empty($c['independent']) ? 'স্বাধীন রাষ্ট্র' : 'অধীনস্থ অঞ্চল',
                            'code' => $code,
                            'cca3' => $c['cca3'] ?? 'N/A',
                            'region' => $c['region'] ?? 'Unknown',
                            'subregion' => $c['subregion'] ?? '',
                            'continent' => $continent,
                            'capital' => !empty($c['capital']) ? $c['capital'][0] : 'তথ্য নেই',
                            'area' => (float) ($c['area'] ?? 0),
                            'population' => (int) $population,
                            'phone_code' => $phoneCode,
                            'flag' => "https://flagcdn.com/w320/{$lowerCode}.png",
                            'flag_emoji' => $c['flag'] ?? '🌐',
                            'languages' => !empty($c['languages']) ? array_values($c['languages']) : [],
                            'landlocked' => !empty($c['landlocked']),
                        ];
                    })
                    ->sortBy('name')
                    ->values()
                    ->toArray();
            } catch (\Throwable $e) {
                \Log::error('Countries fetch error: ' . $e->getMessage());
                return [];
            }
        });
    }

    #[Computed]
    public function filteredCountries()
    {
        $search = strtolower(trim($this->search));

        $col = collect($this->allCountries)->filter(function ($c) use ($search) {
            $match = $search === '' || str_contains(strtolower($c['name']), $search) || str_contains(strtolower($c['name_bengali'] ?? ''), $search) || str_contains(strtolower($c['capital']), $search) || str_contains(strtolower($c['code']), $search);

            $regionOk = $this->regionFilter === '' || $c['region'] === $this->regionFilter;

            return $match && $regionOk;
        });

        $sorted = match ($this->sortBy) {
            'population_desc' => $col->sortByDesc('population'),
            'population_asc' => $col->sortBy('population'),
            'area_desc' => $col->sortByDesc('area'),
            'area_asc' => $col->sortBy('area'),
            default => $col->sortBy('name'),
        };

        return $sorted->values();
    }

    #[Computed]
    public function displayedCountries()
    {
        return $this->filteredCountries->slice(0, $this->loadedCount)->all();
    }

    #[Computed]
    public function stats(): array
    {
        $all = collect($this->allCountries);

        return [
            'total' => $all->count(),
            'population' => $all->sum('population'),
            'regions' => $all->pluck('region')->unique()->filter()->count(),
            'landlocked' => $all->where('landlocked', true)->count(),
        ];
    }

    #[Computed]
    public function regions()
    {
        return collect($this->allCountries)->pluck('region')->unique()->filter()->sort()->values();
    }

    public function loadMore(): void
    {
        if ($this->loadedCount < $this->filteredCountries->count()) {
            $this->loadedCount += $this->perPage;
        }
    }

    public function updatedSearch(): void
    {
        $this->loadedCount = $this->perPage;
    }

    public function updatedRegionFilter(): void
    {
        $this->loadedCount = $this->perPage;
    }

    public function updatedSortBy(): void
    {
        $this->loadedCount = $this->perPage;
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'regionFilter', 'sortBy']);
        $this->loadedCount = $this->perPage;
    }

    public function formatPopulation(int $pop): string
    {
        if ($pop >= 1_000_000_000) {
            return number_format($pop / 1_000_000_000, 2) . ' বিলিয়ন';
        }
        if ($pop >= 1_000_000) {
            return number_format($pop / 1_000_000, 2) . ' মিলিয়ন';
        }
        if ($pop >= 1_000) {
            return number_format($pop / 1_000, 1) . ' হাজার';
        }
        return $pop > 0 ? number_format($pop) : 'তথ্য নেই';
    }

    public function formatArea(float $area): string
    {
        if ($area <= 0) {
            return 'জানা নেই';
        }
        if ($area >= 1_000_000) {
            return number_format($area / 1_000_000, 2) . ' মি. কিমি²';
        }
        return number_format($area) . ' কিমি²';
    }

    public function placeholder()
    {
        return view('partials.countries-skeleton');
    }
}; ?>

<div class="space-y-8">

    {{-- স্ট্যাটস --}}
    @if (count($this->allCountries) > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <flux:callout icon="globe-alt">
                <flux:callout.heading>মোট দেশ</flux:callout.heading>
                <flux:callout.text class="text-xl font-bold">{{ number_format($this->stats['total']) }} টি
                </flux:callout.text>
            </flux:callout>
            <flux:callout icon="users">
                <flux:callout.heading>বিশ্ব জনসংখ্যা</flux:callout.heading>
                <flux:callout.text class="text-xl font-bold">{{ $this->formatPopulation($this->stats['population']) }}
                </flux:callout.text>
            </flux:callout>
            <flux:callout icon="map">
                <flux:callout.heading>অঞ্চল</flux:callout.heading>
                <flux:callout.text class="text-xl font-bold">{{ $this->stats['regions'] }} টি</flux:callout.text>
            </flux:callout>
            <flux:callout icon="flag">
                <flux:callout.heading>স্থলবেষ্টিত</flux:callout.heading>
                <flux:callout.text class="text-xl font-bold">{{ $this->stats['landlocked'] }} টি</flux:callout.text>
            </flux:callout>
        </div>
    @endif

    {{-- ফিল্টার — $this->regions শুধু এখানে --}}
    <div class="bg-zinc-400/10 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-800">
        <div class="flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[200px]">
                <flux:input wire:model.live.debounce.400ms="search" placeholder="দেশের নাম বা রাজধানী খুঁজুন..."
                    icon="magnifying-glass" clearable />
            </div>

            <flux:select wire:model.live="regionFilter" class="w-full sm:w-auto min-w-[150px]">
                <flux:select.option value="">সকল অঞ্চল</flux:select.option>
                @foreach ($this->regions as $region)
                    <flux:select.option value="{{ $region }}">{{ $region }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="sortBy" icon="bars-3-bottom-left" class="w-full sm:w-auto min-w-[160px]">
                <flux:select.option value="name">নাম (A-Z)</flux:select.option>
                <flux:select.option value="population_desc">জনসংখ্যা (বেশি → কম)</flux:select.option>
                <flux:select.option value="population_asc">জনসংখ্যা (কম → বেশি)</flux:select.option>
                <flux:select.option value="area_desc">আয়তন (বড় → ছোট)</flux:select.option>
                <flux:select.option value="area_asc">আয়তন (ছোট → বড়)</flux:select.option>
            </flux:select>
        </div>

        <div class="flex items-center justify-between mt-3">
            <p class="text-sm text-zinc-500 font-medium">
                {{ number_format($this->filteredCountries->count()) }} টি দেশ পাওয়া গেছে
            </p>
            @if ($search !== '' || $regionFilter !== '' || $sortBy !== 'name')
                <flux:button wire:click="resetFilters" variant="ghost" size="sm" icon="x-mark">
                    ফিল্টার মুছুন
                </flux:button>
            @endif
        </div>
    </div>

    {{-- গ্রিড --}}
    <div class="grid md:grid-cols-2 gap-6" wire:key="grid-{{ $regionFilter }}-{{ $sortBy }}-{{ md5($search) }}">
        @forelse ($this->displayedCountries as $idx => $country)
            @if ($idx > 0 && $idx % 6 === 0)
                <div
                    class="md:col-span-2 hidden md:flex bg-zinc-100 dark:bg-zinc-800 rounded-xl items-center justify-center p-4 min-h-[120px] text-zinc-400 text-sm border border-dashed border-zinc-300 dark:border-zinc-700">
                    <span>Advertisement</span>
                </div>
            @endif

            <article wire:key="country-{{ $country['code'] }}"
                class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4 p-4 border-b border-zinc-400/25">
                    <img src="{{ $country['flag'] }}" alt="{{ $country['name_bengali'] }} এর পতাকা" loading="lazy"
                        decoding="async" class="w-16 h-11 object-cover rounded shadow-sm border border-zinc-400/25"
                        onerror="this.src='https://flagcdn.com/w320/un.png'">
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 truncate">
                            {{ $country['name_bengali'] }}
                            <span class="text-base">{{ $country['flag_emoji'] }}</span>
                        </h2>
                        <span
                            class="text-xs font-medium px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-full">
                            {{ $country['continent'] }}
                        </span>
                    </div>
                </div>

                <div class="p-4 space-y-3">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        <strong>{{ $country['name_bengali'] }}</strong> ({{ $country['name'] }})
                        {{ $country['continent'] }} মহাদেশের একটি {{ $country['independent'] }}।
                        রাজধানী: <strong>{{ $country['capital'] }}</strong>।
                        জনসংখ্যা প্রায় {{ $this->formatPopulation($country['population']) }},
                        আয়তন {{ $this->formatArea($country['area']) }}।
                    </p>

                    <div class="grid grid-cols-2 gap-y-2 pt-2 text-sm">
                        <div>
                            <span class="text-zinc-500 block text-xs">ডায়ালিং কোড</span>
                            <span class="font-medium font-mono">{{ $country['phone_code'] }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 block text-xs">ISO কোড</span>
                            <span class="font-medium font-mono">{{ $country['cca3'] }}</span>
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end">
                        <flux:button href="{{ route('international.country', ['slug' => $country['slug']]) }}"
                            variant="subtle" size="sm" icon-trailing="arrow-right" wire:navigate>
                            আরও পড়ুন
                        </flux:button>
                    </div>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 py-12 text-center">
                <flux:icon name="globe-alt" class="size-12 mx-auto text-zinc-300 mb-4" />
                <p class="text-zinc-500 text-lg">কোনো দেশের তথ্য পাওয়া যায়নি।</p>
                <flux:button wire:click="resetFilters" variant="ghost" class="mt-3">
                    সব ফিল্টার মুছুন
                </flux:button>
            </div>
        @endforelse
    </div>

    @if ($this->filteredCountries->count() > $loadedCount)
        <div x-intersect="$wire.loadMore()" class="flex justify-center py-8">
            <div class="flex items-center gap-4 text-zinc-500">
                <flux:icon.loading class="size-5" />
                <span class="text-sm">আরও দেশ লোড হচ্ছে...</span>
            </div>
        </div>
    @endif
</div>
