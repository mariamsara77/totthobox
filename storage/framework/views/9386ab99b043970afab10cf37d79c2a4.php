<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-dashboard')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.dashboard*'),'icon' => 'chart-bar','heading' => ''.e(__('Admin Dashboard')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.dashboard*')),'icon' => 'chart-bar','heading' => ''.e(__('Admin Dashboard')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'home','href' => route('admin.dashboard'),'current' => request()->routeIs('admin.dashboard'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.dashboard')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.dashboard')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Dashboard')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'home','href' => route('admin.dashboard.session'),'current' => request()->routeIs('admin.dashboard.session'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.dashboard.session')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.dashboard.session')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Session Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'home','href' => url('/pulse'),'current' => request()->routeIs('admin.dashboard.pulse'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(url('/pulse')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.dashboard.pulse')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Pule Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'home','href' => route('admin.dashboard.missing-data'),'current' => request()->routeIs('admin.dashboard.missing-data'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.dashboard.missing-data')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.dashboard.missing-data')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Missing Data Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('system-manager')): ?>
            <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'command-line','href' => route('admin.dashboard.system-manager.terminal-command'),'current' => request()->routeIs('admin.dashboard.system-manager.terminal-command'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'command-line','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.dashboard.system-manager.terminal-command')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.dashboard.system-manager.terminal-command')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('Terminal Command Manager')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'circle-stack','href' => route('admin.dashboard.system-manager.database-monitor'),'current' => request()->routeIs('admin.dashboard.system-manager.database-monitor'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'circle-stack','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.dashboard.system-manager.database-monitor')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.dashboard.system-manager.database-monitor')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('Database Monitor')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('google indexing request')): ?>
            <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'home','href' => route('admin.dashboard.google-indexing'),'current' => request()->routeIs('admin.dashboard.google-indexing'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.dashboard.google-indexing')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.dashboard.google-indexing')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('Google Indexing Request')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>

<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-analytics')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.analytics.*'),'icon' => 'users','heading' => ''.e(__('Analytics')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.analytics.*')),'icon' => 'users','heading' => ''.e(__('Analytics')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'user-group','href' => route('admin.analytics.page-performance'),'current' => request()->routeIs('admin.analytics.page-performance'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user-group','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.analytics.page-performance')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.analytics.page-performance')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Page Analytics')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>



