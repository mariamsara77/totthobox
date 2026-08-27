<?php

use Livewire\Volt\Component;

new class extends Component {
    // Parent-এ filter state নেই — শুধু SEO + shell
}; ?>

@php
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
@endphp

<x-seo :title="$seoTitle" :description="$seoDesc" :keywords="$seoKeywords" :image="asset('/og-image.png')" />

<section class="max-w-2xl mx-auto space-y-8">
    <article class="prose dark:prose-invert max-w-none text-center pb-6 border-b border-zinc-200 dark:border-zinc-800">
        <flux:heading size="xl" level="1" class="mb-4">{{ $h1 }}</flux:heading>
        <p class="text-zinc-600 dark:text-zinc-400 text-lg leading-relaxed max-w-3xl mx-auto">{{ $sub }}</p>
    </article>

    {{-- ★ heavy অংশ lazy — এখানে $regions নেই --}}
    <livewire:website.international.grid-all-country lazy />
</section>
