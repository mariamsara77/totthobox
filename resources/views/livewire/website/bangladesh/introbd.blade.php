<?php

use Livewire\Volt\Component;
use App\Models\IntroBd;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    // ১. সার্চ করলে পেজিনেশন রিসেট
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // ২. আগের মতো ক্যাটাগরি ভিত্তিক গ্রুপিং লজিক (Computed Property তে)
    public function groupedIntros()
    {
        return IntroBd::query()
            ->with('media')
            ->when($this->search, function ($query) {
                // গ্রুপ করে সার্চ করা ভালো যাতে অন্য কন্ডিশনে সমস্যা না হয়
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->latest('id') // orderBy('id', 'desc') এর সহজ রূপ
            ->get()
            ->groupBy('intro_category');
    }
    public function resetFilter()
    {
        $this->reset('search');
        $this->resetPage();
    }
}; ?>

<section class="max-w-2xl mx-auto">
    {{-- Header & Search --}}
    <div class="mb-10 space-y-4">
        <flux:heading size="xl" class="text-center">বাংলাদেশের পরিচিতি ও গুরুত্বপূর্ণ তথ্য</flux:heading>

        <div class="flex items-center gap-3">
            <flux:input wire:model.live.debounce.400ms="search" size="sm"
                placeholder="তথ্য খুঁজুন (যেমন: রাজধানী, নদী...)" icon="magnifying-glass" variant="filled"
                class="!rounded-xl flex-1" />

            @if($search)
                <flux:button wire:click="resetFilter" size="sm" variant="ghost" icon="x-mark">মুছে ফেলুন</flux:button>
            @endif
        </div>
    </div>

    {{-- Content List with Grouping --}}
    <div class="space-y-12">
        @forelse ($this->groupedIntros() as $category => $intros)
            <div class="space-y-6">
                {{-- Category Badge --}}
                <div class="flex justify-center">
                    <flux:badge size="lg" color="zinc" variant="solid" class="px-4 !rounded-full uppercase tracking-widest">
                        {{ $category ?: 'সাধারণ তথ্য' }}
                    </flux:badge>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($intros as $item)
                        <article class="py-8 first:pt-0 last:pb-0">
                            {{-- Title --}}
                            <header class="mb-4">
                                <flux:heading size="lg" class="hover:text-zinc-600 transition-colors cursor-default">
                                    {{ $item->title }}
                                </flux:heading>
                            </header>

                            {{-- Grid Gallery --}}
                            @if($item->hasMedia('intro_images'))
                                <flux:media :media="$item->getMedia('intro_images')" />
                            @endif

                            {{-- Description with Expandable feature --}}
                            <div x-data="{ expanded: false }" class="relative">
                                {{-- Description Section --}}
                                <div x-data="{ expanded: false }" class="relative">
                                    {{-- টেক্সট এরিয়া: এখানে ক্লিক করলে expanded টগল হবে --}}
                                    <div @click="expanded = !expanded" class="" title="বিস্তারিত দেখতে ক্লিক করুন">
                                        <div class="transition-all duration-300" :class="expanded ? '' : 'line-clamp-3'">
                                            <flux:text size="lg">
                                                <div class="ql-text-fromat">
                                                    {!! $item->description !!}
                                                </div>
                                            </flux:text>
                                        </div>
                                        @if(mb_strlen($item->description) > 160)
                                            <span x-show="!expanded" class="text-xs text-zinc-400">
                                                বিস্তারিত পড়ুন
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
            <flux:separator variant="faint" />
        @empty
            <livewire:global.nodata-message :title="'বাংলাদেশের তথ্য'" :search="$search" />
        @endforelse
    </div>
</section>