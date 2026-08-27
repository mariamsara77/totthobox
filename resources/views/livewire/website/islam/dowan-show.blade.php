<?php

use App\Models\Dowa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Flux\Flux;

new class extends Component {
    public Dowa $dowa;
    public ?string $audioUrl = null;

    public function mount(string $slug): void
    {
        $this->dowa = Cache::remember("dowa_show_{$slug}", 3600, function () use ($slug) {
            return Dowa::query()->active()->where('slug', $slug)->with('media')->firstOrFail();
        });

        $this->audioUrl = $this->dowa->getFirstMediaUrl('audio') ?: ($this->dowa->audio ? asset($this->dowa->audio) : null);

        views($this->dowa)->record();
    }

    #[Computed]
    public function shareableText(): string
    {
        return "✨ {$this->dowa->bangla_name} " . ($this->dowa->arabic_name ? "({$this->dowa->arabic_name})" : '') . " ✨\n\n" . "🕌 আরবি:\n{$this->dowa->arabic_text}\n\n" . "🗣️ উচ্চারণ:\n{$this->dowa->bangla_text}\n\n" . "📖 অর্থ:\n" . strip_tags($this->dowa->bangla_meaning ?? '');
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

        $this->dowa->react($type);
        $this->dowa->refresh();

        Flux::toast(text: 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
     DOWA – SINGLE SHOW
     SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6" x-data="{
    playing: false,
    audio: @js($audioUrl) ? new Audio(@js($audioUrl)) : null,
    copied: false,
    toggleAudio() {
        if (!this.audio) return;
        if (this.playing) {
            this.audio.pause();
            this.playing = false;
        } else {
            this.audio.play();
            this.playing = true;
            this.audio.onended = () => { this.playing = false };
        }
    },
    copyToClipboard() {
        navigator.clipboard.writeText(@js($this->shareableText));
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
    }
}">

    <x-seo title="{{ $dowa->bangla_name }} - আরবি, উচ্চারণ, অর্থ ও আমল | দোয়া সংগ্রহ"
        description="{{ Str::limit(strip_tags($dowa->bangla_meaning ?? ($dowa->bangla_text ?? $dowa->bangla_name)), 155) }}"
        keywords="{{ $dowa->bangla_name }}, bangla dowa, দোয়ার ফজিলত, প্রতিদিনের দোয়া, আরবি দোয়া ও আমল"
        image="{{ $dowa->getFirstMediaUrl('images') ?? null }}" />

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('islam.dowan') }}">দোয়া সংগ্রহ</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $dowa->bangla_name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </nav>

    {{-- Header --}}
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-4">
            @if ($dowa->is_featured)
                <flux:badge color="amber" icon="star" size="sm">বিশেষ আমল</flux:badge>
            @endif
            @if ($dowa->type)
                <flux:badge color="zinc" size="sm" variant="subtle">{{ $dowa->type }}</flux:badge>
            @endif
            <flux:badge icon="eye" size="sm" variant="subtle">
                {{ bn_num(views($dowa)->count()) }}
            </flux:badge>
        </div>

        <div class="text-center space-y-2">
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-zinc-900 dark:text-white">
                {{ $dowa->bangla_name }}
            </h1>
            @if ($dowa->arabic_name)
                <p class="text-xl font-serif text-emerald-600 dark:text-emerald-400 font-medium">
                    {{ $dowa->arabic_name }}
                </p>
            @endif
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                আরবি · উচ্চারণ · অর্থ ও ফজিলত
            </p>
        </div>
    </div>

    {{-- Audio Player --}}
    @if ($audioUrl)
        <div
            class="bg-zinc-400/10 rounded-2xl p-4 flex items-center justify-between border border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center gap-4">
                <flux:button x-on:click="toggleAudio" x-show="!playing" icon="play" variant="filled" size="sm"
                    aria-label="অডিও প্লে করুন" />
                <flux:button x-on:click="toggleAudio" x-show="playing" x-cloak icon="pause" variant="filled"
                    size="sm" aria-label="অডিও পজ করুন" />
                <div>
                    <p class="text-sm font-bold text-zinc-800 dark:text-zinc-200">দোয়াটির অডিও লিসেনিং</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5"
                        x-text="playing ? 'বর্তমানে প্লে হচ্ছে...' : 'শুনতে বাটনে ক্লিক করুন'"></p>
                </div>
            </div>
        </div>
    @endif

    {{-- Arabic Text --}}
    @if ($dowa->arabic_text)
        <flux:card dir="rtl" class="text-center text-3xl border-l-4 border-l-emerald-500 py-6">
            {{ $dowa->arabic_text }}
        </flux:card>
    @endif

    {{-- উচ্চারণ · অর্থ · ফজিলত --}}
    <section aria-labelledby="dowa-details-heading" class="space-y-4">
        <h2 id="dowa-details-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            বিস্তারিত বিবরণ
        </h2>

        @if ($dowa->bangla_text)
            <div class="">
                <h3 class="text-sm font-bold text-sky-600 dark:text-sky-400 mb-2 tracking-wide">উচ্চারণ</h3>
                <flux:card dir="rtl" class="text-center text-3xl border-l-4 border-l-blue-500 py-6">
                    {{ $dowa->bangla_text }}
                </flux:card>
            </div>
        @endif

        @if ($dowa->bangla_meaning)
            <div class="">
                <h3 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mb-2 tracking-wide">অনুবাদ ও অর্থ
                </h3>
                <flux:card dir="rtl" class="text-center text-3xl border-l-4 border-l-orange-500 py-6">
                    {!! $dowa->bangla_meaning !!}
                </flux:card>
            </div>
        @endif

        @if ($dowa->bangla_fojilot)
            <div
                class="rounded-2xl bg-amber-50/50 dark:bg-amber-950/10 border border-amber-200/60 dark:border-amber-900/40 p-5">
                <h3
                    class="text-sm font-bold text-amber-800 dark:text-amber-400 mb-3 tracking-wide flex items-center gap-4">
                    <flux:icon.information-circle class="size-4 text-amber-600 dark:text-amber-500" />
                    ফজিলত ও আমল
                </h3>
                <div
                    class="prose prose-sm dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 leading-relaxed">
                    {!! $dowa->bangla_fojilot !!}
                </div>
            </div>
        @endif

        @if (!$dowa->bangla_text && !$dowa->bangla_meaning && !$dowa->bangla_fojilot)
            <p class="text-sm text-zinc-500">এই দোয়ার বিস্তারিত বিবরণ এখনো যোগ করা হয়নি।</p>
        @endif
    </section>

    <flux:separator class="my-8" />

    {{-- Back --}}
    <div>
        <flux:link href="{{ route('islam.dowan') }}" icon="arrow-left" class="text-sm"
            aria-label="দোয়া সংগ্রহ তালিকায় ফিরে যান">
            দোয়া সংগ্রহ তালিকায় ফিরে যান
        </flux:link>
    </div>


    {{-- Action Buttons (Like / Dislike / Copy / Share) --}}
    <div class="flex flex-wrap gap-4 items-center justify-between">
        <div class="flex gap-4">
            {{-- LIKE --}}
            <flux:button variant="subtle" wire:click="react('like')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-blue-600': {{ $dowa->hasReaction('like') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-up" class="size-4" />
                    {{ $dowa->countReaction('like') }}
                </div>
            </flux:button>

            {{-- DISLIKE --}}
            <flux:button variant="subtle" wire:click="react('dislike')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-red-600': {{ $dowa->hasReaction('dislike') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-down" class="size-4" />
                    {{ $dowa->countReaction('dislike') }}
                </div>
            </flux:button>
        </div>

        <div class="flex gap-4">
            {{-- Copy --}}
            <flux:button variant="subtle" size="sm" x-on:click="copyToClipboard"
                x-bind:class="copied ? '!text-emerald-600 dark:!text-emerald-400' : ''">
                <span x-show="!copied" class="flex items-center gap-2">
                    <flux:icon.square-2-stack class="size-4" /> কপি
                </span>
                <span x-show="copied" x-cloak class="flex items-center gap-2">
                    <flux:icon.check class="size-4" /> কপি হয়েছে
                </span>
            </flux:button>

            {{-- Share --}}
            <flux:button variant="subtle" size="sm" icon="share" data-share-button
                data-url="{{ route('islam.dowan.show', $dowa->slug) }}" data-title="{{ $dowa->bangla_name }}">
                শেয়ার
            </flux:button>
        </div>
    </div>


    {{-- About --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-dowa">
        <h2 id="about-dowa" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            {{ $dowa->bangla_name }} সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>{{ $dowa->bangla_name }}</strong>
                @if ($dowa->arabic_name)
                    (<span class="font-serif">{{ $dowa->arabic_name }}</span>)
                @endif
                একটি গুরুত্বপূর্ণ ইসলামিক দোয়া/আমল। উপরের আরবি পাঠ, উচ্চারণ, অর্থ ও ফজিলত অনুসরণ করে নিয়মিত পাঠ করুন।
            </p>
        </div>
    </section>

    {{-- Comments --}}
    <div class="mt-4">
        <livewire:website.comments.comments-section :model="$dowa" />
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
                    <span>{{ $dowa->bangla_name }} কখন পড়বেন?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    উপরের “ফজিলত ও আমল” সেকশনে এই দোয়ার উপযুক্ত সময় ও নিয়ম লেখা আছে। নিয়মিত পাঠ করলে বেশি উপকার পাওয়া
                    যায়।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>অন্যান্য দোয়া কোথায় পাব?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    <a href="{{ route('islam.dowan') }}" class="text-emerald-600 hover:underline">
                        দোয়া সংগ্রহ
                    </a>
                    তালিকায় ফিরে গিয়ে আরও অনেক প্রয়োজনীয় দোয়া ও আমল দেখতে পারবেন।
                </div>
            </details>
        </div>
    </section>

</div>
