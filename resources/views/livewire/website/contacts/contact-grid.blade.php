<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\ContactNumber;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    use WithPagination;

    public int $categoryId;
    public string $categoryName = '';

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public $division_id = '';

    #[Url(history: true)]
    public $district_id = '';

    #[Url(history: true)]
    public $thana_id = '';

    #[Url(history: true)]
    public array $types = [];

    public $districts = [];
    public $thanas = [];
    public int $perPage = 15;

    public function mount(): void
    {
        if ($this->division_id !== '' && $this->division_id !== null) {
            $this->districts = $this->fetchDistricts((int) $this->division_id);
        }
        if ($this->district_id !== '' && $this->district_id !== null) {
            $this->thanas = $this->fetchThanas((int) $this->district_id);
        }
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function updatedSearch(): void
    {
        $this->resetList();
    }

    public function updatedDivisionId($value): void
    {
        $this->districts = $value !== '' && $value !== null ? $this->fetchDistricts((int) $value) : [];
        $this->district_id = '';
        $this->thanas = [];
        $this->thana_id = '';
        $this->resetList();
    }

    public function updatedDistrictId($value): void
    {
        $this->thanas = $value !== '' && $value !== null ? $this->fetchThanas((int) $value) : [];
        $this->thana_id = '';
        $this->resetList();
    }

    public function updatedThanaId(): void
    {
        $this->resetList();
    }

    public function updatedTypes(): void
    {
        $this->resetList();
    }

    private function resetList(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    private function fetchDistricts(int $divisionId)
    {
        return Cache::remember("contact:districts:div:{$divisionId}", now()->addDays(30), fn() => District::where('division_id', $divisionId)->select('id', 'name')->orderBy('name')->get());
    }

    private function fetchThanas(int $districtId)
    {
        return Cache::remember("contact:thanas:dis:{$districtId}", now()->addDays(30), fn() => Thana::where('district_id', $districtId)->select('id', 'name')->orderBy('name')->get());
    }

    #[Computed]
    public function divisions()
    {
        return Cache::remember('contact:divisions:all', now()->addDays(30), fn() => Division::select('id', 'name')->orderBy('name')->get());
    }

    #[Computed]
    public function contactTypes(): array
    {
        return Cache::remember("contact:types:cat:{$this->categoryId}", now()->addDays(7), fn() => ContactNumber::where('contact_category_id', $this->categoryId)->whereNotNull('type')->where('type', '!=', '')->distinct()->orderBy('type')->pluck('type')->toArray());
    }

    #[Computed]
    public function contacts()
    {
        $page = method_exists($this, 'getPage') ? $this->getPage() : 1;

        $search = trim($this->search);
        $divisionId = $this->division_id !== '' && $this->division_id !== null ? (int) $this->division_id : null;
        $districtId = $this->district_id !== '' && $this->district_id !== null ? (int) $this->district_id : null;
        $thanaId = $this->thana_id !== '' && $this->thana_id !== null ? (int) $this->thana_id : null;
        $types = array_values(array_filter($this->types));

        $key = 'contact:list:v3:' . md5(json_encode([$this->categoryId, $search, $divisionId, $districtId, $thanaId, $types, $this->perPage, $page]));

        return Cache::remember($key, now()->addDays(7), function () use ($search, $divisionId, $districtId, $thanaId, $types) {
            return ContactNumber::query()
                ->with(['division:id,name', 'district:id,name', 'thana:id,name'])
                ->where('contact_category_id', $this->categoryId)
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhereHas('division', fn($s) => $s->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('district', fn($s) => $s->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('thana', fn($s) => $s->where('name', 'like', "%{$search}%"));
                    });
                })
                ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
                ->when($districtId, fn($q) => $q->where('district_id', $districtId))
                ->when($thanaId, fn($q) => $q->where('thana_id', $thanaId))
                ->when(!empty($types), fn($q) => $q->whereIn('type', $types))
                ->latest()
                ->paginate($this->perPage);
        });
    }

    public function placeholder()
    {
        return view('partials.contact-skeleton');
    }
}; ?>

