<?php

use Livewire\Volt\Component;
use App\Models\Nutrient;
use App\Models\Food;

new class extends Component {
    public $nutrients;
    public $selectedNutrient = null;
    public $search = '';
    public $selectedDetails = null;

    public function mount()
    {
        $this->nutrients = Nutrient::orderBy('name_bn')->get();
    }

    public function updatedSearch()
    {
        $this->nutrients = Nutrient::when($this->search, fn($q) => $q->where('name_bn', 'like', "%{$this->search}%")->orWhere('name_en', 'like', "%{$this->search}%"))->orderBy('name_bn')->get();
    }

    public function filterByNutrient($id)
    {
        $this->selectedNutrient = $id;
        $this->updatedSearch();
    }

    public function showNutrient($id)
    {
        $this->selectedDetails = Nutrient::with([
            'foods' => function ($q) {
                $q->withPivot('amount');
            },
        ])->find($id);
    }

    public function closeModal()
    {
        $this->selectedDetails = null;
    }
};
?>

<section class="max-w-7xl mx-auto">
    {{-- Search + Filter --}}
    <div class="mb-8">
        <div class="mb-6 flex justify-center">
            <div class="relative w-full max-w-md">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="পুষ্টির নাম দিয়ে খুঁজুন..."
                    icon="magnifying-glass" />
            </div>
        </div>

        {{-- Nutrient Filter Buttons --}}
        <div class="flex flex-wrap justify-center gap-3">
            <flux:button wire:click="filterByNutrient(null)"
                variant="{{ $selectedNutrient === null ? 'filled' : 'subtle' }}"
                class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 hover:scale-105">
                সব
            </flux:button>

            @foreach ($nutrients as $nutrient)
                <flux:button wire:click="filterByNutrient({{ $nutrient->id }})"
                    variant="{{ $selectedNutrient === $nutrient->id ? 'filled' : 'subtle' }}"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 hover:scale-105">
                    {{ $nutrient->name_bn }}
                </flux:button>
            @endforeach
        </div>
    </div>

    {{-- Nutrient Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-6">
        @forelse($nutrients as $nutrient)
            <div class="rounded-2xl border border-zinc-400/25 overflow-hidden group">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-2 leading-tight">
                        {{ $nutrient->name_bn }}
                        @if ($nutrient->name_en)
                            <span class="text-sm text-gray-400 font-normal block sm:inline">
                                ({{ $nutrient->name_en }})
                            </span>
                        @endif
                    </h3>

                    <flux:separator class="my-2" />

                    <div class="flex justify-between text-sm mb-3">
                        <div>একক:</div>
                        <div>{{ $nutrient->unit ?? '-' }}</div>
                    </div>

                    {{-- Foods Containing This Nutrient --}}
                    @if ($nutrient->foods->count())
                        <p class="text-sm font-semibold mb-2">যেসব খাবারে আছে:</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($nutrient->foods->take(6) as $food)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-blue-400/10 text-blue-600">
                                    {{ $food->name_bn }} ({{ $food->pivot->amount }}{{ $nutrient->unit }})
                                </span>
                            @endforeach
                            @if ($nutrient->foods->count() > 6)
                                <span class="text-xs text-gray-500">+{{ $nutrient->foods->count() - 6 }} আরও</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <div class="max-w-md mx-auto">
                    <div class="text-6xl mb-4">🧬</div>
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">কোনো পুষ্টি উপাদান পাওয়া যায়নি</h3>
                    <p class="text-gray-500 text-sm">অনুগ্রহ করে ভিন্ন নাম দিয়ে আবার চেষ্টা করুন</p>
                </div>
            </div>
        @endforelse
    </div>
</section>
