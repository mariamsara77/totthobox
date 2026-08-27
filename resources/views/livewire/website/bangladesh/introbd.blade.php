<?php

use App\Models\IntroBd;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    #[Url(history: true, keep: false)]
    public string $search = '';

    public function mount(): void
    {
        // optional: record a general page view if needed
    }

    #[Computed]
    public function allData()
    {
        return Cache::remember('intro_bd_list_v5', now()->addHours(6), function () {
            return IntroBd::query()
                ->with(['media'])
                ->latest('id')
                ->get();
        });
    }

    #[Computed]
    public function filteredData()
    {
        if (blank($this->search)) {
            return $this->allData;
        }

        $term = mb_strtolower(trim($this->search), 'UTF-8');

        return $this->allData->filter(function ($item) use ($term) {
            return str_contains(mb_strtolower($item->title ?? '', 'UTF-8'), $term) || str_contains(mb_strtolower(strip_tags($item->description ?? ''), 'UTF-8'), $term) || str_contains(mb_strtolower($item->intro_category ?? '', 'UTF-8'), $term);
        });
    }

#[Computed]
public function groupedData()
{
    return $this->filteredData->groupBy(fn($item) => ($item['intro_category'] ?? null) ?: 'সাধারণ তথ্য');
}
    #[Computed]
    public function creators()
    {
        return Cache::remember('intro_bd_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', IntroBd::class)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get();
        });
    }

    public function resetFilter(): void
    {
        $this->reset('search');
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
বাংলাদেশের পরিচিতি – ALL DATA LIST
SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="বাংলাদেশের পরিচিতি | তথ্যবক্স"
        description="বাংলাদেশের বিভিন্ন বিভাগ, জেলা ও সাধারণ তথ্যসহ সম্পূর্ণ পরিচিতি পড়ুন। ছবি, বিবরণ এবং যাচাইকৃত তথ্য।"
        keywords="বাংলাদেশের পরিচিতি, বাংলাদেশ তথ্য, বিভাগ, জেলা, বাংলাদেশ পরিচিতি, তথ্যবক্স" />

    {{-- Page Header --}}
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                <flux:icon icon="map" class="text-amber-600 dark:text-amber-500" />
                বাংলাদেশের পরিচিতি
            </flux:heading>

            <flux:text variant="subtle" class="mt-1">
                বাংলাদেশের সকল বিভাগ ও জেলার বিস্তারিত তথ্য, ইতিহাস ও গুরুত্বপূর্ণ পরিচিতি
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
                            এই বাংলাদেশ পরিচিতি পেজের কন্টেন্ট তৈরি ও যাচাইকরণে যারা অবদান রেখেছেন
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

                            <flux:separator class="opacity-50" />

                            <div class="flex items-center justify-between">
                                <flux:text size="sm" variant="subtle">
                                    সর্বশেষ সক্রিয়:
                                    {{ $creator->last_active_at ? bn_num($creator->last_active_at->diffForHumans()) : 'অজানা' }}
                                </flux:text>

                                <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost" size="xs"
                                    icon="arrow-right" aria-label="{{ $creator->name }} এর সম্পূর্ণ প্রোফাইল দেখুন" />
                            </div>
                        </flux:card>
                        @empty
                        <flux:text size="sm" variant="subtle" class="py-2 text-center">
                            এখনো কোনো কন্ট্রিবিউটর পাওয়া যায়নি।
                        </flux:text>
                        @endforelse
                    </div>

                    <flux:separator class="opacity-50" />

                    <flux:text size="sm" variant="subtle" class="text-center">
                        আমাদের সকল তথ্য ভেরিফাইড, নির্ভরযোগ্য এবং নিয়মিত আপডেটেড।
                    </flux:text>
                </flux:tooltip.content>
            </flux:tooltip>
        </div>
    </header>
    {{-- Search --}}
    <nav class="flex items-center gap-4" aria-label="তথ্য সার্চ">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="বিভাগ, জেলা বা শিরোনাম দিয়ে খুঁজুন..."
            icon="magnifying-glass" variant="filled" class="rounded-xl flex-1" aria-label="তথ্য খুঁজুন" />
        @if ($search)
        <flux:button wire:click="resetFilter" variant="ghost" icon="x-mark" size="sm" aria-label="সার্চ মুছুন" />
        @endif
    </nav>

    {{-- Results count --}}
    @if ($search)
    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400" role="status" aria-live="polite">
        “{{ $search }}” এর জন্য {{ bn_num($this->filteredData->count()) }}টি ফলাফল পাওয়া গেছে
    </p>
    @endif

    {{-- Grouped List --}}
    <section class="space-y-10" aria-labelledby="intro-list-heading">
        <h2 id="intro-list-heading" class="sr-only">বাংলাদেশের পরিচিতি — তালিকা</h2>

        @forelse ($this->groupedData as $category => $items)
        <div class="space-y-4">
            <div class="flex justify-center">
                <flux:badge size="sm" color="zinc" variant="solid"
                    class="px-4 rounded-full uppercase tracking-widest text-xs font-bold">
                    {{ $category }}
                </flux:badge>
            </div>

            @foreach ($items as $item)
            @php
            $thumb =
            $item->getFirstMediaUrl('intro_images', 'thumb') ?: $item->getFirstMediaUrl('intro_images');
            @endphp

            <flux:card>
                <div class="flex gap-4 items-start">
                    {{-- Image --}}
                    <div class="shrink-0">
                        <flux:avatar src="{{ $thumb }}" size="xl" name="{{ $item->title ?? 'অজানা তথ্য' }}"
                            class="rounded-xl" />
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 space-y-2">
                        <flux:heading level="2" size="lg">
                            <flux:link variant="ghost"
                                href="{{ route('bangladesh.introduction.show', $item->slug ?? $item->id) }}"
                                wire:navigate>
                                {{ $item->title ?? 'অজানা তথ্য' }}
                            </flux:link>
                        </flux:heading>

                        @if ($item->description)
                        <flux:text class="line-clamp-2 overflow-hidden text-base">
                            {{ strip_tags($item->description) }}
                        </flux:text>
                        @endif
                    </div>
                </div>

                <flux:separator class="opacity-50 my-2" />

                {{-- Footer Action --}}
                <div>
                    <flux:button icon="arrow-right" variant="subtle" size="xs"
                        href="{{ route('bangladesh.introduction.show', $item->slug ?? $item->id) }}"
                        class="inline-flex items-center gap-2 text-xs font-semibold text-amber-600 dark:text-amber-400  dark:hover:text-amber-300"
                        wire:navigate>বিস্তারিত পড়ুন
                    </flux:button>
                </div>
            </flux:card>
            @endforeach
        </div>
        @empty
        <livewire:global.nodata-message :title="'বাংলাদেশের পরিচিতি'" :search="$search" />
        @endforelse
    </section>

    <flux:separator />

    {{-- About Section --}}
    <section aria-labelledby="about-intro" class="space-y-4">
        <flux:heading id="about-intro" level="2" size="lg" class="flex items-center gap-4">
            <flux:icon icon="information-circle" color="orange" />
            বাংলাদেশের পরিচিতি সম্পর্কে
        </flux:heading>

        <div class="space-y-3">
            <flux:text>
                এই পেজে বাংলাদেশের বিভিন্ন বিভাগ, জেলা এবং সাধারণ তথ্যের তালিকা দেওয়া আছে।
                এখানে দেশের ভৌগোলিক অবস্থান, প্রশাসনিক বিভাগ, জনসংখ্যা, সংস্কৃতি, অর্থনীতি এবং অন্যান্য গুরুত্বপূর্ণ
                তথ্য একত্রিত করা হয়েছে।
                প্রতিটি তথ্যের ছবি, শিরোনাম এবং সংক্ষিপ্ত বিবরণ দেখতে পারবেন।
            </flux:text>

            <flux:text>
                বাংলাদেশ সম্পর্কে মৌলিক ও নির্ভরযোগ্য তথ্য জানার জন্য এই সংকলন খুবই উপযোগী।
                “বিস্তারিত পড়ুন” বাটনে ক্লিক করে পুরো ব্যাখ্যা, পরিসংখ্যান এবং প্রাসঙ্গিক তথ্য জানতে পারবেন।
                বিভাগ বা বিষয় অনুসারে ফিল্টার করে সহজেই প্রয়োজনীয় তথ্য খুঁজে নিতে পারবেন।
            </flux:text>

            <flux:text>
                এই তালিকা নিয়মিত আপডেট করা হয় যাতে সর্বশেষ পরিসংখ্যান ও হালনাগাদ তথ্য যুক্ত থাকে।
                শিক্ষার্থী, গবেষক, পর্যটক এবং সাধারণ মানুষ সবাই এই তথ্য থেকে উপকৃত হতে পারেন।
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
                <flux:accordion.heading>বাংলাদেশের পরিচিতি কী?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        এটি বাংলাদেশের বিভিন্ন বিভাগ, জেলা ও সাধারণ তথ্যের একটি সংকলন।
                        উপরের তালিকায় দেশের ভৌগোলিক, প্রশাসনিক, সাংস্কৃতিক ও অর্থনৈতিক তথ্য দেখানো হয়েছে।
                        এখানে বিভাগ, জেলা, জনসংখ্যা, ভাষা, ধর্ম, অর্থনীতিসহ বিভিন্ন বিষয়ের মৌলিক তথ্য একত্রিত করা হয়েছে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>বিস্তারিত তথ্য কোথায় পাব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        প্রতিটি তথ্যের নিচে “বিস্তারিত পড়ুন” বাটনে ক্লিক করলে আলাদা পেজে পুরো বিবরণ দেখা যাবে।
                        সেখানে বিস্তারিত ব্যাখ্যা, পরিসংখ্যান, ইতিহাস এবং প্রাসঙ্গিক তথ্য পাওয়া যায়।
                        প্রয়োজনে সার্চ বা ফিল্টার ব্যবহার করে দ্রুত নির্দিষ্ট বিষয় খুঁজে নিতে পারেন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>তথ্যগুলো কি নিয়মিত আপডেট হয়?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        হ্যাঁ। নতুন পরিসংখ্যান বা তথ্য পরিবর্তন হলে তা নিয়মিত আপডেট করা হয়।
                        তবে কোনো তথ্য ভুল বা পুরনো মনে হলে আমাদের জানাতে পারেন, যাতে দ্রুত সংশোধন করা যায়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>বিভাগ বা বিষয় অনুসারে কীভাবে খুঁজব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        পেজের উপরের সার্চ বক্সে নাম বা কীওয়ার্ড লিখে খুঁজতে পারেন।
                        চাইলে বিভাগ বা বিষয় (ভৌগোলিক, প্রশাসনিক, সাংস্কৃতিক ইত্যাদি) সিলেক্ট করে ফিল্টার করুন।
                        ফিল্টার ব্যবহার করলে শুধুমাত্র আপনার প্রয়োজনীয় তথ্যগুলো দেখাবে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </section>

</div>