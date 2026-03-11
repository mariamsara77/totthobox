<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<meta name="color-scheme" content="light dark">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- ডাইনামিক এসইও মেটা এখান থেকে আসবে --}}
@stack('seo_meta')

{{-- ব্যাকআপ টাইটেল: যদি কোনো পেজে <x-seo /> কম্পোনেন্ট না থাকে --}}
@if(!session()->pull('seo_applied'))
    <title>@yield('title', config('app.name', 'Totthobox'))</title>
@endif

<meta name="author" content="Totthobox Team" />
<meta name="robots" content="index, follow" />

{{-- Favicons - asset() ব্যবহার করা হয়েছে যাতে পাথ মিস না হয় --}}
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
<link rel="icon" type="image/png" href="{{ asset('favicon-96x96.png') }}" sizes="96x96" />
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}" />
<meta name="apple-mobile-web-app-title" content="Totthobox" />

<meta property="fb:app_id" content="888294060752536" />

{{-- PWA & Manifest --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">

{{-- Theme Color --}}
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#18181b" media="(prefers-color-scheme: dark)">

{{-- Vite Assets --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<script>
    // থিম পরিবর্তনের সাথে মেটা ট্যাগ আপডেট
    const updateThemeColor = () => {
        const isDark = document.documentElement.classList.contains('dark');
        const color = isDark ? '#18181b' : '#ffffff';
        document.querySelector('meta[name="theme-color"]').setAttribute('content', color);
    };

    const observer = new MutationObserver(updateThemeColor);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
</script>

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
            @livewire('global.translator')
        </div>
        <div class="flex justify-end pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">বন্ধ করুন</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>

{{-- PWA Smart Bar --}}
<div id="pwa-smart-bar" class="lg:hidden fixed bottom-6 inset-x-4 z-[9999] opacity-0 translate-y-10 transition-all duration-500 pointer-events-none">
    <div class="max-w-md mx-auto bg-white/90 dark:bg-zinc-900/90 backdrop-blur-xl border border-zinc-200/50 dark:border-zinc-800/50 shadow-2xl rounded-3xl p-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                <flux:icon.plus class="text-white w-6 h-6" />
            </div>
            <div class="flex-1 min-w-0">
                <flux:heading size="sm">তথ্যবক্স অ্যাপ</flux:heading>
                <flux:text size="xs" class="truncate">হোম স্ক্রিনে যুক্ত করে দ্রুত ব্যবহার করুন</flux:text>
            </div>
        </div>
        <flux:button id="btn-pwa-install" variant="primary" size="sm" class="flex-shrink-0">ইনস্টল</flux:button>
    </div>
</div>