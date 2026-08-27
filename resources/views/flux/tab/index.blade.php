@aware(['variant', 'size'])

@props([
    'iconTrailing' => null,
    'iconVariant' => null,
    'variant' => null,
    'accent' => true,
    'name' => null,
    'icon' => null,
    'size' => null,
    'action' => false,
    'selected' => false,
    'disabled' => false,
])

@php
    $classes = match ($variant) {
        'pills' => 'flex whitespace-nowrap gap-2 items-center px-3 rounded-full text-sm font-medium cursor-pointer
            bg-zinc-800/5 dark:bg-white/5 hover:bg-zinc-800/10 dark:hover:bg-white/10
            text-zinc-600 hover:text-zinc-800 dark:text-white/70 dark:hover:text-white
            data-[selected]:bg-[var(--color-accent)] data-[selected]:text-[var(--color-accent-foreground)]
            disabled:opacity-50 disabled:cursor-default disabled:pointer-events-none transition-colors',

        'segmented' => 'flex whitespace-nowrap flex-1 justify-center items-center gap-2 rounded-md cursor-pointer
            text-sm font-medium text-zinc-600 hover:text-zinc-800 dark:hover:text-white dark:text-white/70
            data-[selected]:text-zinc-800 data-[selected]:dark:text-white
            data-[selected]:bg-white data-[selected]:dark:bg-white/20 data-[selected]:shadow-sm
            disabled:opacity-50 disabled:cursor-default disabled:pointer-events-none transition-all
            ' . ($size === 'sm' ? 'px-3 text-sm' : 'px-4'),

        default => 'flex whitespace-nowrap gap-2 items-center px-2 -mb-px border-b-2 border-transparent cursor-pointer
            text-sm font-medium text-zinc-400 dark:text-white/50
            data-[selected]:border-[var(--color-accent-content)] data-[selected]:text-[var(--color-accent-content)]
            hover:text-zinc-800 dark:hover:text-white
            disabled:opacity-50 disabled:cursor-default disabled:pointer-events-none transition-colors',
    };

    $iconClasses = 'size-5 shrink-0';
    $iconVariant ??= $variant === 'segmented' ? 'mini' : 'outline';
@endphp

@if ($action)
    {{-- Action button (Add tab etc.) --}}
    <button type="button" {{ $attributes->class($classes) }} data-flux-tab
        @if ($disabled) disabled @endif>
        @if ($icon)
            <flux:icon :icon="$icon" :variant="$iconVariant" class="{{ $iconClasses }}" />
        @endif

        {{ $slot }}

        @if ($iconTrailing)
            <flux:icon :icon="$iconTrailing" variant="micro" />
        @endif
    </button>
@else
    <button type="button" role="tab" name="{{ $name }}" wire:key="tab-{{ $name }}"
        x-on:click="select(@js($name))" :aria-selected="isSelected(@js($name))"
        :data-selected="isSelected(@js($name)) || null" {{ $attributes->class($classes) }}
        data-flux-tab @if ($disabled) disabled @endif>
        @if ($icon)
            <flux:icon :icon="$icon" :variant="$iconVariant" class="{{ $iconClasses }}" />
        @endif

        {{ $slot }}

        @if ($iconTrailing)
            <flux:icon :icon="$iconTrailing" variant="micro" />
        @endif
    </button>
@endif
