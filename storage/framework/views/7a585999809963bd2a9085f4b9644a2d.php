<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'selected' => false,
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
    'name' => null,
    'selected' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div role="tabpanel" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'panel-'.e($name).''; ?>wire:key="panel-<?php echo e($name); ?>" x-show="isSelected(<?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>)" x-cloak
    :data-selected="isSelected(<?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>) || null" <?php echo e($attributes->class('pt-8')); ?>

    data-flux-tab-panel>
    <?php echo e($slot); ?>

</div>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/tab/panel.blade.php ENDPATH**/ ?>