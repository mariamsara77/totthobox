<?php

use App\Models\Sign;
use App\Models\SignCategory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Flux\Flux;

new class extends Component {
    public string $categorySlug;
    public $category;
    public $sign;

    public function mount(string $category, string $sign): void
    {
        $this->categorySlug = $category;

        $data = Cache::remember("sign_show_{$category}_{$sign}", 3600, function () use ($category, $sign) {
            $cat = SignCategory::where('slug', $category)->first();
            if (!$cat) {
                return null;
            }

            $signModel = $cat
                ->signs()
                ->with('media')
                ->where(function ($q) use ($sign) {
                    $q->where('slug', $sign)->orWhere('id', $sign);
                })
                ->first();

            if (!$signModel) {
                return null;
            }

            return [
                'category' => $cat,
                'sign' => $signModel,
            ];
        });

        if (!$data) {
            abort(404);
        }

        $this->category = $data['category'];
        $this->sign = $data['sign'];
        views($this->sign)->record();
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember("sign_creators_{$this->sign->id}", now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', Sign::class)->where('subject_id', $this->sign->id)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

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

        $this->sign->react($type);
        $this->sign->refresh();

        Flux::toast(text: 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
     TRAFFIC SIGN – SINGLE SHOW
     SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="{{ $sign->name }} | {{ $category->name }} | ট্রাফিক সাইন | তথ্যবক্স"
        description="{{ Str::limit(strip_tags($sign->description ?? $sign->name . ' ট্রাফিক সাইনের অর্থ ও ব্যবহার।'), 155) }}"
        keywords="{{ $sign->name }}, {{ $category->name }}, ট্রাফিক সাইন, রোড সাইন, ট্রাফিক চিহ্ন"
        image="{{ $sign->getFirstMediaUrl('images', 'thumb') ?? null }}" />

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('signs.sign', $category->slug) }}">
                {{ $category->name }} সাইন
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $sign->name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </nav>

    {{-- Header --}}
    <div class="space-y-4">

        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-2">
                <flux:badge color="zinc" size="sm" variant="subtle">{{ $category->name }}</flux:badge>

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-zinc-900 dark:text-white">
                    {{ $sign->name ?? 'অজানা সংকেত' }}
                </h1>

                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $category->name }} সাইন · অর্থ ও ব্যবহার
                </p>

                <div class="flex items-center gap-2 flex-wrap">
                    <flux:badge icon="eye" size="sm" variant="subtle">
                        {{ bn_num(views($sign)->count()) }}
                    </flux:badge>
                </div>
            </div>

            {{-- Creators Tooltip (individual for this sign) --}}
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
        <flux:media :media="$sign->getMedia('images')" alt="{{ $sign->name }} সাইন" />
    </div>


    {{-- Description --}}
    <section aria-labelledby="sign-details-heading" class="space-y-3">
        <h2 id="sign-details-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            বিস্তারিত বিবরণ
        </h2>

        @if ($sign->description)
            <div class="prose dark:prose-invert max-w-none text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">
                {!! $sign->description !!}
            </div>
        @else
            <p class="text-sm text-zinc-500">এই চিহ্নের বিস্তারিত বিবরণ এখনো যোগ করা হয়নি।</p>
        @endif
    </section>

    {{-- Action Buttons --}}
    <div class="flex gap-4 items-center justify-between">
        <div class="flex gap-4">
            {{-- LIKE --}}
            <flux:button variant="subtle" wire:click="react('like')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-blue-600': {{ $sign->hasReaction('like') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-up" class="size-4" />
                    {{ $sign->countReaction('like') }}
                </div>
            </flux:button>

            {{-- DISLIKE --}}
            <flux:button variant="subtle" wire:click="react('dislike')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-red-600': {{ $sign->hasReaction('dislike') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-down" class="size-4" />
                    {{ $sign->countReaction('dislike') }}
                </div>
            </flux:button>
        </div>

        <flux:button variant="subtle" size="sm" icon="share" data-share-button
            data-url="{{ route('signs.show', [
                'category' => $category->slug,
                'sign' => $sign->slug ?: $sign->id,
            ]) }}"
            data-title="{{ $sign->name }}">
            শেয়ার
        </flux:button>
    </div>

    {{-- Back --}}
    <div>
        <flux:button as="a" href="{{ route('signs.sign', $category->slug) }}" variant="ghost" icon="arrow-left"
            size="sm" aria-label="{{ $category->name }} তালিকায় ফিরে যান">
            {{ $category->name }} তালিকায় ফিরে যান
        </flux:button>
    </div>

    {{-- About --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-sign">
        <h2 id="about-sign" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            {{ $sign->name }} সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>{{ $sign->name }}</strong> হলো <strong>{{ $category->name }}</strong> ক্যাটাগরির একটি
                ট্রাফিক সাইন/রোড চিহ্ন। রাস্তায় এই চিহ্ন দেখলে উপরের নির্দেশনা অনুসরণ করুন।
            </p>
        </div>
    </section>

    {{-- Comments --}}
    <div class="mt-4">
        <livewire:website.comments.comments-section :model="$sign" />
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
                    <span>{{ $sign->name }} কী বোঝায়?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    উপরের “বিস্তারিত বিবরণ” সেকশনে এই চিহ্নের অর্থ ও ব্যবহার লেখা আছে।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>একই ক্যাটাগরির অন্য সাইন কোথায়?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    <a href="{{ route('signs.sign', $category->slug) }}" class="text-amber-600 hover:underline">
                        {{ $category->name }}
                    </a>
                    তালিকায় ফিরে গিয়ে অন্যান্য চিহ্ন দেখতে পারবেন।
                </div>
            </details>
        </div>
    </section>

</div>
