<?php

use Livewire\Volt\Component;
use App\Models\SignCategory;
use Illuminate\Support\Facades\Cache;

?>

?>

<section class="max-w-7xl mx-auto transition-colors duration-300">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white leading-tight">
            <?php echo e($category->title); ?>

        </h1>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
            এই ক্যাটাগরির অন্তর্ভুক্ত সকল চিহ্নের তালিকা।
        </p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $signs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div
                class="border border-zinc-400/25 rounded-xl p-3 flex flex-col items-center text-center transition-transform duration-200 hover:scale-105 dark:hover:bg-zinc-700">
                <img src="<?php echo e(Storage::url($sign->image)); ?>" alt="<?php echo e($sign->name_en); ?>"
                    class="h-24 object-contain rounded-md">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo e($sign->name_bn); ?></h3>
                <p class="text-md text-gray-500 dark:text-gray-400"><?php echo e($sign->name_en); ?></p>
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed"><?php echo e($sign->description_bn); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>


</section><?php /**PATH /var/www/html/totthobox/resources/views/livewire/website/signs/sign.blade.php ENDPATH**/ ?>