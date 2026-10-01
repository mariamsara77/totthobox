<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([]));

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

foreach (array_filter(([]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = Flux::classes()
        ->add('text-sm text-zinc-600 dark:text-zinc-400 antialiased px-4 pb-4 leading-relaxed pt-1');
?>

<div class="grid" :class="{
        'grid-rows-[0fr] opacity-0 pointer-events-none': ! isOpen,
        'grid-rows-[1fr] opacity-100': isOpen,
        'transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]': transition,
        '': ! transition,
    }" x-show="transition || isOpen" x-transition:enter="transition-none" :id="name + '-content'"
    :aria-labelledby="name + '-heading'" role="region" :aria-hidden="(! isOpen).toString()" data-flux-accordion-content>
    <div class="overflow-hidden">
        <div <?php echo e($attributes->class($classes)); ?>>
            <?php echo e($slot); ?>

        </div>
    </div>
</div><?php /**PATH /var/www/html/totthobox/resources/views/flux/accordion/content.blade.php ENDPATH**/ ?>