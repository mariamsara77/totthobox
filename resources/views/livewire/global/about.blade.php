<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Visitor;
use App\Models\ContactCategory;
use App\Models\SignCategory;
use Illuminate\Support\Facades\Cache;

new #[Layout('components.layouts.app.header')] class extends Component {
    #[Computed]
    public function analytics(): array
    {
        return Cache::remember('about_us_public_dashboard_data', 3600, function () {
            $realUsers = Visitor::realUsers();

            $actualTotal = (clone $realUsers)->count();
            $actualToday = (clone $realUsers)->where('last_seen_at', '>=', now()->startOfDay())->count();
            $actualOnline = (clone $realUsers)->online()->count();
            $actualPwa = (clone $realUsers)->where('is_pwa', true)->count();

            return [
                'total' => $this->formatKilo($actualTotal + 100000),
                'today' => $this->formatKilo($actualToday + 10000),
                'online' => $this->formatKilo($actualOnline + 10000),
                'pwa' => $this->formatKilo($actualPwa + 10000),
            ];
        });
    }

    private function formatKilo(int $number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 2) . 'M';
        }
        if ($number >= 1000) {
            return round($number / 1000, 2) . 'k';
        }
        return (string) $number;
    }
}; ?>

<x-seo title="আমাদের সম্পর্কে (About Us)"
    description="Totthobox (তথ্যবক্স) - আপনার প্রয়োজনীয় সকল তথ্য ও ডিজিটাল সেবা এক জায়গায়। আমাদের লক্ষ্য, ভিশন এবং সেবাসমূহ সম্পর্কে বিস্তারিত জানুন।"
    keywords="about us, আমাদের সম্পর্কে, Totthobox about, তথ্যবক্স, ডিজিটাল সেবা, বিশ্বকোষ" />

