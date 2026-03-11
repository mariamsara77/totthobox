<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default', // default, segmented, pills
    'size' => 'md',        // md, sm
    'scrollable' => false,
    'fade' => false,
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
    'variant' => 'default', // default, segmented, pills
    'size' => 'md',        // md, sm
    'scrollable' => false,
    'fade' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$classes = \Illuminate\Support\Arr::toCssClasses([
    'flex items-center',
    'gap-1 p-1' => $variant === 'segmented' || $variant === 'pills',
    'bg-zinc-100/50 dark:bg-zinc-800/50' => $variant === 'segmented' || $variant === 'pills',
    'rounded-lg' => $variant === 'segmented' && $size === 'md',
    'rounded-md' => $variant === 'segmented' && $size === 'sm',
    'rounded-full' => $variant === 'pills',
    'overflow-x-auto no-scrollbar' => $scrollable,
    'relative' => $fade,
]);

// Fade effect logic
$fadeClasses = $fade ? 'after:absolute after:right-0 after:top-0 after:bottom-0 after:w-12 after:bg-gradient-to-l after:from-white dark:after:from-zinc-900 after:pointer-events-none' : '';
?>

<div <?php echo e($attributes->class([$classes, $fadeClasses])); ?> <?php if($scrollable): ?> style="scrollbar-width: none;" <?php endif; ?>>
    <?php echo e($slot); ?>

</div>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/tabs.blade.php ENDPATH**/ ?>