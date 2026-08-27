<?php

use App\Models\TourismBd;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    use WithPagination;

    #[Url(history: true, keep: false)]
    public string $search = '';

    public $perPage = 10;

    public $selectedDivision = null;
    public $selectedDistrict = null;
    public $selectedThana = null;
    public $selectedType = null;

    public function updated($property)
    {
        if (in_array($property, ['selectedDivision', 'selectedDistrict', 'selectedThana', 'search', 'selectedType'])) {
            $this->perPage = 10;
        }

        if ($property === 'selectedDivision') {
            $this->reset(['selectedDistrict', 'selectedThana']);
        }

        if ($property === 'selectedDistrict') {
            $this->reset('selectedThana');
        }
    }

    #[Computed]
    public function divisions()
    {
        return Cache::remember('divisions_list_opt', 86400, fn() => Division::orderBy('name')->get(['id', 'name']));
    }

    #[Computed]
    public function districts()
    {
        if (!$this->selectedDivision) {
            return collect();
        }

        return Cache::remember(
            "districts_div_{$this->selectedDivision}",
            86400,
            fn() => District::where('division_id', $this->selectedDivision)
                ->orderBy('name')
                ->get(['id', 'name', 'division_id']),
        );
    }

    #[Computed]
    public function thanas()
    {
        if (!$this->selectedDistrict) {
            return collect();
        }

        return Cache::remember(
            "thanas_dist_{$this->selectedDistrict}",
            86400,
            fn() => Thana::where('district_id', $this->selectedDistrict)
                ->orderBy('name')
                ->get(['id', 'name', 'district_id']),
        );
    }

    #[Computed]
    public function tourisms()
    {
        return TourismBd::query()
            ->with(['division:id,name', 'district:id,name', 'thana:id,name', 'media'])
            ->where('status', 1)
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%"))
            ->when($this->selectedType, fn($q) => $q->where('tourism_type', $this->selectedType))
            ->when($this->selectedDivision, fn($q) => $q->where('division_id', $this->selectedDivision))
            ->when($this->selectedDistrict, fn($q) => $q->where('district_id', $this->selectedDistrict))
            ->when($this->selectedThana, fn($q) => $q->where('thana_id', $this->selectedThana))
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember('tourism_bd_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', TourismBd::class)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get();
        });
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'selectedDivision', 'selectedDistrict', 'selectedThana', 'selectedType']);
        $this->perPage = 10;
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
বাংলাদেশের পর্যটন কেন্দ্র – ALL DATA LIST
SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

@php
    $typeLabels = [
        'historical' => 'ঐতিহাসিক ও প্রত্নতাত্ত্বিক',
        'heritage' => 'রাজপ্রাসাদ ও জমিদার বাড়ি',
        'natural' => 'প্রাকৃতিক সৌন্দর্য',
        'waterfall' => 'ঝর্ণা ও জলপ্রপাত',
        'beach' => 'সমুদ্র সৈকত',
        'hill_station' => 'পাহাড় ও পার্বত্য এলাকা',
        'forest' => 'বন ও বন্যপ্রাণী',
        'religious' => 'ধর্মীয় ও পবিত্র স্থান',
        'cultural' => 'সাংস্কৃতিক ও জাদুঘর',
        'adventure' => 'অ্যাডভেঞ্চার ও ট্র্যাকিং',
        'resort' => 'রিসোর্ট ও বিনোদন কেন্দ্র',
        'riverine' => 'হাওর ও নদীকেন্দ্রিক',
        'picnic' => 'পিকনিক স্পট',
    ];
