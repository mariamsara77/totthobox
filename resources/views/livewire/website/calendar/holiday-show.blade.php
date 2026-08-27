<?php

use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Spatie\Activitylog\Models\Activity;
use Flux\Flux;

new class extends Component {
    public $holiday;

    public function mount($holiday = null): void
    {
        $param = $holiday ?? (request()->route('holiday') ?? (request()->route('slug') ?? request()->route('id')));

        if (!$param) {
            abort(404);
        }

        $data = Cache::remember("holiday_show_{$param}", 3600, function () use ($param) {
            return Holiday::query()
                ->where(function ($q) use ($param) {
                    $q->where('slug', $param)->orWhere('id', $param);
                })
                ->first();
        });

        if (!$data) {
            abort(404);
        }

        $this->holiday = $data;
        views($this->holiday)->record();
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember("holiday_creators_{$this->holiday->id}", now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', Holiday::class)->where('subject_id', $this->holiday->id)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

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

        $this->holiday->react($type);
        $this->holiday->refresh();

        Flux::toast(text: 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }

    private function generateSeoData()
    {
        $date = Carbon::parse($this->holiday->date)->format('d F Y');
        $dayName = bn_day(Carbon::parse($this->holiday->date)->format('l'));
        $monthName = Carbon::parse($this->holiday->date)->translatedFormat('F');
        $year = Carbon::parse($this->holiday->date)->format('Y');

        $title = "{$this->holiday->title} – {$date} | ছুটির ক্যালেন্ডার";
        $description = "{$this->holiday->title} ({$date}) – বাংলাদেশের {$this->holiday->type} ছুটি। বিস্তারিত তারিখ, বার এবং বর্ণনা জানুন।";

        $keywords = implode(', ', array_filter([$this->holiday->title, "{$this->holiday->title} {$year}", "{$this->holiday->title} তারিখ", "{$this->holiday->title} কবে", "{$this->holiday->title} কোন দিন", "{$this->holiday->title} ছুটি", $this->holiday->type, "{$this->holiday->type} ছুটি", "বাংলাদেশের {$this->holiday->type} ছুটি", 'সরকারি ছুটি', 'সরকারি ছুটির তালিকা', "সরকারি ছুটি {$year}", 'ছুটির তালিকা', 'ছুটির ক্যালেন্ডার', 'বাংলাদেশের ছুটির ক্যালেন্ডার', 'বাংলাদেশের সরকারি ছুটি', 'জাতীয় ছুটি', 'ধর্মীয় ছুটি', 'বাংলাদেশ ক্যালেন্ডার', "{$monthName} মাসের ছুটি", "{$year} সালের ছুটি", "{$dayName} ছুটি", 'holiday calendar Bangladesh', 'Bangladesh public holidays', "{$this->holiday->title} Bangladesh"]));

        return [
            'title' => "{$title}",
            'description' => $description,
            'keywords' => $keywords,
        ];
    }

    public function with(): array
    {
        return [
            'seo' => $this->generateSeoData(),
        ];
    }
}; ?>

{{-- ═══════════════════════════════════════════════════════════════
ছুটির ক্যালেন্ডার – SINGLE SHOW
SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="{{ $seo['title'] }}" description="{{ $seo['description'] }}" keywords="{{ $seo['keywords'] }}"
        image="{{ $holiday->getFirstMediaUrl('holiday_images') ?? $holiday->getFirstMediaUrl('default', 'preview') }}" />

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('calendar.holiday') }}">
                ছুটির ক্যালেন্ডার
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $holiday->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </nav>

    {{-- Header --}}
    <header class="space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-2">
                @if ($holiday->type)
                    <flux:badge color="zinc" size="sm" variant="subtle">
                        {{ $holiday->type }}
                    </flux:badge>
                @endif

                <flux:heading size="xl" level="1">
                    {{ $holiday->title ?? 'অজানা ছুটি' }}
                </flux:heading>

                <flux:text variant="subtle">
                    ছুটির ক্যালেন্ডার · বিস্তারিত তথ্য
                </flux:text>

                <div class="flex items-center gap-2 flex-wrap">
                    <flux:badge icon="eye" size="sm" variant="subtle">
                        {{ bn_num(views($holiday)->count()) }}
                    </flux:badge>
                </div>
            </div>

            {{-- Creators Tooltip --}}
            <div class="shrink-0">
                <flux:tooltip toggleable>
                    <flux:button icon="user" size="sm" variant="subtle" aria-label="তথ্য প্রদানকারীগণ দেখুন" />

                    <flux:tooltip.content class="w-80 space-y-4 p-4">
                        <div class="space-y-1">
                            <flux:heading size="lg">
                                তথ্য প্রদানকারী
                            </flux:heading>
                            <flux:text size="sm" variant="subtle">
                                এই কন্টেন্ট তৈরিতে অবদান রেখেছেন
                            </flux:text>
                        </div>

                        <div class="max-h-80 overflow-y-auto space-y-3">
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
                                                    <flux:icon.check-badge class="size-4 text-emerald-500"
                                                        variant="solid" />
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
                        </div>

                        <flux:separator />

                        <flux:text size="sm" variant="subtle" class="text-center">
                            আমাদের সকল তথ্য ভেরিফাইড এবং যাচাইকৃত।
                        </flux:text>
                    </flux:tooltip.content>
                </flux:tooltip>
            </div>
        </div>
    </header>

    {{-- Date Card --}}
    <flux:card class="flex gap-4">
        <div
            class="flex flex-col items-center justify-center w-16 h-16 rounded-xl bg-amber-50 dark:bg-amber-900/20 shrink-0">
            <span class="text-xs uppercase font-black text-amber-600 dark:text-amber-500">
                {{ Carbon::parse($holiday->date)->format('M') }}
            </span>
            <span class="text-2xl font-bold leading-none text-zinc-800 dark:text-zinc-100">
                {{ Carbon::parse($holiday->date)->format('d') }}
            </span>
        </div>

        <div class="space-y-0.5">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">তারিখ ও বার</p>
            <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                {{ Carbon::parse($holiday->date)->format('d F, Y') }}
            </p>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ bn_day(Carbon::parse($holiday->date)->format('l')) }}
            </p>
        </div>
    </flux:card>

    {{-- Large image --}}
    <div>
        <flux:media :media="$holiday->getMedia('holiday_images')" alt="{{ $holiday->title }}" />
    </div>

    {{-- Details --}}
    <section aria-labelledby="holiday-details-heading" class="space-y-3">
        <h2 id="holiday-details-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            বিস্তারিত বিবরণ
        </h2>

        @if ($holiday->details)
            <flux:text class="text-base">
                {!! $holiday->details !!}
            </flux:text>
        @else
            <p class="text-sm text-zinc-500">
                এই ছুটির দিন সম্পর্কে অতিরিক্ত কোনো তথ্য পাওয়া যায়নি।
            </p>
        @endif
    </section>

    {{-- Action Buttons (Reaction + Share) --}}
    <div class="flex gap-4 items-center justify-between">
        <div class="flex gap-4">
            <flux:button variant="subtle" wire:click="react('like')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-blue-600': {{ $holiday->hasReaction('like') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-up" class="size-4" />
                    {{ $holiday->countReaction('like') }}
                </div>
            </flux:button>

            <flux:button variant="subtle" wire:click="react('dislike')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-red-600': {{ $holiday->hasReaction('dislike') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-down" class="size-4" />
                    {{ $holiday->countReaction('dislike') }}
                </div>
            </flux:button>
        </div>

        <flux:button variant="subtle" size="sm" icon="share" data-share-button
            data-url="{{ route('calendar.holiday.show', $holiday->slug ?? $holiday->id) }}"
            data-title="{{ $holiday->title }}">
            শেয়ার
        </flux:button>
    </div>

    {{-- Back --}}
    <div>
        <flux:button as="a" href="{{ route('calendar.holiday') }}" variant="ghost" icon="arrow-left"
            size="sm" aria-label="ছুটির ক্যালেন্ডারে ফিরে যান">
            ছুটির ক্যালেন্ডারে ফিরে যান
        </flux:button>
    </div>

    {{-- About --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-holiday-item">
        <h2 id="about-holiday-item" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            {{ $holiday->title }} সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>{{ $holiday->title }}</strong> হলো বাংলাদেশের {{ $holiday->type ?? '' }} ছুটি।
                তারিখ: <strong>{{ Carbon::parse($holiday->date)->format('d F, Y') }}</strong>
                ({{ bn_day(Carbon::parse($holiday->date)->format('l')) }})।
            </p>
        </div>
    </section>

    {{-- Comments --}}
    <div class="mt-4">
        <livewire:website.comments.comments-section :model="$holiday" />
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
                    <span>{{ $holiday->title }} কী?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    উপরের “বিস্তারিত বিবরণ” সেকশনে এই ছুটির পূর্ণাঙ্গ ব্যাখ্যা লেখা আছে।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>অন্যান্য ছুটি কোথায় পাব?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    <a href="{{ route('calendar.holiday') }}" class="text-amber-600 hover:underline">
                        ছুটির ক্যালেন্ডার
                    </a>
                    তালিকায় ফিরে গিয়ে অন্যান্য ছুটি দেখতে পারবেন।
                </div>
            </details>
        </div>
    </section>

</div>
