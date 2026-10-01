<?php foreach ((['variant']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'size' => null,
    'variant' => null,
    'scrollable' => false,
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
    'size' => null,
    'variant' => null,
    'scrollable' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = match ($variant) {
        'pills' => 'flex gap-4 h-8 items-center',
        'segmented' => 'inline-flex p-1 rounded-lg bg-zinc-800/5 dark:bg-white/10 ' .
            ($size === 'sm' ? 'h-8 py-[3px] px-[3px]' : 'h-10 p-1'),
        default => 'flex gap-4 h-10 border-b border-zinc-800/10 dark:border-white/20',
    };

    if ($scrollable) {
        $classes .= ' overflow-x-auto scrollbar-hide';
    }
?>

<div role="tablist"
    <?php echo e($attributes->except(['wire:model', 'scrollable', 'scrollable:scrollbar', 'scrollable:fade'])->class($classes)); ?>

    data-flux-tabs>
    <?php echo e($slot); ?>

</div>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/tabs.blade.php ENDPATH**/ ?>