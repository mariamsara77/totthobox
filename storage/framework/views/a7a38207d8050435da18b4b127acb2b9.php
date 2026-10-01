<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'transition' => false,
    'exclusive' => false,
    'variant' => null, // 'reverse' হলে icon heading-এর আগে বসবে
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
    'transition' => false,
    'exclusive' => false,
    'variant' => null, // 'reverse' হলে icon heading-এর আগে বসবে
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$classes = Flux::classes()
    ->add('w-full divide-y divide-zinc-200 dark:divide-zinc-800')
    ->add('rounded-xl bg-zinc-400/10 overflow-hidden');
?>

<div
    <?php echo e($attributes->class($classes)); ?>

    x-data="{
        active: <?php echo e($exclusive ? 'null' : '[]'); ?>,
        exclusive: <?php echo e($exclusive ? 'true' : 'false'); ?>,
        transition: <?php echo e($transition ? 'true' : 'false'); ?>,
        variant: <?php echo \Illuminate\Support\Js::from($variant)->toHtml() ?>,

        isActive(name) {
            return this.exclusive
                ? this.active === name
                : this.active.includes(name);
        },

        toggleItem(name) {
            if (this.exclusive) {
                this.active = this.isActive(name) ? null : name;
                return;
            }

            this.isActive(name)
                ? this.active = this.active.filter(n => n !== name)
                : this.active.push(name);
        },
    }"
    data-flux-accordion
>
    <?php echo e($slot); ?>

</div><?php /**PATH /var/www/html/totthobox/resources/views/flux/accordion.blade.php ENDPATH**/ ?>