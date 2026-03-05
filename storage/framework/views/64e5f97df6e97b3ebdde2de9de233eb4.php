<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

?>

<section class="max-w-2xl mx-auto">
    
    <div class="mb-6">
        <?php echo $__env->make('partials.settings-heading', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <?php if (isset($component)) { $__componentOriginal951a5936e8413b65cd052beecc1fba57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal951a5936e8413b65cd052beecc1fba57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.layout','data' => ['heading' => __('পাবলিক প্রোফাইল'),'subheading' => '@' . $user->username]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('পাবলিক প্রোফাইল')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('@' . $user->username)]); ?>
        <div class="mt-6 space-y-6">
            
            
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-tr from-primary-500 via-indigo-500 to-purple-500 p-0.5 shadow-xl">
                <div class="relative bg-white dark:bg-zinc-950 rounded-[1.4rem] p-6 sm:p-8 overflow-hidden">
                    
                    <div class="absolute -top-20 -right-20 size-48 bg-primary-500/10 rounded-full blur-3xl"></div>
                    
                    <div class="flex flex-col items-center text-center md:flex-row md:items-start md:text-left gap-6 relative z-10">
                        
                        <div class="relative shrink-0">
                            <?php if (isset($component)) { $__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::avatar.index','data' => ['src' => $user->getFirstMediaUrl('avatars', 'thumb'),'initials' => $user->initials(),'class' => 'size-28 sm:size-32 md:size-40 shadow-xl border-4 border-white dark:border-zinc-800 ring-1 ring-black/5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->getFirstMediaUrl('avatars', 'thumb')),'initials' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->initials()),'class' => 'size-28 sm:size-32 md:size-40 shadow-xl border-4 border-white dark:border-zinc-800 ring-1 ring-black/5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690)): ?>
<?php $attributes = $__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690; ?>
<?php unset($__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690)): ?>
<?php $component = $__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690; ?>
<?php unset($__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690); ?>
<?php endif; ?>
                        </div>

                        
                        <div class="flex-1 space-y-4">
                            <div class="space-y-1">
                                <div class="flex flex-col items-center md:items-start gap-2">
                                    <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">
                                        <?php echo e($user->name); ?>

                                    </h1>
                                    <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['size' => 'sm','color' => 'zinc','variant' => 'subtle','class' => 'font-mono lowercase px-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','color' => 'zinc','variant' => 'subtle','class' => 'font-mono lowercase px-3']); ?>
                                        @ <?php echo e($user->username); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $attributes = $__attributesOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $component = $__componentOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__componentOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
                                </div>
                                <p class="text-base sm:text-lg text-zinc-500 dark:text-zinc-400 font-medium flex items-center justify-center md:justify-start gap-2 mt-2">
                                    <?php if (isset($component)) { $__componentOriginalc88322ae7a991a4d285d3bf35e6f7f6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc88322ae7a991a4d285d3bf35e6f7f6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.briefcase','data' => ['class' => 'size-4 text-primary-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.briefcase'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4 text-primary-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc88322ae7a991a4d285d3bf35e6f7f6c)): ?>
<?php $attributes = $__attributesOriginalc88322ae7a991a4d285d3bf35e6f7f6c; ?>
<?php unset($__attributesOriginalc88322ae7a991a4d285d3bf35e6f7f6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc88322ae7a991a4d285d3bf35e6f7f6c)): ?>
<?php $component = $__componentOriginalc88322ae7a991a4d285d3bf35e6f7f6c; ?>
<?php unset($__componentOriginalc88322ae7a991a4d285d3bf35e6f7f6c); ?>
<?php endif; ?>
                                    <?php echo e($user->profession ?? 'সদস্য'); ?>

                                </p>
                            </div>

                            
                            <div class="flex flex-wrap justify-center md:justify-start gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['variant' => 'solid','color' => 'primary','class' => 'rounded-full px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'solid','color' => 'primary','class' => 'rounded-full px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider']); ?>
                                        <?php echo e($role->name); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $attributes = $__attributesOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $component = $__componentOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__componentOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->id() === $user->id): ?>
                                <div class="pt-2">
                                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['href' => ''.e(route('settings.profile')).'','variant' => 'filled','size' => 'sm','icon' => 'pencil-square','wire:navigate' => true,'class' => 'rounded-xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('settings.profile')).'','variant' => 'filled','size' => 'sm','icon' => 'pencil-square','wire:navigate' => true,'class' => 'rounded-xl']); ?>
                                        প্রোফাইল এডিট করুন
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
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 gap-6">

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->bio): ?>
                    <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm">
                        <div class="prose prose-sm sm:prose-base dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
                            <?php echo $user->bio; ?>

                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                
                <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm">
                    <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['size' => 'lg','class' => 'mb-6 flex items-center gap-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg','class' => 'mb-6 flex items-center gap-2']); ?>
                        <?php if (isset($component)) { $__componentOriginal87e883c336cb6c1575660c48879fa4da = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal87e883c336cb6c1575660c48879fa4da = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.user-circle','data' => ['class' => 'size-5 text-primary-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.user-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5 text-primary-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal87e883c336cb6c1575660c48879fa4da)): ?>
