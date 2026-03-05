<?php

use Livewire\Volt\Component;
use App\Models\EstablishmentBd;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $selectedDivision = null;
    public $selectedDistrict = null;
    public $selectedThana = null;

    // ফিল্টার ডাটা প্রপার্টিতে রাখা
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
        // ১. এস্টাবলিশমেন্ট কুয়েরি অপ্টিমাইজেশন (Eager Loading সহ)
        $query = EstablishmentBd::query()
            ->with(['division:id,name', 'district:id,name', 'thana:id,name'])
            ->where('status', 1);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedDivision)
            $query->where('division_id', $this->selectedDivision);
        if ($this->selectedDistrict)
            $query->where('district_id', $this->selectedDistrict);
        if ($this->selectedThana)
            $query->where('thana_id', $this->selectedThana);

        // ২. ক্যাশ ব্যবহার করে বিভাগ এবং জেলা/থানার কুয়েরি কমানো
        $divisions = Cache::remember('all_divisions', 3600, fn() => Division::orderBy('name')->get(['id', 'name']));

        $districts = $this->selectedDivision
            ? District::where('division_id', $this->selectedDivision)->orderBy('name')->get(['id', 'name'])
            : [];

        $thanas = $this->selectedDistrict
            ? Thana::where('district_id', $this->selectedDistrict)->orderBy('name')->get(['id', 'name'])
            : [];

        return [
            'establishments' => $query->latest()->paginate(10),
            'divisions' => $divisions,
            'districts' => $districts,
            'thanas' => $thanas,
        ];
    }

    public function resetFilter()
    {
        $this->reset(['selectedDivision', 'selectedDistrict', 'selectedThana', 'search']);
        $this->resetPage();
    }
};
?>

<section class="max-w-2xl mx-auto">

    <flux:heading size="xl" class="text-center">বাংলাদেশের স্থাপনাসমূহ</flux:heading>

    <div class="mt-4 space-y-6">

        {{-- Filters Section --}}
        <div class="mb-10 flex items-center gap-3 overflow-x-auto">


            <flux:input wire:model.live.debounce.300ms="search" placeholder="প্রতিষ্ঠানের নাম খুঁজুন..."
                icon="magnifying-glass" clearable size="sm" class="!rounded-xl min-w-[200px]" />


            <flux:select wire:model.live="selectedDivision" size="sm" class="min-w-[120px]">
                <option value="">বিভাগ</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </flux:select>


            <flux:select wire:model.live="selectedDistrict" size="sm" :disabled="!$selectedDivision"
                class="min-w-[120px]">
                <option value="">জেলা</option>
                @foreach ($districts as $district)
                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                @endforeach
            </flux:select>


            <flux:select wire:model.live="selectedThana" size="sm" :disabled="!$selectedDistrict" class="min-w-[120px]">
                <option value="">থানা</option>
                @foreach ($thanas as $thana)
                    <option value="{{ $thana->id }}">{{ $thana->name }}</option>
                @endforeach
            </flux:select>

            @if($search || $selectedDivision || $selectedDistrict || $selectedThana)
                <flux:button wire:click="resetFilter" size="sm" variant="ghost" icon="x-mark" class="shrink-0">মুছে ফেলুন
                </flux:button>
            @endif

        </div>

        {{-- Results --}}
        <div class="space-y-2">
            @forelse ($establishments as $establishment)
                <div wire:key="establishment-{{ $establishment->id }}">
                    <flux:heading size="lg" level="2" class="text-center">
                        {{ $establishment->title }}
                    </flux:heading>

                    {{-- Grid Gallery --}}
                    @if($establishment->hasMedia('establishment_images'))
                        <flux:media :media="$establishment->getMedia('establishment_images')" />
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
                                            {!! $establishment->description !!}
                                        </div>
                                    </flux:text>
                                </div>
                                @if(mb_strlen($establishment->description) > 160)
                                    <span x-show="!expanded" class="text-xs text-zinc-400">
                                        বিস্তারিত পড়ুন
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <flux:text size="xs" class="uppercase tracking-widest font-bold">
                            {{ $establishment->division?->name }}
                            @if ($establishment->district)
                                • {{ $establishment->district?->name }}
                            @endif
                            @if ($establishment->thana)
                                • {{ $establishment->thana?->name }}
                            @endif
                        </flux:text>
                    </div>

                    @if (!$loop->last)
                        <flux:separator class="mb-5" />
                    @endif
                </div>
            @empty
                <livewire:global.nodata-message :title="'বাংলাদেশের তথ্য'" :search="$search" />
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-12">
            {{ $establishments->links() }}
        </div>

    </div>
</section>