<div class="flex aspect-square size-8 items-center justify-center rounded-md ">
    <?php if (isset($component)) { $__componentOriginalcde46ad147e4d63a74354a0e8c832877 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcde46ad147e4d63a74354a0e8c832877 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.brand','data' => ['class' => 'size-12 fill-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-12 fill-current']); ?>
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
</div><?php /**PATH /var/www/html/totthobox/resources/views/components/app-logo.blade.php ENDPATH**/ ?>