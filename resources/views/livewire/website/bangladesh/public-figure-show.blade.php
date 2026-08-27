<?php

use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Flux\Flux;

new class extends Component {
    public $person;

    public function mount($person = null): void
    {
        $param = $person ?? (request()->route('person') ?? (request()->route('slug') ?? request()->route('id')));

        if (!$param) {
            abort(404);
        }

        $data = Cache::remember("person_profile_show_{$param}", 3600, function () use ($param) {
            return Person::query()
                ->with(['media', 'peopleCategories:id,name', 'histories' => fn($q) => $q->with('position:id,title')->orderByDesc('is_current')->orderByDesc('from_date')])
                ->where(function ($q) use ($param) {
                    $q->where('slug', $param)->orWhere('id', $param);
                })
                ->first();
        });

        if (!$data) {
            abort(404);
        }

        $this->person = $data;
        views($this->person)->record();
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember("person_creators_{$this->person->id}", now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', Person::class)->where('subject_id', $this->person->id)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

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

        $this->person->react($type);
        $this->person->refresh();

        Flux::toast(text: 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
     প্রোফাইল আর্কাইভ – SINGLE SHOW
     SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

@php
    $currentRole = $this->person->histories->firstWhere('is_current', true);
@endphp

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="{{ $this->person->name }} | প্রোফাইল আর্কাইভ | তথ্যবক্স"
        description="{{ Str::limit(strip_tags($this->person->bio ?? $this->person->name . ' এর জীবনবৃত্তান্ত ও কর্মজীবনের বিস্তারিত তথ্য।'), 155) }}"
        keywords="{{ $this->person->name }}, প্রোফাইল, জীবনী, কর্মজীবন, তথ্যবক্স"
        image="{{ $this->person->getFirstMediaUrl('images', 'thumb') ?? $this->person->getFirstMediaUrl('images') }}" />

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('bangladesh.public-figure') }}">
                প্রোফাইল আর্কাইভ
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $this->person->name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </nav>

    {{-- Header --}}
    <div class="space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-2">
                <div class="flex flex-wrap gap-4">
                    @foreach ($this->person->peopleCategories as $cat)
                        <flux:badge color="zinc" size="sm" variant="subtle">{{ $cat->name }}</flux:badge>
                    @endforeach

                    @if ($currentRole)
                        <flux:badge color="green" size="sm" variant="subtle">বর্তমানে কর্মরত</flux:badge>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-zinc-900 dark:text-white">
                    {{ $this->person->name ?? 'অজানা' }}
                </h1>

                @if ($currentRole)
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                        <flux:icon.briefcase class="size-4" />
                        {{ $currentRole->position?->title ?? ($currentRole->custom_role ?? 'পদবী অজানা') }}
                        @if ($currentRole->from_date)
                            <span class="text-zinc-400">• {{ $currentRole->from_date->format('Y') }} থেকে</span>
                        @endif
                    </p>
                @endif

                <div class="flex items-center gap-2 flex-wrap">
                    <flux:badge icon="eye" size="sm" variant="subtle">
                        {{ bn_num(views($this->person)->count()) }}
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
            :media="$this->person->getMedia('images')->isNotEmpty() ? $this->person->getMedia('images') : $this->person->getMedia('default')"
            alt="{{ $this->person->name }}" />
    </div>

    {{-- Bio / Description --}}
    <section aria-labelledby="person-bio-heading" class="space-y-3">
        <h2 id="person-bio-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            জীবন বৃত্তান্ত
        </h2>

        @if ($this->person->bio)
            <div class="prose dark:prose-invert max-w-none leading-relaxed text-zinc-600 dark:text-zinc-300">
                {!! $this->person->bio !!}
            </div>
        @else
            <p class="text-sm text-zinc-500">এই ব্যক্তির বিস্তারিত জীবনবৃত্তান্ত এখনো যোগ করা হয়নি।</p>
        @endif
    </section>

    {{-- Career History (optional extra section) --}}
    {{-- @if ($this->person->histories->isNotEmpty())
        <section aria-labelledby="career-heading" class="space-y-3">
            <h2 id="career-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
                কর্মজীবনের ইতিহাস
            </h2>
            <div class="space-y-2">
                @foreach ($this->person->histories as $history)
                    <div
                        class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-400/25">
                        <div class="flex items-center gap-4">
                            @if ($history->is_current)
                                <flux:badge size="xs" color="green" variant="subtle">বর্তমান</flux:badge>
                            @endif
                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $history->position?->title ?? ($history->custom_role ?? 'পদবী অজানা') }}
                            </span>
                        </div>
                        <span class="text-xs text-zinc-500">
                            {{ $history->from_date?->format('Y') ?? '—' }}
                            @if ($history->to_date)
                                – {{ $history->to_date->format('Y') }}
                            @elseif ($history->is_current)
                                – বর্তমান
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif --}}
    <flux:separator class="opacity-50 mt-16" />
    {{-- Action Buttons --}}
    <div class="flex gap-4 items-center justify-between">
        <div class="flex gap-4">
            <flux:button variant="subtle" wire:click="react('like')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-blue-600': {{ $this->person->hasReaction('like') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-up" class="size-4" />
                    {{ $this->person->countReaction('like') }}
                </div>
            </flux:button>

            <flux:button variant="subtle" wire:click="react('dislike')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-red-600': {{ $this->person->hasReaction('dislike') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-down" class="size-4" />
                    {{ $this->person->countReaction('dislike') }}
                </div>
            </flux:button>
        </div>

        <flux:button variant="subtle" size="sm" icon="share" data-share-button
            data-url="{{ route('bangladesh.public-figure.show', $this->person->slug) }}"
            data-title="{{ $this->person->name }}">
            শেয়ার
        </flux:button>
    </div>

    {{-- Back --}}
    <div>
        <flux:button as="a" href="{{ route('bangladesh.public-figure') }}" variant="ghost" icon="arrow-left"
            size="sm" aria-label="প্রোফাইল আর্কাইভে ফিরে যান">
            প্রোফাইল আর্কাইভে ফিরে যান
        </flux:button>
    </div>

    {{-- About --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-person-item">
        <h2 id="about-person-item" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            {{ $this->person->name }} সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>{{ $this->person->name }}</strong> হলো বাংলাদেশের একজন বিশিষ্ট ব্যক্তিত্ব।
                উপরের জীবনবৃত্তান্ত ও কর্মজীবনের ইতিহাস অনুসরণ করে বিস্তারিত জানুন।
            </p>
        </div>
    </section>

    {{-- Comments --}}
    <div class="mt-4">
        <livewire:website.comments.comments-section :model="$this->person" />
    </div>

    {{-- FAQ --}}
    <section class="space-y-3" aria-labelledby="faq-heading">
        <h2 id="faq-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            প্রায়শাই জিজ্ঞাসিত প্রশ্ন
        </h2>

        <div class="space-y-2">
            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>{{ $this->person->name }} কী?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    উপরের “জীবন বৃত্তান্ত” সেকশনে এই ব্যক্তির পূর্ণাঙ্গ তথ্য লেখা আছে।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>অন্যান্য প্রোফাইল কোথায়?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    <a href="{{ route('bangladesh.public-figure') }}" class="text-amber-600 hover:underline">
                        প্রোফাইল আর্কাইভ
                    </a>
                    তালিকায় ফিরে গিয়ে অন্যান্য ব্যক্তিদের প্রোফাইল দেখতে পারবেন।
                </div>
            </details>
        </div>
    </section>

</div>
