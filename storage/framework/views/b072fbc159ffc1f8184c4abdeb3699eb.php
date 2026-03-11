<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'description' => 'Totthobox - আপনার প্রয়োজনীয় সকল ডিজিটাল সেবা এক জায়গায়।',
    'keywords' => 'Totthobox, তথ্যবক্স, বাংলাদেশ জেলা তথ্য, ছুটির তালিকা, ইসলামিক শিক্ষা, স্বাস্থ্য জ্ঞান, এক্সেল টিপস',
    'image' => null,
    'type' => 'website',
    'author' => 'Totthobox Team'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title' => null,
    'description' => 'Totthobox - আপনার প্রয়োজনীয় সকল ডিজিটাল সেবা এক জায়গায়।',
    'keywords' => 'Totthobox, তথ্যবক্স, বাংলাদেশ জেলা তথ্য, ছুটির তালিকা, ইসলামিক শিক্ষা, স্বাস্থ্য জ্ঞান, এক্সেল টিপস',
    'image' => null,
    'type' => 'website',
    'author' => 'Totthobox Team'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $siteName = config('app.name', 'Totthobox');
    $fullTitle = $title ? "$title | $siteName" : "$siteName - প্রয়োজনীয় সকল সেবা এক জায়গায়";
    
    // সোশ্যাল মিডিয়া প্রিভিউর জন্য SVG এর বদলে PNG বেস্ট। 
    // যদি ইমেজ না থাকে তবে একটি ডিফল্ট og-image.png (1200x630) ব্যবহার করা উচিত।
    $ogImage = $image ?: asset('og-image.png'); 
    $cleanDescription = Str::limit(strip_tags($description), 160);
    $url = url()->current();
?>

<?php $__env->startPush('seo_meta'); ?>
    
    <title><?php echo e($fullTitle); ?></title>
    <meta name="title" content="<?php echo e($fullTitle); ?>">

    
    <meta name="description" content="<?php echo e($cleanDescription); ?>">
    <meta name="keywords" content="<?php echo e($keywords); ?>">
    <meta name="author" content="<?php echo e($author); ?>">
    <link rel="canonical" href="<?php echo e($url); ?>">

    
    <meta property="og:type" content="<?php echo e($type); ?>">
    <meta property="og:url" content="<?php echo e($url); ?>">
    <meta property="og:title" content="<?php echo e($fullTitle); ?>">
    <meta property="og:description" content="<?php echo e($cleanDescription); ?>">
    <meta property="og:image" content="<?php echo e($ogImage); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?php echo e($siteName); ?>">

    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo e($url); ?>">
    <meta name="twitter:title" content="<?php echo e($fullTitle); ?>">
    <meta name="twitter:description" content="<?php echo e($cleanDescription); ?>">
    <meta name="twitter:image" content="<?php echo e($ogImage); ?>">

    
    <?php session(['seo_applied' => true]); ?>
<?php $__env->stopPush(); ?><?php /**PATH /var/www/html/totthobox/resources/views/components/seo.blade.php ENDPATH**/ ?>