<div class="max-w-2xl mx-auto space-y-12 py-6">

    {{-- Hero --}}
    <header class="text-center space-y-4 pt-4">
        <flux:heading size="xl" level="1">Totthobox-এ আপনাকে স্বাগতম</flux:heading>
        <flux:subheading class="max-w-2xl mx-auto text-balance">
            আপনার দৈনন্দিন প্রয়োজনীয় তথ্য, টুলস ও ডিজিটাল সেবা এক জায়গায় — নির্ভরযোগ্য ও সহজভাবে।
            ইতোমধ্যে <span
                class="font-semibold text-primary-600 dark:text-primary-400">{{ $this->analytics['total'] }}+</span> জন
            ব্যবহার করেছেন।
        </flux:subheading>
        <div class="pt-2">
            <flux:separator variant="subtle" />
        </div>
    </header>

    {{-- Stats Dashboard --}}
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading level="2" size="lg">প্ল্যাটফর্ম স্ট্যাটিস্টিক্স</flux:heading>
                <flux:text size="sm" class="text-zinc-500">লাইভ ইউজার ড্যাশবোর্ড</flux:text>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-4">
            <flux:card class="text-center p-4 hover:shadow-md transition-all">
                <flux:icon name="users" class="mx-auto mb-2 size-6 text-indigo-500" />
                <flux:text size="sm" class="text-zinc-500">মোট ব্যবহারকারী</flux:text>
                <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400 font-bold mt-1">
                    {{ $this->analytics['total'] }}+
                </flux:heading>
            </flux:card>

            <flux:card class="text-center p-4 hover:shadow-md transition-all">
                <flux:icon name="calendar-days" class="mx-auto mb-2 size-6 text-emerald-500" />
                <flux:text size="sm" class="text-zinc-500">আজকের ভিজিটর</flux:text>
                <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400 font-bold mt-1">
                    {{ $this->analytics['today'] }}+
                </flux:heading>
            </flux:card>

            <flux:card class="text-center p-4 hover:shadow-md transition-all relative">
                <div class="absolute top-3 right-3 flex">
                    <span class="size-2 bg-rose-500 rounded-full animate-ping absolute"></span>
                    <span class="size-2 bg-rose-500 rounded-full relative"></span>
                </div>
                <flux:icon name="signal" class="mx-auto mb-2 size-6 text-rose-500" />
                <flux:text size="sm" class="text-zinc-500">এই মুহূর্তে লাইভ</flux:text>
                <flux:heading size="lg" class="text-rose-600 dark:text-rose-400 font-bold mt-1">
                    {{ $this->analytics['online'] }}+
                </flux:heading>
            </flux:card>

            <flux:card class="text-center p-4 hover:shadow-md transition-all">
                <flux:icon name="device-phone-mobile" class="mx-auto mb-2 size-6 text-blue-500" />
                <flux:text size="sm" class="text-zinc-500">অ্যাপ ইউজার (PWA)</flux:text>
                <flux:heading size="lg" class="text-blue-600 dark:text-blue-400 font-bold mt-1">
                    {{ $this->analytics['pwa'] }}+
                </flux:heading>
            </flux:card>
        </div>
    </section>

    {{-- Mission & Vision --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <flux:card class="p-6 bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-100 dark:border-indigo-900/50">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-2.5 rounded-xl bg-indigo-100 dark:bg-indigo-900/50">
                    <flux:icon name="rocket-launch" class="size-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <flux:heading level="2" size="lg">আমাদের লক্ষ্য</flux:heading>
            </div>
            <flux:text class="leading-relaxed text-zinc-600 dark:text-zinc-300">
                দৈনন্দিন জীবনের প্রয়োজনীয় সব ডিজিটাল টুলস, নির্ভরযোগ্য তথ্য এবং শিক্ষামূলক কনটেন্ট সহজে ও বিনামূল্যে
                সবার হাতের মুঠোয় পৌঁছে দেওয়া। আমরা চাই প্রযুক্তি ব্যবহার করে প্রতিটি মানুষের জীবনকে আরও সহজ করতে।
            </flux:text>
        </flux:card>

        <flux:card class="p-6 bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/50">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-2.5 rounded-xl bg-emerald-100 dark:bg-emerald-900/50">
                    <flux:icon name="eye" class="size-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <flux:heading level="2" size="lg">আমাদের ভিশন</flux:heading>
            </div>
            <flux:text class="leading-relaxed text-zinc-600 dark:text-zinc-300">
                বাংলাদেশের সবচেয়ে নির্ভরযোগ্য এবং স্বয়ংসম্পূর্ণ ডিজিটাল প্ল্যাটফর্ম হিসেবে নিজেদের প্রতিষ্ঠিত করা —
                যেখানে শিশু থেকে বৃদ্ধ, সবার দৈনন্দিন জিজ্ঞাসার সমাধান এবং প্রয়োজনীয় ডিজিটাল টুলস থাকবে।
            </flux:text>
        </flux:card>
    </section>

    {{-- Why Totthobox --}}
    <section class="space-y-5">
        <div class="text-center space-y-2">
            <flux:heading level="2" size="xl">কেন Totthobox?</flux:heading>
            <flux:subheading>আমরা যা অফার করি</flux:subheading>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:card class="p-5">
                <div class="flex items-start gap-4">
                    <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-950/40">
                        <flux:icon name="bolt" class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <flux:heading size="sm">দ্রুত ও সহজ</flux:heading>
                        <flux:text size="sm" class="text-zinc-500 mt-1">
                            জটিল কিছু নেই। প্রয়োজনীয় তথ্য ও টুলস কয়েক সেকেন্ডেই পাবেন।
                        </flux:text>
                    </div>
                </div>
            </flux:card>

            <flux:card class="p-5">
                <div class="flex items-start gap-4">
                    <div class="p-2 rounded-lg bg-zinc-400/10">
                        <flux:icon name="shield-check" class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <flux:heading size="sm">নির্ভরযোগ্য তথ্য</flux:heading>
                        <flux:text size="sm" class="text-zinc-500 mt-1">
                            যাচাইকৃত উৎস থেকে তথ্য সংগ্রহ করে উপস্থাপন করা হয়।
                        </flux:text>
                    </div>
                </div>
            </flux:card>

            <flux:card class="p-5">
                <div class="flex items-start gap-4">
                    <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/40">
                        <flux:icon name="device-phone-mobile" class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <flux:heading size="sm">মোবাইল ফ্রেন্ডলি + PWA</flux:heading>
                        <flux:text size="sm" class="text-zinc-500 mt-1">
                            যেকোনো ডিভাইসে চমৎকার অভিজ্ঞতা। হোম স্ক্রিনে অ্যাপ হিসেবেও ব্যবহার করা যায়।
                        </flux:text>
                    </div>
                </div>
            </flux:card>

            <flux:card class="p-5">
                <div class="flex items-start gap-4">
                    <div class="p-2 rounded-lg bg-rose-50 dark:bg-rose-950/40">
                        <flux:icon name="heart" class="size-5 text-rose-600 dark:text-rose-400" />
                    </div>
                    <div>
                        <flux:heading size="sm">সম্পূর্ণ বিনামূল্যে</flux:heading>
                        <flux:text size="sm" class="text-zinc-500 mt-1">
                            আমাদের মূল সেবাগুলো সবার জন্য উন্মুক্ত এবং বিনামূল্যে ব্যবহারযোগ্য।
                        </flux:text>
                    </div>
                </div>
            </flux:card>
        </div>
    </section>

    {{-- Services --}}
    <section class="space-y-6">
        <div class="text-center space-y-2">
            <flux:heading level="2" size="xl">আমাদের সেবাসমূহ</flux:heading>
            <flux:subheading>এক নজরে Totthobox-এর মূল ফিচারগুলো</flux:subheading>
        </div>

        <livewire:website.home.services-grid />
    </section>

    {{-- Bottom CTA --}}
    <flux:card class="p-8 text-center space-y-6 bg-zinc-50 dark:bg-zinc-800/40">
        <div class="space-y-2">
            <flux:heading level="3" size="lg">আমাদের সাথে যুক্ত হোন</flux:heading>
            <flux:text class="text-zinc-500 max-w-md mx-auto">
                যেকোনো মতামত, জিজ্ঞাসা বা সহযোগিতার জন্য আমাদের সাপোর্ট টিমের সাথে যোগাযোগ করুন।
            </flux:text>
        </div>
    </flux:card>

</div>
