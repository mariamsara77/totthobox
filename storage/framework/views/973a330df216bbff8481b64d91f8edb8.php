<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'placeholder' => null,
    'invalid' => null,
    'size' => null,
    'searchable' => false,
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
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'placeholder' => null,
    'invalid' => null,
    'size' => null,
    'searchable' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$invalid ??= $name && $errors->has($name);
// প্রতিটি সিলেক্টের জন্য একটি ইউনিক আইডি জেনারেট করা হচ্ছে
$id = 'flux-select-' . uniqid();

$classes = Flux::classes()
    ->add(
        'relative flex items-center justify-between w-full ps-3 pe-2 text-left cursor-default transition duration-75 focus:outline-none',
    )
    ->add('bg-zinc-400/10')
    ->add('data-invalid:outline-2 data-invalid:outline-red-600')
    ->add(
        match ($size) {
            'sm' => 'h-8 py-1 text-sm rounded-md',
            'xs' => 'h-6 py-0.5 text-xs rounded-md',
            default => 'h-10 py-2 text-base sm:text-sm rounded-lg',
        },
    );
// ->add('bg-white dark:bg-white/10 text-zinc-800 dark:text-zinc-200 shadow-sm ');
// ->add(
//     $invalid
//         ? 'border border-red-500 ring-1 ring-red-500'
//         : 'border border-zinc-300 dark:border-white/15 border-b-zinc-300/80 focus:ring-2 focus:ring-zinc-500/20 focus:border-zinc-500',
// );
?>

<div x-data="{
    open: false,
    
    value: <?php if(isset($__livewire)): ?> <?php if ((object) ($attributes->wire('model')) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->wire('model')->value()); ?>')<?php echo e($attributes->wire('model')->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->wire('model')); ?>')<?php endif; ?> <?php else: ?> <?php echo e($attributes->get('x-model') ?? 'null'); ?> <?php endif; ?>,
    label: '',
    search: '',
    buttonWidth: 0,

    init() {
        
        <?php if(!isset($__livewire) && $attributes->has('x-model')): ?>
            this.$watch('currentLang', (val) => { this.value = val; });
        <?php endif; ?>

        this.syncLabel();
        this.$watch('value', () => this.syncLabel());
        this.$nextTick(() => { this.buttonWidth = this.$refs.trigger.offsetWidth });
    },

    syncLabel() {
        this.$nextTick(() => {
            const option = this.$refs.optionsContainer?.querySelector(`[data-value='${String(this.value)}']`);
            this.label = option ? (option.getAttribute('data-label') || option.innerText.trim()) : '';
        });
    },

    select(val, lab) {
        this.value = val;
        this.label = lab;
        this.open = false;
        this.search = '';
    }
}" @click.outside="open = false" @keydown.escape="open = false" class="relative w-full">

    
    <button type="button" x-ref="trigger"
        @click="open = !open; if(open) { buttonWidth = $el.offsetWidth; $nextTick(() => $refs.searchInput?.focus()) }"
        <?php echo e($attributes->class($classes)); ?> role="combobox" :aria-expanded="open">

        <span class="block truncate" x-text="label || '<?php echo e($placeholder); ?>'"
            :class="!label ? 'text-zinc-500 dark:text-zinc-400' : 'text-zinc-800 dark:text-zinc-200'"></span>
        <?php if (isset($component)) { $__componentOriginalcc1305822472ccf8aa9a0b8dc7a9cf8c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcc1305822472ccf8aa9a0b8dc7a9cf8c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.chevron-up-down','data' => ['class' => 'size-4 text-zinc-400 dark:text-zinc-500 ml-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.chevron-up-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4 text-zinc-400 dark:text-zinc-500 ml-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcc1305822472ccf8aa9a0b8dc7a9cf8c)): ?>
<?php $attributes = $__attributesOriginalcc1305822472ccf8aa9a0b8dc7a9cf8c; ?>
<?php unset($__attributesOriginalcc1305822472ccf8aa9a0b8dc7a9cf8c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcc1305822472ccf8aa9a0b8dc7a9cf8c)): ?>
<?php $component = $__componentOriginalcc1305822472ccf8aa9a0b8dc7a9cf8c; ?>
<?php unset($__componentOriginalcc1305822472ccf8aa9a0b8dc7a9cf8c); ?>
<?php endif; ?>
    </button>

    
    <template x-teleport="body">
        <div x-show="open" x-ref="optionsContainer" x-anchor.bottom-start.offset.4="$refs.trigger"
            x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="fixed z-[9999] p-1.5 overflow-y-auto rounded-xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-zinc-700 shadow-xl"
             :style="{ width: buttonWidth + 'px', maxHeight: '250px' }" style="display: none;">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($searchable): ?>
                <div
                    class="p-1 mb-1 border-b border-zinc-100 dark:border-white/5 sticky top-0 bg-white dark:bg-zinc-900 z-20">
                    <input x-model="search" x-ref="searchInput" @click.stop type="text" placeholder="Filter..."
                        class="w-full px-2 py-1.5 text-sm bg-zinc-50 dark:bg-white/5 border-none rounded-md focus:ring-0 text-zinc-800 dark:text-zinc-200 placeholder-zinc-400">
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="space-y-0.5">
                <?php echo e($slot); ?>


                
                <div x-show="search && $el.parentElement.querySelectorAll('[role=option]:not([style*=\'display: none\'])').length === 0"
                    class="px-3 py-4 text-center text-sm text-zinc-500">
                    No results found
                </div>
            </div>
        </div>
    </template>
</div>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/select/variants/listbox.blade.php ENDPATH**/ ?>