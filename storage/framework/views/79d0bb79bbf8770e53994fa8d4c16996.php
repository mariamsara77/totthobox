<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([]));

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

foreach (array_filter(([]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $wireModel = $attributes->wire('model');
    $hasWireModel = (bool) $wireModel;
    $wireModelValue = $hasWireModel ? $wireModel->value() : null;
?>

<div x-data="{
    selected: null,
    hasWireModel: <?php echo e($hasWireModel ? 'true' : 'false'); ?>,
    wireModelName: <?php echo \Illuminate\Support\Js::from($wireModelValue)->toHtml() ?>,

    init() {
        // Livewire bound থাকলে server value নাও, নাহলে প্রথম ট্যাব
        if (this.hasWireModel && this.wireModelName) {
            let value = $wire.get(this.wireModelName);
            this.selected = value || this.getFirstTabName();
        } else {
            this.selected = this.getFirstTabName();
        }

        // Livewire থেকে value change হলে Alpine state update করো
        if (this.hasWireModel && this.wireModelName) {
            this.$watch('$wire.' + this.wireModelName, (value) => {
                if (value !== this.selected) {
                    this.selected = value;
                }
            });
        }
    },

    getFirstTabName() {
        const first = this.$el.querySelector('[data-flux-tab][name]');
        return first ? first.getAttribute('name') : null;
    },

    select(name) {
        if (!name || this.selected === name) return;

        this.selected = name;

        if (this.hasWireModel && this.wireModelName) {
            $wire.set(this.wireModelName, name);
        }
    },

    isSelected(name) {
        return this.selected === name;
    }
}" x-on:tabs-select.window="select($event.detail)"
    <?php echo e($attributes->whereDoesntStartWith('wire:model')->class('block')); ?> data-flux-tab-group wire:ignore.self>
    <?php echo e($slot); ?>

</div>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/tab/group.blade.php ENDPATH**/ ?>