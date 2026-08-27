@props([
    'transition' => false,
    'exclusive' => false,
    'variant' => null, // 'reverse' হলে icon heading-এর আগে বসবে
])

@php
$classes = Flux::classes()
    ->add('w-full divide-y divide-zinc-200 dark:divide-zinc-800')
    ->add('rounded-xl bg-zinc-400/10 overflow-hidden');
@endphp

<div
    {{ $attributes->class($classes) }}
    x-data="{
        active: {{ $exclusive ? 'null' : '[]' }},
        exclusive: {{ $exclusive ? 'true' : 'false' }},
        transition: {{ $transition ? 'true' : 'false' }},
        variant: @js($variant),

        isActive(name) {
            return this.exclusive
                ? this.active === name
                : this.active.includes(name);
        },

        toggleItem(name) {
            if (this.exclusive) {
                this.active = this.isActive(name) ? null : name;
                return;
            }

            this.isActive(name)
                ? this.active = this.active.filter(n => n !== name)
                : this.active.push(name);
        },
    }"
    data-flux-accordion
>
    {{ $slot }}
</div>