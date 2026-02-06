<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    <flux:sidebar sticky collapsible
        class="bg-zinc-50 dark:bg-zinc-900 border- border-zinc-200 dark:border-zinc-700 duration-300 overflow-hidden"
        x-data x-init="let saved = localStorage.getItem('sidebar-scroll') || 0;
        $el.scrollTop = saved;
        window.addEventListener('livewire:navigated', () => {
            $nextTick(() => {
                $el.scrollTop = localStorage.getItem('sidebar-scroll') || 0;
            });
        });
        $el.addEventListener('scroll', () => {
            localStorage.setItem('sidebar-scroll', $el.scrollTop);
        });">

        <flux:sidebar.header>
            <flux:sidebar.brand href="/" name="Totthobox" wire:navigate.hover>
                <flux:icon name="brand" />
            </flux:sidebar.brand>

            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:modal.trigger name="search">
            <flux:sidebar.search placeholder="Search..." />
        </flux:modal.trigger>

        <flux:modal name="search" variant="flyout" position="bottom" dismissible="false"
            class="!bg-transparent !border-0 !p-1 !px-6 !mt-2 !overflow-visible">
            <div class="">
                <livewire:global.search />
            </div>
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
                <flux:sidebar.profile
                    avatar="{{ auth()->user()->getFirstMediaUrl('avatars', 'thumb') ? auth()->user()->getFirstMediaUrl('avatars', 'thumb') : null }}"
                    name="{{ auth()->user()->name }}" initials="{{ auth()->user()->initials() }}"
                    icon:trailing="chevrons-up-down" class="" />


                <flux:menu class="w-[220px]">
                    <x-auth-dropdown />
                </flux:menu>
            </flux:dropdown>
        @else
            <flux:sidebar.item icon="arrow-right-start-on-rectangle" :href="route('login')"
                :current="request()->routeIs('login')" wire:navigate.hover>
                {{ __('লগইন') }}
                </flux:navlist.item>
                <flux:modal.trigger name="settings">
                    <flux:sidebar.item icon="cog" variant="fill">
                        {{ __('সেটিংস') }}
                        </flux:navlist.item>
                </flux:modal.trigger>

        @endauth


    </flux:sidebar>



    <!-- Mobile User Menu -->
    <flux:header sticky class="lg:hidden backdrop-blur-lg">
        <flux:sidebar.collapse class="lg:hidden" />
        {{--
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" /> --}}
        <a href="{{ route('home') }}" class="flex items-center space-x-2 rtl:space-x-reverse lg:ms-0"
            wire:navigate.hover>
            <div class="flex aspect-square size-14 items-center justify-center rounded-md ">
                <x-app-logo-icon class="size-8 fill-current" />
            </div>
            <div class="hidden lg:flex ms-1 flex-1 text-start text-sm ">
                <span class="mb-0.5 font-bold text-4xl text-[#6B747D]">Totthobox</span>
            </div>
        </a>
        <flux:spacer />


        <flux:modal.trigger name="search">
            <flux:tooltip :content="__('Search')" position="bottom">
                <flux:button class="mr-0 pr-0" icon='search' variant="subtle" size="sm"><span
                        class="hidden lg:flex">Search</span></flux:button>
            </flux:tooltip>
        </flux:modal.trigger>

        <flux:dropdown>
            <flux:button variant="ghost" size="sm">
                <flux:icon.grid-4 class="w-4 h-4" />
            </flux:button>

            <flux:menu>
                <flux:menu.grid-menu />
            </flux:menu>
        </flux:dropdown>

        <x-auth-header-dropdown />
    </flux:header>

    {{ $slot }}



    @fluxScripts
    @stack('scripts')
</body>

</html>