<?php

use Livewire\Volt\Component;

?>

<div x-data="{
    current: 'bn',
    init() {
        // গুগল ট্রান্সলেটের কুকি ফরম্যাট রিড করা
        const match = document.cookie.match(/googtrans=\/[^/]+\/(\w+)/);
        this.current = match ? match[1] : 'bn';
    },
    setLang(lang) {
        const host = window.location.hostname;
        // রিমুভ করার জন্য সঠিক ব্যাকওয়ার্ড ডেট এক্সপায়ারি
        const expireBase = 'path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
        
        if (lang === 'bn') {
            document.cookie = `googtrans=; domain=.${host}; ${expireBase}`;
            document.cookie = `googtrans=; ${expireBase}`;
        } else {
            // বেইজ ল্যাঙ্গুয়েজ 'bn' থেকে টার্গেটেড ল্যাঙ্গুয়েজে ট্রান্সলেশন সেট করা
            const val = `/bn/${lang}`;
            document.cookie = `googtrans=${val}; domain=.${host}; path=/; SameSite=Lax;`;
            document.cookie = `googtrans=${val}; path=/; SameSite=Lax;`;
        }
        
        // রিলোড দিয়ে ট্রান্সলেশন ক্যাশ এবং ডম রি-ইনিশিয়েট করা
        window.location.reload();
    }
}" data-navigate-ignore wire:ignore class="notranslate">
    
    <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['xModel' => 'current','xOn:change' => 'setLang($event.target.value)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'current','x-on:change' => 'setLang($event.target.value)']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $locales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => ''.e($code).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => ''.e($code).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e($lang['flag'] ?? ''); ?> <?php echo e($lang['name']); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
</div>


<?php $__env->startPush('scripts'); ?>
    <div id="google_translate_element" style="display:none;" data-navigate-ignore></div>

    <script data-navigate-ignore>
        window.googleTranslateElementInit = function () {
            // ১. সেফটি চেক: google.translate লোড হয়েছে কিনা নিশ্চিত করা
            if (typeof google !== 'undefined' && google.translate && google.translate.TranslateElement) {
                new google.translate.TranslateElement({
                    pageLanguage: 'bn',
                    includedLanguages: 'bn,en,ar,hi',
                    // টেম্পোরারি ফিক্স: এরর এড়াতে InlineLayout অবজেক্ট অ্যাক্সেস সেফ রাখা
                    layout: google.translate.TranslateElement.InlineLayout ? google.translate.TranslateElement.InlineLayout.SIMPLE : 0,
                    autoDisplay: false
                }, 'google_translate_element');
            }
        };

        // লাইভওয়্যার SPA নেভিগেশনের পর সেফলি রান করা
        document.addEventListener('livewire:navigated', () => {
            if (typeof window.googleTranslateElementInit === 'function') {
                setTimeout(window.googleTranslateElementInit, 100);
            }
        });
    </script>
    
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer
        data-navigate-ignore></script>
<?php $__env->stopPush(); ?><?php /**PATH /var/www/html/totthobox/resources/views/livewire/global/translator.blade.php ENDPATH**/ ?>