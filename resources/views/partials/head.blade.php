<meta charset="utf-8" />
<meta name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
<meta name="color-scheme" content="light dark">

<title>{{ $title ?? config('app.name') }}</title>

<!-- Basic Meta Tags -->
<meta name="description"
    content="{{ $description ?? 'Welcome to Totthobox, your personal space for managing tasks and projects.' }}" />
<meta name="keywords"
    content="Totthobox, tasks, projects, management, personal space, AI, knowledge management, productivity, বাংলাদেশ, বাংলা" />
<meta name="author" content="Totthobox Team" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="index, follow" />

<!-- Google Site Verification -->
@if (config('services.google.site_verification'))
    <meta name="google-site-verification" content="{{ config('services.google.site_verification') }}" />
@endif

<!-- Windows Specific -->
<meta name="application-name" content="{{ config('app.name') }}" />
<meta name="msapplication-TileColor" content="#007bff" />
<meta name="msapplication-TileImage" content="{{ asset('mstile-144x144.png') }}" />
<meta name="msapplication-square70x70logo" content="{{ asset('mstile-70x70.png') }}" />
<meta name="msapplication-square150x150logo" content="{{ asset('mstile-150x150.png') }}" />
<meta name="msapplication-wide310x150logo" content="{{ asset('mstile-310x150.png') }}" />
<meta name="msapplication-square310x310logo" content="{{ asset('mstile-310x310.png') }}" />
<meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}" />

<!-- PWA Meta Tags -->
<meta name="theme-color" content="#555">
<meta name="theme-color" content="#ff4500" media="(prefers-color-scheme: dark)">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">

<!-- Cache Control for PWA -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

<!-- Apple Touch Icons -->
<link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

<!-- Splash Screens for iOS -->
<link rel="apple-touch-startup-image" href="{{ asset('splashscreens/iphone5_splash.png') }}"
    media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('splashscreens/iphone6_splash.png') }}"
    media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('splashscreens/iphoneplus_splash.png') }}"
    media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('splashscreens/iphonex_splash.png') }}"
    media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('splashscreens/iphonexr_splash.png') }}"
    media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('splashscreens/iphonexsmax_splash.png') }}"
    media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('splashscreens/ipad_splash.png') }}"
    media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('splashscreens/ipadpro1_splash.png') }}"
    media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('splashscreens/ipadpro2_splash.png') }}"
    media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">

<!-- Web App Manifest -->
<link rel="manifest" href="{{ asset('manifest.json') }}">
<link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $title ?? config('app.name') }}">
<meta property="og:description"
    content="{{ $description ?? 'Welcome to Totthobox, your personal space for managing tasks and projects.' }}">
<meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ config('app.name') }}">
<meta property="og:locale" content="bn_BD">
<meta property="og:site_name" content="{{ config('app.name') }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="{{ $title ?? config('app.name') }}">
<meta property="twitter:description"
    content="{{ $description ?? 'Welcome to Totthobox, your personal space for managing tasks and projects.' }}">
<meta property="twitter:image" content="{{ asset('images/twitter-image.jpg') }}">
<meta property="twitter:image:alt" content="{{ config('app.name') }}">

<!-- Additional SEO -->
<meta name="twitter:creator" content="@totthobox">
<meta name="twitter:site" content="@totthobox">
<link rel="canonical" href="{{ url()->current() }}">
<meta name="copyright" content="Totthobox">
<meta name="language" content="Bangla">
<meta name="coverage" content="Worldwide">
<meta name="distribution" content="Global">
<meta name="rating" content="General">
<meta name="revisit-after" content="7 days">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&family=Baloo+Da+2:wght@400..800&family=Noto+Sans+Bengali:wght@100..900&family=Noto+Serif+Bengali:wght@100..900&display=swap"
    rel="stylesheet">
<link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet">

<!-- Styles and Scripts -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance



{{-- Setting modal --}}
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

        <section class="space-y-3">
            <flux:label>ভাষা (Language)</flux:label>

            <div wire:ignore x-data="{ 
            currentLang: (document.cookie.match(/googtrans=\/bn\/([^;]+)/) || [null, 'bn'])[1] 
        }" x-init="$watch('currentLang', value => window.changeAppLanguage(value))">

                <flux:select x-model="currentLang">
                    <flux:select.option value="bn">বাংলা</flux:select.option>
                    <flux:select.option value="en">English</flux:select.option>
                    <flux:select.option value="hi">हिन्दी (Hindi)</flux:select.option>
                    <flux:select.option value="ar">العربية (Arabic)</flux:select.option>
                    <flux:select.option value="fr">Français (French)</flux:select.option>
                    <flux:select.option value="es">Español (Spanish)</flux:select.option>
                </flux:select>

                {{-- Hidden Google Element --}}
                <div id="google_translate_element" style="display:none !important;"></div>
            </div>
        </section>

        <flux:separator />

        <div class="flex justify-end pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">বন্ধ করুন</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>
{{-- Setting modal --}}


{{-- PWA Smart Bar --}}
<div id="pwa-smart-bar"
    class="lg:hidden md:hidden fixed bottom-6 inset-x-4 z-[9999] transition-all duration-500 transform translate-y-10 opacity-0 pointer-events-none">
    <div
        class="max-w-md mx-auto bg-white/80 dark:bg-zinc-900/90 backdrop-blur-2xl border border-zinc-200/50 dark:border-zinc-800/50 shadow-2xl rounded-3xl p-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                <flux:icon.plus class="text-white w-6 h-6" />
            </div>
            <div class="flex-1 min-w-0">
                <flux:heading size="sm">তথ্যবক্স অ্যাপ</flux:heading>
                <flux:text size="xs">হোম স্ক্রিনে যুক্ত করে দ্রুত ব্যবহার করুন</flux:text>
            </div>
        </div>

        <flux:button id="btn-pwa-install" variant="primary" size="sm">ইনস্টল করুন
        </flux:button>
    </div>
</div>
{{-- PWA Button --}}