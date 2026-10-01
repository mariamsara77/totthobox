<?php

use Livewire\Volt\Component;

?>

<section class="max-w-2xl mx-auto">
    <?php if (isset($component)) { $__componentOriginal42da61123f891e63201d7be28f403427 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42da61123f891e63201d7be28f403427 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.seo','data' => ['title' => 'অনলাইন ক্ষেত্রফল রূপান্তরকারী - বর্গমিটার, বর্গফুট, একর, হেক্টর কনভার্টার | Totthobox','description' => 'সহজেই বর্গমিটার, বর্গফুট, একর, হেক্টর, বর্গকিলোমিটার কনভার্ট করুন। জমি বা স্থানের নিখুঁত ক্ষেত্রফল পরিমাপের জন্য Totthobox Area Converter।','keywords' => 'ক্ষেত্রফল রূপান্তরকারী, area converter, square foot to square meter, একর থেকে বর্গফুট, hectare to acre, জমি মাপার ক্যালকুলেটর, Totthobox']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('seo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'অনলাইন ক্ষেত্রফল রূপান্তরকারী - বর্গমিটার, বর্গফুট, একর, হেক্টর কনভার্টার | Totthobox','description' => 'সহজেই বর্গমিটার, বর্গফুট, একর, হেক্টর, বর্গকিলোমিটার কনভার্ট করুন। জমি বা স্থানের নিখুঁত ক্ষেত্রফল পরিমাপের জন্য Totthobox Area Converter।','keywords' => 'ক্ষেত্রফল রূপান্তরকারী, area converter, square foot to square meter, একর থেকে বর্গফুট, hectare to acre, জমি মাপার ক্যালকুলেটর, Totthobox']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42da61123f891e63201d7be28f403427)): ?>
