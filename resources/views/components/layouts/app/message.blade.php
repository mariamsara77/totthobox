<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-950 antialiased">
    <flux:sidebar collapsible sticky
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

        </flux:sidebar.header>

        @stack('sidebar')

        <flux:spacer />



        @auth
            <livewire:chat.notification-badge />

            <flux:dropdown position="top" align="start" class="max-lg:hidden ">
                <flux:sidebar.profile :avatar="auth()->user()->avatar ? auth()->user()->avatar : null"
                    :name="auth()->user()->name" :initials="auth()->user()->initials()" icon:trailing="chevrons-up-down">
                </flux:sidebar.profile>

                <flux:menu class="w-[220px] dark:!bg-zinc-800">
                    <x-auth-dropdown />
                </flux:menu>
            </flux:dropdown>
        @else
            <flux:navlist.item icon="home" :href="route('login')" :current="request()->routeIs('login')"
                wire:navigate.hover>
                {{ __('Login') }}</flux:navlist.item>
        @endauth


    </flux:sidebar>

    <!-- Mobile User Menu -->
    {{-- <flux:header sticky class="backdrop-blur-lg border-b border-zinc-400/10 !px-2">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" size="xs" />
        <div class="w-full">
            @stack('chat-header')
        </div>
    </flux:header> --}}

    <flux:main class="!p-0">
        {{ $slot }}
    </flux:main>
    @stack('scripts')
    @fluxScripts
</body>

</html>
