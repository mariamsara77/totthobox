<?php

use Livewire\Volt\Component;
use App\Models\ExcelTutorial;
use Livewire\Attributes\Computed;
use Illuminate\Support\Str;

new class extends Component {
    public ?string $currentSlug = null;

    public function mount(?string $slug = null): void
    {
        if (!$slug) {
            $first = ExcelTutorial::query()->where('is_published', true)->orderBy('position')->first();

            $this->currentSlug = $first?->slug;
        } else {
            $this->currentSlug = $slug;
        }
    }

    #[Computed]
    public function tutorial()
    {
        return ExcelTutorial::query()->where('slug', $this->currentSlug)->where('is_published', true)->firstOrFail();
    }

    #[Computed]
    public function nextLesson()
    {
        return ExcelTutorial::query()->where('is_published', true)->where('position', '>', $this->tutorial->position)->orderBy('position')->first();
    }

    #[Computed]
    public function prevLesson()
    {
        return ExcelTutorial::query()->where('is_published', true)->where('position', '<', $this->tutorial->position)->orderByDesc('position')->first();
    }
}; ?>

<div>
    {{-- Advanced SEO --}}
    <x-seo :title="$this->tutorial->title . ' | Excel Tutorial – Part ' . $this->tutorial->position" :description="Str::limit(strip_tags($this->tutorial->description), 155)" :keywords="'excel tutorial bangla, ' .
        $this->tutorial->chapter_name .
        ', ' .
        $this->tutorial->title .
        ', excel formula, spreadsheet tutorial'" />

    <main class="max-w-2xl mx-auto space-y-6">
        <article itemscope itemtype="https://schema.org/LearningResource" class="space-y-6">
            {{-- Breadcrumb --}}
            <flux:breadcrumbs class="mb-8">
                <flux:breadcrumbs.item href="/">Home</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="#">Excel Tutorial</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->tutorial->chapter_name }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            {{-- Header --}}
            <header class="mb-10 space-y-5">
                <div class="flex flex-wrap items-center gap-2">
                    <flux:badge color="green" size="sm" variant="pill">
                        Lesson {{ $this->tutorial->position }}
                    </flux:badge>

                    <flux:text size="sm" class="text-zinc-500">
                        আপডেট · {{ $this->tutorial->updated_at->translatedFormat('F Y') }}
                    </flux:text>
                </div>

                <flux:heading size="xl" itemprop="name">
                    {{ $this->tutorial->title }}
                </flux:heading>

                @if ($this->tutorial->chapter_name)
                    <flux:text size="sm" class="text-zinc-500">
                        অধ্যায়: {{ $this->tutorial->chapter_name }}
                    </flux:text>
                @endif
            </header>

            {{-- Featured Image --}}
            @if ($this->tutorial->hasMedia('lesson_image'))
                <figure class="mb-12 overflow-hidden rounded-xl border">
                    <img src="{{ $this->tutorial->getFirstMediaUrl('lesson_image') }}"
                        alt="{{ $this->tutorial->title }}" class="w-full aspect-video object-cover" loading="eager"
                        itemprop="image">
                </figure>
            @endif

            {{-- Main Content --}}
            <div class="prose dark:prose-invert max-w-none mb-14" itemprop="description">
                {!! $this->tutorial->description !!}
            </div>

            {{-- Interactive Formula Box --}}
            @if ($this->tutorial->excel_formula)
                <flux:card class="mb-14">
                    <div x-data="{
                        formula: @js($this->tutorial->excel_formula),
                        copied: false,
                        copy() {
                            navigator.clipboard.writeText(this.formula);
                            this.copied = true;
                            setTimeout(() => this.copied = false, 2200);
                        }
                    }" class="space-y-5">
                        <div class="flex items-center justify-between gap-4">
                            <flux:heading size="lg" class="flex items-center gap-2">
                                <flux:icon.variable class="size-5 text-green-600" />
                                নিজে প্র্যাকটিস করুন
                            </flux:heading>

                            <flux:badge color="green" variant="flat" size="sm">
                                Formula
                            </flux:badge>
                        </div>

                        <flux:callout icon="light-bulb" color="green">
                            নিচের ফর্মুলাটি কপি করে Excel-এ পেস্ট করে দেখুন। প্রয়োজনমতো সেল রেফারেন্স পরিবর্তন করে নিজের
                            ডাটায় প্রয়োগ করতে পারবেন।
                        </flux:callout>

                        <div
                            class="rounded-lg border bg-white dark:bg-zinc-950 p-4 font-mono text-green-600 dark:text-green-400 overflow-x-auto">
                            <code x-text="formula"></code>
                        </div>

                        <flux:button x-on:click="copy" variant="primary" size="sm" icon="document-duplicate">
                            <span x-show="!copied">ফর্মুলা কপি করুন</span>
                            <span x-show="copied" x-cloak class="flex items-center gap-2 text-green-600">
                                <flux:icon.check class="size-4" />
                                কপি হয়েছে
                            </span>
                        </flux:button>
                    </div>
                </flux:card>
            @endif

            {{-- Navigation between lessons --}}
            <div class="flex gap-4">
                @if ($this->prevLesson)
                    <flux:button href="{{ route('excel.view', $this->prevLesson->slug) }}" variant="outline"
                        wire:navigate class="flex-1 justify-start" icon="arrow-left">
                        {{ Str::limit($this->prevLesson->title, 20) }}
                    </flux:button>
                @endif

                @if ($this->nextLesson)
                    <flux:button href="{{ route('excel.view', $this->nextLesson->slug) }}" variant="outline"
                        wire:navigate class="flex-1 justify-end text-right" icon-trailing="arrow-right">
                        {{ Str::limit($this->nextLesson->title, 20) }}
                    </flux:button>
                @endif
            </div>
        </article>

        <flux:separator class="my-12" />

        {{-- About this Tutorial Series --}}
        <section aria-labelledby="about-excel" class="space-y-5 mb-14">
            <flux:heading id="about-excel" level="2" size="lg" class="flex items-center gap-2">
                <flux:icon icon="information-circle" class="size-5 text-green-600" />
                এই টিউটোরিয়াল সিরিজ সম্পর্কে
            </flux:heading>

            <flux:text>
                Excel Expert BD-তে আমরা বাংলায় সহজ ও ধাপে ধাপে Excel শেখানোর চেষ্টা করি।
                বেসিক থেকে শুরু করে প্র্যাকটিক্যাল ফর্মুলা, ডাটা অ্যানালাইসিস আর অফিস ওয়ার্কফ্লো — সবকিছু এক জায়গায়
                সাজানো আছে।
            </flux:text>

            <flux:text>
                প্রতিটি লেসন এমনভাবে লেখা হয় যাতে নতুন শিক্ষার্থীও সহজে বুঝতে পারে।
                জটিল টার্ম এড়িয়ে বাস্তব উদাহরণ আর প্রয়োজনীয় ফর্মুলা দিয়ে বিষয়টা পরিষ্কার করা হয়।
            </flux:text>

            <flux:text>
                তথ্যগুলো নিয়মিত আপডেট করা হয়। নতুন ফিচার বা ভালো কোনো পদ্ধতি পাওয়া গেলে সেটা যোগ করা হয়,
                যাতে পাঠক সবসময় প্রাসঙ্গিক এবং ব্যবহারযোগ্য জ্ঞান পায়।
            </flux:text>
        </section>

        {{-- FAQ --}}
        <section aria-labelledby="faq-excel" class="space-y-5 mb-14">
            <flux:heading id="faq-excel" level="2" size="lg" class="flex items-center gap-2">
                <flux:icon icon="question-mark-circle" class="size-5 text-green-600" />
                সাধারণ জিজ্ঞাসা
            </flux:heading>

            <flux:accordion transition exclusive>
                <flux:accordion.item>
                    <flux:accordion.heading>এই টিউটোরিয়ালগুলো কার জন্য উপযোগী?</flux:accordion.heading>
                    <flux:accordion.content>
                        <flux:text>
                            যারা Excel নতুন করে শিখতে চান বা অফিসের কাজ আরও দ্রুত করতে চান, তাদের জন্যই এই সিরিজ।
                            স্টুডেন্ট, চাকরিজীবী বা ফ্রিল্যান্সার — সবাই উপকৃত হতে পারবেন।
                        </flux:text>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>ফর্মুলাগুলো কি সরাসরি ব্যবহার করা যাবে?</flux:accordion.heading>
                    <flux:accordion.content>
                        <flux:text>
                            হ্যাঁ। প্রতিটি ফর্মুলা প্র্যাকটিক্যাল উদাহরণসহ দেওয়া আছে।
                            কপি করে নিজের শিটে পেস্ট করে সেল রেফারেন্স মিলিয়ে নিলেই কাজ করবে।
                        </flux:text>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>লেসনগুলো কোন অর্ডারে পড়া উচিত?</flux:accordion.heading>
                    <flux:accordion.content>
                        <flux:text>
                            সিরিয়াল অনুযায়ী পড়লে ভালো বোঝা যায়। তবে আপনি যদি কোনো নির্দিষ্ট টপিক খুঁজছেন,
                            সরাসরি সেই লেসনে চলে যেতে পারেন। প্রতিটি পেজে আগের ও পরের লেসনের লিংকও দেওয়া আছে।
                        </flux:text>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>নতুন লেসন কতদিন পরপর আসে?</flux:accordion.heading>
                    <flux:accordion.content>
                        <flux:text>
                            নিয়মিত নতুন কন্টেন্ট যোগ করা হয়। কোনো গুরুত্বপূর্ণ আপডেট বা নতুন ফিচার এলে
                            দ্রুত লেসন আকারে প্রকাশ করা হয়।
                        </flux:text>
                    </flux:accordion.content>
                </flux:accordion.item>
            </flux:accordion>
        </section>

        <footer class="text-center mt-4">
            <flux:text size="sm" class="text-zinc-500">
                © {{ date('Y') }} Excel Expert BD · সহজ ভাষায় Excel শেখার নির্ভরযোগ্য জায়গা
            </flux:text>
        </footer>
    </main>
</div>
