<meta charset="utf-8" />
{{-- ১. অ্যাপ যেন জুম না হয় এবং ফোনের ফুল স্ক্রিন জুড়ে থাকে (Modern & Native Viewport) --}}
<meta name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">

<meta name="color-scheme" content="light dark">
<meta name="csrf-token" content="{{ csrf_token() }}">


<title>{{ $title ?? config('app.name') }}</title>

<meta name="description"
    content="{{ $description ?? 'Welcome to Totthobox, your personal space for managing tasks and projects.' }}" />
<meta name="author" content="Totthobox Team" />
<meta name="robots" content="index, follow" />

<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-title" content="Totthobox" />

{{-- ২. PWA & Manifest Connection --}}
<link rel="manifest" href="/manifest.json">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="theme-color" content="#007bff">


{{-- ৪. Vite & Flux --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

{{-- Fonts --}}
<link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet">

{{-- Setting modal --}}
<flux:modal name="settings" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">ডিসপ্লে এবং সেটিংস</flux:heading>
            <flux:subheading>আপনার পছন্দ অনুযায়ী ইন্টারফেস সেট করুন।</flux:subheading>
        </div>

        <flux:separator />

        {{-- Theme Selection --}}
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
<div id="pwa-smart-bar"
    class="lg:hidden md:hidden fixed bottom-6 inset-x-4 z-[9999] transition-all duration-500 transform translate-y-10 opacity-0 pointer-events-none">
    <div
        class="max-w-md mx-auto bg-white/80 dark:bg-zinc-900/90 backdrop-blur-2xl border border-zinc-200/50 dark:border-zinc-800/50 shadow-2xl rounded-3xl p-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                {{-- এখানে আপনার লোগো বা আইকনটি দিন --}}
                <flux:icon.plus class="text-white w-6 h-6" />
            </div>
            <div class="flex-1 min-w-0">
                <flux:heading size="sm">তথ্যবক্স অ্যাপ</flux:heading>
                <flux:text size="xs" class="whitespace-nowrap overflow-hidden text-ellipsis">হোম স্ক্রিনে যুক্ত করে
                    দ্রুত ব্যবহার করুন</flux:text>
            </div>
        </div>

        <flux:button id="btn-pwa-install" variant="primary" size="sm" class="flex-shrink-0">ইনস্টল</flux:button>
    </div>
</div>