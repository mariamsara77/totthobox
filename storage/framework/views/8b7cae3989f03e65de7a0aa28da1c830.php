<?php

use App\Models\ChatSession;
use Livewire\Attributes\Url;
use Livewire\Component;

?>

<?php if (isset($component)) { $__componentOriginal061367e9976089f15083f05bd78a70e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal061367e9976089f15083f05bd78a70e4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.nav','data' => ['class' => 'overflow-hidden p-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'overflow-hidden p-2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


    
    <div class="mb-3 px-1">
        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['href' => ''.e(route('ai.chat.show')).'','wire:navigate' => true,'size' => 'sm','variant' => 'ghost','icon' => 'plus','class' => 'w-full justify-start text-xs text-emerald-600 dark:text-emerald-400
                   hover:bg-emerald-50 dark:hover:bg-emerald-950/30
                   rounded-xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('ai.chat.show')).'','wire:navigate' => true,'size' => 'sm','variant' => 'ghost','icon' => 'plus','class' => 'w-full justify-start text-xs text-emerald-600 dark:text-emerald-400
                   hover:bg-emerald-50 dark:hover:bg-emerald-950/30
                   rounded-xl']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            নতুন প্রশ্ন শুরু করো
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->sessions->isNotEmpty()): ?>
    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500 px-2 mb-2">
        আগের প্রশ্নগুলো
    </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <?php
    $isNew = $newlyCreatedUuid === $session->uuid;
    ?>

    <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'sidebar-item-'.e($session->uuid).''; ?>wire:key="sidebar-item-<?php echo e($session->uuid); ?>" x-data="{ isNew: <?php echo \Illuminate\Support\Js::from($isNew)->toHtml() ?>, show: <?php echo \Illuminate\Support\Js::from(!$isNew)->toHtml() ?> }"
        x-init="if (isNew) { setTimeout(() => show = true, 80) }" x-show="show"
        x-transition:enter=" ease-in duration-[1500ms]" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" class="mb-2">
        <?php if (isset($component)) { $__componentOriginalfe86969babb72517ecf97426e7c9330d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe86969babb72517ecf97426e7c9330d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.item','data' => ['href' => ''.e(route('ai.chat.show', $session->uuid)).'','wire:navigate' => true,'current' => $currentUuid === $session->uuid,'class' => 'group relative rounded-xl py-2 pr-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('ai.chat.show', $session->uuid)).'','wire:navigate' => true,'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentUuid === $session->uuid),'class' => 'group relative rounded-xl py-2 pr-2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <div class="flex flex-col min-w-0 flex-1 gap-0.5">
                <span class="truncate text-sm leading-snug"><?php echo e($session->title); ?></span>

                
                
    </div>

    
    <div
        class="absolute right-1 top-0 bottom-0 flex items-center
                                                                                    opacity-0 group-hover:opacity-100 ">
        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','size' => 'xs','icon' => 'trash','wire:click.stop' => 'deleteSession('.e($session->id).')','wire:confirm' => 'এই প্রশ্নটি মুছে ফেলতে চাও?','class' => 'text-zinc-400 hover:text-zinc-400/25']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'xs','icon' => 'trash','wire:click.stop' => 'deleteSession('.e($session->id).')','wire:confirm' => 'এই প্রশ্নটি মুছে ফেলতে চাও?','class' => 'text-zinc-400 hover:text-zinc-400/25']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
    </div>
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
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    <div class="flex flex-col items-center justify-center py-8 gap-2 opacity-50">
        <span class="text-2xl">📚</span>
        <p class="text-xs text-zinc-400 text-center">
            এখনো কোনো প্রশ্ন করোনি।<br>শুরু করো!
        </p>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal061367e9976089f15083f05bd78a70e4)): ?>
<?php $attributes = $__attributesOriginal061367e9976089f15083f05bd78a70e4; ?>
<?php unset($__attributesOriginal061367e9976089f15083f05bd78a70e4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal061367e9976089f15083f05bd78a70e4)): ?>
<?php $component = $__componentOriginal061367e9976089f15083f05bd78a70e4; ?>
<?php unset($__componentOriginal061367e9976089f15083f05bd78a70e4); ?>
<?php endif; ?><?php /**PATH /var/www/html/totthobox/resources/views/livewire/ai/sidebar-history.blade.php ENDPATH**/ ?>