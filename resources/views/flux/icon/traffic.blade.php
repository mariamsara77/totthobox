{{-- Credit: Lucide (https://lucide.dev) --}}

@props([
    'variant' => 'outline',
])

@php
    if ($variant === 'solid') {
        throw new \Exception('The "solid" variant is not supported in Lucide.');
    }

    $classes = Flux::classes('shrink-0')->add(
        match ($variant) {
            'outline' => '[:where(&)]:size-6',
            'solid' => '[:where(&)]:size-6',
            'mini' => '[:where(&)]:size-5',
            'micro' => '[:where(&)]:size-4',
        },
    );

    $strokeWidth = match ($variant) {
        'outline' => 2,
        'mini' => 2.25,
        'micro' => 2.5,
    };
@endphp

<svg {{ $attributes->class($classes) }} data-flux-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
    fill="none" stroke="currentColor" stroke-width="{{ $strokeWidth }}" stroke-linecap="round" stroke-linejoin="round"
    aria-hidden="true" data-slot="icon">
    <rect x="5" y="1" width="6" height="14" rx="2" fill="none" stroke="currentColor"
        stroke-width="1" />
    <circle cx="8" cy="4" r="1.5" fill="none" stroke="currentColor" stroke-width="1" />
    <circle cx="8" cy="8" r="1.5" fill="none" stroke="currentColor" stroke-width="1" />
    <circle cx="8" cy="12" r="1.5" fill="none" stroke="currentColor" stroke-width="1" />
</svg>
