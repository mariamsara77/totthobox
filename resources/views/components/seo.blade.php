@props([
    'title' => null,
    'description' => 'Totthobox - আপনার প্রয়োজনীয় সকল ডিজিটাল সেবা এক জায়গায়।',
    'keywords' => 'Totthobox, তথ্যবক্স, বাংলাদেশ জেলা তথ্য, ছুটির তালিকা, ইসলামিক শিক্ষা, স্বাস্থ্য জ্ঞান, এক্সেল টিপস',
    'image' => null,
    'type' => 'website',
    'author' => 'Totthobox Team'
])

@php
    $siteName = config('app.name', 'Totthobox');
    $fullTitle = $title ? "$title | $siteName" : "$siteName - প্রয়োজনীয় সকল সেবা এক জায়গায়";
    
    // সোশ্যাল মিডিয়া প্রিভিউর জন্য SVG এর বদলে PNG বেস্ট। 
    // যদি ইমেজ না থাকে তবে একটি ডিফল্ট og-image.png (1200x630) ব্যবহার করা উচিত।
    $ogImage = $image ?: asset('og-image.png'); 
    $cleanDescription = Str::limit(strip_tags($description), 160);
    $url = url()->current();
@endphp

@push('seo_meta')
    {{-- ১. টাইটেল --}}
    <title>{{ $fullTitle }}</title>
    <meta name="title" content="{{ $fullTitle }}">

    {{-- ২. জেনারেল মেটা --}}
    <meta name="description" content="{{ $cleanDescription }}">
    <meta name="keywords" content="{{ $keywords }}">
    <meta name="author" content="{{ $author }}">
    <link rel="canonical" href="{{ $url }}">

    {{-- ৩. ওপেন গ্রাফ (Facebook / WhatsApp / Discord) --}}
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $cleanDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="{{ $siteName }}">

    {{-- ৪. টুইটার --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $url }}">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $cleanDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- ৫. সিগন্যাল (যাতে head ফাইল ডুপ্লিকেট টাইটেল না দেয়) --}}
    @php session(['seo_applied' => true]); @endphp
@endpush