

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'outline',
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
    'variant' => 'outline',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    if ($variant === 'solid') {
        throw new \Exception('The "solid" variant is not supported in Lucide.');
    }

    $classes = Flux::classes('shrink-0')->add(
        match ($variant) {
            'outline' => '[:where(&)]:size-6',
            'solid' => '[:where(&)]:size-6',
            'mini' => '[:where(&)]:size-5',
            'micro' => '[:where(&)]:size-4',
        },
    );

    $strokeWidth = match ($variant) {
        'outline' => 2,
        'mini' => 2.25,
        'micro' => 2.5,
    };
?>

<svg <?php echo e($attributes->class($classes)); ?> data-flux-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
    fill="none" stroke="currentColor" stroke-width="<?php echo e($strokeWidth); ?>" stroke-linecap="round" stroke-linejoin="round"
    aria-hidden="true" data-slot="icon">
    <rect x="5" y="1" width="6" height="14" rx="2" fill="none" stroke="currentColor"
        stroke-width="1" />
    <circle cx="8" cy="4" r="1.5" fill="none" stroke="currentColor" stroke-width="1" />
    <circle cx="8" cy="8" r="1.5" fill="none" stroke="currentColor" stroke-width="1" />
    <circle cx="8" cy="12" r="1.5" fill="none" stroke="currentColor" stroke-width="1" />
</svg>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/icon/traffic.blade.php ENDPATH**/ ?>