<?php $attributes = $__attributesOriginal42da61123f891e63201d7be28f403427; ?>
<?php unset($__attributesOriginal42da61123f891e63201d7be28f403427); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42da61123f891e63201d7be28f403427)): ?>
<?php $component = $__componentOriginal42da61123f891e63201d7be28f403427; ?>
<?php unset($__componentOriginal42da61123f891e63201d7be28f403427); ?>
<?php endif; ?>

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Area Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                ক্ষেত্রফল রূপান্তরকারী — Square Meter, Square Foot, Acre, Hectare
            </h2>
        </div>

        <div class="space-y-6 w-full">
            <div class="w-full flex justify-center">
                <?php if (isset($component)) { $__componentOriginalaacc4db8bc37495e56387e4de4703432 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaacc4db8bc37495e56387e4de4703432 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.group.index','data' => ['class' => 'w-full flex justify-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-full flex justify-center']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['id' => 'areaInputUnit','wire:model.live' => 'inputUnit','label' => 'যে ইউনিট থেকে (From)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'areaInputUnit','wire:model.live' => 'inputUnit','label' => 'যে ইউনিট থেকে (From)']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <option value="square_meter">Square Meter (m²)</option>
                        <option value="square_kilometer">Square Kilometer (km²)</option>
                        <option value="square_centimeter">Square Centimeter (cm²)</option>
                        <option value="square_millimeter">Square Millimeter (mm²)</option>
                        <option value="square_mile">Square Mile (mi²)</option>
                        <option value="acre">Acre (ac)</option>
                        <option value="hectare">Hectare (ha)</option>
                        <option value="square_yard">Square Yard (yd²)</option>
                        <option value="square_foot">Square Foot (ft²)</option>
                        <option value="square_inch">Square Inch (in²)</option>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['id' => 'areaInputValue','type' => 'number','wire:model.live.debounce.400ms' => 'inputValue','placeholder' => 'মান লিখুন','label' => 'ইনপুট মান']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'areaInputValue','type' => 'number','wire:model.live.debounce.400ms' => 'inputValue','placeholder' => 'মান লিখুন','label' => 'ইনপুট মান']); ?>
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
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaacc4db8bc37495e56387e4de4703432)): ?>
<?php $attributes = $__attributesOriginalaacc4db8bc37495e56387e4de4703432; ?>
<?php unset($__attributesOriginalaacc4db8bc37495e56387e4de4703432); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaacc4db8bc37495e56387e4de4703432)): ?>
<?php $component = $__componentOriginalaacc4db8bc37495e56387e4de4703432; ?>
<?php unset($__componentOriginalaacc4db8bc37495e56387e4de4703432); ?>
<?php endif; ?>
            </div>

            <div class="w-full flex justify-center">
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'swapAreaUnits','icon' => 'arrows-right-left','variant' => 'subtle','tooltip' => 'ইউনিট অদলবদল করুন']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'swapAreaUnits','icon' => 'arrows-right-left','variant' => 'subtle','tooltip' => 'ইউনিট অদলবদল করুন']); ?>
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

            <div class="w-full flex justify-center">
                <?php if (isset($component)) { $__componentOriginalaacc4db8bc37495e56387e4de4703432 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaacc4db8bc37495e56387e4de4703432 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.group.index','data' => ['class' => 'w-full flex justify-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-full flex justify-center']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['id' => 'areaOutputUnit','wire:model.live' => 'outputUnit','label' => 'যে ইউনিটে রূপান্তর (To)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'areaOutputUnit','wire:model.live' => 'outputUnit','label' => 'যে ইউনিটে রূপান্তর (To)']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <option value="square_meter">Square Meter (m²)</option>
                        <option value="square_kilometer">Square Kilometer (km²)</option>
                        <option value="square_centimeter">Square Centimeter (cm²)</option>
                        <option value="square_millimeter">Square Millimeter (mm²)</option>
                        <option value="square_mile">Square Mile (mi²)</option>
                        <option value="acre">Acre (ac)</option>
                        <option value="hectare">Hectare (ha)</option>
                        <option value="square_yard">Square Yard (yd²)</option>
                        <option value="square_foot">Square Foot (ft²)</option>
                        <option value="square_inch">Square Inch (in²)</option>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['id' => 'areaOutputValue','type' => 'number','wire:model' => 'outputValue','disabled' => true,'placeholder' => 'ফলাফল','label' => 'রূপান্তরিত মান']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'areaOutputValue','type' => 'number','wire:model' => 'outputValue','disabled' => true,'placeholder' => 'ফলাফল','label' => 'রূপান্তরিত মান']); ?>
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
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaacc4db8bc37495e56387e4de4703432)): ?>
<?php $attributes = $__attributesOriginalaacc4db8bc37495e56387e4de4703432; ?>
<?php unset($__attributesOriginalaacc4db8bc37495e56387e4de4703432); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaacc4db8bc37495e56387e4de4703432)): ?>
<?php $component = $__componentOriginalaacc4db8bc37495e56387e4de4703432; ?>
<?php unset($__componentOriginalaacc4db8bc37495e56387e4de4703432); ?>
<?php endif; ?>
            </div>
        </div>

        <div class="mt-24 pt-12 border-t border-zinc-400/25 space-y-6 text-sm text-zinc-600 dark:text-zinc-400">
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">কীভাবে ব্যবহার করবেন?</h3>
            <ul class="list-disc list-inside space-y-2">
                <li>“From” থেকে ইউনিট সিলেক্ট করুন।</li>
                <li>মান লিখুন — ফলাফল সাথে সাথে দেখাবে।</li>
                <li>মাঝের বাটনে ক্লিক করে ইউনিট অদলবদল করতে পারবেন।</li>
            </ul>

            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">গুরুত্বপূর্ণ কনভার্শন</h3>
            <ul class="list-disc list-inside space-y-1">
                <li>1 Square Meter = 10.7639 Square Foot</li>
                <li>1 Acre ≈ 4046.86 Square Meter</li>
                <li>1 Hectare = 2.47105 Acre</li>
                <li>1 Square Kilometer = 100 Hectare</li>
            </ul>
        </div>
    </div>
</section><?php /**PATH /var/www/html/totthobox/resources/views/livewire/website/converter/area-converter.blade.php ENDPATH**/ ?>