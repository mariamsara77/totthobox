<?php

use App\Models\AppResource;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Flux\Flux;

new class extends Component {
    public $app;

    public function mount($app = null): void
    {
        $param = $app ?? (request()->route('app') ?? (request()->route('slug') ?? request()->route('id')));

        if (!$param) {
            abort(404);
        }

        $data = Cache::remember("app_resource_show_{$param}", 3600, function () use ($param) {
            return AppResource::query()
                ->with(['media'])
                ->where(function ($q) use ($param) {
                    $q->where('slug', $param)->orWhere('id', $param);
                })
                ->first();
        });

        if (!$data) {
            abort(404);
        }

        $this->app = $data;
        views($this->app)->record();
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember("app_resource_creators_{$this->app->id}", now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', AppResource::class)->where('subject_id', $this->app->id)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get();
        });
    }

    public function download()
    {
        $this->app->increment('download_count');

        return redirect()->away($this->app->download_type === 'external' ? $this->app->external_url : $this->app->getFirstMediaUrl('app_files'));
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

        $this->app->react($type);
        $this->app->refresh();

        Flux::toast(text: 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
     SOFTWARE DETAIL PAGE – SINGLE SHOW
     SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="{{ $this->app->name }} v{{ $this->app->version }} Free Download | Safe & Verified | তথ্যবক্স"
        description="Download {{ $this->app->name }} v{{ $this->app->version }} for {{ $this->app->platform }} for free on Totthobox. 100% safe, fast, and verified direct download."
        keywords="{{ $this->app->name }} free download, {{ $this->app->name }} {{ $this->app->platform }}, download {{ $this->app->name }} safe, totthobox software"
        image="{{ $this->app->getFirstMediaUrl('app_icons', 'thumb') ?? $this->app->getFirstMediaUrl('app_icons') }}" />

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('software.all') ?? '/software/all' }}">
                Digital Resource Library
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $this->app->name }} v{{ $this->app->version }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </nav>

    {{-- Header --}}
    <div class="space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-2">
                <div class="flex flex-wrap gap-4">
                    @if ($this->app->platform)
                        <flux:badge color="zinc" size="sm" variant="subtle">{{ $this->app->platform }}
                        </flux:badge>
                    @endif
                    @if ($this->app->version)
                        <flux:badge color="zinc" size="sm" variant="subtle">v{{ $this->app->version }}
                        </flux:badge>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-zinc-900 dark:text-white">
                    {{ $this->app->name ?? 'Unknown App' }}
                </h1>

                <div class="flex items-center gap-2 flex-wrap">
                    <flux:badge icon="arrow-down-tray" size="sm" variant="subtle" color="green">
                        {{ bn_num($this->app->download_count ?? 0) }}+ Downloads
                    </flux:badge>
                    <flux:badge icon="eye" size="sm" variant="subtle">
                        {{ bn_num(views($this->app)->count()) }}
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

    {{-- App Icon / Media --}}
    <div class="flex justify-center">
        <flux:avatar
            src="{{ $this->app->getFirstMediaUrl('app_icons', 'thumb') ?: $this->app->getFirstMediaUrl('app_icons') }}"
            size="xl" class="rounded-2xl border border-zinc-400/25" name="{{ $this->app->name }}" />
    </div>

    {{-- Download Button --}}
    <flux:button wire:click="download" variant="primary" icon="arrow-down-tray" class="w-full"
        aria-label="{{ $this->app->name }} for {{ $this->app->platform }} ফ্রি ডাউনলোড করুন">
        Download {{ $this->app->name }} for {{ $this->app->platform }} (Free)
    </flux:button>

    {{-- Description --}}
    <section aria-labelledby="how-to-heading" class="space-y-3">
        <h2 id="how-to-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            How to download and install {{ $this->app->name }}
        </h2>

        @if ($this->app->description)
            <div class="prose dark:prose-invert max-w-none leading-relaxed text-zinc-600 dark:text-zinc-300">
                {!! function_exists('linkify') ? linkify($this->app->description) : $this->app->description !!}
            </div>
        @else
            <p class="text-sm text-zinc-500">এই অ্যাপের বিস্তারিত নির্দেশনা এখনো যোগ করা হয়নি।</p>
        @endif
    </section>

    {{-- Password Box --}}
    @if ($this->app->download_password)
        <section
            class="bg-blue-50 dark:bg-blue-900/20 p-5 rounded-xl border border-blue-200 dark:border-blue-800 flex items-center gap-4"
            aria-labelledby="password-heading">
            <div class="bg-blue-500 p-2 rounded-lg text-white" aria-hidden="true">
                <flux:icon name="key" />
            </div>
            <div>
                <h2 id="password-heading" class="font-bold text-zinc-800 dark:text-zinc-200">Archive Password</h2>
                <p class="font-mono text-xl text-zinc-700 dark:text-zinc-300">{{ $this->app->download_password }}</p>
            </div>
        </section>
    @endif

    {{-- Action Buttons --}}
    <div class="flex gap-4 items-center justify-between">
        <div class="flex gap-4">
            <flux:button variant="subtle" wire:click="react('like')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-blue-600': {{ $this->app->hasReaction('like') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-up" class="size-4" />
                    {{ $this->app->countReaction('like') }}
                </div>
            </flux:button>

            <flux:button variant="subtle" wire:click="react('dislike')" size="sm">
                <div class="flex items-center gap-2"
                    :class="{ 'text-red-600': {{ $this->app->hasReaction('dislike') ? 'true' : 'false' }} }">
                    <flux:icon name="thumb-down" class="size-4" />
                    {{ $this->app->countReaction('dislike') }}
                </div>
            </flux:button>
        </div>

        <flux:button variant="subtle" size="sm" icon="share" data-share-button
            data-url="{{ route('software.show', $this->app->slug) }}" data-title="{{ $this->app->name }}">
            শেয়ার
        </flux:button>
    </div>

    {{-- Back --}}
    <div>
        <flux:button as="a" href="{{ route('software.all') ?? '/software/all' }}" variant="ghost"
            icon="arrow-left" size="sm" aria-label="Digital Resource Library তে ফিরে যান">
            Digital Resource Library তে ফিরে যান
        </flux:button>
    </div>

    {{-- About --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-download">
        <h2 id="about-download" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            {{ $this->app->name }} ফ্রি ডাউনলোড সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>{{ $this->app->name }}</strong> (Version {{ $this->app->version }})
                {{ $this->app->platform }} প্ল্যাটফর্মের
                জন্য Totthobox থেকে ফ্রি ডাউনলোড করুন। ফাইলটি ভেরিফাইড এবং ম্যালওয়্যার-ফ্রি।
            </p>
            <p>
                উপরের ডাউনলোড বাটনে ক্লিক করে সরাসরি ফাইল নিতে পারবেন।
                @if ($this->app->download_password)
                    আর্কাইভ পাসওয়ার্ড প্রয়োজন হলে উপরের পাসওয়ার্ড বক্স থেকে কপি করুন।
                @endif
            </p>
        </div>
    </section>

    {{-- Comments --}}
    <div class="mt-4">
        <livewire:website.comments.comments-section :model="$this->app" />
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
                    <span>{{ $this->app->name }} কি ফ্রি?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    হ্যাঁ। Totthobox থেকে {{ $this->app->name }} সম্পূর্ণ ফ্রি ডাউনলোড করা যায়।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>ডাউনলোড নিরাপদ কি?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    হ্যাঁ। ফাইলটি ভেরিফাইড এবং ম্যালওয়্যার-ফ্রি হিসেবে চিহ্নিত। তবে ডাউনলোডের পর নিজের অ্যান্টিভাইরাস
                    দিয়ে স্ক্যান করার পরামর্শ দেওয়া হয়।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>কোন প্ল্যাটফর্মের জন্য?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    এই ভার্সনটি <strong>{{ $this->app->platform }}</strong> প্ল্যাটফর্মের জন্য।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>কীভাবে ইনস্টল করব?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    উপরের “How to download and install” সেকশনে বিস্তারিত নির্দেশনা দেওয়া আছে।
                    ডাউনলোড করে ফাইলটি রান/এক্সট্রাক্ট করুন এবং নির্দেশনা অনুসরণ করুন।
                </div>
            </details>
        </div>
    </section>

</div>
