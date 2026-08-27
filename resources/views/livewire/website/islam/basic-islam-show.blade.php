<?php

use App\Models\BasicIslam;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Flux\Flux;

new class extends Component {
    public BasicIslam $item;

    public function mount(string $slug): void
    {
        $this->item = BasicIslam::where('slug', $slug)->with('media')->firstOrFail();
        views($this->item)->record();
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember("basic_islam_creators_{$this->item->id}", now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', BasicIslam::class)->where('subject_id', $this->item->id)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get();
        });
    }

    public function react($type)
    {
        if (!auth()->check()) {
            Flux::toast(
                text: 'রিয়্যাকশন করার জন্য লগইন করতে হবে।',
                variant: 'danger', // বা 'warning'
            );

            Flux::modal('auth-modal')->show();
            // অথবা $this->modal('auth-modal')->show();

            return; // ← এটা খুব জরুরি
        }

        $this->item->react($type);
        $this->item->refresh();

        Flux::toast(text: 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
BASIC ISLAM – SINGLE SHOW PAGE
SEO Optimized · AdSense Policy Compliant · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="{{ strip_tags($item->title) }} | ইসলামের মৌলিক জ্ঞান"
        description="{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 155) }}"
        keywords="{{ strip_tags($item->title) }}, ইসলামিক জ্ঞান, ইসলামের মৌলিক জ্ঞান, তথ্যবক্স"
        image="{{ $item->getFirstMediaUrl('images') ?? null }}" />

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('islam.basicislam') }}">ইসলামের মৌলিক জ্ঞান</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ strip_tags($item->title) }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </nav>

    {{-- ══════════════════════════════════════
    Article Header (Only one H1)
    ══════════════════════════════════════ --}}
    <header class="space-y-2 border-b pb-4 dark:border-zinc-700">
        <div class="flex items-center gap-4">
            {{-- <div class="h-8 w-1.5 bg-zinc-400/10 rounded-full" aria-hidden="true"></div> --}}
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-emerald-500">
                {{ strip_tags($item->title) }}
            </h1>
        </div>
        <div class="flex justify-between gap-4 text-sm text-zinc-500 dark:text-zinc-400">
            <p class="">
                ইসলামের মৌলিক জ্ঞান · তথ্যবক্স
            </p>
            {{-- Creators Tooltip (এই আইটেমের জন্য) --}}
            <div class="shrink-0">
                <flux:tooltip toggleable>
                    <flux:button icon="user" size="sm" variant="subtle" aria-label="তথ্য প্রদানকারীগণ দেখুন" />

                    <flux:tooltip.content class="rounded-2xl! space-y-4 p-4">
                        <div class="space-y-1">
                            <h2 class="font-semibold text-zinc-800 dark:text-zinc-200">
                                তথ্য প্রদানকারী
                            </h2>
                            <p class="text-xs text-zinc-500">এই কন্টেন্ট তৈরিতে অবদান রেখেছেন</p>
                        </div>

                        <div class="max-h-80 overflow-y-auto space-y-3 custom-scrollbar">
                            @forelse ($this->creators as $creator)
                                <div class="p-2.5 rounded-xl bg-zinc-100 dark:bg-zinc-800/60 transition-all space-y-2">
                                    <div class="flex items-start gap-4">
                                        <flux:avatar src="{{ $creator->avatar_url }}" size="md" badge
                                            badge:color="{{ $creator->isOnline() ? 'green' : 'zinc' }}"
                                            alt="{{ $creator->name }}" />

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="font-medium text-sm text-zinc-800 dark:text-zinc-200 truncate">
                                                    {{ $creator->name }}
                                                </span>
                                                @if ($creator->email_verified_at)
                                                    <flux:icon.check-badge class="size-4 text-emerald-500"
                                                        variant="solid" aria-hidden="true" />
                                                @endif
                                            </div>
                                            <p class="text-xs text-zinc-500 truncate">
                                                {{ $creator->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর' }}
                                            </p>
                                        </div>
                                    </div>
                                    <flux:separator class="opacity-50" />
                                    <div class="flex items-center justify-between text-xs text-zinc-500">
                                        <span>
                                            একটিভ:
                                            {{ $creator->last_active_at ? bn_num($creator->last_active_at->diffForHumans()) : 'অজানা' }}
                                        </span>
                                        <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost"
                                            size="xs" icon="arrow-right"
                                            aria-label="{{ $creator->name }} প্রোফাইল দেখুন" />
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-zinc-500 text-center py-2">কোনো কন্ট্রিবিউটর পাওয়া যায়নি।</p>
                            @endforelse
                        </div>

                        <p class="text-xs text-zinc-400 text-center border-t border-zinc-400/25 pt-2">
                            আমাদের সকল তথ্য ভেরিফাইড এবং যাচাইকৃত।
                        </p>
                    </flux:tooltip.content>
                </flux:tooltip>
            </div>

        </div>
        <flux:badge icon="eye" size="sm"> {{ bn_num(views($item)->count()) }}
        </flux:badge>
    </header>

    {{-- Content --}}
    <article class="space-y-6" aria-labelledby="article-title">
        <h2 id="article-title" class="sr-only">{{ strip_tags($item->title) }} — বিস্তারিত</h2>
        <div class="">
            <flux:media :media="$item->getMedia('images')" />
        </div>
        <flux:text>
            {!! $item->description !!}
        </flux:text>

    </article>

    <flux:separator class="my-8" />

    {{-- Back link --}}
    <div>
        <flux:button as="a" href="{{ route('islam.basicislam') }}" variant="ghost" icon="arrow-left"
            size="sm" aria-label="সব বিষয়ে ফিরে যান">
            সব বিষয়ে ফিরে যান
        </flux:button>
    </div>


    <!-- Action Buttons -->
    <div class="flex gap-4 mt-6 items-center justify-between">
        <div class="flex justify-center gap-4">
            <!-- LIKE -->
            <flux:button variant="subtle" wire:click="react('like')" size="sm">
                <div class="flex gap-4"
                    :class="{ 'text-blue-600': {{ $item->hasReaction('like') ? 'true' : 'false' }} }"
                    x-data="{ reacted: {{ $item->hasReaction('like') ? 'true' : 'false' }} }"
                    x-on:reaction-updated.window="reacted = event.detail.type == 'like' ? true : false">
                    <flux:icon name="thumb-up" class="w-5 h-5 mr-1" />
                    {{ $item->countReaction('like') }}
                </div>
            </flux:button>

            <!-- DISLIKE -->
            <flux:button variant="subtle" wire:click="react('dislike')" size="sm">
                <div class="flex gap-4"
                    :class="{ 'text-red-600': {{ $item->hasReaction('dislike') ? 'true' : 'false' }} }"
                    x-data="{ reacted: {{ $item->hasReaction('dislike') ? 'true' : 'false' }} }"
                    x-on:reaction-updated.window="reacted = event.detail.type == 'dislike' ? true : false">
                    <flux:icon name="thumb-down" class="w-5 h-5 mr-1" />
                    {{ $item->countReaction('dislike') }}
                </div>
            </flux:button>
        </div>



        <flux:button variant="subtle" size="sm" icon="share" data-share-button
            data-url="{{ route('islam.basicislam.show', $item->slug) }}" data-title="{{ $item->name }}">
            শেয়ার
        </flux:button>
    </div>

    <!-- Comments Section -->
    <div class="mt-8">
        <livewire:website.comments.comments-section :model="$item" />
    </div>


    {{-- ══════════════════════════════════════
    Related / About
    ══════════════════════════════════════ --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-topic">
        <h2 id="about-topic" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            এই বিষয় সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>{{ strip_tags($item->title) }}</strong> ইসলামের মৌলিক জ্ঞানের অংশ।
                দ্বীনের সঠিক ধারণা জানতে আরও বিষয় দেখুন ইসলামের মৌলিক জ্ঞান সেকশনে।
            </p>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="space-y-3" aria-labelledby="faq-heading">
        <h2 id="faq-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            প্রায়শই জিজ্ঞাসিত প্রশ্ন
        </h2>

        <div class="space-y-2">
            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>{{ strip_tags($item->title) }} কেন গুরুত্বপূর্ণ?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    এটি ইসলামের মৌলিক জ্ঞানের অংশ। সঠিক ধারণা রাখা প্রতিটি মুসলমানের জন্য প্রয়োজনীয়। উপরের বিবরণে
                    বিস্তারিত ব্যাখ্যা আছে।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>আরও বিষয় কোথায় পাব?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    <a href="{{ route('islam.basicislam') }}" class="text-emerald-600 hover:underline">ইসলামের মৌলিক
                        জ্ঞান</a> পেজে সব বিষয় একসাথে দেখতে পারবেন।
                </div>
            </details>
        </div>
    </section>

</div>
