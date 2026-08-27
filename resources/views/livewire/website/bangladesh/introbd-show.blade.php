<?php

use App\Models\IntroBd;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Flux\Flux;

new class extends Component {
    public $intro;

    public function mount($intro = null): void
    {
        $param = $intro ?? (request()->route('intro') ?? (request()->route('slug') ?? request()->route('id')));

        if (!$param) {
            abort(404);
        }

        $data = Cache::remember("intro_bd_show_{$param}", 3600, function () use ($param) {
            return IntroBd::query()
                ->with('media')
                ->where(function ($q) use ($param) {
                    $q->where('slug', $param)->orWhere('id', $param);
                })
                ->first();
        });

        if (!$data) {
            abort(404);
        }

        $this->intro = $data;
        views($this->intro)->record();
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember("intro_bd_creators_{$this->intro->id}", now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', IntroBd::class)->where('subject_id', $this->intro->id)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

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

        $this->intro->react($type);
        $this->intro->refresh();

        Flux::toast(text: 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }
};
?>

{{-- rest of the blade stays exactly the same as you already have --}}

{{-- ═══════════════════════════════════════════════════════════════
বাংলাদেশের পরিচিতি – SINGLE SHOW
SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="{{ $intro->title }} | বাংলাদেশের পরিচিতি | তথ্যবক্স"
        description="{{ Str::limit(strip_tags($intro->description ?? $intro->title . ' সম্পর্কে বিস্তারিত তথ্য।'), 155) }}"
        keywords="{{ $intro->title }}, বাংলাদেশের পরিচিতি, {{ $intro->intro_category }}, তথ্যবক্স"
        image="{{ $intro->getFirstMediaUrl('intro_images', 'thumb') ?? null }}" />

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('bangladesh.introduction') }}">
                বাংলাদেশের পরিচিতি
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $intro->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </nav>

    {{-- Header --}}
    <header class="space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-2">
                @if ($intro->intro_category)
                <flux:badge color="zinc" size="sm" variant="subtle">{{ $intro->intro_category }}</flux:badge>
                @endif

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-zinc-900 dark:text-white">
                    {{ $intro->title ?? 'অজানা তথ্য' }}
                </h1>

                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    বাংলাদেশের পরিচিতি · বিস্তারিত তথ্য
                </p>

                <div class="flex items-center gap-2 flex-wrap">
                    <flux:badge icon="eye" size="sm" variant="subtle">
                        {{ bn_num(views($intro)->count()) }}
                    </flux:badge>
                </div>
            </div>

            {{-- Creators Tooltip --}}
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

                        <div class="max-h-80 w-80 overflow-y-auto space-y-3">
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
                                <div class="flex items-center justify-between text-xs text-zinc-500">
                                    <span>
                                        একটিভ:
                                        {{ $creator->last_active_at ? bn_num($creator->last_active_at->diffForHumans()) : 'অজানা' }}
                                    </span>
                                    <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost"
                                        size="xs" icon="arrow-right" aria-label="{{ $creator->name }} প্রোফাইল দেখুন" />
                                </div>
                            </flux:card>
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
    </header>

    {{-- Large image --}}
    <flux:media :media="$intro->getMedia('intro_images')" alt="{{ $intro->title }}" />

    {{-- Description --}}
    <section aria-labelledby="intro-details-heading" class="space-y-3">
        <h2 id="intro-details-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            বিস্তারিত বিবরণ
        </h2>

        @if ($intro->description)
        <div class="prose dark:prose-invert max-w-none leading-relaxed text-zinc-600 dark:text-zinc-300">
            {!! $intro->description !!}
        </div>
        @else
        <p class="text-sm text-zinc-500">এই তথ্যের বিস্তারিত বিবরণ এখনো যোগ করা হয়নি।</p>
        @endif
    </section>


    <flux:separator class="opacity-50 mt-16" />

    {{-- Action Buttons --}}
    <div class="flex gap-4 items-center justify-between">
        <div class="flex gap-4">
            <flux:button variant="subtle" wire:click="react('like')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-blue-600': {{ $intro->hasReaction('like') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-up" class="size-4" />
                    {{ $intro->countReaction('like') }}
                </div>
            </flux:button>

            <flux:button variant="subtle" wire:click="react('dislike')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-red-600': {{ $intro->hasReaction('dislike') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-down" class="size-4" />
                    {{ $intro->countReaction('dislike') }}
                </div>
            </flux:button>
        </div>

        <flux:button variant="subtle" size="sm" icon="share" data-share-button
            data-url="{{ route('bangladesh.introduction.show', $intro->slug ?? $intro->id) }}"
            data-title="{{ $intro->title }}">
            শেয়ার
        </flux:button>
    </div>

    {{-- Back --}}
    <div>
        <flux:button as="a" href="{{ route('bangladesh.introduction') }}" variant="ghost" icon="arrow-left" size="sm"
            aria-label="বাংলাদেশের পরিচিতি তালিকায় ফিরে যান">
            বাংলাদেশের পরিচিতি তালিকায় ফিরে যান
        </flux:button>
    </div>


    {{-- About --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-intro-item">
        <h2 id="about-intro-item" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            {{ $intro->title }} সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>{{ $intro->title }}</strong> হলো বাংলাদেশের পরিচিতির অংশ।
                @if ($intro->intro_category)
                এটি <strong>{{ $intro->intro_category }}</strong> ক্যাটাগরির অন্তর্গত।
                @endif
                উপরের বিবরণ অনুসরণ করে বিস্তারিত জানুন।
            </p>
        </div>
    </section>

    {{-- Comments --}}
    <div class="mt-4">
        <livewire:website.comments.comments-section :model="$intro" />
    </div>

    {{-- FAQ --}}
    <section class="space-y-3" aria-labelledby="faq-heading">
        <h2 id="faq-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            প্রায়শই জিজ্ঞাসিত প্রশ্ন
        </h2>

        <div class="space-y-2">
            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>{{ $intro->title }} কী?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    উপরের “বিস্তারিত বিবরণ” সেকশনে এই তথ্যের পূর্ণাঙ্গ ব্যাখ্যা লেখা আছে।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>অন্যান্য তথ্য কোথায় পাব?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    <a href="{{ route('bangladesh.introduction') }}" class="text-amber-600 hover:underline">
                        বাংলাদেশের পরিচিতি
                    </a>
                    তালিকায় ফিরে গিয়ে অন্যান্য তথ্য দেখতে পারবেন।
                </div>
            </details>
        </div>
    </section>

</div>