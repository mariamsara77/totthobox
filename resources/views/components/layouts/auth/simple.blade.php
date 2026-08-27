<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-4">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate.hover>
                <div class="flex items-center justify-center rounded-md gap-4">
                    <flux:icon.brand class="size-12" />
                    {{-- <flux:text class="text-xl font-bold">{{ config('app.name', 'Totthobox') }}</flux:text> --}}
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
