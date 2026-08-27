@php
    $classes = Flux::classes()->add('p-4 rounded-3xl')->add('bg-zinc-400/10 dark:bgzinc-400/5');
@endphp

<div {{ $attributes->class($classes) }} data-flux-card>
    {{ $slot }}
</div>
