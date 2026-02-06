<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value' => null,
    'label' => null,
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
    'value' => null,
    'label' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // label না থাকলে slot-কে label হিসেবে ধরা হবে
    $displayLabel = $label ?? $slot;
    $stringValue = (string) $value;

    $classes = Flux::classes()
        ->add('group relative flex items-center gap-2 px-2 py-1.5 rounded-md cursor-pointer select-none transition-colors')
        ->add('text-zinc-700 dark:text-zinc-300 text-sm')
        // হোভার এবং সিলেক্টেড স্টেট
        ->add('hover:bg-zinc-100 dark:hover:bg-white/5')
        ->add('data-[selected=true]:bg-zinc-50 dark:data-[selected=true]:bg-white/10 data-[selected=true]:font-medium');
?>

<div 
    role="option" 
    x-show="!search || $el.innerText.toLowerCase().includes(search.toLowerCase())"
    @click="select('<?php echo e($stringValue); ?>', '<?php echo e($displayLabel); ?>')"
    data-value="<?php echo e($stringValue); ?>" 
    data-label="<?php echo e($displayLabel); ?>"
    :data-selected="String(value) === '<?php echo e($stringValue); ?>'"
    <?php echo e($attributes->class($classes)); ?>

>
    
    <div class="flex items-center justify-center size-4 shrink-0">
        <span x-show="String(value) === '<?php echo e($stringValue); ?>'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => 'check','variant' => 'micro','class' => 'size-4 text-zinc-800 dark:text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'check','variant' => 'micro','class' => 'size-4 text-zinc-800 dark:text-white']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $attributes = $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $component = $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
        </span>
    </div>

    
    <span class="block truncate flex-1">
        <?php echo e($displayLabel); ?>

    </span>
</div><?php /**PATH /var/www/html/totthobox/resources/views/flux/select/option/variants/listbox.blade.php ENDPATH**/ ?>