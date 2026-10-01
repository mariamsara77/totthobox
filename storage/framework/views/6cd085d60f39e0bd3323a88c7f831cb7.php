<?php

use Livewire\Volt\Component;

?>

<?php
    $site = 'Totthobox';

    // URL থেকে পড়া (ক্রলার / ফুল রিলোডের জন্য) — Livewire Url নয়
    $search = (string) request('search', '');
    $regionFilter = (string) request('regionFilter', '');
    $sortBy = (string) request('sortBy', 'name');

    if ($search !== '') {
        $seoTitle = '"' . str($search)->limit(40) . '" — দেশের তথ্য | ' . $site;
        $seoDesc = '"' . $search . '" সম্পর্কিত দেশের রাজধানী, জনসংখ্যা, আয়তন — ' . $site . '।';
        $seoKeywords = "{$search}, দেশের তালিকা, রাজধানী, জনসংখ্যা, {$site}";
        $h1 = '"' . $search . '" খোঁজার ফলাফল';
        $sub = 'মিল থাকা দেশসমূহের তথ্য';
    } elseif ($regionFilter !== '') {
        $seoTitle = "{$regionFilter} অঞ্চলের দেশসমূহ — রাজধানী ও তথ্য | {$site}";
        $seoDesc = "{$regionFilter} অঞ্চলের দেশের রাজধানী, জনসংখ্যা ও আন্তর্জাতিক কোড — {$site}।";
        $seoKeywords = "{$regionFilter}, দেশের তালিকা, {$regionFilter} দেশ, {$site}";
        $h1 = "{$regionFilter} অঞ্চলের দেশসমূহ";
        $sub = 'এই অঞ্চলের দেশের বিস্তারিত তথ্য';
    } else {
        $seoTitle = "বিশ্বকোষ: পৃথিবীর সব দেশের তালিকা, রাজধানী ও সাধারণ জ্ঞান | {$site}";
        $seoDesc =
            'পৃথিবীর ২৫০+ দেশের রাজধানী, জনসংখ্যা, আয়তন, ভাষা ও আন্তর্জাতিক কোডসহ সম্পূর্ণ তথ্যভাণ্ডার — ' .
            $site .
            '।';
        $seoKeywords =
            'দেশের তালিকা, সব দেশের রাজধানী, পৃথিবীর দেশসমূহ, দেশের জনসংখ্যা, সাধারণ জ্ঞান, বিশ্বকোষ, ' . $site;
        $h1 = 'বিশ্বকোষ: পৃথিবীর সকল দেশের বিস্তারিত তথ্য';
        $sub = 'পৃথিবীতে রয়েছে অসংখ্য বৈচিত্র্যময় দেশ। ২৫০টিরও বেশি দেশের রাজধানী, জনসংখ্যা, আয়তন ও কোড এক জায়গায়।';
    }
?>

<?php if (isset($component)) { $__componentOriginal42da61123f891e63201d7be28f403427 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42da61123f891e63201d7be28f403427 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.seo','data' => ['title' => $seoTitle,'description' => $seoDesc,'keywords' => $seoKeywords,'image' => asset('/og-image.png')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('seo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoTitle),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoDesc),'keywords' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoKeywords),'image' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(asset('/og-image.png'))]); ?>
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

<section class="max-w-2xl mx-auto space-y-8">
    <article class="prose dark:prose-invert max-w-none text-center pb-6 border-b border-zinc-200 dark:border-zinc-800">
        <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['size' => 'xl','level' => '1','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xl','level' => '1','class' => 'mb-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($h1); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
        <p class="text-zinc-600 dark:text-zinc-400 text-lg leading-relaxed max-w-3xl mx-auto"><?php echo e($sub); ?></p>
    </article>

    
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('website.international.grid-all-country', ['lazy' => true]);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-737130263-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
</section><?php /**PATH /var/www/html/totthobox/resources/views/livewire/website/international/all-country.blade.php ENDPATH**/ ?>