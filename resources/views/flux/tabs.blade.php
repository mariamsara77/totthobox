@aware(['variant'])

@props([
    'size' => null,
    'variant' => null,
    'scrollable' => false,
])

@php
    $classes = match ($variant) {
        'pills' => 'flex gap-4 h-8 items-center',
        'segmented' => 'inline-flex p-1 rounded-lg bg-zinc-800/5 dark:bg-white/10 ' .
            ($size === 'sm' ? 'h-8 py-[3px] px-[3px]' : 'h-10 p-1'),
        default => 'flex gap-4 h-10 border-b border-zinc-800/10 dark:border-white/20',
    };

    if ($scrollable) {
        $classes .= ' overflow-x-auto scrollbar-hide';
    }
@endphp

<div role="tablist"
    {{ $attributes->except(['wire:model', 'scrollable', 'scrollable:scrollbar', 'scrollable:fade'])->class($classes) }}
    data-flux-tabs>
    {{ $slot }}
</div>
