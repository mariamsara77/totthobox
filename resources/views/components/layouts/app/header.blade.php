<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

    <flux:header container class="border-b border-zinc-200  dark:border-zinc-700 ">

        <a href="{{ route('home') }}" class="flex items-center rtl:space-x-reverse lg:ms-0" wire:navigate.hover aria-label="Totthobox Home">
            <div class="flex aspect-square size-14 items-center justify-center rounded-md ">
                <flux:icon.brand class="w-8 h-8" />
            </div>
            <div class="hidden lg:flex flex-1">
                <h1 class="text-3xl font-semibold text-black dark:text-white font-sans">Totthobox</h1>
            </div>
        </a>

        <flux:spacer />

        <flux:modal.trigger name="search">
            <flux:button icon='search' variant="subtle" size="sm" tooltip="Search" aria-label="Search">
                <flux:text class="hidden lg:flex" tooltip="Search">Search</flux:text>
            </flux:button>
        </flux:modal.trigger>

        <flux:modal name="search" variant="flyout" position="bottom"
            class="!bg-transparent !border-0 !p-1 !mt-4 !overflow-visible">
            <div class="">
                <livewire:global.search />
            </div>
        </flux:modal>

        @auth
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :avatar="auth()->user()->getFirstMediaUrl('avatars', 'thumb') ? auth()->user()->getFirstMediaUrl('avatars', 'thumb') : null"
                    class="cursor-pointer" :initials="auth()->user()->initials()">
                </flux:profile>

                <flux:menu class="w-[220px]">
                    <x-auth-head />
                </flux:menu>
            </flux:dropdown>
        @else
            <flux:modal.trigger name="settings">
                <flux:button icon="cog" variant="subtle" size="sm" tooltip="Settings" />
            </flux:modal.trigger>
            <flux:button variant="subtle" size="sm" icon="arrow-right-start-on-rectangle" wire:navigate
                href="{{ route('login') }}" tooltip="Login & Register">
                {{ __('Login') }}
            </flux:button>
        @endauth

    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar stashable sticky
        class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

        <a href="{{ route('home') }}" class="ms-1 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate.hover aria-label="Totthobox Home">
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

    @stack('scripts')
    @fluxScripts
</body>

</html>