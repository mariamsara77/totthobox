@props([])

@php
    $classes = Flux::classes()
        ->add('text-sm text-zinc-600 dark:text-zinc-400 antialiased px-4 pb-4 leading-relaxed pt-1');
@endphp

<div class="grid" :class="{
        'grid-rows-[0fr] opacity-0 pointer-events-none': ! isOpen,
        'grid-rows-[1fr] opacity-100': isOpen,
        'transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]': transition,
        '': ! transition,
    }" x-show="transition || isOpen" x-transition:enter="transition-none" :id="name + '-content'"
    :aria-labelledby="name + '-heading'" role="region" :aria-hidden="(! isOpen).toString()" data-flux-accordion-content>
    <div class="overflow-hidden">
        <div {{ $attributes->class($classes) }}>
            {{ $slot }}
        </div>
    </div>
</div>