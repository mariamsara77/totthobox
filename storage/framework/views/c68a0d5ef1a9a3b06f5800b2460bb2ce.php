

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
        throw new \Exception('The "solid" variant is not supported in this icon.');
    }

    $classes = Flux::classes('shrink-0')->add(
        match ($variant) {
            'outline' => '',
        },
    );

    $strokeWidth = match ($variant) {
        'outline' => 0,
    };
?>





<svg <?php echo e($attributes->class($classes)); ?> data-flux-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 227 227"
    fill="currentColor" stroke="currentColor" stroke-width="<?php echo e($strokeWidth); ?>" stroke-linecap="round"
    stroke-linejoin="round" aria-hidden="true" data-slot="icon">
    <path
        d="M63.5 27.2c-.3.8-.4 32-.3 69.3l.3 68 3.9 7.2c7.2 13.5 22.7 25.2 37.5 28.4 29.1 6.2 52.4-15.9 58.1-54.9 2.7-18.3 0-43.6-6-57.7-9.2-21.6-28.9-31.4-49.2-24.6-7.4 2.5-16.7 8.2-21.2 13-1.7 1.7-3.3 3.1-3.8 3.1-.4 0-.8-10.5-.8-23.4 0-23.3 0-23.5-2.4-26.2-2-2.4-3.2-2.8-9-3.2-5-.3-6.7-.1-7.1 1M126.4 83c5.1 3.5 9.2 10.3 11.7 19.6 2.9 10.8 3.2 36.4.6 46.9-5 19.7-15 31-28.4 32.2-10.4.9-22.5-5.6-26.4-14.1-1.7-3.7-1.9-7.1-1.9-33.2v-29.1l5-6.5c5.3-7.1 10.8-12.1 18-16.3 6-3.5 15.8-3.3 21.4.5" />
</svg>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/icon/bdnews24.blade.php ENDPATH**/ ?>