<?php

use App\Models\SignCategory;
use App\Models\User;
use App\Models\Sign;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    public string $slug;
    public $category = null;
    public bool $isAll = false;

    #[Url(history: true, keep: false)]
    public string $search = '';

    public function mount(string $slug = 'all'): void
    {
        $this->slug = $slug;
        $this->isAll = $slug === 'all';

        if ($this->isAll) {
            $this->category = null;
            return;
        }

        $this->category = Cache::remember("sign_category_{$slug}", now()->addHour(), function () use ($slug) {
            return SignCategory::query()->where('slug', $slug)->first();
        });

        if (!$this->category) {
            abort(404);
        }
    }

    #[Computed]
    public function allData()
    {
        $cacheKey = $this->isAll ? 'sign_all_signs' : "sign_category_signs_{$this->slug}";

        return Cache::remember($cacheKey, now()->addHour(), function () {
            if ($this->isAll) {
                return Sign::query()
                    ->with(['media', 'category'])
                    ->latest()
                    ->get();
            }

            return $this->category->signs()->with('media')->latest()->get();
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
            return str_contains(mb_strtolower($item->name ?? '', 'UTF-8'), $term) || str_contains(mb_strtolower(strip_tags($item->description ?? ''), 'UTF-8'), $term);
        });
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember('dowas_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', Sign::class)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

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
TRAFFIC SIGNS – CATEGORY / ALL LIST
SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    {{-- SEO --}}
    @if ($isAll)
        <x-seo title="সকল ট্রাফিক সাইন ও চিহ্ন | তথ্যবক্স"
            description="বাংলাদেশের সকল ট্রাফিক সাইন ও রোড চিহ্নের সম্পূর্ণ তালিকা। প্রতিটি সাইনের ছবি, নাম, অর্থ এবং বিস্তারিত ব্যাখ্যা একসাথে দেখুন।"
            keywords="ট্রাফিক সাইন, ট্রাফিক চিহ্ন, রোড সাইন, বাংলাদেশ ট্রাফিক সাইন, সকল ট্রাফিক সাইন, ট্রাফিক রুলস, road signs bangladesh" />
    @else
        <x-seo title="{{ $category->name }} | ট্রাফিক সাইন ও চিহ্ন | তথ্যবক্স"
            description="{{ $category->name }} ক্যাটাগরির সকল ট্রাফিক সাইন ও চিহ্নের ছবি, নাম এবং অর্থসহ বিস্তারিত বিবরণ পড়ুন।"
            keywords="{{ $category->name }}, ট্রাফিক সাইন, ট্রাফিক চিহ্ন, রোড সাইন, ট্রাফিক রুলস, বাংলাদেশ ট্রাফিক সাইন" />
    @endif

    {{-- Page Header --}}
    <header class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-4">
        <div class="space-y-4">
            <flux:heading level="1" size="xl" class="flex gap-4">
                @if ($isAll)
                    <flux:icon icon="sign" class="size-6" aria-hidden="true" />
                    সকল ট্রাফিক সাইন
                @else
                    <flux:icon icon="{{ $category->icon }}" class="" aria-hidden="true" />
                    {{ $category->name }}
                @endif
            </flux:heading>
            <flux:text class="text-sm">
                @if ($isAll)
                    বাংলাদেশের সকল ট্রাফিক সাইন ও রোড চিহ্নের সম্পূর্ণ তালিকা
                @else
                    এই ক্যাটাগরির অন্তর্ভুক্ত সকল চিহ্নের তালিকা
                @endif
            </flux:text>
        </div>

        <div class="flex items-center gap-4">
            <flux:tooltip toggleable>
                <flux:button icon="users" size="sm" variant="subtle" aria-label="তথ্য প্রদানকারীগণ দেখুন" />

                <flux:tooltip.content class="rounded-2xl! space-y-4 p-4">
                    <div class="space-y-1">
                        <flux:heading level="2">
                            তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                        </flux:heading>
                        <flux:text class="text-xs">এই কন্টেন্ট তৈরিতে যারা অবদান রেখেছেন</flux:text>
                    </div>

                    <div class="max-h-80 overflow-y-auto space-y-3 custom-scrollbar">
                        @forelse ($this->creators as $creator)
                            <flux:card class="space-y-2">
                                <div class="flex items-start gap-4">
                                    <flux:avatar src="{{ $creator->avatar_url }}" size="md" badge
                                        badge:color="{{ $creator->isOnline() ? 'green' : 'zinc' }}"
                                        alt="{{ $creator->name }}" />

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-sm text-zinc-800 dark:text-zinc-200 truncate">
                                                {{ $creator->name }}
                                            </span>
                                            @if ($creator->email_verified_at)
                                                <flux:icon.check-badge class="size-4 text-emerald-500" variant="solid"
                                                    aria-hidden="true" />
                                            @endif
                                        </div>
                                        <p class="text-xs text-zinc-500 truncate">
                                            {{ $creator->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর' }}
                                        </p>
                                    </div>
                                </div>
                                <flux:separator class="opacity-50" />
                                <div class="flex items-center justify-between text-xs">
                                    <flux:text>
                                        একটিভ:
                                        {{ $creator->last_active_at ? bn_num($creator->last_active_at->diffForHumans()) : 'অজানা' }}
                                    </flux:text>
                                    <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost"
                                        size="xs" icon="arrow-right"
                                        aria-label="{{ $creator->name }} প্রোফাইল দেখুন" />
                                </div>
                            </flux:card>
                        @empty
                            <flux:text class="text-xs">কোনো কন্ট্রিবিউটর পাওয়া যায়নি।</flux:text>
                        @endforelse
                    </div>
                    <flux:separator class="opacity-50" />
                    <flux:text class="text-xs">
                        আমাদের সকল তথ্য ভেরিফাইড এবং যাচাইকৃত।
                    </flux:text>
                </flux:tooltip.content>
            </flux:tooltip>
        </div>
    </header>

    {{-- Search --}}
    <nav class="flex items-center gap-4" aria-label="সাইন সার্চ">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="সাইন খুঁজুন (যেমন: স্টপ, জিগজ্যাগ)..."
            icon="magnifying-glass" variant="filled" class="rounded-xl flex-1" aria-label="সাইন খুঁজুন" />
        @if ($search)
            <flux:button wire:click="resetFilter" variant="ghost" icon="x-mark" size="sm"
                aria-label="সার্চ মুছুন" />
        @endif
    </nav>

    {{-- Results count --}}
    @if ($search)
        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400" role="status" aria-live="polite">
            “{{ $search }}” এর জন্য {{ bn_num($this->filteredData->count()) }}টি ফলাফল পাওয়া গেছে
        </p>
    @endif

    {{-- Signs List --}}
    <section class="space-y-4" aria-labelledby="signs-list-heading">
        <h2 id="signs-list-heading" class="sr-only">
            {{ $isAll ? 'সকল ট্রাফিক সাইন' : $category->name }} — সাইন তালিকা
        </h2>

        @forelse ($this->filteredData as $sign)
            @php
                $thumb = $sign->getFirstMediaUrl('images', 'thumb') ?: $sign->getFirstMediaUrl('images');
                $categorySlug = $isAll ? $sign->category?->slug ?? 'all' : $category->slug;
            @endphp

            <flux:card>
                <div class="flex gap-4 items-start">
                    {{-- Image --}}
                    <div class="shrink-0">
                        <flux:avatar src="{{ $thumb }}" size="xl"
                            name="{{ $sign->name ?? 'অজানা সংকেত' }}" class="rounded-xl" />
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 space-y-2">
                        <flux:heading level="2" size="lg">
                            <flux:link variant="ghost"
                                href="{{ route('signs.show', [
                                    'category' => $categorySlug,
                                    'sign' => $sign->slug ?: $sign->id,
                                ]) }}"
                                wire:navigate>
                                {{ $sign->name ?? 'অজানা সংকেত' }}
                            </flux:link>
                        </flux:heading>

                        @if ($isAll && $sign->category)
                            <flux:text
                                class="
                                                                                                                        text-xs">
                                {{ $sign->category->name }}
                            </flux:text>
                        @endif

                        @if ($sign->description)
                            <flux:text class="line-clamp-2 overflow-hidden text-base">
                                {{ strip_tags($sign->description) }}
                            </flux:text>
                        @endif
                    </div>
                </div>

                {{-- Footer Action --}}
                <flux:separator class="opacity-50 my-2" />

                {{-- Footer Action --}}
                <div>
                    <flux:button icon="arrow-right" variant="subtle" size="xs"
                        href="{{ route('signs.show', [
                            'category' => $categorySlug,
                            'sign' => $sign->slug ?: $sign->id,
                        ]) }}"
                        wire:navigate>বিস্তারিত পড়ুন
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <livewire:global.nodata-message :title="'ট্রাফিক সাইন'" :search="$search" />
        @endforelse
    </section>

    <flux:separator />

    {{-- About Section --}}
    <section aria-labelledby="about-category" class="space-y-4">
        <flux:heading id="about-category" level="2" size="lg" class="flex items-center gap-4">
            <flux:icon icon="information-circle" color="orange" />
            @if ($isAll)
                সকল ট্রাফিক সাইন সম্পর্কে
            @else
                {{ $category->name }} সম্পর্কে
            @endif
        </flux:heading>

        <div class="space-y-3">
            @if ($isAll)
                <flux:text>
                    এই পেজে বাংলাদেশের <strong>সকল ট্রাফিক সাইন ও রোড চিহ্নের</strong> সম্পূর্ণ তালিকা দেওয়া আছে।
                    এখানে সতর্কতামূলক, নিষেধাজ্ঞামূলক, নির্দেশমূলক এবং তথ্যমূলক সব ধরনের চিহ্ন একত্রিত করা হয়েছে।
                    প্রতিটি চিহ্নের ছবি, নাম, ক্যাটাগরি এবং সংক্ষিপ্ত অর্থ দেখতে পারবেন।
                </flux:text>

                <flux:text>
                    ড্রাইভার, শিক্ষার্থী এবং সাধারণ মানুষ যারা ট্রাফিক নিয়ম জানতে চান, তাদের জন্য এই তালিকা খুবই উপযোগী।
                    “বিস্তারিত পড়ুন” বাটনে ক্লিক করে পুরো ব্যাখ্যা, অর্থ এবং ব্যবহার সম্পর্কে জানতে পারবেন।
                </flux:text>

                <flux:text>
                    এই সংকলন নিয়মিত আপডেট করা হয় যাতে নতুন চিহ্ন ও হালনাগাদ তথ্য যুক্ত থাকে।
                    সঠিক ও নির্ভরযোগ্য তথ্য পাওয়ার জন্য এটি একটি সহজ ও কার্যকর উৎস।
                </flux:text>
            @else
                <flux:text>
                    এই পেজে <strong>{{ $category->name }}</strong> ক্যাটাগরির ট্রাফিক সাইন ও রোড চিহ্নের তালিকা দেওয়া
                    আছে।
                    প্রতিটি চিহ্নের ছবি, নাম এবং সংক্ষিপ্ত অর্থ দেখতে পারবেন।
                    এই ক্যাটাগরির চিহ্নগুলো রাস্তায় নিরাপদ চলাচলের জন্য গুরুত্বপূর্ণ।
                </flux:text>

                <flux:text>
                    “বিস্তারিত পড়ুন” বাটনে ক্লিক করে পুরো ব্যাখ্যা, অর্থ এবং ব্যবহার সম্পর্কে জানতে পারবেন।
                    ড্রাইভার ও শিক্ষার্থীদের জন্য এই তথ্য খুবই সহায়ক।
                </flux:text>

                <flux:text>
                    এই তালিকা নিয়মিত আপডেট করা হয় যাতে সঠিক ও নির্ভরযোগ্য তথ্য থাকে।
                </flux:text>
            @endif
        </div>
    </section>

    {{-- FAQ Section --}}
    <section aria-labelledby="faq-heading" class="space-y-4">
        <flux:heading id="faq-heading" level="2" size="lg">
            প্রায়শাই জিজ্ঞাসিত প্রশ্ন
        </flux:heading>

        <flux:accordion transition exclusive>
            <flux:accordion.item>
                <flux:accordion.heading>
                    @if ($isAll)
                        সকল ট্রাফিক সাইন কী?
                    @else
                        {{ $category->name }} কী?
                    @endif
                </flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        @if ($isAll)
                            এটি বাংলাদেশের সকল ট্রাফিক সাইন ও রোড চিহ্নের সম্পূর্ণ তালিকা।
                            উপরের তালিকায় সব ক্যাটাগরির চিহ্ন দেখানো হয়েছে।
                            সতর্কতামূলক, নিষেধাজ্ঞামূলক, নির্দেশমূলক এবং তথ্যমূলক চিহ্ন এখানে অন্তর্ভুক্ত।
                        @else
                            এটি ট্রাফিক সাইন/রোড চিহ্নের একটি ক্যাটাগরি।
                            উপরের তালিকায় এ ক্যাটাগরির সব চিহ্ন দেখানো হয়েছে।
                            প্রতিটি চিহ্নের অর্থ ও ব্যবহার বিস্তারিতভাবে জানা যায়।
                        @endif
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>বিস্তারিত তথ্য কোথায় পাব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        প্রতিটি সাইনের নিচে “বিস্তারিত পড়ুন” বাটনে ক্লিক করলে আলাদা পেজে পুরো ব্যাখ্যা দেখা যাবে।
                        সেখানে চিহ্নের অর্থ, ব্যবহারের নিয়ম এবং প্রাসঙ্গিক তথ্য পাওয়া যায়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>তথ্যগুলো কি নিয়মিত আপডেট হয়?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        হ্যাঁ। নতুন চিহ্ন যোগ হলে বা পুরনো তথ্যে পরিবর্তন এলে তা নিয়মিত আপডেট করা হয়।
                        তবে কোনো তথ্য ভুল বা পুরনো মনে হলে আমাদের জানাতে পারেন, যাতে দ্রুত সংশোধন করা যায়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>কীভাবে নির্দিষ্ট সাইন খুঁজব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        উপরের সার্চ বক্সে সাইনের নাম বা কীওয়ার্ড লিখে খুঁজতে পারেন।
                        ক্যাটাগরি ফিল্টার ব্যবহার করে নির্দিষ্ট ধরনের চিহ্নও দেখা যায়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </section>

</div>
