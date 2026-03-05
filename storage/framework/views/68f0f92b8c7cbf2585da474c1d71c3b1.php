<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['paginate' => null, 'responsive' => true, 'shadow' => true]));

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

foreach (array_filter((['paginate' => null, 'responsive' => true, 'shadow' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'flex flex-col'])); ?>>
    <div class="<?php if($responsive): ?> -mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8 <?php endif; ?>">
        <div class="<?php if($responsive): ?> inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8 <?php endif; ?>">
            <div
                class="<?php if($shadow): ?> overflow-hidden shadow-sm ring-1 ring-gray-900/5 dark:ring-white/10 <?php else: ?> overflow-hidden <?php endif; ?> rounded-lg">
                <?php echo e($slot); ?>


                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginate && $paginate->hasPages()): ?>
                    <div>
                        <?php echo e($paginate->links()); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/html/totthobox/resources/views/flux/table/wrapper.blade.php ENDPATH**/ ?>