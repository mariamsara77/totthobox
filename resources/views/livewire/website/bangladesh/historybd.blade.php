<?php

use App\Models\HistoryBd;
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
    public $selectedEra = null;

    public function updated($property)
    {
        if (in_array($property, ['selectedDivision', 'selectedDistrict', 'selectedThana', 'selectedEra', 'search'])) {
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
    public function eras()
    {
        return Cache::remember('history_bd_eras', 3600, function () {
            return HistoryBd::query()->active()->whereNotNull('era')->where('era', '!=', '')->distinct()->orderBy('era')->pluck('era');
        });
    }

    #[Computed]
    public function histories()
    {
        return HistoryBd::query()
            ->with(['division:id,name', 'district:id,name', 'thana:id,name', 'media'])
            ->active()
            ->when(
                $this->search,
                fn($q) => $q->where(function ($query) {
                    $query
                        ->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
                        ->orWhere('era', 'like', "%{$this->search}%");
                }),
            )
            ->when($this->selectedDivision, fn($q) => $q->where('division_id', $this->selectedDivision))
            ->when($this->selectedDistrict, fn($q) => $q->where('district_id', $this->selectedDistrict))
            ->when($this->selectedThana, fn($q) => $q->where('thana_id', $this->selectedThana))
            ->when($this->selectedEra, fn($q) => $q->where('era', $this->selectedEra))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember('history_bd_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', HistoryBd::class)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get();
        });
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'selectedDivision', 'selectedDistrict', 'selectedThana', 'selectedEra']);
        $this->perPage = 10;
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
বাংলাদেশের ঐতিহাসিক স্থান – ALL DATA LIST
SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="বাংলাদেশের ইতিহাস - প্রাচীনকাল থেকে বর্তমান সময়ের পূর্ণাঙ্গ ইতিহাস ও ঐতিহ্য | তথ্যবক্স"
        description="প্রাচীনকাল, মধ্যযুগ ও মুক্তিযুদ্ধের গৌরবময় ইতিহাসসহ বাংলাদেশের ৬৪ জেলার ঐতিহাসিক স্থান, প্রাচীন নিদর্শন ও ঐতিহ্যবাহী জনপদের বিস্তারিত বিবরণ।"
        keywords="বাংলাদেশের ইতিহাস, প্রাচীন বাংলা, মধ্যযুগের ইতিহাস, বাংলাদেশের মুক্তিযুদ্ধ, ঐতিহাসিক স্থান, প্রাচীন নিদর্শন, ঐতিহ্য, তথ্যবক্স"
        og:title="বাংলাদেশের ইতিহাস - প্রাচীনকাল থেকে বর্তমান সময়ের পূর্ণাঙ্গ ইতিহাস ও ঐতিহ্য"
        og:description="প্রাচীনকাল, মধ্যযুগ ও মুক্তিযুদ্ধের গৌরবময় ইতিহাসসহ বাংলাদেশের ৬৪ জেলার ঐতিহাসিক স্থান ও প্রত্নতাত্ত্বিক নিদর্শনের বিবরণ।" />

    {{-- Page Header --}}
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                <flux:icon icon="building-library" class="text-amber-600 dark:text-amber-500" />
                বাংলাদেশের ইতিহাস ও ঐতিহ্য
            </flux:heading>

            <flux:text variant="subtle" class="mt-1">
                প্রাচীনকাল থেকে বর্তমান পর্যন্ত বাংলাদেশের গৌরবময় ঐতিহাসিক প্রেক্ষাপট ও প্রত্নতাত্ত্বিক নিদর্শন
            </flux:text>
        </div>

        <div>
            <flux:tooltip toggleable>
                <flux:button icon="users" size="sm" variant="subtle" aria-label="তথ্য প্রদানকারীগণ দেখুন" />

                <flux:tooltip.content class="w-80 space-y-4 p-4">
                    <div class="space-y-1">
                        <flux:heading size="lg">
                            তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                        </flux:heading>
                        <flux:text size="sm" variant="subtle">
                            এই কন্টেন্ট তৈরিতে যারা অবদান রেখেছেন
                        </flux:text>
                    </div>

                    @forelse ($this->creators as $creator)
                        <flux:card class="space-y-2">
                            <div class="flex items-start gap-4">
                                <flux:avatar src="{{ $creator->avatar_url }}" size="md" badge
                                    badge:color="{{ $creator->isOnline() ? 'green' : 'zinc' }}"
                                    alt="{{ $creator->name }}" />

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <flux:text variant="strong" class="truncate">
                                            {{ $creator->name }}
                                        </flux:text>

                                        @if ($creator->email_verified_at)
                                            <flux:icon.check-badge class="size-4 text-emerald-500" variant="solid" />
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
                                    aria-label="{{ $creator->name }} প্রোফাইল দেখুন" />
                            </div>
                        </flux:card>
                    @empty
                        <flux:text size="sm" variant="subtle" class="py-2 text-center">
                            কোনো কন্ট্রিবিউটর পাওয়া যায়নি।
                        </flux:text>
                    @endforelse

                    <flux:separator />

                    <flux:text size="sm" variant="subtle" class="text-center">
                        আমাদের সকল তথ্য ভেরিফাইড এবং যাচাইকৃত।
                    </flux:text>
                </flux:tooltip.content>
            </flux:tooltip>
        </div>
    </header>

    {{-- Filters --}}
    <div class="space-y-3">
        <nav class="flex items-center gap-4" aria-label="ঐতিহাসিক স্থান সার্চ">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="নামে, যুগে বা বিবরণে খুঁজুন..."
                icon="magnifying-glass" variant="filled" class="rounded-xl flex-1" aria-label="ঐতিহাসিক স্থান খুঁজুন" />
            @if ($search || $selectedDivision || $selectedDistrict || $selectedThana || $selectedEra)
                <flux:button wire:click="resetFilter" variant="ghost" icon="x-mark" size="sm"
                    aria-label="ফিল্টার মুছুন" />
            @endif
        </nav>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
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

            {{-- New Era Filter --}}
            <flux:select wire:model.live="selectedEra" variant="listbox" placeholder="যুগ / Era" class="min-w-36">
                <flux:select.option value="">সকল যুগ</flux:select.option>
                @foreach ($this->eras as $era)
                    <flux:select.option value="{{ $era }}">{{ $era }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    {{-- Results count --}}
    @if ($search || $selectedDivision || $selectedEra)
        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400" role="status" aria-live="polite">
            {{ bn_num($this->histories->total()) }}টি ফলাফল পাওয়া গেছে
        </p>
    @endif

    {{-- History List --}}
    <section class="space-y-4" aria-labelledby="history-list-heading">
        <h2 id="history-list-heading" class="sr-only">বাংলাদেশের ঐতিহাসিক স্থান — তালিকা</h2>

        @forelse ($this->histories as $item)
            @php
                $thumb =
                    $item->getFirstMediaUrl('images', 'thumb') ?:
                    $item->getFirstMediaUrl('images') ?:
                    $item->getFirstMediaUrl('default', 'preview') ?:
                    $item->getFirstMediaUrl('default') ?:
                    $item->image_url ?? null;
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
                                <flux:link variant="ghost" href="{{ route('bangladesh.history.show', $item->slug) }}"
                                    wire:navigate>
                                    {{ $item->title ?? 'অজানা স্থান' }}
                                </flux:link>
                            </flux:heading>

                            @if ($item->is_featured)
                                <flux:badge size="sm" color="amber" variant="solid" class="text-xs">
                                    Featured
                                </flux:badge>
                            @endif

                            <flux:badge size="sm" color="zinc" variant="outline" class="text-xs">
                                ইতিহাস ও ঐতিহ্য
                            </flux:badge>
                        </div>

                        {{-- Era + Year --}}
                        <div class="flex flex-wrap items-center text-xs">
                            @if ($item->era)
                                <span class="inline-flex items-center gap-1">
                                    <flux:icon.clock class="size-3.5" />
                                    {{ $item->era }}
                                </span>
                            @endif

                            @if ($item->start_year || $item->end_year)
                                <span class="inline-flex items-center gap-1">
                                    <flux:icon.calendar class="size-3.5" />
                                    {{ $item->start_year ? bn_num($item->start_year) : '?' }}
                                    –
                                    {{ $item->end_year ? bn_num($item->end_year) : '?' }}
                                </span>
                            @endif
                        </div>
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
                        href="{{ route('bangladesh.history.show', $item->slug) }}" wire:navigate>বিস্তারিত পড়ুন
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <livewire:global.nodata-message :title="'বাংলাদেশের ঐতিহাসিক স্থান'" :search="$search" />
        @endforelse
    </section>

    {{-- Infinite Scroll Trigger --}}
    @if ($this->histories->hasMorePages())
        <div x-intersect="$wire.loadMore()" class="flex justify-center p-6" aria-live="polite"
            aria-label="আরও লোড হচ্ছে">
            <flux:icon.loading aria-hidden="true" />
        </div>
    @endif

    <flux:separator />

    {{-- About Section --}}
    <section aria-labelledby="about-history" class="space-y-4">
        <flux:heading id="about-history" level="2" size="lg" class="flex items-center gap-4">
            <flux:icon icon="information-circle" class="size-5" color="orange" />
            বাংলাদেশের ঐতিহাসিক স্থান সম্পর্কে
        </flux:heading>

        <div class="space-y-3">
            <flux:text>
                এই পেজে বাংলাদেশের সকল জেলার প্রাচীন রাজপ্রাসাদ, জমিদার বাড়ি, প্রত্নতাত্ত্বিক নিদর্শন ও গৌরবময়
                ইতিহাসের তালিকা দেওয়া আছে।
                এখানে মসজিদ, মন্দির, দুর্গ, প্রাসাদ, স্মৃতিস্তম্ভ এবং অন্যান্য ঐতিহাসিক স্থাপনার বিবরণ একত্রিত করা
                হয়েছে।
                প্রতিটি স্থানের ছবি, নাম, যুগ, সময়কাল এবং সংক্ষিপ্ত বিবরণ দেখতে পারবেন।
            </flux:text>

            <flux:text>
                বাংলাদেশের সমৃদ্ধ ইতিহাস ও ঐতিহ্যকে কাছে থেকে জানার জন্য এই তালিকা খুবই উপযোগী।
                “বিস্তারিত পড়ুন” বাটনে ক্লিক করে পুরো ইতিহাস, নির্মাণকাল, গুরুত্ব এবং ভ্রমণ গাইড জানতে পারবেন।
                জেলা বা সময়কাল অনুসারে ফিল্টার করে সহজেই প্রয়োজনীয় স্থান খুঁজে নিতে পারবেন।
            </flux:text>

            <flux:text>
                এই সংকলন নিয়মিত আপডেট করা হয় যাতে নতুন আবিষ্কৃত নিদর্শন ও হালনাগাদ তথ্য যুক্ত থাকে।
                শিক্ষার্থী, গবেষক, পর্যটক এবং ইতিহাসপ্রেমী সবাই এই তথ্য থেকে উপকৃত হতে পারেন।
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
                <flux:accordion.heading>বাংলাদেশের ঐতিহাসিক স্থান কী?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        এটি বাংলাদেশের সকল প্রাচীন নিদর্শন, রাজপ্রাসাদ, জমিদার বাড়ি, প্রত্নতাত্ত্বিক স্থান ও ঐতিহাসিক
                        স্থাপনার একটি সংকলন।
                        উপরের তালিকায় সব জেলার উল্লেখযোগ্য ঐতিহাসিক স্থান দেখানো হয়েছে।
                        এখানে মসজিদ, মন্দির, দুর্গ, প্রাসাদ, স্মৃতিস্তম্ভসহ বিভিন্ন ধরনের নিদর্শনের তথ্য একত্রিত করা
                        হয়েছে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>বিস্তারিত তথ্য কোথায় পাব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        প্রতিটি স্থানের নিচে “বিস্তারিত পড়ুন” বাটনে ক্লিক করলে আলাদা পেজে পুরো ইতিহাস ও ভ্রমণ গাইড দেখা
                        যাবে।
                        সেখানে নির্মাণকাল, ঐতিহাসিক গুরুত্ব, অবস্থান, যোগাযোগের তথ্য এবং ভ্রমণের পরামর্শসহ বিস্তারিত
                        বিবরণ পাওয়া যায়।
                        প্রয়োজনে সার্চ বা ফিল্টার ব্যবহার করে দ্রুত নির্দিষ্ট স্থান খুঁজে নিতে পারেন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>তথ্যগুলো কি নিয়মিত আপডেট হয়?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        হ্যাঁ। নতুন আবিষ্কৃত নিদর্শন যোগ হলে বা পুরনো তথ্যে পরিবর্তন এলে তা নিয়মিত আপডেট করা হয়।
                        তবে কোনো তথ্য ভুল বা পুরনো মনে হলে আমাদের জানাতে পারেন, যাতে দ্রুত সংশোধন করা যায়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>জেলা বা যুগ অনুসারে কীভাবে খুঁজব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        পেজের উপরের সার্চ বক্সে নাম লিখে খুঁজতে পারেন।
                        চাইলে জেলা বা সময়কাল/যুগ (প্রাচীন, মধ্যযুগ, ঔপনিবেশিক ইত্যাদি) সিলেক্ট করে ফিল্টার করুন।
                        ফিল্টার ব্যবহার করলে শুধুমাত্র আপনার প্রয়োজনীয় ঐতিহাসিক স্থানগুলো দেখাবে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </section>

</div>
