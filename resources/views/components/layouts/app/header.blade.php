<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="bg-white dark:bg-zinc-800">

    @if (!request()->is('admin*'))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-HGE2T2J8ZT"></script>
    <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-HGE2T2J8ZT');
    </script>
    @endif

    <flux:header container class="border-b border-zinc-400/25 ">

        <a href="https://totthobox.com" class="flex items-center rtl:space-x-reverse lg:ms-0" wire:navigate.hover
            aria-label="Totthobox Home">
            <div class="flex aspect-square size-14 items-center justify-center rounded-md ">
                <flux:icon.brand class="w-8 h-8" />
            </div>
            <div class="hidden lg:flex flex-1">
                <h1 class="text-3xl font-semibold text-black dark:text-white font-sans">Totthobox</h1>
            </div>
        </a>

        <flux:spacer />

        <flux:modal.trigger name="search">
            <flux:button icon='search' variant="subtle" tooltip="Search" aria-label="Search" class="m-0!">
                {{-- <flux:text class="hidden lg:flex">Search</flux:text> --}}
            </flux:button>
        </flux:modal.trigger>

        <flux:modal name="search" class="p-0! w-full">
            <livewire:global.global-search lazy />
        </flux:modal>


        @auth
        <livewire:chat.notification-badge variant="header" />
        <flux:dropdown position="top" align="end">
            <flux:tooltip>
                <flux:profile
                    :avatar="auth()->user()->getFirstMediaUrl('avatars', 'thumb') ? auth()->user()->getFirstMediaUrl('avatars', 'thumb') : null"
                    class="cursor-pointer" :initials="auth()->user()->initials()">
                </flux:profile>

                <flux:tooltip.content>
                    <flux:heading class="text-white/80">{{ auth()->user()->name }}</flux:heading>
                    <flux:text>{{ auth()->user()->email }}</flux:text>
                </flux:tooltip.content>
            </flux:tooltip>

            <flux:menu class="w-[220px]">
                <x-auth-head />
            </flux:menu>
        </flux:dropdown>
        @else
        <flux:modal.trigger name="settings">
            <flux:button icon="cog" variant="subtle" tooltip="Settings" aria-label="Settings" />
        </flux:modal.trigger>
        <flux:button variant="ghost" icon="arrow-right-start-on-rectangle" wire:navigate href="{{ route('login') }}"
            tooltip="Login & Register">
            {{ __('Login') }}
        </flux:button>
        @endauth

    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar stashable sticky class="lg:hidden border-e border-zinc-400/25 bg-zinc-50 dark:bg-zinc-900">

        <a href="https://totthobox.com" class="ms-1 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate.hover
            aria-label="Totthobox Home">
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')">
                <flux:navlist.item icon="layout-grid" :href="route('home')" :current="request()->routeIs('home')"
                    wire:navigate.hover>
                    {{ __('Home') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            {{-- <x-auth-head /> --}}
            @auth
            <livewire:chat.notification-badge variant="sidebar" />
            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:sidebar.profile :avatar="auth()->user()->avatar ? auth()->user()->avatar : null"
                    :name="auth()->user()->name" :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down">
                </flux:sidebar.profile>

                <flux:menu class="w-[220px]">
                    <x-auth-head />
                </flux:menu>
            </flux:dropdown>
            @else
            <flux:navlist.item icon="home" :href="route('login')" :current="request()->routeIs('login')"
                wire:navigate.hover>
                {{ __('Login') }}
            </flux:navlist.item>
            @endauth

        </flux:navlist>
    </flux:sidebar>

    <main id="main-content" role="main">
        {{ $slot }}
    </main>


    {{-- ফুটার --}}
    <div class="my-4">
        <flux:separator />
        <livewire:layout.footer-section />
    </div>

    @persist('toast')
    <flux:toast.group>
        <flux:toast />
    </flux:toast.group>
    @endpersist

    @stack('scripts')
    @fluxScripts
</body>

</html>