@php
    $catName = $categoryName;

    $divisionName =
        $division_id !== '' && $division_id !== null
            ? $this->divisions->firstWhere('id', (int) $division_id)?->name ?? ''
            : '';

    $districtName =
        $district_id !== '' && $district_id !== null
            ? collect($districts)->firstWhere('id', (int) $district_id)?->name ?? ''
            : '';

    $thanaName =
        $thana_id !== '' && $thana_id !== null ? collect($thanas)->firstWhere('id', (int) $thana_id)?->name ?? '' : '';

    if (trim($search) !== '') {
        $h1 = '"' . $search . '" খোঁজার ফলাফল';
        $sub = $catName . ' বিভাগে মিল থাকা নম্বর';
    } elseif ($thanaName !== '') {
        $h1 = $thanaName . ' — ' . $catName . ' নম্বর';
        $sub = collect([$districtName, $divisionName])
            ->filter()
            ->implode(', ');
    } elseif ($districtName !== '') {
        $h1 = $districtName . ' জেলার ' . $catName . ' নম্বর';
        $sub = $divisionName !== '' ? $divisionName . ' বিভাগ' : 'জেলাভিত্তিক তালিকা';
    } elseif ($divisionName !== '') {
        $h1 = $divisionName . ' বিভাগের ' . $catName . ' নম্বর';
        $sub = 'বিভাগভিত্তিক জরুরী যোগাযোগ';
    } else {
        $h1 = 'জরুরী ' . $catName . ' ফোন নাম্বার';
        $sub = 'সারাদেশের গুরুত্বপূর্ণ জরুরী যোগাযোগ নম্বরসমূহ';
    }
@endphp