<?php $attributes = $__attributesOriginal87e883c336cb6c1575660c48879fa4da; ?>
<?php unset($__attributesOriginal87e883c336cb6c1575660c48879fa4da); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal87e883c336cb6c1575660c48879fa4da)): ?>
<?php $component = $__componentOriginal87e883c336cb6c1575660c48879fa4da; ?>
<?php unset($__componentOriginal87e883c336cb6c1575660c48879fa4da); ?>
<?php endif; ?>
                        পরিচয় ও শিক্ষা
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        
                        <div class="flex items-start gap-4">
                            <div class="p-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-xl text-zinc-500">
                                <?php if (isset($component)) { $__componentOriginale0880cb6488d85d9ca54288aa080a834 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0880cb6488d85d9ca54288aa080a834 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.academic-cap','data' => ['class' => 'size-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.academic-cap'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0880cb6488d85d9ca54288aa080a834)): ?>
<?php $attributes = $__attributesOriginale0880cb6488d85d9ca54288aa080a834; ?>
<?php unset($__attributesOriginale0880cb6488d85d9ca54288aa080a834); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0880cb6488d85d9ca54288aa080a834)): ?>
<?php $component = $__componentOriginale0880cb6488d85d9ca54288aa080a834; ?>
<?php unset($__componentOriginale0880cb6488d85d9ca54288aa080a834); ?>
<?php endif; ?>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block mb-0.5">শিক্ষাগত যোগ্যতা</span>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200"><?php echo e($user->education ?? 'তথ্য নেই'); ?></p>
                            </div>
                        </div>

                        
                        <div class="flex items-start gap-4">
                            <div class="p-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-xl text-zinc-500">
                                <?php if (isset($component)) { $__componentOriginal98fc5ac8c021a7fe2f0e7c6fb76b601d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98fc5ac8c021a7fe2f0e7c6fb76b601d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.list-bullet','data' => ['class' => 'size-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.list-bullet'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98fc5ac8c021a7fe2f0e7c6fb76b601d)): ?>
<?php $attributes = $__attributesOriginal98fc5ac8c021a7fe2f0e7c6fb76b601d; ?>
<?php unset($__attributesOriginal98fc5ac8c021a7fe2f0e7c6fb76b601d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98fc5ac8c021a7fe2f0e7c6fb76b601d)): ?>
<?php $component = $__componentOriginal98fc5ac8c021a7fe2f0e7c6fb76b601d; ?>
<?php unset($__componentOriginal98fc5ac8c021a7fe2f0e7c6fb76b601d); ?>
<?php endif; ?>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block mb-0.5">শ্রেণী/স্তর</span>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200"><?php echo e($user->classLevel?->name ?? '—'); ?></p>
                            </div>
                        </div>

                        
                        <div class="flex items-start gap-4">
                            <div class="p-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-xl text-zinc-500">
                                <?php if (isset($component)) { $__componentOriginal0d48bd54d72df81b49ee07c1a3735f04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d48bd54d72df81b49ee07c1a3735f04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.map-pin','data' => ['class' => 'size-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.map-pin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d48bd54d72df81b49ee07c1a3735f04)): ?>
