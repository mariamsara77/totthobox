  <div class="">
      <div class="text-center mb-6 mt-4">
          <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">মূল সেবা</h1>
          <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">আপনার প্রয়োজনীয় সকল সেবা এক জায়গায়</p>
      </div>
      <div class="flex flex-wrap justify-center text-center">


          <?php
          $services = [
          [
          'route' => 'bangladesh.introduction',
          'icon_name' => 'bd-map',
          'label' => 'বাংলাদেশ',
          'details' => 'বাংলাদেশ সকল সম্পর্কিত তথ্য, বিভাগ ও জেলা।'
          ],
          [
          'route' => 'countryflag',
          'icon_name' => 'earth',
          'label' => 'আন্তর্জাতিক',
          'details' => 'বিভিন্ন দেশের পতাকা, রাজধানী, মুদ্রা ইত্যাদি।'
          ],
          [
          'route' => 'islam.basic',
          'icon_name' => 'islamic',
          'label' => 'ইসলামিক',
          'details' => 'ইসলামের মূল শিক্ষা, নামাজ, কালেমা ও দোয়া।'
          ],
          [
          'route' => 'health.knowledge',
          'icon_name' => 'health',
          'label' => 'স্বাস্থ',
          'details' => 'সাধারণ স্বাস্থ্য জ্ঞান, প্রাথমিক চিকিৎসা ও পরিচর্যা।'
          ],
          [
          'route' => 'police',
          'icon_name' => 'contact',
          'label' => 'জরুরী সেবা',
          'details' => 'জরুরি হেল্পলাইন নম্বর, পিলিশ, অ্যাম্বুলেন্স ও ফায়ার সার্ভিস।'
          ],
          [
          'route' => 'bangla',
          'icon_name' => 'child-edu',
          'label' => 'শিশুশিক্ষা',
          'details' => 'শিশুদের জন্য বাংলা বর্ণমালা, সংখ্যা ও শিক্ষামূলক উপকরণ।'
          ],
          [
          'route' => 'mcq.index',
          'icon_name' => 'mcq',
          'label' => 'এমসিকিউ',
          'details' => 'বিভিন্ন বিষয়ের উপর অনলাইন এমসিকিউ পরীক্ষা।'
          ],
          [
          'route' => 'converter.currency',
          'icon_name' => 'converter',
          'label' => 'কনভার্টার',
          'details' => 'মুদ্রা, দৈর্ঘ্য, ওজন, সময় ইত্যাদির রূপান্তরকরণ।'
          ],
          [
          'route' => 'health.sign',
          'icon_name' => 'sign',
          'label' => 'সংকেত',
          'details' => 'বিভিন্ন স্বাস্থ্য সংকেত এবং তাদের অর্থ।'
          ],
          [
          'route' => 'calendar',
          'icon_name' => 'calendar',
          'label' => 'ক্যালেন্ডার',
          'details' => 'বাংলাদেশের ক্যালেন্ডার, ছুটির তালিকা ও বিশেষ দিন।'
          ],
          ];
          ?>

          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="w-1/3 p-2 mb-4">
              <a <?php if(Route::has($service['route'])): ?> href="<?php echo e(route($service['route']) ?? ''); ?>" <?php else: ?> href="#" <?php endif; ?> wire:navigate class="p-2 h-full flex flex-col items-center text-decoration-none hover:bg-gray-400/10 rounded-2xl border border-gray-400/50 transition-colors duration-200">
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($service['icon_name'])): ?>
                  <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['name' => ''.e($service['icon_name']).'','class' => 'text-[#6B747D]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($service['icon_name']).'','class' => 'text-[#6B747D]']); ?>
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
                  <?php echo $service['icon']; ?>

                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <div class="mt-2 font-bold text-lg"><?php echo e($service['label']); ?></div>
                  
              </a>
          </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
  </div>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/menu/grid-menu.blade.php ENDPATH**/ ?>