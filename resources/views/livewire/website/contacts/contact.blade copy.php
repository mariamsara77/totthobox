<?php

use Livewire\Volt\Component;
use App\Models\ContactNumber;
use App\Models\ContactCategory;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;

new class extends Component {
    public $contacts;
    public $filteredContacts;
    public $category;

    // Filter values
    public $search = '';
    public $division_id = null;
    public $district_id = null;
    public $thana_id = null;
    public $types = [];

    // Dynamic filter options
    public $divisions;
    public $districts = [];
    public $thanas = [];
    public $contactTypes;

    public function mount($slug)
    {
        $this->category = ContactCategory::where('slug', $slug)->firstOrFail();

        $this->contacts = ContactNumber::with(['division', 'district', 'thana'])
            ->where('contact_category_id', $this->category->id)
            ->get();

        $this->filteredContacts = $this->contacts;

        // Dynamic filter options
        $this->divisions = Division::all();
        $this->contactTypes = $this->contacts->pluck('type')->filter()->unique()->toArray();
    }

    // ✅ Division updated
    public function updatedDivisionId()
    {
        $this->districts = $this->division_id
            ? District::where('division_id', $this->division_id)->get()
            : collect();

        $this->district_id = null;
        $this->thanas = collect();
        $this->thana_id = null;

        $this->applyFilters();
    }

    // ✅ District updated
    public function updatedDistrictId()
    {
        $this->thanas = $this->district_id
            ? Thana::where('district_id', $this->district_id)->get()
            : collect();

        $this->thana_id = null;

        $this->applyFilters();
    }

    public function updatedThanaId()
    {
        $this->applyFilters();
    }
    public function updatedSearch()
    {
        $this->applyFilters();
    }
    public function updatedTypes()
    {
        $this->applyFilters();
    }

    protected function applyFilters()
    {
        $this->filteredContacts = $this->contacts->filter(function ($contact) {
            $matchesSearch = empty($this->search) ||
                str_contains(strtolower($contact->name), strtolower($this->search)) ||
                str_contains(strtolower($contact->division->name ?? ''), strtolower($this->search)) ||
                str_contains(strtolower($contact->district->name ?? ''), strtolower($this->search)) ||
                str_contains(strtolower($contact->thana->name ?? ''), strtolower($this->search));

            $matchesDivision = !$this->division_id || $contact->division_id == $this->division_id;
            $matchesDistrict = !$this->district_id || $contact->district_id == $this->district_id;
            $matchesThana = !$this->thana_id || $contact->thana_id == $this->thana_id;
            $matchesType = empty($this->types) || in_array($contact->type, $this->types);

            return $matchesSearch && $matchesDivision && $matchesDistrict && $matchesThana && $matchesType;
        });
    }
};

?>

<section class="max-w-2xl mx-auto space-y-4">
    {{-- Header --}}

    <div class="text-center">
        <flux:heading level="1" size="xl">জরুরী {{ $category->name }} ফোন নাম্বার</flux:heading>
        <flux:subheading level="2">সারাদেশের গুরুত্বপূর্ণ জরুরী যোগাযোগ নম্বরসমূহ</flux:subheading>
    </div>


    <div class="flex gap-4 overflow-auto pb-2 mb-4">
        <div class="max-w-[150px] flex-shrink-0">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="নাম বা ঠিকানা দিয়ে খুঁজুন..." size="sm" />
        </div>

        <div class="max-w-[150px] flex-shrink-0">
            <flux:select wire:model.live.debounce.300ms="division_id" size="sm" variant="listbox"
                placeholder="বিভাগ নির্বাচন করুন">
                @foreach($divisions as $division)
                    <flux:select.option value="{{ $division->id }}">{{ $division->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="max-w-[150px] flex-shrink-0">
            <flux:select wire:model.live.debounce.300ms="district_id" size="sm" variant="listbox"
                placeholder="জেলা নির্বাচন করুন">
                @foreach($districts as $district)
                    <flux:select.option value="{{ $district->id }}">{{ $district->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="max-w-[150px] flex-shrink-0">
            <flux:select wire:model.live.debounce.300ms="thana_id" size="sm" variant="listbox" searchable
                placeholder="থানা নির্বাচন করুন">
                @foreach($thanas as $thana)
                    <flux:select.option value="{{ $thana->id }}">{{ $thana->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

    </div>
    <div class="flex flex-wrap gap-2 items-center mb-4">
        @foreach($contactTypes as $type)
            <flux:checkbox wire:model.live.debounce.300ms="types" value="{{ $type }}" label="{{ $type }}" />
        @endforeach
    </div>


    @forelse($filteredContacts as $contact)
        <flux:card class="space-y-4">

            {{-- Top Section: Profile & Identity --}}
            <div class="flex items-center gap-4">
                {{-- Avatar: অ্যাপ স্টাইল গোল বা রাউন্ডেড আইকন --}}

                <flux:icon.user variant="outline" class="size-10 bg-zinc-400/25 p-2 rounded" />

                <div class="">
                    <div class="flex items-center gap-2">
                        <flux:heading size="lg">
                            {{ $contact->name }}
                        </flux:heading>
                    </div>

                    @if($contact->thana || $contact->district)

                        <flux:subheading>
                            {{ $contact->thana?->name ?? '' }}{{ $contact->thana && $contact->district ? ', ' : '' }}{{ $contact->district?->name ?? '' }}
                        </flux:subheading>

                    @endif
                </div>
            </div>

            {{-- Middle Section: Info List --}}
            <div class="flex items-center gap-4 justify-between">
                <div>
                    @if($contact->phone)
                        <div class="flex items-center gap-3">
                            <flux:icon.phone />
                            <flux:text size="xl">{{ $contact->phone }}</flux:text>
                        </div>
                    @endif

                    @if($contact->thana || $contact->district || $contact->division)
                        <div class="flex items-start gap-3">
                            <flux:icon.map-pin variant="mini" />
                            <flux:text size="xl">
                                {{ $contact->thana?->name ?? '' }}{{ $contact->thana && $contact->district ? ', ' : '' }}{{ $contact->district?->name ?? '' }}{{ $contact->district && $contact->division ? ', ' : '' }}{{ $contact->division?->name ?? '' }}
                            </flux:text>
                        </div>
                    @endif
                </div>
                <flux:button as="a" href="tel:{{ $contact->phone }}" variant="filled" icon="phone" size="sm">
                    কল করুন
                </flux:button>
            </div>
        </flux:card>
    @empty
        <div class="col-span-full py-10">
            <livewire:global.nodata-message :title="$category->name" :search="$search" />
        </div>
    @endforelse
</section>