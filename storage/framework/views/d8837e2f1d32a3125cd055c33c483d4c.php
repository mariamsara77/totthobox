<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">

<head>
    <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body class="min-h-screen bg-white dark:bg-zinc-950 antialiased">
    <?php if (isset($component)) { $__componentOriginal17e56bc23bb0192e474b351c4358d446 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal17e56bc23bb0192e474b351c4358d446 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.index','data' => ['collapsible' => true,'sticky' => true,'class' => 'bg-zinc-50 dark:bg-zinc-900 border- border-zinc-200 dark:border-zinc-700 duration-300 overflow-hidden','xData' => true,'xInit' => 'let saved = localStorage.getItem(\'sidebar-scroll\') || 0;
        $el.scrollTop = saved;
        window.addEventListener(\'livewire:navigated\', () => {
            $nextTick(() => {
                $el.scrollTop = localStorage.getItem(\'sidebar-scroll\') || 0;
            });
        });
        $el.addEventListener(\'scroll\', () => {
            localStorage.setItem(\'sidebar-scroll\', $el.scrollTop);
        });']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['collapsible' => true,'sticky' => true,'class' => 'bg-zinc-50 dark:bg-zinc-900 border- border-zinc-200 dark:border-zinc-700 duration-300 overflow-hidden','x-data' => true,'x-init' => 'let saved = localStorage.getItem(\'sidebar-scroll\') || 0;
        $el.scrollTop = saved;
        window.addEventListener(\'livewire:navigated\', () => {
            $nextTick(() => {
                $el.scrollTop = localStorage.getItem(\'sidebar-scroll\') || 0;
            });
        });
        $el.addEventListener(\'scroll\', () => {
            localStorage.setItem(\'sidebar-scroll\', $el.scrollTop);
        });']); ?>

        <?php if (isset($component)) { $__componentOriginal837232b594bf97def5cd04bcaa1b6bb0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal837232b594bf97def5cd04bcaa1b6bb0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.header','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <?php if (isset($component)) { $__componentOriginalc383431c42a29d2b8e3c3717e2d2226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc383431c42a29d2b8e3c3717e2d2226b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.brand','data' => ['href' => '/','name' => 'Totthobox','wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => '/','name' => 'Totthobox','wire:navigate.hover' => true]); ?>
                <?php if (isset($component)) { $__componentOriginalcde46ad147e4d63a74354a0e8c832877 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcde46ad147e4d63a74354a0e8c832877 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.brand','data' => ['class' => 'w-8 h-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-8 h-8']); ?>
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
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc383431c42a29d2b8e3c3717e2d2226b)): ?>
<?php $attributes = $__attributesOriginalc383431c42a29d2b8e3c3717e2d2226b; ?>
<?php unset($__attributesOriginalc383431c42a29d2b8e3c3717e2d2226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc383431c42a29d2b8e3c3717e2d2226b)): ?>
<?php $component = $__componentOriginalc383431c42a29d2b8e3c3717e2d2226b; ?>
<?php unset($__componentOriginalc383431c42a29d2b8e3c3717e2d2226b); ?>
<?php endif; ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal837232b594bf97def5cd04bcaa1b6bb0)): ?>
<?php $attributes = $__attributesOriginal837232b594bf97def5cd04bcaa1b6bb0; ?>
<?php unset($__attributesOriginal837232b594bf97def5cd04bcaa1b6bb0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal837232b594bf97def5cd04bcaa1b6bb0)): ?>
<?php $component = $__componentOriginal837232b594bf97def5cd04bcaa1b6bb0; ?>
<?php unset($__componentOriginal837232b594bf97def5cd04bcaa1b6bb0); ?>
<?php endif; ?>

        <?php echo $__env->yieldPushContent('sidebar'); ?>

        <?php if (isset($component)) { $__componentOriginal4a4f7aa062a095c651c2f80bb685a42a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::spacer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::spacer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a)): ?>
