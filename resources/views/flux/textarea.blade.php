@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'resize' => 'vertical',
    'invalid' => null,
    'rows' => 4,
])

@php
    $invalid ??= ($name && $errors->has($name));

    $classes = Flux::classes()
        ->add('block p-3 w-full')
        ->add('shadow-xs disabled:shadow-none rounded-lg')
        ->add('bg-zinc-400/10 dark:disabled:bg-white/[7%]')
    ->add('data-invalid:outline-2 data-invalid:outline-red-600')
        ->add($resize ? 'resize-y' : 'resize-none')
        ->add('text-base sm:text-sm text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500')
        ->add($invalid)
        ;

    $resizeStyle = match ($resize) {
        'none' => 'resize: none',
        'both' => 'resize: both',
        'horizontal' => 'resize: horizontal',
        'vertical' => 'resize: vertical',
    };
@endphp

<flux:with-field :$attributes>
    <textarea
        {{ $attributes->class($classes) }}
        rows="{{ $rows }}"
        style="{{ $resizeStyle }}; {{ $rows === 'auto' ? 'field-sizing: content' : '' }}"
        @isset ($name) name="{{ $name }}" @endisset
        @if ($invalid) aria-invalid="true" data-invalid @endif
        data-flux-control
        data-flux-textarea
    >{{ $slot }}</textarea>
</flux:with-field>
