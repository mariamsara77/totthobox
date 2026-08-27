<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light dark">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- ডাইনামিক এসইও --}}
@stack('seo_meta')
@if (!session()->get('seo_applied'))
    <title>@yield('title', config('app.name', 'Totthobox'))</title>
@endif

<meta name="author" content="Totthobox Team" />
<meta name="robots" content="index, follow" />
<link rel="canonical" href="{{ url()->current() }}">

<meta name="google-site-verification" content="1-VsthqfGvXga4zKLbfjBjP6L0UFc-xBQ_aOzn1g9Ps" />

{{-- Favicons - asset() নিশ্চিত করা হয়েছে --}}
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<meta name="apple-mobile-web-app-title" content="Totthobox" />

<meta property="fb:app_id" content="1108131871544005" />

{{-- PWA --}}

<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">

<meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">

{{-- Theme Color --}}
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#18181b" media="(prefers-color-scheme: dark)">

<meta name="google-adsense-account" content="ca-pub-9522604367420521">

{{-- Performance --}}
<link rel="preconnect" href="https://pagead2.googlesyndication.com" crossorigin>
<link rel="dns-prefetch" href="https://pagead2.googlesyndication.com">

<link rel="preload" href="{{ asset('fonts/SolaimanLipi-Normal.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('fonts/adorsholipi-exp.woff2') }}" as="font" type="font/woff2" crossorigin>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9522604367420521"
    crossorigin="anonymous"></script>


{{-- Setting Modal --}}
<flux:modal name="settings" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">ডিসপ্লে এবং সেটিংস</flux:heading>
            <flux:subheading>আপনার পছন্দ অনুযায়ী ইন্টারফেস সেট করুন।</flux:subheading>
        </div>
        <flux:separator />
        <section class="space-y-3">
            <flux:label>অ্যাপের থিম</flux:label>
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">লাইট</flux:radio>
                <flux:radio value="dark" icon="moon">ডার্ক</flux:radio>
                <flux:radio value="system" icon="computer-desktop">সিস্টেম</flux:radio>
            </flux:radio.group>
        </section>
        <div wire:ignore>
            <livewire:global.translator />
        </div>
        <div class="flex justify-end pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">বন্ধ করুন</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>


{{-- Android / Chrome - Native Bottom Sheet --}}
<div id="pwa-bar"
    class="sm:hidden fixed inset-x-0 bottom-0 z-50 hidden translate-y-full transition-transform duration-300 ease-out">
    <div class="bg-white dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700 rounded-t-4xl">
        <div class="px-4 pt-4 pb-4 flex items-center gap-2">

            {{-- App Icon --}}
            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center shrink-0 overflow-hidden">
                <flux:icon.brand class="size-7 text-primary" />
            </div>

            {{-- Text --}}
            <div class="flex-1 min-w-0">
                <flux:heading size="sm" class="leading-tight">Totthobox</flux:heading>
                <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400 mt-0.5">
                    হোম স্ক্রিনে যোগ করুন
                </flux:text>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 shrink-0">
                <flux:button id="pwa-install-btn" variant="primary" size="sm" class="rounded-full px-5">
                    ইনস্টল
                </flux:button>
                <button id="pwa-close-btn"
                    class="w-9 h-9 flex items-center justify-center rounded-full text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>
        </div>
    </div>
</div>


{{-- iOS - Native style floating card --}}
<div id="pwa-bar-ios"
    class="sm:hidden fixed bottom-6 inset-x-4 z-50 hidden opacity-0 translate-y-8 pointer-events-none transition-all duration-300 ease-out">
    <div
        class="max-w-md mx-auto bg-white dark:bg-zinc-800 backdrop-blur-2xl border border-zinc-400/25 rounded-2xl shadow-xl p-4 flex items-center gap-2">

        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center shrink-0">
            <flux:icon.brand class="size-7 text-primary" />
        </div>

        <div class="flex-1 min-w-0">
            <flux:heading size="sm" class="leading-tight">Totthobox</flux:heading>
            <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400 mt-0.5">
                Share → Add to Home Screen
            </flux:text>
        </div>

        <button id="pwa-close-btn"
            class="w-9 h-9 flex items-center justify-center rounded-full text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition shrink-0">
            <flux:icon.x-mark class="size-5" />
        </button>
    </div>
</div>
