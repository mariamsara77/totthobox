<?php

use Livewire\Volt\Component;
use App\Models\HistoryBd;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $selectedDivision = null;
    public $selectedDistrict = null;
    public $selectedThana = null;

    public function updated($property)
    {
        if (in_array($property, ['selectedDivision', 'selectedDistrict', 'selectedThana', 'search'])) {
            $this->resetPage();
        }
        if ($property === 'selectedDivision') {
            $this->selectedDistrict = null;
            $this->selectedThana = null;
        }
        if ($property === 'selectedDistrict') {
            $this->selectedThana = null;
        }
    }

    public function with(): array
    {
        $query = HistoryBd::query()
            ->with(['division', 'district', 'thana'])
            ->where('status', 1);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedDivision) {
            $query->where('division_id', $this->selectedDivision);
        }
        if ($this->selectedDistrict) {
            $query->where('district_id', $this->selectedDistrict);
        }
        if ($this->selectedThana) {
            $query->where('thana_id', $this->selectedThana);
        }

        return [
            'histories' => $query->latest()->paginate(10),
            'divisions' => Division::orderBy('name')->get(),
            'districts' => $this->selectedDivision ? District::where('division_id', $this->selectedDivision)->orderBy('name')->get() : [],
            'thanas' => $this->selectedDistrict ? Thana::where('district_id', $this->selectedDistrict)->orderBy('name')->get() : [],
        ];
    }

    public function resetFilter()
    {
        $this->selectedDivision = null;
        $this->selectedDistrict = null;
        $this->selectedThana = null;
        $this->search = '';
    }
};
?>

<section class="max-w-2xl mx-auto">

    <flux:heading size="xl" class="text-center">বাংলাদেশের ইতিহাসের সংক্ষিপ্ত পরিচয়</flux:heading>

    <div class="mt-4 space-y-6">

        {{-- Filters --}}
        <div class="mb-10 flex items-center gap-3 overflow-x-auto">


            <flux:input wire:model.live.debounce.300ms="search" placeholder="ইতিহাসের পাতা খুঁজুন..."
                icon="magnifying-glass" clearable size="sm" class="!rounded-xl min-w-[200px]" />


            <flux:select variant="listbox" placeholder="বিভাগ" wire:model.live="selectedDivision" size="sm"
                class="min-w-[120px]">
                @foreach ($divisions as $division)
                    <flux:select.option value="{{ $division->id }}">{{ $division->name }}</flux:select.option>
                @endforeach
            </flux:select>


            <flux:select variant="listbox" placeholder="জেলা" wire:model.live="selectedDistrict" size="sm"
                :disabled="!$selectedDivision" class="min-w-[120px]">
                @foreach ($districts as $district)
                    <flux:select.option value="{{ $district->id }}">{{ $district->name }}</flux:select.option>
                @endforeach
            </flux:select>


            <flux:select variant="listbox" placeholder="থানা" wire:model.live="selectedThana" size="sm"
                :disabled="!$selectedDistrict" class="min-w-[120px]">
                @foreach ($thanas as $thana)
                    <flux:select.option value="{{ $thana->id }}">{{ $thana->name }}</flux:select.option>
                @endforeach
            </flux:select>

            @if($search || $selectedDivision || $selectedDistrict || $selectedThana)
                <flux:button wire:click="resetFilter" size="sm" variant="ghost" icon="x-mark" class="shrink-0">মুছে ফেলুন
                </flux:button>
            @endif

        </div>

        {{-- Results --}}
        <div class="space-y-2">
            @forelse ($histories as $history)
                <flux:heading size="xl" level="2" class="text-center">
                    {{ $history->title }}
                </flux:heading>

                {{-- Grid Gallery --}}
                @if($history->hasMedia('images'))
                    <flux:media :media="$history->getMedia('images')" />
                @endif

                <flux:text class="leading-relaxed text-base">
                    {{ $history->description }}
                </flux:text>
                <div class="mt-4">
                    <flux:text size="xs" class="uppercase tracking-widest font-bold">
                        {{ $history->division?->name }}
                        @if ($history->district)
                            • {{ $history->district?->name }}
                        @endif
                    </flux:text>
                </div>

                @if (!$loop->last)
                    <flux:separator class="mb-5" />
                @endif

            @empty
                <livewire:global.nodata-message :title="'বাংলাদেশের তথ্য'" :search="$search" />
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-12">
            {{ $histories->links() }}
        </div>

    </div>
</section>