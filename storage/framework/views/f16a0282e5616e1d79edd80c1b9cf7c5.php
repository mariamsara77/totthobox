<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">

<head>
    <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    
    <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-2">
            <a href="<?php echo e(route('home')); ?>" class="flex flex-col items-center gap-2 font-medium" wire:navigate.hover>
                <div class="flex items-center justify-center rounded-md gap-4">
                    <?php if (isset($component)) { $__componentOriginalcde46ad147e4d63a74354a0e8c832877 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcde46ad147e4d63a74354a0e8c832877 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.brand','data' => ['class' => 'size-12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-12']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcde46ad147e4d63a74354a0e8c832877)): ?>
<?php $attributes = $__attributesOriginalcde46ad147e4d63a74354a0e8c832877; ?>
<?php unset($__attributesOriginalcde46ad147e4d63a74354a0e8c832877); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcde46ad147e4d63a74354a0e8c832877)): ?>
<?php $component = $__componentOriginalcde46ad147e4d63a74354a0e8c832877; ?>
<?php unset($__componentOriginalcde46ad147e4d63a74354a0e8c832877); ?>
<?php endif; ?>
                    
                </div>
            </a>
            <div class="flex flex-col gap-6">
                <?php echo e($slot); ?>

            </div>
        </div>
    </div>
    <?php app('livewire')->forceAssetInjection(); ?>
<?php echo app('flux')->scripts(); ?>

</body>

</html><?php /**PATH /var/www/html/totthobox/resources/views/components/layouts/auth/simple.blade.php ENDPATH**/ ?>