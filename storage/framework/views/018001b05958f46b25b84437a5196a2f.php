<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'content' => '',
    'limit' => 200,
    'lineClamp' => 'line-clamp-3',
    'variant' => 'default'
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
    'content' => '',
    'limit' => 200,
    'lineClamp' => 'line-clamp-3',
    'variant' => 'default'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$plainText = strip_tags($content);
$shouldTruncate = mb_strlen($plainText) > $limit;

$variants = [
    'default' => '',
    'subtle' => 'text-zinc-500 dark:text-zinc-400',
    'accent' => 'text-zinc-900 dark:text-white font-medium',
];
?>

<div 
    x-data="{ 
        expanded: false, 
        isTruncated: <?php echo e($shouldTruncate ? 'true' : 'false'); ?>,
        init() {
            // নিশ্চিত করে যে কম্পোনেন্টটি লোড হওয়ার সময় সঠিক অবস্থায় আছে
            this.expanded = false;
        }
    }" 
    x-cloak
    <?php echo e($attributes->merge(['class' => 'relative w-full group/readmore'])); ?>

>
    
    <div 
        @click="if(isTruncated) expanded = !expanded" 
        :class="{ [expanded ? '' : '<?php echo e($lineClamp); ?>']: isTruncated, 'cursor-pointer': isTruncated }"
        class="transition-all duration-700 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden relative"
    >
        <div class="<?php echo e($variants[$variant] ?? $variants['default']); ?> leading-relaxed">
            <?php if (isset($component)) { $__componentOriginal0638ebfbd490c7a414275d493e14cb4e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0638ebfbd490c7a414275d493e14cb4e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::text','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <?php echo $content; ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0638ebfbd490c7a414275d493e14cb4e)): ?>
<?php $attributes = $__attributesOriginal0638ebfbd490c7a414275d493e14cb4e; ?>
<?php unset($__attributesOriginal0638ebfbd490c7a414275d493e14cb4e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0638ebfbd490c7a414275d493e14cb4e)): ?>
<?php $component = $__componentOriginal0638ebfbd490c7a414275d493e14cb4e; ?>
<?php unset($__componentOriginal0638ebfbd490c7a414275d493e14cb4e); ?>
<?php endif; ?>
        </div>

        
        <template x-if="isTruncated && !expanded">
            <div class="absolute bottom-0 left-0 w-full h-8 bg-gradient-to-t from-white/10 dark:from-zinc-900/10 pointer-events-none"></div>
        </template>
    </div>

    
    <template x-if="isTruncated">
        <div class="flex items-center justify-start">
            <?php if (isset($component)) { $__componentOriginal54ddb5b70b37b1e1cf0f2f95e4c53477 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal54ddb5b70b37b1e1cf0f2f95e4c53477 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::link','data' => ['@click' => 'expanded = !expanded','class' => 'text-xs text-zinc-400/50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['@click' => 'expanded = !expanded','class' => 'text-xs text-zinc-400/50']); ?>
                <span x-text="expanded ? '' : 'বিস্তারিত পড়ুন'"></span>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal54ddb5b70b37b1e1cf0f2f95e4c53477)): ?>
<?php $attributes = $__attributesOriginal54ddb5b70b37b1e1cf0f2f95e4c53477; ?>
<?php unset($__attributesOriginal54ddb5b70b37b1e1cf0f2f95e4c53477); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal54ddb5b70b37b1e1cf0f2f95e4c53477)): ?>
<?php $component = $__componentOriginal54ddb5b70b37b1e1cf0f2f95e4c53477; ?>
<?php unset($__componentOriginal54ddb5b70b37b1e1cf0f2f95e4c53477); ?>
<?php endif; ?>
        </div>
    </template>
</div><?php /**PATH /var/www/html/totthobox/resources/views/flux/longtext.blade.php ENDPATH**/ ?>