<?php $attributes = $__attributesOriginal0d48bd54d72df81b49ee07c1a3735f04; ?>
<?php unset($__attributesOriginal0d48bd54d72df81b49ee07c1a3735f04); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d48bd54d72df81b49ee07c1a3735f04)): ?>
<?php $component = $__componentOriginal0d48bd54d72df81b49ee07c1a3735f04; ?>
<?php unset($__componentOriginal0d48bd54d72df81b49ee07c1a3735f04); ?>
<?php endif; ?>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block mb-0.5">বর্তমান এলাকা</span>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200"><?php echo e($user->location ?? 'গোপন'); ?></p>
                            </div>
                        </div>

                        
                        <div class="flex items-start gap-4">
                            <div class="p-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-xl text-zinc-500">
                                <?php if (isset($component)) { $__componentOriginalec36de86b0c342dc76f8040ade97006d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec36de86b0c342dc76f8040ade97006d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.calendar-days','data' => ['class' => 'size-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.calendar-days'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec36de86b0c342dc76f8040ade97006d)): ?>
<?php $attributes = $__attributesOriginalec36de86b0c342dc76f8040ade97006d; ?>
<?php unset($__attributesOriginalec36de86b0c342dc76f8040ade97006d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec36de86b0c342dc76f8040ade97006d)): ?>
<?php $component = $__componentOriginalec36de86b0c342dc76f8040ade97006d; ?>
<?php unset($__componentOriginalec36de86b0c342dc76f8040ade97006d); ?>
<?php endif; ?>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block mb-0.5">নিবন্ধন তারিখ</span>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200"><?php echo e(bn_date($user->created_at?->Format('d M Y'))); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm">
                        <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['size' => 'sm','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','class' => 'mb-4']); ?>আঞ্চলিক অবস্থান <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
                        <div class="space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                                ['label' => 'বিভাগ', 'value' => $user->division?->name, 'icon' => 'building-office'],
                                ['label' => 'জেলা', 'value' => $user->district?->name, 'icon' => 'map-pin'],
                                ['label' => 'থানা', 'value' => $user->thana?->name, 'icon' => 'home']
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center gap-3">
                                    <div class="size-8 flex items-center justify-center bg-primary-50 dark:bg-primary-900/20 text-primary-600 rounded-lg">
                                        <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['name' => $loc['icon'],'class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($loc['icon']),'class' => 'size-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $attributes = $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $component = $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
                                    </div>
                                    <div class="flex-1 border-b border-zinc-100 dark:border-zinc-800 pb-1">
                                        <span class="text-[9px] font-bold uppercase text-zinc-400 block"><?php echo e($loc['label']); ?></span>
                                        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200"><?php echo e($loc['value'] ?? '—'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="p-6 bg-zinc-900 dark:bg-black rounded-3xl text-white shadow-lg relative overflow-hidden">
                        <?php if (isset($component)) { $__componentOriginalf870514c33bb1b53395ba02235f60146 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf870514c33bb1b53395ba02235f60146 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.shield-check','data' => ['class' => 'absolute -top-2 -right-2 size-20 text-white/5 rotate-12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.shield-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'absolute -top-2 -right-2 size-20 text-white/5 rotate-12']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf870514c33bb1b53395ba02235f60146)): ?>
<?php $attributes = $__attributesOriginalf870514c33bb1b53395ba02235f60146; ?>
<?php unset($__attributesOriginalf870514c33bb1b53395ba02235f60146); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf870514c33bb1b53395ba02235f60146)): ?>
<?php $component = $__componentOriginalf870514c33bb1b53395ba02235f60146; ?>
<?php unset($__componentOriginalf870514c33bb1b53395ba02235f60146); ?>
<?php endif; ?>
                        
                        <div class="relative z-10 space-y-5">
                            <div>
                                <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest">অ্যাকাউন্ট স্ট্যাটাস</span>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-sm">ভেরিফিকেশন</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm font-bold <?php echo e($user->email_verified_at ? 'text-green-400' : 'text-orange-400'); ?>">
                                            <?php echo e($user->email_verified_at ? 'Verified' : 'Not verified'); ?>

                                        </span>
                                        <?php if (isset($component)) { $__componentOriginaldb480e8d5d7476402b0c7e6f30ee2bdb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldb480e8d5d7476402b0c7e6f30ee2bdb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.check-badge','data' => ['class' => 'size-5 '.e($user->email_verified_at ? 'text-green-400' : 'text-zinc-600').'','variant' => 'solid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.check-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5 '.e($user->email_verified_at ? 'text-green-400' : 'text-zinc-600').'','variant' => 'solid']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldb480e8d5d7476402b0c7e6f30ee2bdb)): ?>
<?php $attributes = $__attributesOriginaldb480e8d5d7476402b0c7e6f30ee2bdb; ?>
<?php unset($__attributesOriginaldb480e8d5d7476402b0c7e6f30ee2bdb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldb480e8d5d7476402b0c7e6f30ee2bdb)): ?>
<?php $component = $__componentOriginaldb480e8d5d7476402b0c7e6f30ee2bdb; ?>
<?php unset($__componentOriginaldb480e8d5d7476402b0c7e6f30ee2bdb); ?>
<?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t border-white/10">
                                <span class="text-[9px] text-zinc-500 uppercase font-bold">মেম্বার আইডি</span>
                                <p class="text-lg font-mono text-primary-400">#<?php echo e(str_pad($user->id, 6, '0', STR_PAD_LEFT)); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->details || $user->description): ?>
                    <div class="p-6 bg-zinc-50 dark:bg-zinc-900/50 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-3xl">
                        <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>বিস্তারিত তথ্য <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
                        <div class="text-sm sm:text-base leading-relaxed text-zinc-600 dark:text-zinc-400 whitespace-pre-line">
                            <?php echo e($user->details ?? $user->description); ?>

                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal951a5936e8413b65cd052beecc1fba57)): ?>
<?php $attributes = $__attributesOriginal951a5936e8413b65cd052beecc1fba57; ?>
<?php unset($__attributesOriginal951a5936e8413b65cd052beecc1fba57); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal951a5936e8413b65cd052beecc1fba57)): ?>
<?php $component = $__componentOriginal951a5936e8413b65cd052beecc1fba57; ?>
<?php unset($__componentOriginal951a5936e8413b65cd052beecc1fba57); ?>
<?php endif; ?>
</section><?php /**PATH /var/www/html/totthobox/resources/views/livewire/settings/profile-view.blade.php ENDPATH**/ ?>