<?php $attributes = $__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a; ?>
<?php unset($__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4a4f7aa062a095c651c2f80bb685a42a)): ?>
<?php $component = $__componentOriginal4a4f7aa062a095c651c2f80bb685a42a; ?>
<?php unset($__componentOriginal4a4f7aa062a095c651c2f80bb685a42a); ?>
<?php endif; ?>



        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('chat.notification-badge', []);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2110338486-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

            <?php if (isset($component)) { $__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::dropdown','data' => ['position' => 'top','align' => 'start','class' => 'max-lg:hidden ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['position' => 'top','align' => 'start','class' => 'max-lg:hidden ']); ?>
                <?php if (isset($component)) { $__componentOriginal78cc0fd2c5379f598cd86bbc76981fe3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78cc0fd2c5379f598cd86bbc76981fe3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.profile','data' => ['avatar' => auth()->user()->getFirstMediaUrl('avatars', 'thumb') ? auth()->user()->getFirstMediaUrl('avatars', 'thumb') : null,'name' => auth()->user()->name,'initials' => auth()->user()->initials(),'icon:trailing' => 'chevrons-up-down']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.profile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['avatar' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->getFirstMediaUrl('avatars', 'thumb') ? auth()->user()->getFirstMediaUrl('avatars', 'thumb') : null),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->name),'initials' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->initials()),'icon:trailing' => 'chevrons-up-down']); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78cc0fd2c5379f598cd86bbc76981fe3)): ?>
<?php $attributes = $__attributesOriginal78cc0fd2c5379f598cd86bbc76981fe3; ?>
<?php unset($__attributesOriginal78cc0fd2c5379f598cd86bbc76981fe3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78cc0fd2c5379f598cd86bbc76981fe3)): ?>
<?php $component = $__componentOriginal78cc0fd2c5379f598cd86bbc76981fe3; ?>
<?php unset($__componentOriginal78cc0fd2c5379f598cd86bbc76981fe3); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.index','data' => ['class' => 'w-[220px] dark:!bg-zinc-800']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-[220px] dark:!bg-zinc-800']); ?>
                    <?php if (isset($component)) { $__componentOriginal10cb598dbf40c537a66b1aebf414029f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal10cb598dbf40c537a66b1aebf414029f = $attributes; } ?>
<?php $component = App\View\Components\AuthDropdown::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AuthDropdown::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal10cb598dbf40c537a66b1aebf414029f)): ?>
<?php $attributes = $__attributesOriginal10cb598dbf40c537a66b1aebf414029f; ?>
<?php unset($__attributesOriginal10cb598dbf40c537a66b1aebf414029f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal10cb598dbf40c537a66b1aebf414029f)): ?>
<?php $component = $__componentOriginal10cb598dbf40c537a66b1aebf414029f; ?>
<?php unset($__componentOriginal10cb598dbf40c537a66b1aebf414029f); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a)): ?>
<?php $attributes = $__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a; ?>
<?php unset($__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a)): ?>
<?php $component = $__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a; ?>
<?php unset($__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888)): ?>
<?php $attributes = $__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888; ?>
<?php unset($__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888)): ?>
<?php $component = $__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888; ?>
<?php unset($__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888); ?>
<?php endif; ?>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('login'),'current' => request()->routeIs('login'),'wire:navigate.hover' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('login')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('login')),'wire:navigate.hover' => true]); ?>
                <?php echo e(__('Login')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal17e56bc23bb0192e474b351c4358d446)): ?>
<?php $attributes = $__attributesOriginal17e56bc23bb0192e474b351c4358d446; ?>
<?php unset($__attributesOriginal17e56bc23bb0192e474b351c4358d446); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal17e56bc23bb0192e474b351c4358d446)): ?>
<?php $component = $__componentOriginal17e56bc23bb0192e474b351c4358d446; ?>
<?php unset($__componentOriginal17e56bc23bb0192e474b351c4358d446); ?>
<?php endif; ?>

    <!-- Mobile User Menu -->
    

    <?php if (isset($component)) { $__componentOriginal95c5505ccad18880318521d2bba3eac7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95c5505ccad18880318521d2bba3eac7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::main','data' => ['class' => '!p-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::main'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!p-0']); ?>
        <?php echo e($slot); ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95c5505ccad18880318521d2bba3eac7)): ?>
<?php $attributes = $__attributesOriginal95c5505ccad18880318521d2bba3eac7; ?>
<?php unset($__attributesOriginal95c5505ccad18880318521d2bba3eac7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95c5505ccad18880318521d2bba3eac7)): ?>
<?php $component = $__componentOriginal95c5505ccad18880318521d2bba3eac7; ?>
<?php unset($__componentOriginal95c5505ccad18880318521d2bba3eac7); ?>
<?php endif; ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <?php app('livewire')->forceAssetInjection(); ?>
<?php echo app('flux')->scripts(); ?>

</body>

</html><?php /**PATH /var/www/html/totthobox/resources/views/components/layouts/app/message.blade.php ENDPATH**/ ?>