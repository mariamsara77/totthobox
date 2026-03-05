<!-- Contact Modal -->
<?php if (isset($component)) { $__componentOriginal8cc9d3143946b992b324617832699c5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cc9d3143946b992b324617832699c5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.index','data' => ['name' => 'contactModal','wire:model' => 'showContactModal','class' => 'w-full max-w-lg','closeButton' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'contactModal','wire:model' => 'showContactModal','class' => 'w-full max-w-lg','close-button' => true]); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPost): ?>
        <div class="">
            <div class="text-center mb-5">
                <h3 class="text-xl font-semibold">
                    পণ্যের বিস্তারিত তথ্য
                </h3>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPost->images && $selectedPost->images->count() > 0): ?>
                <div data-viewer-gallery="post" class="flex overflow-x-auto gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $selectedPost->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <img src="<?php echo e(asset($image->path)); ?>"
                            class="w-full h-full object-cover viewer-image transition-all duration-500 ease-in-out cursor-pointer"
                            loading="lazy" alt="Post Image">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Description -->
            <div class="mb-4 p-4 rounded-lg border border-zinc-400/25">
                <h4 class="font-medium text-gray-900 dark:text-white mb-2">বর্ণনা</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    <?php echo nl2br(e($selectedPost->description ?? 'কোনো বর্ণনা নেই')); ?>

                </p>
            </div>

            <!-- Note -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPost->note): ?>
                <div class="mb-4 p-4 rounded-lg border border-zinc-400/25">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">নোট</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        <?php echo nl2br(e($selectedPost->note)); ?>

                    </p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Condition & Stock -->
            <div class="grid grid-cols-2 gap-4 p-4 rounded-lg border border-zinc-400/25">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">অবস্থা</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo e(ucfirst($selectedPost->condition ?? 'অজানা')); ?>

                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">স্টক</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        <?php echo e($selectedPost->stock ?? '0'); ?>

                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $attributes = $__attributesOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__attributesOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $component = $__componentOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__componentOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/totthobox/resources/views/partials/buy-sell/contact-modal.blade.php ENDPATH**/ ?>