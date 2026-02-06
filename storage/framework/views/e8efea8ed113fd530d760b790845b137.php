<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['media' => []]));

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

foreach (array_filter((['media' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // যদি সিঙ্গেল স্ট্রিং পাঠানো হয়, তবে তাকে অ্যারেতে রূপান্তর করবে
    $mediaArray = is_array($media) || $media instanceof \Illuminate\Support\Collection ? $media : [$media];

    if (empty($mediaArray) || count($mediaArray) === 0 || !$mediaArray[0])
        return;

    $items = collect($mediaArray)->map(function ($m) {
        // যদি এটি স্প্যাটি মিডিয়া অবজেক্ট হয়
        if (is_object($m) && method_exists($m, 'getUrl')) {
            return [
                'url' => $m->getUrl(),
                'thumb' => $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl(),
                'type' => str_contains($m->mime_type ?? '', 'video') ? 'video' : 'image',
                'caption' => $m->name ?? ''
            ];
        }

        // যদি এটি সরাসরি স্ট্রিং পাথ (URL) হয়
        return [
            'url' => (string) $m,
            'thumb' => (string) $m,
            'type' => 'image', // স্ট্রিং পাথের ক্ষেত্রে ডিফল্ট ইমেজ ধরা হয়েছে
            'caption' => ''
        ];
    })->values()->all();

    $count = count($items);
?>

<div x-data="{ galleryItems: <?php echo \Illuminate\Support\Js::from($items)->toHtml() ?> }" <?php echo e($attributes->merge(['class' => 'w-full'])); ?>>
    <div class="grid gap-2 overflow-hidden rounded-2xl"
        style="grid-template-columns: repeat(<?php echo e($count > 1 ? 2 : 1); ?>, 1fr);">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = collect($items)->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="<?php echo e($count > 2 && $index === 0 ? 'row-span-2' : ''); ?> relative group cursor-pointer overflow-hidden bg-zinc-100 dark:bg-zinc-900 border border-black/5 dark:border-white/5"
                @click="$dispatch('open-lightbox', { index: <?php echo e($index); ?>, items: galleryItems })">

                <img src="<?php echo e($item['thumb']); ?>"
                    class="w-full h-full object-cover <?php echo e($count > 2 && $index === 0 ? 'aspect-auto' : 'aspect-[4/3]'); ?> group-hover:scale-105 transition-transform duration-500"
                    loading="lazy">

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['type'] === 'video'): ?>
                    <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/40">
                        <div class="bg-white/20 backdrop-blur-md p-2 rounded-full text-white border border-white/30">
                            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['name' => 'play','variant' => 'solid','class' => 'size-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'play','variant' => 'solid','class' => 'size-6']); ?>
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
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 3 && $index === 2): ?>
                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center text-white font-bold">
                        <span class="text-lg">+<?php echo e($count - 3); ?> টি</span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if (! $__env->hasRenderedOnce('e1fffe25-1a01-42ec-b13a-c5d23619ecaf')): $__env->markAsRenderedOnce('e1fffe25-1a01-42ec-b13a-c5d23619ecaf'); ?>
        <?php if (isset($component)) { $__componentOriginal9b11bf7ce1f72fecce31dd160cb86c23 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9b11bf7ce1f72fecce31dd160cb86c23 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.global-lightbox','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('global-lightbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9b11bf7ce1f72fecce31dd160cb86c23)): ?>
<?php $attributes = $__attributesOriginal9b11bf7ce1f72fecce31dd160cb86c23; ?>
<?php unset($__attributesOriginal9b11bf7ce1f72fecce31dd160cb86c23); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9b11bf7ce1f72fecce31dd160cb86c23)): ?>
<?php $component = $__componentOriginal9b11bf7ce1f72fecce31dd160cb86c23; ?>
<?php unset($__componentOriginal9b11bf7ce1f72fecce31dd160cb86c23); ?>
<?php endif; ?>
    <?php endif; ?>
</div><?php /**PATH /var/www/html/totthobox/resources/views/flux/media.blade.php ENDPATH**/ ?>