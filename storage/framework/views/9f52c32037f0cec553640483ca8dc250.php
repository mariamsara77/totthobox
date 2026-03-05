<?php if (isset($component)) { $__componentOriginal1a71817979719de27eee27e59dc2a686 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a71817979719de27eee27e59dc2a686 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app.header','data' => ['title' => __('অফলাইন'),'description' => __('আপনি বর্তমানে অফলাইনে আছেন'),'image' => asset('images/logo.gif')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('অফলাইন')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('আপনি বর্তমানে অফলাইনে আছেন')),'image' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(asset('images/logo.gif'))]); ?>
    <div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-6">
        <div class="relative flex items-center justify-center py-10">
            <div
                class="absolute w-44 h-44 bg-zinc-400/10 dark:bg-zinc-500/5 rounded-full animate-[ping_3s_linear_infinite]">
            </div>
            <div
                class="absolute w-32 h-32 bg-zinc-300/20 dark:bg-zinc-700/10 rounded-full animate-[pulse_4s_ease-in-out_infinite]">
            </div>

            <div
                class="relative z-10 p-7 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-white dark:border-zinc-800 animate-[float_4s_ease-in-out_infinite] group">

                <div class="relative flex items-center justify-center">
                    <?php if (isset($component)) { $__componentOriginal03f679e608f7698978062350c9757def = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal03f679e608f7698978062350c9757def = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.wifi','data' => ['variant' => 'outline','class' => 'w-16 h-16 text-zinc-300 dark:text-zinc-600 transition-colors duration-500 group-hover:text-zinc-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.wifi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','class' => 'w-16 h-16 text-zinc-300 dark:text-zinc-600 transition-colors duration-500 group-hover:text-zinc-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal03f679e608f7698978062350c9757def)): ?>
<?php $attributes = $__attributesOriginal03f679e608f7698978062350c9757def; ?>
<?php unset($__attributesOriginal03f679e608f7698978062350c9757def); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal03f679e608f7698978062350c9757def)): ?>
<?php $component = $__componentOriginal03f679e608f7698978062350c9757def; ?>
<?php unset($__componentOriginal03f679e608f7698978062350c9757def); ?>
<?php endif; ?>

                    <div
                        class="absolute w-[120%] h-1 bg-gradient-to-r from-transparent via-red-500 to-transparent rounded-full -rotate-45 shadow-[0_0_15px_rgba(239,68,68,0.4)] opacity-80">
                    </div>

                    <div class="absolute -top-1 -right-1 flex h-6 w-6">
                        <span
                            class="animate-[ping_1.5s_cubic-bezier(0,0,0.2,1)_infinite] absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-40"></span>
                        <div
                            class="relative flex items-center justify-center rounded-full h-6 w-6 bg-amber-500 border-2 border-white dark:border-zinc-900 shadow-sm">
                            <span class="text-[10px] font-black text-white">!</span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="absolute -bottom-2 w-16 h-3 bg-black/10 dark:bg-black/40 blur-xl rounded-[100%] animate-[shadow_4s_ease-in-out_infinite]">
            </div>
        </div>

        <style>
            /* স্মুথ অ্যানিমেশনের জন্য কাস্টম কীফ্রেম */
            @keyframes float {

                0%,
                100% {
                    transform: translateY(0px) scale(1);
                }

                50% {
                    transform: translateY(-15px) scale(1.02);
                }
            }

            @keyframes shadow {

                0%,
                100% {
                    transform: scale(1);
                    opacity: 0.3;
                }

                50% {
                    transform: scale(1.5);
                    opacity: 0.1;
                }
            }
        </style>

        <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['size' => 'xl','level' => '1','class' => 'font-black tracking-tight mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xl','level' => '1','class' => 'font-black tracking-tight mb-3']); ?>
            আপনি অফলাইনে আছেন!
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

        <?php if (isset($component)) { $__componentOriginal43e8c568bbb8b06b9124aad3ccf4ec97 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal43e8c568bbb8b06b9124aad3ccf4ec97 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::subheading','data' => ['size' => 'lg','class' => 'max-w-md mx-auto mb-10 leading-relaxed text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::subheading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg','class' => 'max-w-md mx-auto mb-10 leading-relaxed text-gray-500']); ?>
            আপনার ইন্টারনেট সংযোগ বিচ্ছিন্ন হয়ে গেছে। তবে আপনি আপনার আগে থেকে লোড হওয়া তথ্যগুলো এখান থেকে দেখতে
            পারবেন।
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal43e8c568bbb8b06b9124aad3ccf4ec97)): ?>
<?php $attributes = $__attributesOriginal43e8c568bbb8b06b9124aad3ccf4ec97; ?>
<?php unset($__attributesOriginal43e8c568bbb8b06b9124aad3ccf4ec97); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal43e8c568bbb8b06b9124aad3ccf4ec97)): ?>
<?php $component = $__componentOriginal43e8c568bbb8b06b9124aad3ccf4ec97; ?>
<?php unset($__componentOriginal43e8c568bbb8b06b9124aad3ccf4ec97); ?>
<?php endif; ?>

        <div class="flex flex-col sm:flex-row gap-4 w-full justify-center items-center">
            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['href' => '/','variant' => 'primary','icon' => 'home','class' => 'w-full sm:w-auto px-10 rounded-2xl shadow-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => '/','variant' => 'primary','icon' => 'home','class' => 'w-full sm:w-auto px-10 rounded-2xl shadow-lg']); ?>
                হোম পেজে যান
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
<?php /**PATH /var/www/html/totthobox/resources/views/offline.blade.php ENDPATH**/ ?>