<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => 'chevron-down',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'icon' => 'chevron-down',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = Flux::classes()
        ->add('flex w-full items-center gap-2 py-3.5 px-4 text-left font-medium')
        ->add('text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/40')
        ->add('focus: focus-visible:ring-2 focus-visible:ring-zinc-500 focus-visible:ring-offset-1')
        ->add('disabled:cursor-not-allowed disabled:hover:bg-transparent')
        ->add('transition-colors duration-200 ease-in-out');
?>

<button
    type="button"
    x-on:click="toggle()"
    :disabled="disabled"
    :id="name + '-heading'"
    :aria-expanded="isOpen"
    :aria-controls="name + '-content'"
    <?php echo e($attributes->class($classes)); ?>

    data-flux-accordion-heading
>
    
    <template x-if="variant === 'reverse'">
        <span
            class="inline-flex shrink-0 items-center justify-center text-zinc-400 dark:text-zinc-500 transition-transform duration-200 ease-out"
            :class="isOpen ? 'rotate-180 text-zinc-800 dark:text-zinc-200' : ''"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </span>
    </template>

    <span class="flex-1 text-sm md:text-base select-none">
        <?php echo e($slot); ?>

    </span>

    
    <template x-if="variant !== 'reverse'">
        <span
            class="inline-flex shrink-0 items-center justify-center text-zinc-400 dark:text-zinc-500 transition-transform duration-200 ease-out"
            :class="isOpen ? 'rotate-180 text-zinc-800 dark:text-zinc-200' : ''"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </span>
    </template>
</button><?php /**PATH /var/www/html/totthobox/resources/views/flux/accordion/heading.blade.php ENDPATH**/ ?>