<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('manage-users') || auth()->user()->can('manage-roles')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.users.*'),'icon' => 'users','heading' => ''.e(__('User Manage')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.users.*')),'icon' => 'users','heading' => ''.e(__('User Manage')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-users')): ?>
            <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'user-group','href' => route('admin.users.manage'),'current' => request()->routeIs('admin.users.manage'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user-group','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.users.manage')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.users.manage')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Manage Users')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'user-group','href' => route('admin.users.activity.all'),'current' => request()->routeIs('admin.users.activity.all'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user-group','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.users.activity.all')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.users.activity.all')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('All Users Activity')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-roles')): ?>
            <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'user-group','href' => route('admin.users.role.manage'),'current' => request()->routeIs('admin.users.role.manage'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user-group','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.users.role.manage')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.users.role.manage')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('Manage Users Role')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'user-group','href' => route('admin.users.permission.manage'),'current' => request()->routeIs('admin.users.permission.manage'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user-group','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.users.permission.manage')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.users.permission.manage')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('Manage Users Permission')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('manage-apps') || auth()->user()->can('manage-roles')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.apps.*'),'icon' => 'users','heading' => ''.e(__('App Manage')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.apps.*')),'icon' => 'users','heading' => ''.e(__('App Manage')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-apps')): ?>
            <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'user-group','href' => route('admin.apps.manage'),'current' => request()->routeIs('admin.apps.manage'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user-group','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.apps.manage')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.apps.manage')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('Manage Apps & Resources')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-ai-generator')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.ai-generator.*'),'icon' => 'flag','heading' => ''.e(__('AI Generators')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.ai-generator.*')),'icon' => 'flag','heading' => ''.e(__('AI Generators')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'document-text','href' => route('admin.ai-generator.all-models'),'current' => request()->routeIs('admin.ai-generator.all-models'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.ai-generator.all-models')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.ai-generator.all-models')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('All Models')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-bangladesh')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.bangladesh.*'),'icon' => 'flag','heading' => ''.e(__('Bangladesh Data')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.bangladesh.*')),'icon' => 'flag','heading' => ''.e(__('Bangladesh Data')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'document-text','href' => route('admin.bangladesh.introduction'),'current' => request()->routeIs('admin.bangladesh.introduction'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.bangladesh.introduction')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.bangladesh.introduction')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Introduction Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'home','href' => route('admin.bangladesh.tourism'),'current' => request()->routeIs('admin.bangladesh.tourism'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.bangladesh.tourism')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.bangladesh.tourism')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Tourism Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'clock','href' => route('admin.bangladesh.history'),'current' => request()->routeIs('admin.bangladesh.history'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'clock','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.bangladesh.history')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.bangladesh.history')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('History Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'building-office','href' => route('admin.bangladesh.establishment'),'current' => request()->routeIs('admin.bangladesh.establishment'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'building-office','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.bangladesh.establishment')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.bangladesh.establishment')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Establishment Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'calendar','href' => route('admin.bangladesh.holiday'),'current' => request()->routeIs('admin.bangladesh.holiday'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'calendar','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.bangladesh.holiday')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.bangladesh.holiday')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Holiday Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-location')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.location.*'),'icon' => 'map','heading' => ''.e(__('Location Management')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.location.*')),'icon' => 'map','heading' => ''.e(__('Location Management')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'globe-alt','href' => route('admin.location.division'),'current' => request()->routeIs('admin.location.division'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'globe-alt','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.location.division')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.location.division')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Division Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'building-library','href' => route('admin.location.district'),'current' => request()->routeIs('admin.location.district'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'building-library','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.location.district')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.location.district')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('District Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'building-office-2','href' => route('admin.location.thana'),'current' => request()->routeIs('admin.location.thana'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'building-office-2','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.location.thana')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.location.thana')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Thana Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-person')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.person.*'),'icon' => 'users','heading' => ''.e(__('People Management')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.person.*')),'icon' => 'users','heading' => ''.e(__('People Management')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'user-group','href' => route('admin.person.manage'),'current' => request()->routeIs('admin.person.manage'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user-group','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.person.manage')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.person.manage')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('All People')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'tag','href' => route('admin.person.category'),'current' => request()->routeIs('admin.person.category'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'tag','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.person.category')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.person.category')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Categories')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'briefcase','href' => route('admin.person.position'),'current' => request()->routeIs('admin.person.position'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'briefcase','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.person.position')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.person.position')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Positions')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'clock','href' => route('admin.person.role-history'),'current' => request()->routeIs('admin.person.role-history'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'clock','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.person.role-history')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.person.role-history')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Role History')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-islam')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.islam.*'),'icon' => 'moon','heading' => ''.e(__('Islam')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.islam.*')),'icon' => 'moon','heading' => ''.e(__('Islam')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'book-open','href' => route('admin.islam.basicislam'),'current' => request()->routeIs('admin.islam.basicislam'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'book-open','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.islam.basicislam')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.islam.basicislam')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Basic Islam Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'hand-raised','href' => route('admin.islam.dowa'),'current' => request()->routeIs('admin.islam.dowa'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'hand-raised','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.islam.dowa')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.islam.dowa')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Dowa Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'bookmark','href' => route('admin.islam.para'),'current' => request()->routeIs('admin.islam.para'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bookmark','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.islam.para')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.islam.para')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Para Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'document','href' => route('admin.islam.surah'),'current' => request()->routeIs('admin.islam.surah'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.islam.surah')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.islam.surah')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Surah Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'academic-cap','href' => route('admin.islam.quran'),'current' => request()->routeIs('admin.islam.quran'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'academic-cap','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.islam.quran')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.islam.quran')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Quran Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-health')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.health.*'),'icon' => 'heart','heading' => ''.e(__('Health')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.health.*')),'icon' => 'heart','heading' => ''.e(__('Health')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'tag','href' => route('admin.health.food.category'),'current' => request()->routeIs('admin.health.food.category'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'tag','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.health.food.category')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.health.food.category')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Food Category Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'home','href' => route('admin.health.food'),'current' => request()->routeIs('admin.health.food'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.health.food')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.health.food')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Food Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'beaker','href' => route('admin.health.vitamins'),'current' => request()->routeIs('admin.health.vitamins'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'beaker','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.health.vitamins')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.health.vitamins')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Vitamins Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'cube','href' => route('admin.health.nutrient'),'current' => request()->routeIs('admin.health.nutrient'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'cube','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.health.nutrient')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.health.nutrient')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Nutrient Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'link','href' => route('admin.health.food.nutrient'),'current' => request()->routeIs('admin.health.food.nutrient'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'link','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.health.food.nutrient')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.health.food.nutrient')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Food Nutrient Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'shield-check','href' => route('admin.health.basic-health'),'current' => request()->routeIs('admin.health.basic-health'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'shield-check','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.health.basic-health')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.health.basic-health')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Basic Health')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-contacts')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.contact.*'),'icon' => 'phone','heading' => ''.e(__('Contact')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.contact.*')),'icon' => 'phone','heading' => ''.e(__('Contact')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'folder','href' => route('admin.contact.contact-category'),'current' => request()->routeIs('admin.contact.contact-category'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'folder','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.contact.contact-category')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.contact.contact-category')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Category Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'phone','href' => route('admin.contact.contact-number'),'current' => request()->routeIs('admin.contact.contact-number'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'phone','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.contact.contact-number')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.contact.contact-number')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Number Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-signs')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.sign.*'),'icon' => 'exclamation-triangle','heading' => ''.e(__('Sign')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.sign.*')),'icon' => 'exclamation-triangle','heading' => ''.e(__('Sign')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'folder','href' => route('admin.sign.category-manage'),'current' => request()->routeIs('admin.sign.category-manage'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'folder','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.sign.category-manage')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.sign.category-manage')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Category Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'home','href' => route('admin.sign.manage'),'current' => request()->routeIs('admin.sign.manage'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.sign.manage')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.sign.manage')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Sign Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>


