<?php

use Livewire\Volt\Component;
use App\Models\ContactNumber;
use App\Models\ContactCategory;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;

new class extends Component
{
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

    public function updatedThanaId() { $this->applyFilters(); }
    public function updatedSearch() { $this->applyFilters(); }
    public function updatedTypes() { $this->applyFilters(); }

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

<section class="">

    <div class="text-center mb-4">
        <h1 class="font-bold text-3xl">জরুরী {{ $category->name }} ফোন নাম্বার</h1>
        <p class="mt-1 text-lg">সারাদেশের গুরুত্বপূর্ণ জরুরী যোগাযোগ নম্বরসমূহ</p>
    </div>


    <div class="flex gap-4 overflow-auto pb-2 mb-4">
        <div class="max-w-[150px] flex-shrink-0">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="নাম বা ঠিকানা দিয়ে খুঁজুন..." size="sm" />
        </div>

        <div class="max-w-[150px] flex-shrink-0">
            <flux:select wire:model.live.debounce.300ms="division_id" size="sm">
                <option value="">সকল বিভাগ</option>
                @foreach($divisions as $division)
                <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="max-w-[150px] flex-shrink-0">
            <flux:select wire:model.live.debounce.300ms="district_id" size="sm">
                <option value="">সকল জেলা</option>
                @foreach($districts as $district)
                <option value="{{ $district->id }}">{{ $district->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="max-w-[150px] flex-shrink-0">
            <flux:select wire:model.live.debounce.300ms="thana_id" size="sm">
                <option value="">সকল থানা</option>
                @foreach($thanas as $thana)
                <option value="{{ $thana->id }}">{{ $thana->name }}</option>
                @endforeach
            </flux:select>
        </div>

    </div>
    <div class="flex flex-wrap gap-2 items-center mb-4">
        @foreach($contactTypes as $type)
        <flux:checkbox wire:model.live.debounce.300ms="types" value="{{ $type }}" label="{{ $type }}" />
        @endforeach
    </div>

    <div class="grid lg:grid-cols-4 gap-x-4 gap-y-4">
        @forelse($filteredContacts as $contact)
        <div class="border border- bgzinc-700 border-zinc-400/25 rounded-4xl p-4 flex flex-col justify-between h-full">
            <div>
                <h2 class="font-semibold text-lg mb-2">{{ $contact->name }}</h2>

                <div class="mt-2 text-sm space-y-1">
                    @if($contact->division || $contact->district || $contact->thana)
                    <div class="flex items-center">
                        <svg class="h-4 w-4 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        <span>
                            @if($contact->thana) {{ $contact->thana->name }}, @endif
                            @if($contact->district) {{ $contact->district->name }}, @endif
                            @if($contact->division) {{ $contact->division->name }} @endif
                        </span>
                    </div>
                    @endif
                    @if($contact->phone)
                    <div class="flex items-center">
                        <svg class="h-4 w-4 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.742 4.456a1 1 0 01-.54 1.06l-1.548.774a11.09 11.09 0 005.485 5.485l.774-1.548a1 1 0 011.06-.54l4.456.742a1 1 0 01.836.986V17a1 1 0 01-1 1h-4A15.987 15.987 0 012 3z" />
                        </svg>
                        <span class="font-medium">{{ $contact->phone }}</span>
                    </div>
                    @endif
                    @if($contact->type)
                    <div class="flex items-center">
                        <svg class="h-4 w-4 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17.44 14.5a.25.25 0 00-.25-.25h-2.5a.25.25 0 00-.25.25v2.5a.25.25 0 00.25.25h2.5a.25.25 0 00.25-.25v-2.5zm-5 0a.25.25 0 00-.25-.25h-2.5a.25.25 0 00-.25.25v2.5a.25.25 0 00.25.25h2.5a.25.25 0 00.25-.25v-2.5zm-5 0a.25.25 0 00-.25-.25h-2.5a.25.25 0 00-.25.25v2.5a.25.25 0 00.25.25h2.5a.25.25 0 00.25-.25v-2.5zM12 9a3 3 0 11-6 0 3 3 0 016 0zM17.44 4.5a.25.25 0 00-.25-.25h-2.5a.25.25 0 00-.25.25v2.5a.25.25 0 00.25.25h2.5a.25.25 0 00.25-.25v-2.5zm-5 0a.25.25 0 00-.25-.25h-2.5a.25.25 0 00-.25.25v2.5a.25.25 0 00.25.25h2.5a.25.25 0 00.25-.25v-2.5zm-5 0a.25.25 0 00-.25-.25h-2.5a.25.25 0 00-.25.25v2.5a.25.25 0 00.25.25h2.5a.25.25 0 00.25-.25v-2.5z" />
                        </svg>
                        <span>{{ $contact->type }}</span>
                    </div>
                    @endif
                    @if($contact->email)
                    <div class="flex items-center">
                        <svg class="h-4 w-4 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                        <span>{{ $contact->email }}</span>
                    </div>
                    @endif
                </div>

            </div>

            @if($contact->phone)
            <div class="mt-4">
                <a href="tel:{{ $contact->phone }}">
                    {{-- <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.185l-2.616 1.636a10.037 10.037 0 006.015 6.015l1.636-2.616a1 1 0 011.185-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-4.5A15.463 15.463 0 013 5.5V3z" />
                    </svg> --}}
                    <flux:button icon="phone" variant="primary" color="black" size="sm" class="w-full  !rounded-full">
                        কল করুন
                    </flux:button>
                </a>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-full text-center py-6 text-gray-500">কোনো তথ্য পাওয়া যায়নি।</div>
        @endforelse
    </div>
</section>
