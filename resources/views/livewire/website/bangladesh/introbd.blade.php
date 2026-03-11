<?php

use Livewire\Volt\Component;
use App\Models\IntroBd;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public $search = '';

    /**
     * Fetches all data cached for 1 hour. 
     * Uses eager loading for nested media relationships.
     */
    #[Computed]
    public function allData()
    {
        return Cache::remember(IntroBd::CACHE_KEY, now()->addHour(), function () {
            return IntroBd::query()
                ->with([
                    'media',
                    'creator' => fn($q) => $q
                        ->select(['id', 'name', 'avatar', 'slug'])
                        ->with(['media']) // Eager load user avatars
                        ->without(['roles', 'permissions']) // Prevent overhead
                ])
                ->latest('id')
                ->get();
        });
    }

    /**
     * Filters the already cached collection. 
     * This avoids new database hits when searching.
     */
    #[Computed]
    public function filteredData()
    {
        if (empty($this->search))
            return $this->allData;

        $term = strtolower($this->search);
        return $this->allData->filter(function ($intro) use ($term) {
            return str_contains(strtolower($intro->title), $term) ||
                str_contains(strtolower($intro->description), $term);
        });
    }

    #[Computed]
    public function creators()
    {
        return $this->filteredData->pluck('creator')->unique('id');
    }

    #[Computed]
    public function groupedIntros()
    {
        return $this->filteredData->groupBy('intro_category');
    }

    public function resetFilter()
    {
        $this->reset('search');
    }
}; ?>

<x-seo title="বাংলাদেশের পরিচিতি"
    description="Totthobox-এ পাবেন বাংলাদেশ জেলা তথ্য, ইসলামিক শিক্ষা, স্বাস্থ্য জ্ঞান এবং প্রয়োজনীয় সকল সেবা।"
    keywords="তথ্যবক্স, সার্ভিস পোর্টাল, বাংলাদেশ, ইসলামিক সেবা" />
<section class="max-w-2xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <flux:heading size="xl">বাংলাদেশের পরিচিতি ও তথ্য</flux:heading>

        <div class="flex items-center gap-2">
            {{-- Main Creator Info Icon --}}
            <flux:tooltip toggleable>
                <flux:button icon="users" size="sm" variant="subtle" />

                <flux:tooltip.content class="rounded-2xl! space-y-4">
                    {{-- Header of Tooltip --}}
                    <flux:heading>তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                    </flux:heading>
                    <flux:subheading size="sm">এই কন্টেন্ট তৈরিতে যারা অবদান রেখেছেন</flux:subheading>
                    {{-- Scrollable List Area --}}
                    <div class="max-h-80 max-w-80 overflow-y-auto space-y-4 custom-scrollbar">
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
                                    </div>
                                </div>
                                <flux:separator class="my-2" />
                                {{-- Hover Action (Optional: Profile Link) --}}
                                <div class="flex items-center justify-between">
                                    <flux:text size="sm">
                                        একটিভ: {{ bn_num($creator->last_active_at?->diffForHumans()) ?? 'অজানা' }}
                                    </flux:text>
                                    <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost" size="xs"
                                        icon="arrow-right" class="group-hover:translate-x-1 transition-transform" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Footer --}}
                    <flux:text>
                        আমাদের সকল তথ্য ভেরিফাইড এবং যাচাইকৃত।
                    </flux:text>
                </flux:tooltip.content>
            </flux:tooltip>
        </div>
    </div>
    {{-- Header & Search --}}

    <div class="flex items-center gap-3">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="তথ্য খুঁজুন (যেমন: রাজধানী, নদী...)"
            icon="magnifying-glass" variant="filled" class="rounded-xl! flex-1" />

        @if($search)
            <flux:button wire:click="resetFilter" variant="ghost" icon="x-mark">মুছে ফেলুন</flux:button>
        @endif
    </div>

    {{-- Content List with Grouping --}}
    @forelse ($this->groupedIntros as $category => $intros)

        {{-- Category Badge --}}
        <div class="flex justify-center">
            <flux:badge size="lg" color="zinc" variant="solid">
                {{ $category ?: 'সাধারণ তথ্য' }}
            </flux:badge>
        </div>
        @foreach ($intros as $item)

            {{-- Title --}}
            <header class="flex items-center gap-2">
                <div class="h-6 w-1 bg-zinc-600 dark:bg-zinc-200 rounded-full"></div>
                <flux:heading level="2" size="lg" class="group-hover:text-emerald-600 transition-colors">
                    {{ $item->title }}
                </flux:heading>
            </header>

            {{-- Grid Gallery --}}
            @if($item->hasMedia('intro_images'))
                <flux:media :media="$item->getMedia('intro_images')" />
            @endif

            <flux:longtext :content="$item->description" />
            <flux:separator variant="faint" />
        @endforeach

    @empty
        <livewire:global.nodata-message :title="'বাংলাদেশের তথ্য'" :search="$search" />
    @endforelse
</section>