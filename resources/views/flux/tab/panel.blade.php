@props([
    'name' => null,
    'selected' => false,
])

<div role="tabpanel" wire:key="panel-{{ $name }}" x-show="isSelected(@js($name))" x-cloak
    :data-selected="isSelected(@js($name)) || null" {{ $attributes->class('pt-8') }}
    data-flux-tab-panel>
    {{ $slot }}
</div>
