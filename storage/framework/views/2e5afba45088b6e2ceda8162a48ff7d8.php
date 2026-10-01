<?php if (isset($component)) { $__componentOriginal1a71817979719de27eee27e59dc2a686 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a71817979719de27eee27e59dc2a686 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app.header','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


    <div class="max-w-2xl mx-auto mt-8">

        <section role="alert" aria-live="polite" class="rounded-4xl flex flex-col items-center text-center gap-6">

            <!-- Big numeric header -->
            <h1
                class="font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-pink-500
           leading-none select-none text-[clamp(3.5rem,8vw,7rem)]">
                404
            </h1>

            <div class="max-w-prose">
                <h2 class="font-semibold text-zinc-900 dark:text-zinc-100 text-[clamp(1.25rem,2vw,1.875rem)]">
                    দুঃখিত! পৃষ্ঠা পাওয়া যায়নি
                </h2>

                <p class="mt-3 text-base  text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    আপনি হয়তো ভুল URL এ চলে গেছেন বা পৃষ্ঠাটি মুছে ফেলা হয়েছে। হোমপেজে ফিরে যান বা নীচের বিকল্প ব্যবহার
                    করুন।
                </p>
            </div>


            <!-- Action buttons -->
            <div class="mt-4 w-full flex items-center justify-center gap-4">

                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'primary','href' => ''.e(url('/')).'','class' => '!rounded-full','ariaLabel' => 'হোমপেজে ফিরে যান','icon' => 'home']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','href' => ''.e(url('/')).'','class' => '!rounded-full','aria-label' => 'হোমপেজে ফিরে যান','icon' => 'home']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    হোমপেজে ফিরে যান
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

                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'filled','onclick' => 'window.location.href = document.referrer ? document.referrer : \''.e(url('/')).'\';','class' => '!rounded-full','ariaLabel' => 'পেছনে ফিরে যান','icon' => 'arrow-left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'filled','onclick' => 'window.location.href = document.referrer ? document.referrer : \''.e(url('/')).'\';','class' => '!rounded-full','aria-label' => 'পেছনে ফিরে যান','icon' => 'arrow-left']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    পেছনে ফিরে যান
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

            <!-- Optional contextual links -->
            <nav aria-label="alternative navigation" class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">
                <ul class="flex flex-wrap gap-4 justify-center">
                    <li><a href="<?php echo e(url('/help')); ?>"
                            class="underline hover:text-zinc-800 dark:hover:text-zinc-100">হেল্প সেন্টার</a></li>
                    <li><a href="<?php echo e(url('/status')); ?>"
                            class="underline hover:text-zinc-800 dark:hover:text-zinc-100">সিস্টেম স্ট্যাটাস</a></li>
                    <li><a href="<?php echo e(url('/contact-us')); ?>"
                            class="underline hover:text-zinc-800 dark:hover:text-zinc-100">আমাদেরকে জানাবেন</a></li>
                </ul>
            </nav>

        </section>
    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1a71817979719de27eee27e59dc2a686)): ?>
<?php $attributes = $__attributesOriginal1a71817979719de27eee27e59dc2a686; ?>
<?php unset($__attributesOriginal1a71817979719de27eee27e59dc2a686); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1a71817979719de27eee27e59dc2a686)): ?>
<?php $component = $__componentOriginal1a71817979719de27eee27e59dc2a686; ?>
<?php unset($__componentOriginal1a71817979719de27eee27e59dc2a686); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/totthobox/resources/views/errors/404.blade.php ENDPATH**/ ?>