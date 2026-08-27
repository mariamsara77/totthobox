@props([])

@php
    $wireModel = $attributes->wire('model');
    $hasWireModel = (bool) $wireModel;
    $wireModelValue = $hasWireModel ? $wireModel->value() : null;
@endphp

<div x-data="{
    selected: null,
    hasWireModel: {{ $hasWireModel ? 'true' : 'false' }},
    wireModelName: @js($wireModelValue),

    init() {
        // Livewire bound থাকলে server value নাও, নাহলে প্রথম ট্যাব
        if (this.hasWireModel && this.wireModelName) {
            let value = $wire.get(this.wireModelName);
            this.selected = value || this.getFirstTabName();
        } else {
            this.selected = this.getFirstTabName();
        }

        // Livewire থেকে value change হলে Alpine state update করো
        if (this.hasWireModel && this.wireModelName) {
            this.$watch('$wire.' + this.wireModelName, (value) => {
                if (value !== this.selected) {
                    this.selected = value;
                }
            });
        }
    },

    getFirstTabName() {
        const first = this.$el.querySelector('[data-flux-tab][name]');
        return first ? first.getAttribute('name') : null;
    },

    select(name) {
        if (!name || this.selected === name) return;

        this.selected = name;

        if (this.hasWireModel && this.wireModelName) {
            $wire.set(this.wireModelName, name);
        }
    },

    isSelected(name) {
        return this.selected === name;
    }
}" x-on:tabs-select.window="select($event.detail)"
    {{ $attributes->whereDoesntStartWith('wire:model')->class('block') }} data-flux-tab-group wire:ignore.self>
    {{ $slot }}
</div>
