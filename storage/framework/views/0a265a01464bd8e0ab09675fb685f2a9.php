
<?php
    $notifications = [
        'success' => ['icon' => 'check-circle', 'color' => 'text-green-500'],
        'error' => ['icon' => 'x-circle', 'color' => 'text-red-500'],
        'status' => ['icon' => 'info', 'color' => 'text-blue-500'],
        'message' => ['icon' => 'bell', 'color' => 'text-zinc-500'],
    ];
?>

<div
    class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999] w-full max-w-sm px-4 flex flex-col gap-3 pointer-events-none">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $settings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has($type)): ?>
            <div class="pointer-events-auto">
                <?php if (isset($component)) { $__componentOriginald243037031d21873ca0c386193020d6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald243037031d21873ca0c386193020d6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification-card','data' => ['icon' => $settings['icon'],'color' => $settings['color'],'message' => session($type),'type' => $type]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notification-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($settings['icon']),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($settings['color']),'message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session($type)),'type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($type)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald243037031d21873ca0c386193020d6f)): ?>
<?php $attributes = $__attributesOriginald243037031d21873ca0c386193020d6f; ?>
<?php unset($__attributesOriginald243037031d21873ca0c386193020d6f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald243037031d21873ca0c386193020d6f)): ?>
<?php $component = $__componentOriginald243037031d21873ca0c386193020d6f; ?>
<?php unset($__componentOriginald243037031d21873ca0c386193020d6f); ?>
<?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['messageText'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="pointer-events-auto">
            <?php if (isset($component)) { $__componentOriginald243037031d21873ca0c386193020d6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald243037031d21873ca0c386193020d6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification-card','data' => ['icon' => 'exclamation-triangle','color' => 'text-red-500','message' => $message,'type' => 'error']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notification-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'exclamation-triangle','color' => 'text-red-500','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($message),'type' => 'error']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald243037031d21873ca0c386193020d6f)): ?>
<?php $attributes = $__attributesOriginald243037031d21873ca0c386193020d6f; ?>
<?php unset($__attributesOriginald243037031d21873ca0c386193020d6f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald243037031d21873ca0c386193020d6f)): ?>
<?php $component = $__componentOriginald243037031d21873ca0c386193020d6f; ?>
<?php unset($__componentOriginald243037031d21873ca0c386193020d6f); ?>
<?php endif; ?>
        </div>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($errorMessage) && $errorMessage): ?>
        <div class="pointer-events-auto">
            <?php if (isset($component)) { $__componentOriginald243037031d21873ca0c386193020d6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald243037031d21873ca0c386193020d6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification-card','data' => ['icon' => 'x-circle','color' => 'text-red-500','message' => $errorMessage,'type' => 'error']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notification-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'x-circle','color' => 'text-red-500','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errorMessage),'type' => 'error']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald243037031d21873ca0c386193020d6f)): ?>
<?php $attributes = $__attributesOriginald243037031d21873ca0c386193020d6f; ?>
<?php unset($__attributesOriginald243037031d21873ca0c386193020d6f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald243037031d21873ca0c386193020d6f)): ?>
<?php $component = $__componentOriginald243037031d21873ca0c386193020d6f; ?>
<?php unset($__componentOriginald243037031d21873ca0c386193020d6f); ?>
<?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /var/www/html/totthobox/resources/views/partials/toast.blade.php ENDPATH**/ ?>