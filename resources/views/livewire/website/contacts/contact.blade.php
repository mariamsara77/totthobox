<?php

use Livewire\Component;
use App\Models\ContactCategory;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public ContactCategory $category;

    public function mount(string $slug): void
    {
        $this->category = Cache::remember("contact:category:slug:{$slug}", now()->addDays(30), fn() => ContactCategory::where('slug', $slug)->firstOrFail());
    }
}; ?>

@php
    $catName = $category->name;
    $search = trim((string) request('search', ''));

    if ($search !== '') {
        $seoTitle = "\"{$search}\" — {$catName} নম্বর";
        $seoDesc = "\"{$search}\" সম্পর্কিত {$catName} যোগাযোগ নম্বর ও ঠিকানা।";
        $seoKeywords = "{$search}, {$catName}, জরুরী নম্বর, হেল্পলাইন";
    } else {
        $seoTitle = "জরুরী {$catName} ফোন নম্বর সারা বাংলাদেশ";
        $seoDesc = "সারাদেশের গুরুত্বপূর্ণ {$catName} যোগাযোগ নম্বর ও ঠিকানা। বিভাগ, জেলা, থানা দিয়ে খুঁজুন।";
        $seoKeywords = "{$catName}, {$catName} নম্বর, জরুরী সেবা, হেল্পলাইন, বাংলাদেশ";
    }
@endphp

<x-seo :title="$seoTitle" :description="$seoDesc" :keywords="$seoKeywords" :image="asset('/og-image.png')" />

<div class="max-w-2xl mx-auto space-y-6 pb-20">

    <livewire:website.contacts.contact-grid :category-id="$category->id" :category-name="$category->name" lazy />

    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3">
        <h2 class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            জরুরী {{ $catName }} যোগাযোগ নম্বর সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                বাংলাদেশের সারাদেশের গুরুত্বপূর্ণ <strong>{{ $catName }}</strong> যোগাযোগ নম্বর ও ঠিকানা
                এক জায়গায় খুঁজে নিন। বিভাগ, জেলা ও থানা অনুযায়ী ফিল্টার করে সহজেই প্রয়োজনীয় নম্বর পেয়ে যান।
            </p>
            <p>নম্বরে ক্লিক করে সরাসরি কল করতে পারবেন, কপি বা শেয়ারও করা যায়।</p>
        </div>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-bold text-zinc-800 dark:text-zinc-200">প্রায়শই জিজ্ঞাসিত প্রশ্ন</h2>
        <div class="space-y-2">
            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">
                    <span>কীভাবে নির্দিষ্ট এলাকার নম্বর খুঁজব?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400">
                    উপরের ফিল্টার থেকে বিভাগ, জেলা এবং থানা সিলেক্ট করুন। সার্চ বক্সে নামও লিখে খুঁজতে পারবেন।
                </div>
            </details>
            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">
                    <span>সরাসরি কল করা যায় কি?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400">
                    হ্যাঁ। “কল করুন” বাটনে ক্লিক করলে মোবাইল থেকে সরাসরি কল যাবে।
                </div>
            </details>
            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">
                    <span>শেয়ার কীভাবে করব?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400">
                    “শেয়ার করুন” বাটনে ক্লিক করলে শেয়ার অপশন ওপেন হবে। কপি বাটনে নম্বর ক্লিপবোর্ডে যাবে।
                </div>
            </details>
        </div>
    </section>
</div>
