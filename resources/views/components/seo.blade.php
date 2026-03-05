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
    config(['app.current_page_title' => $title ?: $siteName]);
    $ogImage = $image ?: asset('favicon.svg'); 
    $cleanDescription = Str::limit(strip_tags($description), 160);
    $url = url()->current();
@endphp

@push('seo_meta')
    {{-- ১. টাইটেল --}}
    <title>{{ $fullTitle }}</title>

    {{-- ২. জেনারেল মেটা --}}
    <meta name="description" content="{{ $cleanDescription }}">
    <meta name="keywords" content="{{ $keywords }}">
    <meta name="author" content="{{ $author }}">
    <link rel="canonical" href="{{ $url }}">

    {{-- ৩. ওপেন গ্রাফ (Facebook) --}}
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $cleanDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:site_name" content="{{ $siteName }}">

    {{-- ৪. টুইটার --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $cleanDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
@endpush