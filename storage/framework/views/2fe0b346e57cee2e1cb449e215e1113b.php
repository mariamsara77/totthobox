<?php

use Livewire\Volt\Component;
use Carbon\Carbon;
use App\Models\Holiday;

?>

<div class="max-w-md mx-auto antialiased space-y-4">
    <div class="bg-zinc-400/10 p-6 pb-4 rounded-4xl">
        <div class="flex justify-between items-center mb-6">
            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'navigateMonth(\'prev\')','variant' => 'subtle','icon' => 'chevron-left','circular' => true,'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'navigateMonth(\'prev\')','variant' => 'subtle','icon' => 'chevron-left','circular' => true,'size' => 'sm']); ?>
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
            <div class="text-center">
                <h2 class="text-xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">
                    <?php echo e($currentEnglishDate); ?>

                </h2>
                <p class="text-emerald-500 font-bold text-sm">
                    <?php echo e($currentBanglaMonthRange); ?>, <?php echo e(bn_num($currentBanglaYear)); ?> বঙ্গাব্দ
                </p>
            </div>
            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'navigateMonth(\'next\')','variant' => 'subtle','icon' => 'chevron-right','circular' => true,'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'navigateMonth(\'next\')','variant' => 'subtle','icon' => 'chevron-right','circular' => true,'size' => 'sm']); ?>
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

        <div class="flex gap-2 items-center">
            <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['type' => 'date','wire:model.live' => 'selectedDate','size' => 'sm','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'date','wire:model.live' => 'selectedDate','size' => 'sm','class' => 'flex-1']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $attributes = $__attributesOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $component = $__componentOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__componentOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => '$set(\'selectedDate\', \''.e($today).'\')','size' => 'sm','variant' => 'filled','class' => 'rounded-2xl px-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'selectedDate\', \''.e($today).'\')','size' => 'sm','variant' => 'filled','class' => 'rounded-2xl px-5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
আজ <?php echo $__env->renderComponent(); ?>
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
    </div>

    <div class="">
        <div class="grid grid-cols-7 mb-4">
            <?php
                $days = [
                    ['en' => 'Sun', 'bn' => 'রবি'],
                    ['en' => 'Mon', 'bn' => 'সোম'],
                    ['en' => 'Tue', 'bn' => 'মঙ্গল'],
                    ['en' => 'Wed', 'bn' => 'বুধ'],
                    ['en' => 'Thu', 'bn' => 'বৃহঃ'],
                    ['en' => 'Fri', 'bn' => 'শুক্র'],
                    ['en' => 'Sat', 'bn' => 'শনি'],
                ];
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div class="text-center flex flex-col">
                    <span
                        class="text-[10px] font-black uppercase tracking-widest <?php echo e($index >= 5 ? 'text-rose-500' : 'text-zinc-400'); ?>">
                        <?php echo e($day['en']); ?>

                    </span>
                    <span class="text-[9px] font-bold <?php echo e($index >= 5 ? 'text-rose-400/80' : 'text-zinc-400/70'); ?>">
                        <?php echo e($day['bn']); ?>

                    </span>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <div class="grid grid-cols-7 gap-2 px-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = collect($calendarDays)->flatten(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div <?php if($day): ?> wire:click="$set('selectedDate', '<?php echo e($day['date']); ?>')" <?php endif; ?>
                    class="aspect-square relative rounded-xl transition-all duration-300
                                                                                                                            <?php echo e(!$day ? 'opacity-0' : 'cursor-pointer'); ?>

                                                                                                                            <?php echo e($day && $day['date'] === $selectedDate ? 'bg-emerald-500 shadow-lg shadow-emerald-200 dark:shadow-none scale-110 z-10' : ''); ?>

                                                                                                                            <?php echo e($day && $day['date'] !== $selectedDate ? 'hover:bg-zinc-100 dark:hover:bg-zinc-800' : ''); ?>">

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($day): ?>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span
                                class="text-lg font-black leading-none
                                                                                                                                                                                                                                                <?php echo e($day['date'] === $selectedDate ? 'text-white' : ($day['isToday'] ? 'text-emerald-500' : ($day['isWeekend'] ? 'text-rose-500' : 'text-zinc-800 dark:text-zinc-200'))); ?>">
                                <?php echo e($day['engDay']); ?>

                            </span>

                            <span
                                class="text-xs font-bold mt-1 <?php echo e($day['date'] === $selectedDate ? 'text-emerald-100' : 'text-emerald-600/60'); ?>">
                                <?php echo e(bn_num($day['bnDay'])); ?>

                            </span>

                            <div class="absolute flex gap-0.5">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($day['holiday']): ?>
                                    <div
                                        class="w-1 h-1 rounded-full <?php echo e($day['date'] === $selectedDate ? 'bg-white' : $day['holiday']['color']); ?>">
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($day['isToday'] && $day['date'] !== $selectedDate): ?>
                                    <div class="w-1 h-1 rounded-full bg-emerald-500"></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    <div class="px-6 py-2 flex flex-wrap justify-center gap-4 border-t border-zinc-100 dark:border-zinc-800 pt-4">
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-[10px] font-bold text-zinc-500 uppercase">আজ</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            <span class="text-[10px] font-bold text-zinc-500 uppercase">সরকারি ছুটি</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span class="text-[10px] font-bold text-zinc-500 uppercase">ঐচ্ছিক ছুটি</span>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedDate): ?>
        <?php
            $selDateObj = Carbon::parse($selectedDate);
            $selHoliday = $holidays[$selDateObj->format('m-d')] ?? null;
            $selBn = $this->getBanglaDateDetails($selectedDate);
        ?>
        <div class="">
            <div
                class="bg-zinc-400/10 rounded-4xl p-4 border border-zinc-100 dark:border-zinc-800 flex items-center gap-4 shadow-sm transition-all animate-in fade-in">
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center text-white font-black text-xl shadow-inner">
                    <?php echo e($selDateObj->day); ?>

                </div>
                <div class="flex-1">
                    <h4 class="font-black text-zinc-900 dark:text-white leading-tight">
                        <?php echo e($selDateObj->format('l, d F Y')); ?>

                    </h4>
                    <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider">
                        <?php echo e(bn_num($selBn['day'])); ?> <?php echo e($selBn['month']); ?>, <?php echo e(bn_num($selBn['year'])); ?> বঙ্গাব্দ
                    </p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selHoliday): ?>
                    <span
                        class="px-3 py-1 rounded-full font-black text-white <?php echo e($selHoliday['color']); ?> uppercase tracking-tighter shadow-sm">
                        <?php echo e($selHoliday['title']); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /var/www/html/totthobox/resources/views/livewire/website/calendar/calendar.blade.php ENDPATH**/ ?>