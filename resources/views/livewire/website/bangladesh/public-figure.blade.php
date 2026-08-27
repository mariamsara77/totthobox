<?php

use App\Models\Person;
use App\Models\PeopleCategory;
use App\Models\Position;
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

    public $categoryFilter = '';
    public $positionFilter = '';
    public $statusFilter = 'all'; // all, current, former
    public $fromDate = '';
    public $toDate = '';

    public function updated($property)
    {
        if (in_array($property, ['search', 'categoryFilter', 'positionFilter', 'statusFilter', 'fromDate', 'toDate'])) {
            $this->perPage = 10;
        }
    }

    #[Computed]
    public function categories()
    {
        return Cache::remember('people_categories_list', 86400, fn() => PeopleCategory::orderBy('name')->get(['id', 'name']));
    }

    #[Computed]
    public function positions()
    {
        return Cache::remember('positions_list', 86400, fn() => Position::orderBy('title')->get(['id', 'title']));
    }

    #[Computed]
    public function people()
    {
        return Person::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, function ($q) {
                $q->whereHas('peopleCategories', fn($sq) => $sq->where('people_categories.id', $this->categoryFilter));
            })
            ->whereHas('histories', function ($q) {
                $q->when($this->positionFilter, fn($sq) => $sq->where('position_id', $this->positionFilter))
                    ->when($this->statusFilter === 'current', fn($sq) => $sq->where('is_current', true))
                    ->when($this->statusFilter === 'former', fn($sq) => $sq->where('is_current', false))
                    ->when($this->fromDate, fn($sq) => $sq->whereDate('from_date', '>=', $this->fromDate))
                    ->when($this->toDate, fn($sq) => $sq->whereDate('from_date', '<=', $this->toDate));
            })
            ->with(['peopleCategories', 'histories.position', 'media'])
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember('person_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', Person::class)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get();
        });
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'categoryFilter', 'positionFilter', 'statusFilter', 'fromDate', 'toDate']);
        $this->perPage = 10;
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
প্রোফাইল আর্কাইভ – ALL DATA LIST
SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="প্রোফাইল আর্কাইভ: বিশিষ্ট ব্যক্তিবর্গের জীবনী ও কর্মজীবন | তথ্যবক্স"
        description="বাংলাদেশের গুরুত্বপূর্ণ ব্যক্তিবর্গ, রাজনীতিবিদ এবং পেশাজীবীদের জীবনবৃত্তান্ত, বর্তমান পদবী এবং কর্মজীবনের বিস্তারিত ইতিহাস দেখুন তথ্যবক্স আর্কাইভে।"
        keywords="ব্যক্তিত্ব আর্কাইভ, জীবনী, বাংলাদেশের বিখ্যাত ব্যক্তি, প্রোফাইল লিস্ট, কর্মজীবন ইতিহাস, তথ্যবক্স" />

    {{-- Page Header --}}
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                <flux:icon icon="user-group" class="text-amber-600 dark:text-amber-500" />
                প্রোফাইল আর্কাইভ
            </flux:heading>

            <flux:text variant="subtle" class="mt-1">
                বিশিষ্ট ব্যক্তিবর্গের জীবনী, কর্মজীবন ও অবদানের সম্পূর্ণ ইতিহাস
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
                            এই প্রোফাইল আর্কাইভের কন্টেন্ট তৈরি ও যাচাইকরণে যারা অবদান রেখেছেন
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
        <nav class="flex items-center gap-4" aria-label="প্রোফাইল সার্চ">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="নামে খুঁজুন..." icon="magnifying-glass"
                variant="filled" class="rounded-xl flex-1" aria-label="ব্যক্তি খুঁজুন" />
            @if ($search || $categoryFilter || $positionFilter || $statusFilter !== 'all' || $fromDate || $toDate)
                <flux:button wire:click="resetFilter" variant="ghost" icon="x-mark" size="sm"
                    aria-label="ফিল্টার মুছুন" />
            @endif
        </nav>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <flux:select wire:model.live="categoryFilter" variant="listbox" placeholder="ক্যাটাগরি" class="min-w-36">
                <flux:select.option value="">সকল ক্যাটাগরি</flux:select.option>
                @foreach ($this->categories as $cat)
                    <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="positionFilter" variant="listbox" placeholder="পদবী" class="min-w-36">
                <flux:select.option value="">সকল পদবী</flux:select.option>
                @foreach ($this->positions as $pos)
                    <flux:select.option value="{{ $pos->id }}">{{ $pos->title }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="statusFilter" variant="listbox" class="min-w-36">
                <flux:select.option value="all">অবস্থা (সকল)</flux:select.option>
                <flux:select.option value="current">বর্তমানে কর্মরত</flux:select.option>
                <flux:select.option value="former">সাবেক</flux:select.option>
            </flux:select>

            <flux:input type="date" wire:model.live="fromDate" class="min-w-36" aria-label="শুরুর তারিখ" />
            <flux:input type="date" wire:model.live="toDate" class="min-w-36" aria-label="শেষ তারিখ" />
        </div>
    </div>

    {{-- Results count --}}
    @if ($search || $categoryFilter || $positionFilter || $statusFilter !== 'all' || $fromDate || $toDate)
        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400" role="status" aria-live="polite">
            {{ bn_num($this->people->total()) }}টি ফলাফল পাওয়া গেছে
        </p>
    @endif

    {{-- People List --}}
    <section class="space-y-4" aria-labelledby="people-list-heading">
        <h2 id="people-list-heading" class="sr-only">প্রোফাইল আর্কাইভ — তালিকা</h2>

        @forelse ($this->people as $person)
            @php
                $thumb = $person->getFirstMediaUrl('images', 'thumb') ?: $person->getFirstMediaUrl('images');
                $activeRole = $person->histories->where('is_current', true)->first();
            @endphp

            <flux:card>
                <div class="flex gap-4 items-start">
                    {{-- Image --}}
                    <div class="shrink-0">
                        <flux:avatar src="{{ $thumb }}" size="xl" name="{{ $person->name ?? 'অজানা' }}"
                            class="rounded-xl" />
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 space-y-2">
                        <div class="flex flex-wrap items-center gap-4">
                            <flux:heading level="2" size="lg">
                                <flux:link variant="ghost"
                                    href="{{ route('bangladesh.public-figure.show', $person->slug) }}" wire:navigate>
                                    {{ $person->name ?? 'অজানা' }}
                                </flux:link>
                            </flux:heading>

                            @if ($activeRole)
                                <flux:badge size="sm" color="green" variant="subtle" class="text-xs">
                                    বর্তমান
                                </flux:badge>
                            @endif
                        </div>

                        {{-- Categories --}}
                        @if ($person->peopleCategories->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach ($person->peopleCategories as $cat)
                                    <flux:badge size="xs" color="zinc" variant="outline">{{ $cat->name }}
                                    </flux:badge>
                                @endforeach
                            </div>
                        @endif

                        {{-- Current / Latest Role --}}
                        <div class="text-xs text-zinc-500 flex items-center gap-1">
                            <flux:icon.briefcase class="size-3.5" />
                            @if ($activeRole)
                                {{ $activeRole->position?->title ?? ($activeRole->custom_role ?? 'পদবী অজানা') }}
                                @if ($activeRole->from_date)
                                    <span class="text-zinc-400">({{ $activeRole->from_date->format('Y') }})</span>
                                @endif
                            @else
                                সাবেক / কর্মজীবনের ইতিহাস নেই
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer Action --}}
                <flux:separator class="opacity-50 my-2" />

                {{-- Footer Action --}}
                <div>
                    <flux:button icon="arrow-right" variant="subtle" size="xs"
                        href="{{ route('bangladesh.public-figure.show', $person->slug) }}"
                        class="inline-flex items-center gap-2 text-xs font-semibold text-amber-600 dark:text-amber-400  dark:hover:text-amber-300"
                        wire:navigate>বিস্তারিত পড়ুন
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <livewire:global.nodata-message :title="'প্রোফাইল আর্কাইভ'" :search="$search" />
        @endforelse
    </section>

    {{-- Infinite Scroll Trigger --}}
    @if ($this->people->hasMorePages())
        <div x-intersect="$wire.loadMore()" class="flex justify-center p-6" aria-live="polite"
            aria-label="আরও লোড হচ্ছে">
            <flux:icon.loading aria-hidden="true" />
        </div>
    @endif

    <flux:separator />

    {{-- About Section --}}
    <section aria-labelledby="about-people" class="space-y-4">
        <flux:heading id="about-people" level="2" size="lg" class="flex items-center gap-4">
            <flux:icon icon="information-circle" color="orange" />
            প্রোফাইল আর্কাইভ সম্পর্কে
        </flux:heading>

        <div class="space-y-3">
            <flux:text>
                এই পেজে বাংলাদেশের গুরুত্বপূর্ণ ব্যক্তিবর্গ, রাজনীতিবিদ, পেশাজীবী এবং বিশিষ্ট ব্যক্তিদের জীবনবৃত্তান্ত ও
                কর্মজীবনের ইতিহাস সংরক্ষিত আছে।
                এখানে নেতৃবৃন্দ, শিক্ষাবিদ, বিজ্ঞানী, শিল্পী, সাহিত্যিক, ব্যবসায়ী এবং অন্যান্য প্রভাবশালী ব্যক্তিত্বের
                প্রোফাইল একত্রিত করা হয়েছে।
                প্রতিটি প্রোফাইলে নাম, পদবী, সংক্ষিপ্ত পরিচিতি এবং কর্মজীবনের মূল তথ্য দেখতে পারবেন।
            </flux:text>

            <flux:text>
                বাংলাদেশের বিশিষ্ট ব্যক্তিদের সম্পর্কে নির্ভরযোগ্য তথ্য জানার জন্য এই আর্কাইভ খুবই উপযোগী।
                “বিস্তারিত পড়ুন” বাটনে ক্লিক করে পুরো প্রোফাইল, জীবনী, অর্জন এবং কর্মজীবনের বিস্তারিত তথ্য জানতে
                পারবেন।
                পেশা বা ক্যাটাগরি অনুসারে ফিল্টার করে সহজেই প্রয়োজনীয় প্রোফাইল খুঁজে নিতে পারবেন।
            </flux:text>

            <flux:text>
                এই সংকলন নিয়মিত আপডেট করা হয় যাতে নতুন ব্যক্তিত্ব ও হালনাগাদ তথ্য যুক্ত থাকে।
                শিক্ষার্থী, গবেষক, সাংবাদিক এবং সাধারণ মানুষ সবাই এই তথ্য থেকে উপকৃত হতে পারেন।
                সঠিক ও নির্ভরযোগ্য তথ্য পাওয়ার জন্য এটি একটি সহজ ও কার্যকর উৎস।
            </flux:text>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section aria-labelledby="faq-heading" class="space-y-4">
        <flux:heading id="faq-heading" level="2" size="lg">
            প্রায়শাই জিজ্ঞাসিত প্রশ্ন
        </flux:heading>

        <flux:accordion transition exclusive>
            <flux:accordion.item>
                <flux:accordion.heading>প্রোফাইল আর্কাইভ কী?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        এটি বাংলাদেশের বিশিষ্ট ব্যক্তিবর্গের জীবনী, পদবী এবং কর্মজীবনের ইতিহাসের একটি সংকলন।
                        উপরের তালিকায় সকল প্রোফাইল দেখানো হয়েছে।
                        এখানে রাজনীতিবিদ, পেশাজীবী, শিক্ষাবিদ, শিল্পী, সাহিত্যিকসহ বিভিন্ন ক্ষেত্রের গুরুত্বপূর্ণ
                        ব্যক্তিত্বের তথ্য একত্রিত করা হয়েছে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>বিস্তারিত তথ্য কোথায় পাব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        প্রতিটি প্রোফাইলের নিচে “বিস্তারিত পড়ুন” বাটনে ক্লিক করলে আলাদা পেজে পুরো জীবনী ও কর্মজীবনের
                        তথ্য দেখা যাবে।
                        সেখানে জন্মতারিখ, শিক্ষা, পেশাগত অর্জন, পদবী এবং অন্যান্য গুরুত্বপূর্ণ তথ্য পাওয়া যায়।
                        প্রয়োজনে সার্চ বা ফিল্টার ব্যবহার করে দ্রুত নির্দিষ্ট ব্যক্তির প্রোফাইল খুঁজে নিতে পারেন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>তথ্যগুলো কি নিয়মিত আপডেট হয়?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        হ্যাঁ। নতুন ব্যক্তিত্ব যোগ হলে বা পুরনো তথ্যে পরিবর্তন এলে তা নিয়মিত আপডেট করা হয়।
                        তবে কোনো তথ্য ভুল বা পুরনো মনে হলে আমাদের জানাতে পারেন, যাতে দ্রুত সংশোধন করা যায়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>পেশা বা ক্যাটাগরি অনুসারে কীভাবে খুঁজব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        পেজের উপরের সার্চ বক্সে নাম লিখে খুঁজতে পারেন।
                        চাইলে পেশা বা ক্যাটাগরি (রাজনীতি, শিক্ষা, সংস্কৃতি, ব্যবসা ইত্যাদি) সিলেক্ট করে ফিল্টার করুন।
                        ফিল্টার ব্যবহার করলে শুধুমাত্র আপনার প্রয়োজনীয় প্রোফাইলগুলো দেখাবে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </section>

</div>
