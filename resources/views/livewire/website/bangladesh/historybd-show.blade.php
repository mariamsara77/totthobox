<?php

use App\Models\HistoryBd;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Flux\Flux;

new class extends Component {
    public $history;

    public function mount($history = null): void
    {
        $param = $history ?? (request()->route('history') ?? (request()->route('slug') ?? request()->route('id')));

        if (!$param) {
            abort(404);
        }

        $data = Cache::remember("history_bd_show_{$param}", 3600, function () use ($param) {
            return HistoryBd::query()
                ->with(['division', 'district', 'thana', 'media'])
                ->where(function ($q) use ($param) {
                    $q->where('slug', $param)->orWhere('id', $param);
                })
                ->active()
                ->first();
        });

        if (!$data) {
            abort(404);
        }

        $this->history = $data;
        views($this->history)->record();
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember("history_bd_creators_{$this->history->id}", now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', HistoryBd::class)->where('subject_id', $this->history->id)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

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
            Flux::toast(text: 'রিয়্যাকশন করার জন্য লগইন করতে হবে।', variant: 'danger');

            Flux::modal('auth-modal')->show();
            return;
        }

        $this->history->react($type);
        $this->history->refresh();

        Flux::toast(text: 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
বাংলাদেশের ঐতিহাসিক স্থান – SINGLE SHOW
SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

@php
    $pageTitle = ($this->history->title ?? 'অজানা স্থান') . ' | বাংলাদেশের ঐতিহাসিক স্থান | তথ্যবক্স';
    $pageDesc = Str::limit(
        strip_tags($this->history->description ?? $this->history->title . ' সম্পর্কে বিস্তারিত ইতিহাস ও ভ্রমণ গাইড।'),
        155,
    );
@endphp

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="{{ $pageTitle }}" description="{{ $pageDesc }}"
        keywords="{{ $this->history->title }}, বাংলাদেশ ইতিহাস, ঐতিহাসিক স্থান, প্রাচীন নিদর্শন, তথ্যবক্স{{ $this->history->era ? ', ' . $this->history->era : '' }}"
        image="{{ $this->history->getFirstMediaUrl('images', 'thumb') ?? ($this->history->getFirstMediaUrl('images') ?? $this->history->getFirstMediaUrl('default', 'preview')) }}" />

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('bangladesh.history') }}">
                ঐতিহাসিক স্থান
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $this->history->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </nav>

    {{-- Header --}}
    <div class="space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <flux:badge color="zinc" size="sm" variant="subtle">ইতিহাস ও ঐতিহ্য</flux:badge>

                    @if ($this->history->is_featured)
                        <flux:badge color="amber" size="sm" variant="solid" icon="star">
                            Featured
                        </flux:badge>
                    @endif

                    @if ($this->history->era)
                        <flux:badge color="violet" size="sm" variant="subtle">
                            {{ $this->history->era }}
                        </flux:badge>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-zinc-900 dark:text-white">
                    {{ $this->history->title ?? 'অজানা স্থান' }}
                </h1>

                {{-- Year Range --}}
                @if ($this->history->start_year || $this->history->end_year)
                    <p class="text-sm font-medium text-amber-700 dark:text-amber-400 flex items-center gap-2">
                        <flux:icon.calendar class="size-4" />
                        {{ $this->history->start_year ? bn_num($this->history->start_year) : '?' }}
                        –
                        {{ $this->history->end_year ? bn_num($this->history->end_year) : '?' }}
                    </p>
                @endif

                {{-- @if ($this->history->thana || $this->history->district || $this->history->division)
                <p class="text-sm text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                    <flux:icon.map-pin class="size-4" />
                    {{ $this->history->thana?->name ?? '...' }} • {{ $this->history->district?->name ?? '...' }} •
                    {{ $this->history->division?->name ?? '...' }}
                </p>
                @endif --}}

                <div class="flex items-center gap-2 flex-wrap">
                    <flux:badge icon="eye" size="sm" variant="subtle">
                        {{ bn_num(views($this->history)->count()) }}
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
    </div>

    {{-- Large image --}}
    <div>
        <flux:media
            :media="$this->history->getMedia('images')->isNotEmpty() ? $this->history->getMedia('images') : $this->history->getMedia('default')"
            alt="{{ $this->history->title }}" />
    </div>

    {{-- Meta Info Card (Era + Years + Location) --}}
    @if ($this->history->era || $this->history->start_year || $this->history->end_year)
        <div class="rounded-2xl border border-zinc-400/25 bg-zinc-50 dark:bg-zinc-800/40 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                @if ($this->history->era)
                    <div>
                        <p class="text-xs text-zinc-500 mb-2">যুগ / Era</p>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $this->history->era }}</p>
                    </div>
                @endif

                @if ($this->history->start_year || $this->history->end_year)
                    <div>
                        <p class="text-xs text-zinc-500 mb-2">সময়কাল</p>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">
                            {{ $this->history->start_year ? bn_num($this->history->start_year) : '?' }}
                            –
                            {{ $this->history->end_year ? bn_num($this->history->end_year) : '?' }}
                        </p>
                    </div>
                @endif
                {{--
                @if ($this->history->division || $this->history->district)
                <div>
                    <p class="text-xs text-zinc-500 mb-2">অবস্থান</p>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">
                        {{ $this->history->district?->name ?? $this->history->division?->name }}
                    </p>
                </div>
                @endif --}}
            </div>
        </div>
    @endif

    {{-- Description --}}
    <section aria-labelledby="history-details-heading" class="space-y-3">
        <h2 id="history-details-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            বিস্তারিত বিবরণ
        </h2>

        @if ($this->history->description)
            <div class="prose dark:prose-invert max-w-none leading-relaxed text-zinc-600 dark:text-zinc-300">
                {!! $this->history->description !!}
            </div>
        @else
            <p class="text-sm text-zinc-500">এই স্থানের বিস্তারিত বিবরণ এখনো যোগ করা হয়নি।</p>
        @endif
    </section>

    <flux:separator class="opacity-50 mt-16" />

    {{-- Action Buttons --}}
    <div class="flex gap-4 items-center justify-between">
        <div class="flex gap-4">
            <flux:button variant="subtle" wire:click="react('like')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-blue-600': {{ $this->history->hasReaction('like') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-up" class="size-4" />
                    {{ $this->history->countReaction('like') }}
                </div>
            </flux:button>

            <flux:button variant="subtle" wire:click="react('dislike')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-red-600': {{ $this->history->hasReaction('dislike') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-down" class="size-4" />
                    {{ $this->history->countReaction('dislike') }}
                </div>
            </flux:button>
        </div>

        <flux:button variant="subtle" size="sm" icon="share" data-share-button
            data-url="{{ route('bangladesh.history.show', $this->history->slug) }}"
            data-title="{{ $this->history->title }}">
            শেয়ার
        </flux:button>
    </div>

    {{-- Back --}}
    <div>
        <flux:button as="a" href="{{ route('bangladesh.history') }}" variant="ghost" icon="arrow-left"
            size="sm" aria-label="ঐতিহাসিক স্থান তালিকায় ফিরে যান">
            ঐতিহাসিক স্থান তালিকায় ফিরে যান
        </flux:button>
    </div>

    {{-- About --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-history-item">
        <h2 id="about-history-item" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            {{ $this->history->title }} সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>{{ $this->history->title }}</strong> হলো বাংলাদেশের একটি ঐতিহাসিক স্থান
                @if ($this->history->era)
                    ({{ $this->history->era }})
                @endif
                @if ($this->history->start_year || $this->history->end_year)
                    । সময়কাল: {{ $this->history->start_year ?? '?' }} – {{ $this->history->end_year ?? '?' }}
                @endif
                । উপরের বিবরণ অনুসরণ করে বিস্তারিত জানুন।
            </p>
        </div>
    </section>

    {{-- Comments --}}
    <div class="mt-4">
        <livewire:website.comments.comments-section :model="$this->history" />
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
                    <span>{{ $this->history->title }} কী?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    উপরের “বিস্তারিত বিবরণ” সেকশনে এই স্থানের পূর্ণাঙ্গ তথ্য লেখা আছে।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>অন্যান্য ঐতিহাসিক স্থান কোথায়?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    <a href="{{ route('bangladesh.history') }}" class="text-amber-600 hover:underline">
                        ঐতিহাসিক স্থান
                    </a>
                    তালিকায় ফিরে গিয়ে অন্যান্য স্থান দেখতে পারবেন।
                </div>
            </details>
        </div>
    </section>

</div>
