<?php

use Livewire\Volt\Component;
use App\Models\{HistoryBd, Division, District, Thana};
use Livewire\{WithPagination, Attributes\Computed};
use Illuminate\Support\Facades\Cache;

new class extends Component {
    use WithPagination;

    public $search = '', $selectedDivision = null, $selectedDistrict = null, $selectedThana = null;

    public function updated($property)
    {
        if (in_array($property, ['selectedDivision', 'selectedDistrict', 'selectedThana', 'search'])) {
            $this->resetPage();
        }

        if ($property === 'selectedDivision') {
            $this->reset(['selectedDistrict', 'selectedThana']);
        }
        if ($property === 'selectedDistrict') {
            $this->reset('selectedThana');
        }
    }

    #[Computed]
    public function histories()
    {
        return HistoryBd::query()
            ->with(['division:id,name', 'district:id,name', 'thana:id,name', 'media'])
            ->active()
            ->published()
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->selectedDivision, fn($q) => $q->where('division_id', $this->selectedDivision))
            ->when($this->selectedDistrict, fn($q) => $q->where('district_id', $this->selectedDistrict))
            ->when($this->selectedThana, fn($q) => $q->where('thana_id', $this->selectedThana))
            ->latest('published_at')
            ->paginate(12);
    }
}; ?>

<section class="max-w-2xl mx-auto space-y-4">
    <header class="mb-8">
        <flux:heading size="xl">ঐতিহাসিক স্থানসমূহ</flux:heading>
        <flux:subheading>বাংলাদেশের সমৃদ্ধ ইতিহাসের বিভিন্ন নিদর্শনের তালিকা।</flux:subheading>
    </header>

    {{-- Filters (Tourism এর মতোই) --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="নামে খুঁজুন..." icon="magnifying-glass" />
        {{-- এখানে ডুপ্লিকেট ড্রপডাউনগুলো আগের মতো বসিয়ে নিন --}}
    </div>

    <div class="space-y-4">
        @forelse ($this->histories as $item)
            <a href="{{ route('bangladesh.history.show', $item->slug) }}">
                <flux:card class="flex gap-4 items-center">
                    {{-- ইমেজ ডিসপ্লে --}}
                    <img src="{{ $item->getFirstMediaUrl('images', 'thumb') ?: $item->image_url }}" class="size-16 rounded-lg object-cover" />
                    <div>
                        <flux:heading size="lg">{{ $item->title }}</flux:heading>
                        <p class="text-sm text-zinc-500">{{ $item->short_description }}</p>
                    </div>
                </flux:card>
            </a>
        @empty
            <livewire:global.nodata-message :title="'ঐতিহাসিক স্থানসমূহ'" :search="$search" />
        @endforelse
    </div>

    <div class="pt-4">{{ $this->histories->links() }}</div>
</section>