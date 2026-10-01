<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default',
    'sticky' => false,
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
    'variant' => 'default',
    'sticky' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<td
    <?php echo e($attributes->class([
        'px-4 py-3.5 whitespace-nowrap text-zinc-600 dark:text-zinc-300',
        'font-medium text-zinc-900 dark:text-white' => $variant === 'strong',
        'sticky left-0 z-10' => $sticky,
    ])); ?>>
    <?php echo e($slot); ?>

</td>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/table/cell.blade.php ENDPATH**/ ?>