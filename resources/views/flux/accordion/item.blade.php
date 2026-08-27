@props([
    'name' => null,
    'heading' => null, // shorthand: ট্রু হলে default slot = content ধরা হবে
    'expanded' => false,
    'disabled' => false,
])

@php
$name = $name ?? 'flux-item-' . \Illuminate\Support\Str::random(8);

$classes = Flux::classes()
    ->add('group/accordion-item flex flex-col transition-colors duration-200')
    ->add($disabled ? 'opacity-60 pointer-events-none select-none' : '');
@endphp

<div
    {{ $attributes->class($classes) }}
    x-data="{
        name: @js($name),
        disabled: {{ $disabled ? 'true' : 'false' }},

        get isOpen() { return this.isActive(this.name) },

        open()   { if (! this.disabled) this.toggleItem(this.name) },
        close()  { if (this.isOpen) this.toggleItem(this.name) },
        toggle() { if (! this.disabled) this.toggleItem(this.name) },
    }"
    x-init="@if($expanded) toggleItem(name) @endif"
    :data-open="isOpen ? 'true' : 'false'"
    :data-disabled="disabled ? 'true' : 'false'"
    data-flux-accordion-item
>
    @if ($heading)
        <flux:accordion.heading>{{ $heading }}</flux:accordion.heading>
        <flux:accordion.content>{{ $slot }}</flux:accordion.content>
    @else
        {{ $slot }}
    @endif
</div>