<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-excel')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.excel.*'),'icon' => 'table-cells','heading' => ''.e(__('Excel Expert')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.excel.*')),'icon' => 'table-cells','heading' => ''.e(__('Excel Expert')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'document-text','href' => route('admin.excel.formula-manage'),'current' => request()->routeIs('admin.excel.formula-manage'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.excel.formula-manage')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.excel.formula-manage')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Manage Tutorials')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

        
        
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-news')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.news.*'),'icon' => 'newspaper','heading' => ''.e(__('News Headlines')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.news.*')),'icon' => 'newspaper','heading' => ''.e(__('News Headlines')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'document-text','href' => route('admin.news.headlines-manage'),'current' => request()->routeIs('admin.news.headlines-manage'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.news.headlines-manage')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.news.headlines-manage')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Manage Headlines')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'document-text','href' => route('admin.news.headlines-dashboard'),'current' => request()->routeIs('admin.news.headlines-dashboard'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.news.headlines-dashboard')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.news.headlines-dashboard')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Headlines Dashboard')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>

        
        
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-blogs')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.blogs.*'),'icon' => 'newspaper','heading' => ''.e(__('Blogs')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.blogs.*')),'icon' => 'newspaper','heading' => ''.e(__('Blogs')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'document-text','href' => route('admin.blogs.live-matches'),'current' => request()->routeIs('admin.blogs.live-matches'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.blogs.live-matches')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.blogs.live-matches')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Manage live TV')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-messages')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.messages.*'),'icon' => 'newspaper','heading' => ''.e(__('Messages')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.messages.*')),'icon' => 'newspaper','heading' => ''.e(__('Messages')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'document-text','href' => route('admin.messages.messages-manager'),'current' => request()->routeIs('admin.messages.messages-manager'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.messages.messages-manager')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.messages.messages-manager')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Messages Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-notification')): ?>
    <?php if (isset($component)) { $__componentOriginal31257750338e37e989bcfa8eb3c88bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.group','data' => ['expandable' => true,'expanded' => request()->routeIs('admin.notification.*'),'icon' => 'newspaper','heading' => ''.e(__('Notification')).'','class' => 'grid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.notification.*')),'icon' => 'newspaper','heading' => ''.e(__('Notification')).'','class' => 'grid']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['icon' => 'document-text','href' => route('admin.notification.notification-manager'),'current' => request()->routeIs('admin.notification.notification-manager'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.notification.notification-manager')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.notification.notification-manager')),'wire:navigate.hover' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e(__('Notification Manage')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $attributes = $__attributesOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__attributesOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe86969babb72517ecf97426e7c9330d)): ?>
<?php $component = $__componentOriginalfe86969babb72517ecf97426e7c9330d; ?>
<?php unset($__componentOriginalfe86969babb72517ecf97426e7c9330d); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $attributes = $__attributesOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__attributesOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1)): ?>
<?php $component = $__componentOriginal31257750338e37e989bcfa8eb3c88bb1; ?>
<?php unset($__componentOriginal31257750338e37e989bcfa8eb3c88bb1); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH /var/www/html/totthobox/resources/views/components/admin-menu.blade.php ENDPATH**/ ?>