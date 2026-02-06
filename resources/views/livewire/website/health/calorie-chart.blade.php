<?php

use Livewire\Volt\Component;
use App\Models\Food;
use App\Models\FoodCategory;

new class extends Component {
    public $foods;
    public $categories;
    public $selectedCategory = null;
    public $search = '';

    public function mount()
    {
        $this->loadFoods();
        $this->categories = FoodCategory::orderBy('name_bn')->get();
    }

    public function loadFoods()
    {
        $this->foods = Food::with(['category', 'nutrients'])
            ->when($this->search, function ($q) {
                $q->where('name_bn', 'like', "%{$this->search}%")
                    ->orWhere('name_en', 'like', "%{$this->search}%");
            })
            ->when($this->selectedCategory, fn($q) => $q->where('food_category_id', $this->selectedCategory))
            ->latest()
            ->get();
    }

    public function updatedSearch()
    {
        $this->loadFoods();
    }

    public function filterByCategory($id)
    {
        $this->selectedCategory = $id;
        $this->loadFoods();
    }

    // View Count বাড়ানোর মেথড
    public function incrementView($id)
    {
        Food::where('id', $id)->increment('view_count');
    }
};
?>

<section class="max-w-2xl mx-auto">
    {{-- Search + Category Filter --}}
    <div class="mb-8">
        <div class="mb-6 flex justify-center">
            <div class="relative w-full max-w-md">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="খাবারের নাম দিয়ে খুঁজুন..."
                    icon="magnifying-glass" />
            </div>
        </div>

        <div class="flex flex-wrap justify-center gap-3">
            <flux:button wire:click="filterByCategory(null)"
                variant="{{ $selectedCategory === null ? 'filled' : 'subtle' }}"
                class="px-4 py-2 rounded-full text-sm font-medium transition-all">সব</flux:button>

            @foreach ($categories as $cat)
                <flux:button wire:click="filterByCategory({{ $cat->id }})"
                    variant="{{ $selectedCategory === $cat->id ? 'filled' : 'subtle' }}"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all">
                    {{ $cat->name_bn }}
                </flux:button>
            @endforeach
        </div>
    </div>

    {{-- Food Cards Grid --}}
    <div class="space-y-4">
        @forelse($foods as $food)
            <div x-data="{ open: false }"
                class="rounded-2xl border border-zinc-400/25 overflow-hidden group  transition-all shadow-sm">

                {{-- Header (Clickable) --}}
                <div @click="open = !open; if(open) $wire.incrementView({{ $food->id }})" class="p-4 cursor-pointer">
                    <div class="flex gap-4 items-center">
                        @if ($food->image)
                            <div class="flex-shrink-0 w-16 h-16">
                                <flux:media :media="asset('storage/' . $food->image)" />
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-lg leading-tight">
                                {{ $food->name_bn }}
                                @if ($food->name_en)
                                    <span
                                        class="text-sm text-gray-400 font-normal block sm:inline">({{ $food->name_en }})</span>
                                @endif
                            </h3>
                            <div
                                class="inline-flex items-center px-3 py-0.5 rounded-full text-[10px] font-medium bg-blue-100/10 text-blue-500 mt-1">
                                {{ $food->category?->name_bn ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <flux:icon.chevron-down size="sm" class="transition-transform duration-300"
                                ::class="open ? 'rotate-180 text-primary' : 'text-slate-400'" />
                        </div>
                    </div>

                    {{-- Simple summary when closed --}}
                    <div x-show="!open" class="mt-2 flex justify-between items-center text-sm text-zinc-500">
                        <span>শক্তি: <span
                                class="font-bold text-zinc-700 dark:text-zinc-300">{{ $food->calorie ?? '-' }}</span>
                            ক্যালোরী</span>
                        @if($food->view_count > 0)
                            <span class="text-[10px] opacity-70">দেখা হয়েছে: {{ $food->view_count }} বার</span>
                        @endif
                    </div>
                </div>

                {{-- Collapsible Content --}}
                <div x-show="open" x-collapse x-cloak>
                    <div class="px-4 pb-5 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        @if ($food->serving_size)
                            <div class="text-xs mb-3 text-primary font-medium italic">
                                * প্রতি {{ $food->serving_size }} পরিবেশন অনুযায়ী
                            </div>
                        @endif

                        <div class="flex justify-between text-lg font-bold mb-4">
                            <div>মোট শক্তিঃ</div>
                            <div class="text-primary">{{ $food->calorie ?? '-' }} ক্যালোরী</div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm mb-5">
                            <div class="bg-zinc-400/10 rounded-lg p-2 text-center">
                                <div class="text-[10px] uppercase opacity-60">কার্বস</div>
                                <div class="font-bold">{{ $food->carb ?? '-' }}g</div>
                            </div>
                            <div class="bg-zinc-400/10 rounded-lg p-2 text-center">
                                <div class="text-[10px] uppercase opacity-60">প্রোটিন</div>
                                <div class="font-bold">{{ $food->protein ?? '-' }}g</div>
                            </div>
                            <div class="bg-zinc-400/10 rounded-lg p-2 text-center">
                                <div class="text-[10px] uppercase opacity-60">ফ্যাট</div>
                                <div class="font-bold">{{ $food->fat ?? '-' }}g</div>
                            </div>
                            <div class="bg-zinc-400/10 rounded-lg p-2 text-center">
                                <div class="text-[10px] uppercase opacity-60">ফাইবার</div>
                                <div class="font-bold">{{ $food->fiber ?? '-' }}g</div>
                            </div>
                        </div>

                        @if ($food->nutrients->count())
                            <div class="mb-4">
                                <p class="text-xs font-bold uppercase text-zinc-400 mb-2">খনিজ ও ভিটামিন</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($food->nutrients as $nutrient)
                                        <span
                                            class="px-2 py-1 rounded-md text-[11px] bg-green-400/10 text-green-600 border border-green-500/20">
                                            {{ $nutrient->name_bn }}: {{ $nutrient->pivot->amount }}{{ $nutrient->unit }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($food->description)
                            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                <p class="text-sm text-zinc-500 leading-relaxed">
                                    <strong>বর্ণনা:</strong> {{ $food->description }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-4 text-[10px] text-zinc-400 flex justify-end">
                            Views: {{ number_format($food->view_count) }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🍽️</div>
                <h3 class="text-lg font-semibold text-gray-600">কোনো খাবারের তথ্য পাওয়া যায়নি</h3>
            </div>
        @endforelse
    </div>
</section>