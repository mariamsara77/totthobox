<?php

use App\Models\TourismBd;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Flux\Flux;

new class extends Component {
    public $tourism;

    public function mount($tourism = null): void
    {
        $param = $tourism ?? (request()->route('tourism') ?? (request()->route('slug') ?? request()->route('id')));

        if (!$param) {
            abort(404);
        }

        $data = Cache::remember("tourism_bd_show_{$param}", 3600, function () use ($param) {
            return TourismBd::query()
                ->with(['division', 'district', 'thana', 'media'])
                ->where(function ($q) use ($param) {
                    $q->where('slug', $param)->orWhere('id', $param);
                })
                ->where('status', 1)
                ->first();
        });

        if (!$data) {
            abort(404);
        }

        $this->tourism = $data;
        views($this->tourism)->record();
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember("tourism_bd_creators_{$this->tourism->id}", now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', TourismBd::class)->where('subject_id', $this->tourism->id)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

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

        $this->tourism->react($type);
        $this->tourism->refresh();

        Flux::toast(text: 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
     বাংলাদেশের পর্যটন কেন্দ্র – SINGLE SHOW
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

    $typeLabel = $typeLabels[$this->tourism->tourism_type] ?? null;
@endphp

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="{{ $tourism->title }} | বাংলাদেশের পর্যটন কেন্দ্র | তথ্যবক্স"
        description="{{ Str::limit(strip_tags($tourism->description ?? $tourism->title . ' সম্পর্কে বিস্তারিত ভ্রমণ গাইড।'), 155) }}"
        keywords="{{ $tourism->title }}, বাংলাদেশ পর্যটন, {{ $typeLabel }}, ভ্রমণ গাইড, তথ্যবক্স"
        image="{{ $tourism->getFirstMediaUrl('tourism_images', 'thumb') ?? $tourism->getFirstMediaUrl('default', 'preview') }}" />

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('bangladesh.tourism') }}">
                পর্যটন কেন্দ্র
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $tourism->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </nav>

    {{-- Header --}}
    <div class="space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-2">
                @if ($typeLabel)
                    <flux:badge color="zinc" size="sm" variant="subtle">{{ $typeLabel }}</flux:badge>
                @endif

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-zinc-900 dark:text-white">
                    {{ $tourism->title ?? 'অজানা স্থান' }}
                </h1>

                <p class="text-sm text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                    <flux:icon.map-pin class="size-4" />
                    {{ $tourism->thana?->name ?? '...' }} • {{ $tourism->district?->name ?? '...' }} •
                    {{ $tourism->division?->name ?? '...' }}
                </p>

                <div class="flex items-center gap-2 flex-wrap">
                    <flux:badge icon="eye" size="sm" variant="subtle">
                        {{ bn_num(views($tourism)->count()) }}
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
            :media="$tourism->getMedia('tourism_images')->isNotEmpty() ? $tourism->getMedia('tourism_images') : $tourism->getMedia('default')"
            alt="{{ $tourism->title }}" />
    </div>

    {{-- Description --}}
    <section aria-labelledby="tourism-details-heading" class="space-y-3">
        <h2 id="tourism-details-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            বিস্তারিত বিবরণ
        </h2>

        @if ($tourism->description)
            <div class="prose dark:prose-invert max-w-none leading-relaxed text-zinc-600 dark:text-zinc-300">
                {!! $tourism->description !!}
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
                    :class="{ 'text-blue-600': {{ $tourism->hasReaction('like') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-up" class="size-4" />
                    {{ $tourism->countReaction('like') }}
                </div>
            </flux:button>

            <flux:button variant="subtle" wire:click="react('dislike')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-red-600': {{ $tourism->hasReaction('dislike') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-down" class="size-4" />
                    {{ $tourism->countReaction('dislike') }}
                </div>
            </flux:button>
        </div>

        <flux:button variant="subtle" size="sm" icon="share" data-share-button
            data-url="{{ route('bangladesh.tourism.show', $tourism->slug) }}" data-title="{{ $tourism->title }}">
            শেয়ার
        </flux:button>
    </div>

    {{-- Back --}}
    <div>
        <flux:button as="a" href="{{ route('bangladesh.tourism') }}" variant="ghost" icon="arrow-left"
            size="sm" aria-label="পর্যটন কেন্দ্র তালিকায় ফিরে যান">
            পর্যটন কেন্দ্র তালিকায় ফিরে যান
        </flux:button>
    </div>

    {{-- About --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-tourism-item">
        <h2 id="about-tourism-item" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            {{ $tourism->title }} সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>{{ $tourism->title }}</strong> হলো বাংলাদেশের একটি দর্শনীয় স্থান।
                @if ($typeLabel)
                    এটি <strong>{{ $typeLabel }}</strong> ধরনের পর্যটন কেন্দ্র।
                @endif
                উপরের বিবরণ অনুসরণ করে বিস্তারিত জানুন।
            </p>
        </div>
    </section>

    {{-- Comments --}}
    <div class="mt-4">
        <livewire:website.comments.comments-section :model="$tourism" />
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
                    <span>{{ $tourism->title }} কী?</span>
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
                    <span>অন্যান্য পর্যটন কেন্দ্র কোথায়?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    <a href="{{ route('bangladesh.tourism') }}" class="text-amber-600 hover:underline">
                        পর্যটন কেন্দ্র
                    </a>
                    তালিকায় ফিরে গিয়ে অন্যান্য স্থান দেখতে পারবেন।
                </div>
            </details>
        </div>
    </section>

</div>
