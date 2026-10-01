<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['id', 'name' => null, 'value' => '']));

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

foreach (array_filter((['id', 'name' => null, 'value' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    <?php echo e($attributes->whereDoesntStartWith('wire:')); ?>

    <?php if($attributes->has('wire:model')): ?>
    x-data="{ content: $wire.entangle('<?php echo e($attributes->wire('model')->value()); ?>') }"
    x-on:lexxy:initialize="$nextTick(() => $refs.input.value = content)"
    x-on:lexxy:change="content = $refs.input.value"
    <?php endif; ?>
>
    <lexxy-editor
        id="<?php echo e($id); ?>"
        class="lexxy-content"
        value="<?php echo e($value); ?>"
        <?php if($name ?? false): ?>
        name="<?php echo e($name); ?>"
        <?php endif; ?>
        <?php if($attributes->has('wire:model')): ?>
        wire:ignore
        x-ref="input"
        <?php endif; ?>
    ><?php echo e($slot); ?></lexxy-editor>
</div>
<?php /**PATH /var/www/html/totthobox/vendor/tonysm/rich-text-laravel/resources/views/components/lexxy.blade.php ENDPATH**/ ?>