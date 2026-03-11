<?php

use Livewire\Volt\Component;
use App\Models\BasicIslam;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public $search = '';

    /**
     * ডাটাবেস থেকে ১ ঘণ্টার জন্য ক্যাশ করে ডাটা নিয়ে আসা।
     * এখানে creator এবং তাদের প্রোফাইল পিকচার (media) ইগার লোড করা হয়েছে।
     */
    #[Computed]
    public function allData()
    {
        return Cache::remember('basic_islams_list', now()->addHour(), function () {
            return BasicIslam::query()
                ->with([
                    'creator' => fn($q) => $q
                        ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                        ->with(['media'])
                        ->without(['roles', 'permissions'])
                ])
                ->latest()
                ->get();
        });
    }

    /**
     * ক্যাশ করা কালেকশন থেকে সার্চ ফিল্টারিং।
     */
    #[Computed]
    public function filteredData()
    {
        if (empty($this->search)) return $this->allData;

        $term = mb_strtolower($this->search, 'UTF-8');
        return $this->allData->filter(function ($item) use ($term) {
            return str_contains(mb_strtolower($item->title, 'UTF-8'), $term) ||
                   str_contains(mb_strtolower($item->description, 'UTF-8'), $term);
        });
    }

    /**
     * কন্টেন্ট তৈরিতে যারা অবদান রেখেছেন তাদের ইউনিক লিস্ট।
     */
    #[Computed]
    public function creators()
    {
        return $this->allData->pluck('creator')->unique('id')->filter();
    }

    public function resetFilter()
    {
        $this->reset('search');
    }
}; ?>

<div>
    {{-- SEO Setup --}}
    <x-seo 
        title="ইসলামের মৌলিক জ্ঞান"
        description="ইসলামের মূল ভিত্তি, আরকান এবং মৌলিক জ্ঞান সম্পর্কে সঠিক ও যাচাইকৃত তথ্য।"
        keywords="ইসলামিক জ্ঞান, ইসলামের মূলভিত্তি, ঈমান, নামাজ, যাকাত, হজ, তথ্যবক্স ইসলাম" 
    />

    <section class="max-w-2xl mx-auto space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between border-b pb-4 dark:border-zinc-700">
            <div>
                <flux:heading level="1" size="xl" class="flex items-center gap-2">
                    <flux:icon icon="book-open" variant="mini" class="text-emerald-600" />
                    ইসলামের মৌলিক জ্ঞান
                </flux:heading>
                <flux:subheading level="2">দ্বীনের সঠিক পথ ও মৌলিক ধারণা</flux:subheading>
            </div>

            <div class="flex items-center gap-2">
                {{-- Main Creator Info Icon --}}
                <flux:tooltip toggleable>
                    <flux:button icon="users" size="sm" variant="subtle" />

                    <flux:tooltip.content
                        class="rounded-2xl! space-y-4">
                        {{-- Header of Tooltip --}}
                            <flux:heading>তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                            </flux:heading>
                            <flux:subheading size="sm">এই কন্টেন্ট তৈরিতে যারা অবদান রেখেছেন</flux:subheading>
                        {{-- Scrollable List Area --}}
                        <div class="max-h-80 overflow-y-auto space-y-4 custom-scrollbar">
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

        {{-- Search Box --}}
        <div class="flex items-center gap-3">
            <flux:input 
                wire:model.live.debounce.400ms="search" 
                placeholder="বিষয় খুঁজুন (যেমন: ঈমান, নামাজ...)"
                icon="magnifying-glass" 
                variant="filled" 
                class="rounded-xl flex-1" 
            />
            @if($search)
                <flux:button wire:click="resetFilter" variant="ghost" icon="x-mark" size="sm" />
            @endif
        </div>

        {{-- Content List --}}
        <div class="space-y-8">
            @forelse($this->filteredData as $item)
                <article class="space-y-3 group">
                    <header class="flex items-center gap-2">
                        <div class="h-6 w-1 bg-emerald-500 rounded-full"></div>
                        <flux:heading level="2" size="lg" class="group-hover:text-emerald-600 transition-colors">
                            {{ $item->title }}
                        </flux:heading>
                    </header>

                    <div class="pl-3 prose dark:prose-invert max-w-none">
                        <flux:longtext :content="$item->description"/>
                    </div>

                    <flux:separator variant="subtle" />
                </article>
            @empty
                <livewire:global.nodata-message :title="'ইসলামিক তথ্য'" :search="$search" />
            @endforelse
        </div>
    </section>
</div>