<div class="space-y-6">

    <header class="text-center space-y-1">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
            {{ $h1 }}
        </h1>
        <p class="text-base text-zinc-500 dark:text-zinc-400">{{ $sub }}</p>
    </header>

    <nav class="bg-white dark:bg-zinc-800 rounded-lg p-4 shadow-sm" aria-label="যোগাযোগ ফিল্টার">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[180px]">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                    placeholder="নাম বা ঠিকানা দিয়ে খুঁজুন..." size="sm" label="খুঁজুন" />
            </div>

            <div class="flex-1 min-w-[150px]">
                <flux:select wire:model.live="division_id" size="sm" variant="listbox"
                    placeholder="বিভাগ নির্বাচন করুন" label="বিভাগ">
                    <flux:select.option value="">সব বিভাগ</flux:select.option>
                    @foreach ($this->divisions as $division)
                        <flux:select.option value="{{ $division->id }}">{{ $division->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex-1 min-w-[150px]">
                <flux:select wire:model.live="district_id" size="sm" variant="listbox"
                    placeholder="জেলা নির্বাচন করুন" label="জেলা" :disabled="!$division_id">
                    <flux:select.option value="">সব জেলা</flux:select.option>
                    @foreach ($districts as $district)
                        <flux:select.option value="{{ $district->id }}">{{ $district->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex-1 min-w-[150px]">
                <flux:select wire:model.live="thana_id" size="sm" variant="listbox" searchable
                    placeholder="থানা নির্বাচন করুন" label="থানা" :disabled="!$district_id">
                    <flux:select.option value="">সব থানা</flux:select.option>
                    @foreach ($thanas as $thana)
                        <flux:select.option value="{{ $thana->id }}">{{ $thana->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        @if (!empty($this->contactTypes))
            <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-zinc-400/25">
                <div class="w-full">
                    <flux:label>ধরণ অনুযায়ী ফিল্টার</flux:label>
                </div>
                @foreach ($this->contactTypes as $type)
                    <flux:checkbox wire:model.live="types" value="{{ $type }}" label="{{ $type }}"
                        size="sm" />
                @endforeach
            </div>
        @endif
    </nav>

    <div class="flex justify-between items-center">
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            মোট {{ number_format($this->contacts->total()) }}টি ফলাফল পাওয়া গেছে
        </p>
    </div>

    <section class="space-y-4">
        @forelse ($this->contacts as $contact)
            @php
                $location = collect([$contact->thana?->name, $contact->district?->name, $contact->division?->name])
                    ->filter()
                    ->implode(', ');
            @endphp

            <article wire:key="contact-{{ $contact->id }}">
                <flux:card class="space-y-4 hover:shadow-lg transition-shadow duration-200">
                    <div class="flex items-center gap-4">
                        <div
                            class="size-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md shrink-0">
                            @if ($categoryName === 'পুলিশ')
                                <flux:icon.shield-check class="size-6 text-white" />
                            @elseif ($categoryName === 'হাসপাতাল')
                                <flux:icon.heart class="size-6 text-white" />
                            @elseif ($categoryName === 'ফায়ার সার্ভিস')
                                <flux:icon.fire class="size-6 text-white" />
                            @else
                                <flux:icon.phone class="size-6 text-white" />
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $contact->name }}
                                </h3>
                                @if ($contact->type)
                                    <flux:badge size="sm" color="blue" variant="subtle">{{ $contact->type }}
                                    </flux:badge>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                @if ($location)
                                    <p class="text-sm text-zinc-500 flex items-center gap-1">
                                        <flux:icon.map-pin variant="mini" class="size-3" />
                                        {{ $location }}
                                    </p>
                                @endif
                                @if ($contact->designation)
                                    <span class="text-xs text-zinc-500">• {{ $contact->designation }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-3">
                        @if ($contact->phone)
                            <div class="flex items-center gap-4">
                                <flux:icon.phone class="size-4 text-green-600 dark:text-green-400" />
                                <span class="font-mono text-sm">{{ $contact->phone }}</span>
                            </div>
                        @endif
                        @if ($contact->alt_phone)
                            <div class="flex items-center gap-4">
                                <flux:icon.device-phone-mobile class="size-4 text-blue-600 dark:text-blue-400" />
                                <span class="font-mono text-sm text-zinc-600">{{ $contact->alt_phone }}</span>
                            </div>
                        @endif
                        @if ($contact->email)
                            <div class="flex items-center gap-4">
                                <flux:icon.envelope class="size-4 text-zinc-500" />
                                <span class="text-sm truncate">{{ $contact->email }}</span>
                            </div>
                        @endif
                        @if ($contact->address)
                            <div class="flex items-start gap-2 col-span-full">
                                <flux:icon.map class="size-4 text-zinc-500 mt-0.5" />
                                <span class="text-sm text-zinc-600 flex-1">{{ $contact->address }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-4 justify-end pt-2 border-t border-zinc-100 dark:border-zinc-700">
                        @if ($contact->phone)
                            <flux:button as="a" href="tel:{{ $contact->phone }}" variant="filled"
                                icon="phone" size="sm" class="flex-1 md:flex-none">
                                কল করুন
                            </flux:button>
                        @endif

                        <flux:button type="button" icon="share" size="sm" class="flex-1 md:flex-none"
                            data-share-button data-title="{{ $contact->name }}{{ $location ? ', ' . $location : '' }}"
                            data-text="{{ $contact->phone }}">
                            শেয়ার করুন
                        </flux:button>

                        @if ($contact->phone)
                            <flux:button type="button" variant="ghost" icon="clipboard" size="sm"
                                class="flex-1 md:flex-none" x-data
                                x-on:click="
                                    navigator.clipboard.writeText(@js($contact->phone));
                                    $el.innerText = 'কপি হয়েছে!';
                                    setTimeout(() => { $el.innerText = 'কপি'; }, 2000);
                                ">
                                কপি
                            </flux:button>
                        @endif
                    </div>
                </flux:card>
            </article>
        @empty
            <div class="py-10">
                <livewire:global.nodata-message :title="$categoryName" :search="$search" />
            </div>
        @endforelse
    </section>

    @if ($this->contacts->hasMorePages())
        <div x-intersect="$wire.loadMore()" class="flex justify-center py-6">
            <flux:icon.loading />
        </div>
    @endif

    @if ($this->contacts->total() > 0)
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center">
            <p class="text-sm text-blue-700 dark:text-blue-300">
                💡 জরুরী প্রয়োজনে সরাসরি কল করতে নম্বরে ক্লিক করুন। শেয়ার বা কপিও করতে পারবেন।
            </p>
        </div>
    @endif
</div>
