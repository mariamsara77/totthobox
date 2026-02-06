<?php if (isset($component)) { $__componentOriginal1a71817979719de27eee27e59dc2a686 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a71817979719de27eee27e59dc2a686 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app.header','data' => ['title' => __('Home'),'description' => __('Welcome to the home page'),'image' => asset('images/logo.gif')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Home')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Welcome to the home page')),'image' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(asset('images/logo.gif'))]); ?>

    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-6 mt-4">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">মূল সেবা</h1>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">আপনার প্রয়োজনীয় সকল সেবা এক জায়গায়</p>
        </div>
        <div class="flex flex-wrap justify-center text-center">


            <?php
$firstContact = App\Models\ContactCategory::query()->active()->first();
$firstSign = App\Models\SignCategory::query()->active()->first();
$firstExcel = App\Models\ExcelTutorial::query()->first();

$services = [
    [
        'route' => 'bangladesh.introduction',
        'icon_name' => 'bd-map',
        'label' => 'বাংলাদেশ',
        'details' => 'বাংলাদেশ সকল সম্পর্কিত তথ্য, বিভাগ ও জেলা।',
    ],
    [
        'route' => 'international.all-country',
        'icon_name' => 'earth',
        'label' => 'আন্তর্জাতিক',
        'details' => 'বিভিন্ন দেশের পতাকা, রাজধানী, মুদ্রা ইত্যাদি।',
    ],
    [
        'route' => 'islam.basicislam',
        'icon_name' => 'islamic',
        'label' => 'ইসলামিক',
        'details' => 'ইসলামের মূল শিক্ষা, নামাজ, কালেমা ও দোয়া।',
    ],
    [
        'route' => 'health.calorie-chart',
        'icon_name' => 'health',
        'label' => 'স্বাস্থ',
        'details' => 'সাধারণ স্বাস্থ্য জ্ঞান, প্রাথমিক চিকিৎসা ও পরিচর্যা।',
    ],
    [
        'route' => 'contact.number',
        'slug' => $firstContact?->slug ?? 'police',
        'icon_name' => 'contact',
        'label' => 'জরুরী সেবা',
        'details' => 'জরুরি হেল্পলাইন নম্বর, পিলিশ, অ্যাম্বুলেন্স ও ফায়ার সার্ভিস।',
    ],
    [
        'route' => 'buysell.all',
        'icon_name' => 'buysell',
        'label' => 'বিক্রয়/ক্রয়',
        'details' => 'বিক্রয় ও ক্রয়ের খোলা বাজার।',
    ],
    [
        'route' => 'education.child.practice',
        'icon_name' => 'child-edu',
        'label' => 'শিশুশিক্ষা',
        'details' => 'শিশুদের জন্য বাংলা বর্ণমালা, সংখ্যা ও শিক্ষামূলক উপকরণ।',
    ],
    [
        'route' => 'mcq.home',
        'icon_name' => 'mcq',
        'label' => 'এমসিকিউ',
        'details' => 'বিভিন্ন বিষয়ের উপর অনলাইন এমসিকিউ পরীক্ষা।',
    ],
    [
        'route' => 'converter.currency',
        'icon_name' => 'converter',
        'label' => 'কনভার্টার',
        'details' => 'মুদ্রা, দৈর্ঘ্য, ওজন, সময় ইত্যাদির রূপান্তরকরণ।',
    ],
    [
        'route' => 'signs.sign',
        'slug' => $firstSign?->slug ?? 'emergency',
        'icon_name' => 'sign',
        'label' => 'সংকেত',
        'details' => 'বিভিন্ন স্বাস্থ্য সংকেত এবং তাদের অর্থ।',
    ],
    [
        'route' => 'calendar.calendar',
        'icon_name' => 'calendar',
        'label' => 'ক্যালেন্ডার',
        'details' => 'বাংলাদেশের ক্যালেন্ডার, ছুটির তালিকা ও বিশেষ দিন।',
    ],
    [
        'route' => 'excel.view',
        'slug' => $firstExcel?->slug ?? 'excel-expert',
        'icon_name' => 'table-cells', // এক্সেল বোঝাতে আইকনটি পরিবর্তন করা হয়েছে
        'label' => 'এক্সেল এক্সপার্ট',
        'details' => 'অ্যাডভান্সড এক্সেল ফর্মুলা, ডাটা অ্যানালাইসিস এবং রিপোর্ট তৈরির টিপস।',
    ],
];
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6 p-2 mb-4">
                    <a <?php if(Route::has($service['route'])): ?> href="<?php echo e(isset($service['slug']) ? route($service['route'], ['slug' => $service['slug']]) : route($service['route'])); ?>" <?php else: ?> href="#" <?php endif; ?>
                        wire:navigate.hover
                        class="p-2 h-full flex flex-col items-center text-decoration-none hover:bg-gray-400/10 rounded-4xl bg-gray-500/10 border-gray-400/50 transition-colors duration-200">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($service['icon_name'])): ?>
                            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['name' => ''.e($service['icon_name']).'','class' => 'size-18 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($service['icon_name']).'','class' => 'size-18 text-gray-500']); ?>
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
                        <?php elseif(!empty($service['icon'])): ?>
                            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => ' '.$service['icon'].'','class' => 'size-18 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => ' '.$service['icon'].'','class' => 'size-18 text-gray-500']); ?>
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
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['size' => 'xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xl']); ?><?php echo e($service['label']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
                        <span class="text-gray-500 mt-1 text-sm text-center"><?php echo e($service['details']); ?></span>
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
<?php /**PATH /var/www/html/totthobox/resources/views/welcome.blade.php ENDPATH**/ ?>