

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

   
   

       
<svg <?php echo e($attributes->class($classes)); ?> data-flux-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="currentColor" stroke="currentColor" stroke-width="<?php echo e($strokeWidth); ?>" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" data-slot="icon">
    <path d="M206.8 288L433.2 288C476.7 288 512 252.7 512 209.2C512 183.7 499.7 159.8 478.9 145L329.3 38.6C323.7 34.7 316.3 34.7 310.8 38.6L161.1 145C140.3 159.8 128 183.7 128 209.2C128 252.7 163.3 288 206.8 288zM544 576C579.3 576 608 547.3 608 512L608 288C608 270.3 593.7 256 576 256C558.3 256 544 270.3 544 288L544 336L96 336L96 288C96 270.3 81.7 256 64 256C46.3 256 32 270.3 32 288L32 512C32 547.3 60.7 576 96 576L544 576zM272 448C272 421.5 293.5 400 320 400C346.5 400 368 421.5 368 448L368 528L272 528L272 448z" /></svg>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/icon/islamic.blade.php ENDPATH**/ ?>