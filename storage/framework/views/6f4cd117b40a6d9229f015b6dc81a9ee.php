<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'modal' => null,
    'minLength' => 1,
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
    'modal' => null,
    'minLength' => 1,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div role="option"
    x-show="search && search.length >= <?php echo e($minLength); ?> && ! Array.from($el.parentElement.querySelectorAll('[data-label]')).some(el => el.getAttribute('data-label').toLowerCase() === search.toLowerCase())"
    @click="<?php echo e($modal ? "\$flux.modal('$modal').show()" : '$el.dispatchEvent(new CustomEvent(\'click\'))'); ?>"
    class="flex items-center gap-3 px-3 py-2 rounded-md cursor-default text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors">
    <div class="flex-none flex items-center justify-center size-5 rounded-full bg-indigo-100 dark:bg-indigo-500/20">
        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
    </div>

    <span class="block truncate sm:text-sm font-medium">
        <?php echo e($slot->isEmpty() ? 'Create new' : $slot); ?>

        <span x-show="search" x-text="'&quot;' + search + '&quot;'"></span>
    </span>
</div>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/select/option/create.blade.php ENDPATH**/ ?>