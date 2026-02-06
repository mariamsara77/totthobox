<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    {{-- <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div
            class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-indigo-600/10 blur-[120px] animate-glow">
        </div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] rounded-full bg-blue-600/10 blur-[120px] animate-glow"
            style="animation-delay: 4s;"></div>
        <div
            class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-overlay">
        </div>
    </div> --}}
    <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-2">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate.hover>
                <div class="flex items-center justify-center rounded-md gap-4">
                    <x-app-logo-icon class="w-6 h-6" />
                    <flux:text class="text-xl font-bold">{{ config('app.name', 'Totthobox') }}</flux:text>
                </div>
            </a>
            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
