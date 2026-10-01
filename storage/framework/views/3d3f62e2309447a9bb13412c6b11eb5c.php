

<div class="space-y-8 animate-pulse">

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 0; $i < 4; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php if (isset($component)) { $__componentOriginale27b9f538b18752a4e62486fb1a784aa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale27b9f538b18752a4e62486fb1a784aa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::skeleton.index','data' => ['animate' => 'shimmer','class' => 'aspect-[4/1] h-30 size-full rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::skeleton'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['animate' => 'shimmer','class' => 'aspect-[4/1] h-30 size-full rounded-lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale27b9f538b18752a4e62486fb1a784aa)): ?>
<?php $attributes = $__attributesOriginale27b9f538b18752a4e62486fb1a784aa; ?>
<?php unset($__attributesOriginale27b9f538b18752a4e62486fb1a784aa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale27b9f538b18752a4e62486fb1a784aa)): ?>
<?php $component = $__componentOriginale27b9f538b18752a4e62486fb1a784aa; ?>
<?php unset($__componentOriginale27b9f538b18752a4e62486fb1a784aa); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    
    <div class="bg-zinc-400/10 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
        <div class="flex gap-4">
            <div class="h-10 bg-zinc-400/10 rounded-lg flex-1 min-w-[200px]"></div>
            <div class="h-10 bg-zinc-400/10 rounded-lg w-full sm:w-36"></div>
            <div class="h-10 bg-zinc-400/10 rounded-lg w-full sm:w-40"></div>
        </div>
        <div class="mt-3 h-4 bg-zinc-400/10 rounded w-40"></div>
    </div>

    
    <div class="grid md:grid-cols-2 gap-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 0; $i < 6; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="bg-zinc-400/10 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                
                <div class="flex items-center gap-4 p-4 border-b border-zinc-400/25">
                    <div class="w-16 h-11 bg-zinc-400/10 rounded shrink-0"></div>
                    <div class="space-y-2 flex-1 min-w-0">
                        <div class="h-5 bg-zinc-400/10 rounded w-4/4"></div>
                        <div class="h-4 bg-zinc-400/10 rounded-full w-20"></div>
                    </div>
                </div>

                
                <div class="p-4 space-y-3">
                    <div class="space-y-2">
                        <div class="h-3.5 bg-zinc-400/10 rounded w-full"></div>
                        <div class="h-3.5 bg-zinc-400/10 rounded w-5/6"></div>
                        <div class="h-3.5 bg-zinc-400/10 rounded w-4/6"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <div class="space-y-1">
                            <div class="h-3 bg-zinc-400/10 rounded w-16"></div>
                            <div class="h-4 bg-zinc-400/10 rounded w-12"></div>
                        </div>
                        <div class="space-y-1">
                            <div class="h-3 bg-zinc-400/10 rounded w-14"></div>
                            <div class="h-4 bg-zinc-400/10 rounded w-10"></div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <div class="h-8 bg-zinc-400/10 rounded-lg w-24"></div>
                    </div>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

</div>
<?php /**PATH /var/www/html/totthobox/resources/views/partials/countries-skeleton.blade.php ENDPATH**/ ?>