@endphp

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="বাংলাদেশের সকল পর্যটন কেন্দ্র ও ভ্রমণ গাইড | তথ্যবক্স"
        description="বাংলাদেশের ৬৪ জেলার সেরা পর্যটন কেন্দ্র, ঐতিহাসিক স্থান ও প্রাকৃতিক সৌন্দর্যের বিস্তারিত ভ্রমণ গাইড।"
        keywords="বাংলাদেশ পর্যটন, ভ্রমণ গাইড, দর্শনীয় স্থান, পর্যটন কেন্দ্র, তথ্যবক্স" />

    {{-- Page Header --}}
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                <flux:icon icon="map" class="text-amber-600 dark:text-amber-500" />
                বাংলাদেশের পর্যটন কেন্দ্র
            </flux:heading>

            <flux:text variant="subtle" class="mt-1">
                সকল জেলার দর্শনীয় স্থান, ভ্রমণ গাইড ও পর্যটন তথ্য
            </flux:text>
        </div>

        <div>
            <flux:tooltip toggleable>
                <flux:button icon="users" size="sm" variant="subtle"
                    aria-label="তথ্য প্রদানকারী ও কন্ট্রিবিউটরদের তালিকা দেখুন" />

                <flux:tooltip.content class="w-80 space-y-4 p-4">
                    <div class="space-y-1">
                        <flux:heading size="lg">
                            তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                        </flux:heading>
                        <flux:text size="sm" variant="subtle">
                            এই পর্যটন কেন্দ্র পেজের কন্টেন্ট তৈরি ও যাচাইকরণে যারা অবদান রেখেছেন
                        </flux:text>
                    </div>

                    <div class="max-h-80 overflow-y-auto space-y-3">
                        @forelse ($this->creators as $creator)
                            <flux:card class="space-y-2">
                                <div class="flex items-start gap-4">
                                    <flux:avatar src="{{ $creator->avatar_url }}" size="md" badge
                                        badge:color="{{ $creator->isOnline() ? 'green' : 'zinc' }}"
                                        alt="{{ $creator->name }} এর প্রোফাইল ছবি" />

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <flux:text variant="strong" class="truncate">
                                                {{ $creator->name }}
                                            </flux:text>

                                            @if ($creator->email_verified_at)
                                                <flux:icon.check-badge class="size-4 text-emerald-500" variant="solid"
                                                    aria-label="ভেরিফাইড অ্যাকাউন্ট" />
                                            @endif
                                        </div>

                                        <flux:text size="sm" variant="subtle" class="truncate">
                                            {{ $creator->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর' }}
                                        </flux:text>
                                    </div>
                                </div>

                                <flux:separator />

                                <div class="flex items-center justify-between">
                                    <flux:text size="sm" variant="subtle">
                                        সর্বশেষ সক্রিয়:
                                        {{ $creator->last_active_at ? bn_num($creator->last_active_at->diffForHumans()) : 'অজানা' }}
                                    </flux:text>

                                    <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost"
                                        size="xs" icon="arrow-right"
                                        aria-label="{{ $creator->name }} এর সম্পূর্ণ প্রোফাইল দেখুন" />
                                </div>
                            </flux:card>
                        @empty
                            <flux:text size="sm" variant="subtle" class="py-2 text-center">
                                এখনো কোনো কন্ট্রিবিউটর পাওয়া যায়নি।
                            </flux:text>
                        @endforelse
                    </div>

                    <flux:separator />

                    <flux:text size="sm" variant="subtle" class="text-center">
                        আমাদের সকল তথ্য ভেরিফাইড, নির্ভরযোগ্য এবং নিয়মিত আপডেটেড।
                    </flux:text>
                </flux:tooltip.content>
            </flux:tooltip>
        </div>
    </header>

    {{-- Filters --}}
    <div class="space-y-3">
        <nav class="flex items-center gap-4" aria-label="পর্যটন সার্চ">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="নামে বা বিবরণে খুঁজুন..."
                icon="magnifying-glass" variant="filled" class="rounded-xl flex-1" aria-label="পর্যটন খুঁজুন" />
            @if ($search || $selectedDivision || $selectedDistrict || $selectedThana || $selectedType)
                <flux:button wire:click="resetFilter" variant="ghost" icon="x-mark" size="sm"
                    aria-label="ফিল্টার মুছুন" />
            @endif
        </nav>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <flux:select wire:model.live="selectedType" variant="listbox" placeholder="সব ধরন" class="min-w-40">
                <flux:select.option value="">সকল ধরন</flux:select.option>
                @foreach ($typeLabels as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="selectedDivision" variant="listbox" placeholder="বিভাগ" class="min-w-32">
                <flux:select.option value="">সকল বিভাগ</flux:select.option>
                @foreach ($this->divisions as $div)
                    <flux:select.option value="{{ $div->id }}">{{ $div->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="selectedDistrict" variant="listbox" placeholder="জেলা" class="min-w-32"
                :disabled="!$selectedDivision">
                <flux:select.option value="">সকল জেলা</flux:select.option>
                @foreach ($this->districts as $dis)
                    <flux:select.option value="{{ $dis->id }}">{{ $dis->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="selectedThana" variant="listbox" placeholder="থানা" class="min-w-32"
                :disabled="!$selectedDistrict">
                <flux:select.option value="">সকল থানা</flux:select.option>
                @foreach ($this->thanas as $thana)
                    <flux:select.option value="{{ $thana->id }}">{{ $thana->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    {{-- Results count --}}
    @if ($search || $selectedType || $selectedDivision)
        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400" role="status" aria-live="polite">
            {{ bn_num($this->tourisms->total()) }}টি ফলাফল পাওয়া গেছে
        </p>
    @endif

    {{-- Tourism List --}}
    <section class="space-y-4" aria-labelledby="tourism-list-heading">
        <h2 id="tourism-list-heading" class="sr-only">বাংলাদেশের পর্যটন কেন্দ্র — তালিকা</h2>

        @forelse ($this->tourisms as $item)
            @php
                $thumb =
                    $item->getFirstMediaUrl('tourism_images', 'thumb') ?:
                    $item->getFirstMediaUrl('tourism_images') ?:
                    $item->getFirstMediaUrl('default', 'preview') ?:
                    $item->getFirstMediaUrl('default');
            @endphp

            <flux:card>
                <div class="flex gap-4 items-start">
                    {{-- Image --}}
                    <div class="shrink-0">
                        <flux:avatar src="{{ $thumb }}" size="xl"
                            name="{{ $item->title ?? 'অজানা স্থান' }}" class="rounded-xl" />
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 space-y-2">
                        <div class="flex flex-wrap items-center gap-4">
                            <flux:heading level="2" size="lg">
                                <flux:link href="{{ route('bangladesh.tourism.show', $item->slug) }}" wire:navigate>
                                    {{ $item->title ?? 'অজানা স্থান' }}
                                </flux:link>
                            </flux:heading>

                            @if ($item->tourism_type && isset($typeLabels[$item->tourism_type]))
                                <flux:badge size="sm" color="zinc" variant="outline">
                                    {{ $typeLabels[$item->tourism_type] }}
                                </flux:badge>
                            @endif
                        </div>

                        <flux:text class="text-xs flex items-center gap-1">
                            <flux:icon.map-pin class="size-3" />
                            {{ $item->thana?->name ?? '...' }} • {{ $item->district?->name ?? '...' }}
                        </flux:text>

                        @if ($item->description)
                            <flux:text class="line-clamp-2 overflow-hidden text-base">
                                {{ strip_tags($item->description) }}
                            </flux:text>
                        @endif
                    </div>
                </div>

                {{-- Footer Action --}}
                <flux:separator class="opacity-50 my-2" />

                {{-- Footer Action --}}
                <div>
                    <flux:button icon="arrow-right" variant="subtle" size="xs"
                        href="{{ route('bangladesh.tourism.show', $item->slug) }}" wire:navigate>বিস্তারিত পড়ুন
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <livewire:global.nodata-message :title="'বাংলাদেশের পর্যটন কেন্দ্র'" :search="$search" />
        @endforelse
    </section>

    {{-- Infinite Scroll Trigger --}}
    @if ($this->tourisms->hasMorePages())
        <div x-intersect="$wire.loadMore()" class="flex justify-center p-6" aria-live="polite"
            aria-label="আরও লোড হচ্ছে">
            <flux:icon.loading aria-hidden="true" />
        </div>
    @endif

    <flux:separator />

    {{-- About Section --}}
    <section aria-labelledby="about-tourism" class="space-y-4">
        <flux:heading id="about-tourism" level="2" size="lg" class="flex items-center gap-4">
            <flux:icon icon="information-circle" color="orange" />
            বাংলাদেশের পর্যটন কেন্দ্র সম্পর্কে
        </flux:heading>

        <div class="space-y-3">
            <flux:text>
                এই পেজে বাংলাদেশের সকল জেলার দর্শনীয় স্থান, ঐতিহাসিক নিদর্শন ও প্রাকৃতিক সৌন্দর্যের তালিকা দেওয়া আছে।
                এখানে সমুদ্র সৈকত, পাহাড়, বন, নদী, হ্রদ, ঐতিহাসিক স্থান এবং অন্যান্য আকর্ষণীয় পর্যটন কেন্দ্রের বিবরণ
                একত্রিত করা হয়েছে।
                প্রতিটি স্থানের ছবি, নাম, ধরন এবং সংক্ষিপ্ত বিবরণ দেখতে পারবেন।
            </flux:text>

            <flux:text>
                বাংলাদেশের প্রাকৃতিক ও ঐতিহাসিক সৌন্দর্যকে কাছে থেকে জানার জন্য এই তালিকা খুবই উপযোগী।
                “বিস্তারিত পড়ুন” বাটনে ক্লিক করে পুরো ভ্রমণ গাইড, যাওয়ার উপায়, সেরা সময় এবং প্রয়োজনীয় তথ্য জানতে
                পারবেন।
                জেলা বা ধরন অনুসারে ফিল্টার করে সহজেই প্রয়োজনীয় স্থান খুঁজে নিতে পারবেন।
            </flux:text>

            <flux:text>
                এই সংকলন নিয়মিত আপডেট করা হয় যাতে নতুন পর্যটন কেন্দ্র ও হালনাগাদ তথ্য যুক্ত থাকে।
                পর্যটক, শিক্ষার্থী, গবেষক এবং ভ্রমণপিপাসু সবাই এই তথ্য থেকে উপকৃত হতে পারেন।
                সঠিক ও নির্ভরযোগ্য তথ্য পাওয়ার জন্য এটি একটি সহজ ও কার্যকর উৎস।
            </flux:text>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section aria-labelledby="faq-heading" class="space-y-4">
        <flux:heading id="faq-heading" level="2" size="lg">
            প্রায়শই জিজ্ঞাসিত প্রশ্ন
        </flux:heading>

        <flux:accordion transition exclusive>
            <flux:accordion.item>
                <flux:accordion.heading>বাংলাদেশের পর্যটন কেন্দ্র কী?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        এটি বাংলাদেশের সকল দর্শনীয় স্থান, ঐতিহাসিক নিদর্শন ও প্রাকৃতিক সৌন্দর্যের একটি সংকলন।
                        উপরের তালিকায় সব জেলার উল্লেখযোগ্য পর্যটন কেন্দ্র দেখানো হয়েছে।
                        এখানে সমুদ্র সৈকত, পাহাড়, বন, নদী, হ্রদ, ঐতিহাসিক স্থানসহ বিভিন্ন ধরনের আকর্ষণীয় স্থানের তথ্য
                        একত্রিত করা হয়েছে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>বিস্তারিত তথ্য কোথায় পাব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        প্রতিটি স্থানের নিচে “বিস্তারিত পড়ুন” বাটনে ক্লিক করলে আলাদা পেজে পুরো ভ্রমণ গাইড দেখা যাবে।
                        সেখানে অবস্থান, যাওয়ার উপায়, সেরা ভ্রমণকাল, থাকার ব্যবস্থা এবং প্রয়োজনীয় পরামর্শসহ বিস্তারিত
                        তথ্য পাওয়া যায়।
                        প্রয়োজনে সার্চ বা ফিল্টার ব্যবহার করে দ্রুত নির্দিষ্ট স্থান খুঁজে নিতে পারেন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>তথ্যগুলো কি নিয়মিত আপডেট হয়?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        হ্যাঁ। নতুন পর্যটন কেন্দ্র যোগ হলে বা পুরনো তথ্যে পরিবর্তন এলে তা নিয়মিত আপডেট করা হয়।
                        তবে কোনো তথ্য ভুল বা পুরনো মনে হলে আমাদের জানাতে পারেন, যাতে দ্রুত সংশোধন করা যায়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>জেলা বা ধরন অনুসারে কীভাবে খুঁজব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        পেজের উপরের সার্চ বক্সে নাম লিখে খুঁজতে পারেন।
                        চাইলে জেলা বা ধরন (প্রাকৃতিক, ঐতিহাসিক, সমুদ্র সৈকত, পাহাড় ইত্যাদি) সিলেক্ট করে ফিল্টার করুন।
                        ফিল্টার ব্যবহার করলে শুধুমাত্র আপনার প্রয়োজনীয় পর্যটন কেন্দ্রগুলো দেখাবে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </section>

</div>
