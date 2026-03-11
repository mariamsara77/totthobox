<?php

use Livewire\Volt\Component;
use App\Models\Visitor;
use App\Models\VisitorSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

?>

<section>
    <div class="relative">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Live Analytics</h3>
        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Live</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div
            class="relative bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800 transition-all duration-300 hover:scale-105 hover:shadow-lg group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 dark:text-blue-300">Active Visitors</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1 transition-all duration-500">
                        <?php echo e(number_format($activeVisitors)); ?>

                    </p>
                    <p class="text-xs text-blue-500 dark:text-blue-400 mt-1">Last 5 minutes</p>
                </div>
                <div
                    class="p-3 bg-blue-500 rounded-lg shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <?php if (isset($component)) { $__componentOriginal4e4f522adb19cc742fb2b199df7e6c95 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e4f522adb19cc742fb2b199df7e6c95 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.users','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.users'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e4f522adb19cc742fb2b199df7e6c95)): ?>
<?php $attributes = $__attributesOriginal4e4f522adb19cc742fb2b199df7e6c95; ?>
<?php unset($__attributesOriginal4e4f522adb19cc742fb2b199df7e6c95); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e4f522adb19cc742fb2b199df7e6c95)): ?>
<?php $component = $__componentOriginal4e4f522adb19cc742fb2b199df7e6c95; ?>
<?php unset($__componentOriginal4e4f522adb19cc742fb2b199df7e6c95); ?>
<?php endif; ?>
                </div>
            </div>
        </div>

        <div
            class="relative bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800 transition-all duration-300 hover:scale-105 hover:shadow-lg group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-green-600 dark:text-green-300">Active Sessions</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100 mt-1 transition-all duration-500">
                        <?php echo e(number_format($activeSessions)); ?>

                    </p>
                    <p class="text-xs text-green-500 dark:text-green-400 mt-1">Last 30 minutes</p>
                </div>
                <div
                    class="p-3 bg-green-500 rounded-lg shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <?php if (isset($component)) { $__componentOriginal82067727c95f13dc4198f80e35cb9c11 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal82067727c95f13dc4198f80e35cb9c11 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.chart-bar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.chart-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal82067727c95f13dc4198f80e35cb9c11)): ?>
<?php $attributes = $__attributesOriginal82067727c95f13dc4198f80e35cb9c11; ?>
<?php unset($__attributesOriginal82067727c95f13dc4198f80e35cb9c11); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal82067727c95f13dc4198f80e35cb9c11)): ?>
<?php $component = $__componentOriginal82067727c95f13dc4198f80e35cb9c11; ?>
<?php unset($__componentOriginal82067727c95f13dc4198f80e35cb9c11); ?>
<?php endif; ?>
                </div>
            </div>
        </div>

        <div
            class="relative bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 rounded-xl p-4 border border-purple-100 dark:border-purple-800 transition-all duration-300 hover:scale-105 hover:shadow-lg group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-purple-600 dark:text-purple-300">Page Views/Min</p>
                    <p class="text-2xl font-bold text-purple-900 dark:text-blue-100 mt-1 transition-all duration-500">
                        <?php echo e(number_format($currentPageViews)); ?>

                    </p>
                    <p class="text-xs text-purple-500 dark:text-purple-400 mt-1">Last minute</p>
                </div>
                <div
                    class="p-3 bg-purple-500 rounded-lg shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <?php if (isset($component)) { $__componentOriginalb07d48b1997b1d8be5c0db0dac7941df = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb07d48b1997b1d8be5c0db0dac7941df = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.document-chart-bar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.document-chart-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb07d48b1997b1d8be5c0db0dac7941df)): ?>
<?php $attributes = $__attributesOriginalb07d48b1997b1d8be5c0db0dac7941df; ?>
<?php unset($__attributesOriginalb07d48b1997b1d8be5c0db0dac7941df); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb07d48b1997b1d8be5c0db0dac7941df)): ?>
<?php $component = $__componentOriginalb07d48b1997b1d8be5c0db0dac7941df; ?>
<?php unset($__componentOriginalb07d48b1997b1d8be5c0db0dac7941df); ?>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</section><?php /**PATH /var/www/html/totthobox/resources/views/livewire/admin/dashboard/real-time-visitors.blade.php ENDPATH**/ ?>