<?php

use App\Search\GlobalSearchService;
use App\Search\SearchRegistry;
use Livewire\Volt\Component;

new class extends Component {
    public string $search = '';
    public int $perPage = 10;

    public function updatedSearch()
    {
        $this->perPage = 10;
    }

    public function loadMore(): void
    {
        $this->perPage += 10;
    }

    public function with(): array
    {
        $result = app(GlobalSearchService::class)->search($this->search, $this->perPage + 1);

        $hasMore = $result->items->count() > $this->perPage;
        $items = $result->items->take($this->perPage);

        return [
            'results' => $items,
            'hasMore' => $hasMore,
            'scope' => $result->scope,
            'isEmpty' => $items->isEmpty() && mb_strlen(trim($this->search)) >= 2,
            'prefixes' => SearchRegistry::keys(),
            'hints' => SearchRegistry::prefixHints(),
        ];
    }
}; ?>

<div class="w-full">
    {{-- Search Input --}}
    <div class="pe-10 ps-4 py-4 border-b border-zinc-200 dark:border-zinc-800">
        <flux:input wire:model.live.debounce.250ms="search"
            placeholder="বাংলা বা ইংরেজিতে খুঁজুন… (tourism:cox বা পর্যটন:কক্স)" autofocus clearable
            icon="magnifying-glass" />
    </div>

    <div class="max-h-[60vh] overflow-y-auto">
        @if (strlen(trim($search)) < 2)
            {{-- Empty State --}}
            <div class="p-6">
                <flux:heading size="sm" class="mb-3">দ্রুত ফিল্টার</flux:heading>
                <div class="flex flex-wrap gap-4">
                    @foreach ($prefixes as $prefix)
                        <flux:button size="xs" variant="filled"
                            @click="$wire.set('search', '{{ $prefix }}:')">
                            {{ $prefix }}:
                        </flux:button>
                    @endforeach
                </div>

                <flux:text class="mt-4 text-xs text-zinc-500">
                    টিপস:
                    @foreach ($hints as $bn => $en)
                        <flux:badge size="sm" inset="top bottom" class="mx-0.5">{{ $en }}:</flux:badge>
                    @endforeach
                    লিখেও সার্চ করা যায়।
                </flux:text>
            </div>
        @elseif ($isEmpty)
            {{-- No Results --}}
            <div class="py-14 text-center">
                <flux:icon.face-frown class="mx-auto size-12 text-zinc-300 mb-4" />
                <flux:heading>ফলাফল পাওয়া যায়নি</flux:heading>
                <flux:text>
                    "<span class="text-blue-500 font-medium">{{ $search }}</span>" এর জন্য কিছু পাওয়া যায়নি।
                </flux:text>
            </div>
        @else
            {{-- Scope Indicator --}}
            @if ($scope)
                <div
                    class="px-4 py-2 bg-zinc-50 dark:bg-white/5 border-b border-zinc-200 dark:border-white/10 flex items-center gap-4">
                    <flux:icon.funnel variant="mini" class="text-zinc-400" />
                    <flux:text size="sm">
                        Searching in:
                        <flux:badge color="blue" size="sm">{{ $scope }}</flux:badge>
                    </flux:text>
                </div>
            @endif

            {{-- Results --}}
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($results as $item)
                    <a href="{{ $item->_search_url }}" wire:navigate @click="open = false"
                        class="group flex items-center gap-4 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors">

                        <flux:avatar src="{{ $item->_search_image }}" name="{{ $item->_search_title }}" />

                        <div class="flex-1 min-w-0">
                            <flux:heading class="font-bold truncate">
                                {{ $item->_search_title }}
                            </flux:heading>
                            @if ($item->_search_subtitle)
                                <flux:text class="truncate" size="xs">{{ $item->_search_subtitle }}</flux:text>
                            @endif
                        </div>

                        <flux:badge size="sm" variant="subtle"
                            class="shrink-0 uppercase tracking-tighter text-[9px]">
                            {{ $item->_search_label }}
                        </flux:badge>

                        <flux:icon.chevron-right variant="micro"
                            class="text-zinc-300 group-hover:font-bold transition-all group-hover:translate-x-0.5" />
                    </a>
                @endforeach
            </div>

            {{-- Infinite scroll --}}
            <div class="flex justify-center">
                @if ($hasMore)
                    <div wire:intersect="loadMore" class="p-4 flex justify-center">
                        <flux:icon.loading variant="mini" />
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div
                class="sticky bottom-0 bg-white/80 dark:bg-zinc-900/80 backdrop-blur px-4 py-2 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <flux:text size="xs">{{ $results->count() }} results</flux:text>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1">
                        <flux:text variant="clear" class="text-xs">↵</flux:text>
                        <flux:text size="xs">Open</flux:text>
                    </div>
                    <div class="flex items-center gap-1">
                        <flux:text variant="clear" class="text-xs">ESC</flux:text>
                        <flux:text size="xs">Close</flux:text>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
