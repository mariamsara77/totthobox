<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">

    <flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900" x-data="{
            saveScroll() { localStorage.setItem('sidebar-scroll', $el.scrollTop); },
                loadScroll() { $el.scrollTop = localStorage.getItem('sidebar-scroll') || 0; }
        }" x-init="loadScroll();
        $el.addEventListener('scroll', saveScroll);
        // Livewire নেভিগেশনের সময় নতুন DOM এর সাথে সিঙ্ক করা
        document.addEventListener('livewire:navigated', () => {
            $nextTick(() => loadScroll());
        });">

        <flux:sidebar.header>
            <flux:sidebar.brand href="/" name="Totthobox" wire:navigate.hover>
                <flux:icon name="brand" class="w-8 h-8" />
            </flux:sidebar.brand>
            <flux:sidebar.collapse />
        </flux:sidebar.header>

        <flux:modal.trigger name="search">
            <flux:sidebar.search placeholder="Search..." />
        </flux:modal.trigger>

        <flux:modal name="search" variant="flyout" position="bottom" dismissible="false"
            class="bg-transparent! border-0! p-1! px-6! mt-2! overflow-visible!">
            <livewire:global.search />
        </flux:modal>

        <flux:sidebar.nav>
            @if (Request::is('admin*'))
                <x-admin-menu />
            @else
                <x-website-menu />
            @endif
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        @auth
            <livewire:chat.notification-badge />
            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:sidebar.profile avatar="{{ auth()->user()->getFirstMediaUrl('avatars', 'thumb') ?: null }}"
                    name="{{ auth()->user()->name }}" initials="{{ auth()->user()->initials() }}"
                    icon:trailing="chevrons-up-down" />
                <flux:menu class="w-[220px]">
                    <x-auth-head />
                </flux:menu>
            </flux:dropdown>
        @else
            <flux:sidebar.item icon="arrow-right-start-on-rectangle" :href="route('login')"
                :current="request()->routeIs('login')" wire:navigate.hover>
                {{ __('লগইন') }}
            </flux:sidebar.item>
            <flux:modal.trigger name="settings">
                <flux:sidebar.item icon="cog" variant="subtle">
                    {{ __('সেটিংস') }}
                </flux:sidebar.item>
            </flux:modal.trigger>
        @endauth
    </flux:sidebar>

    <flux:header sticky class="lg:hidden backdrop-blur-lg">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <a href="{{ route('home') }}" class="flex items-center space-x-2" wire:navigate.hover>
            <flux:icon name="brand" class="w-8 h-8" />
            <span class="font-bold text-xl text-[#6B747D]">Totthobox</span>
        </a>
        <flux:spacer />

        <flux:modal.trigger name="search">
            <flux:button icon="search" variant="subtle" size="sm" />
        </flux:modal.trigger>

        @auth
            <flux:dropdown position="top" align="end">
                <flux:profile :avatar="auth()->user()->getFirstMediaUrl('avatars', 'thumb') ?: null"
                    :initials="auth()->user()->initials()" class="cursor-pointer" />
                <flux:menu class="w-[220px]">
                    <x-auth-head />
                </flux:menu>
            </flux:dropdown>
        @else
            <flux:button icon="arrow-right-start-on-rectangle" variant="subtle" size="sm" wire:navigate
                href="{{ route('login') }}" />
        @endauth
    </flux:header>

    {{ $slot }}

    @fluxScripts
    @stack('scripts')
</body>

</html>