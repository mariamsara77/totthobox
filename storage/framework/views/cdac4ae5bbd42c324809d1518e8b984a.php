<?php if (isset($component)) { $__componentOriginal5b7892ee212a227d5787ad0d17b33f34 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b7892ee212a227d5787ad0d17b33f34 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::tabs','data' => ['variant' => 'segmented','class' => 'mb-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::tabs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'segmented','class' => 'mb-8']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
        'file.converter.image' => ['Image', 'photo'],
        'file.converter.document' => ['Document', 'document-text'],
        'file.converter.media' => ['Video / Audio', 'film'],
        'file.converter.file-data' => ['Data (JSON/XML)', 'code-bracket'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route => [$label, $icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal94d341e2fe92ba523942b34a2f045b44 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal94d341e2fe92ba523942b34a2f045b44 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::tab.index','data' => ['href' => ''.e(route($route)).'','icon' => $icon,'current' => request()->routeIs($route)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::tab'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route($route)).'','icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs($route))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e($label); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal94d341e2fe92ba523942b34a2f045b44)): ?>
<?php $attributes = $__attributesOriginal94d341e2fe92ba523942b34a2f045b44; ?>
<?php unset($__attributesOriginal94d341e2fe92ba523942b34a2f045b44); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal94d341e2fe92ba523942b34a2f045b44)): ?>
<?php $component = $__componentOriginal94d341e2fe92ba523942b34a2f045b44; ?>
<?php unset($__componentOriginal94d341e2fe92ba523942b34a2f045b44); ?>
<?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b7892ee212a227d5787ad0d17b33f34)): ?>
<?php $attributes = $__attributesOriginal5b7892ee212a227d5787ad0d17b33f34; ?>
<?php unset($__attributesOriginal5b7892ee212a227d5787ad0d17b33f34); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b7892ee212a227d5787ad0d17b33f34)): ?>
<?php $component = $__componentOriginal5b7892ee212a227d5787ad0d17b33f34; ?>
<?php unset($__componentOriginal5b7892ee212a227d5787ad0d17b33f34); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/totthobox/resources/views/components/converter/nav.blade.php ENDPATH**/ ?>