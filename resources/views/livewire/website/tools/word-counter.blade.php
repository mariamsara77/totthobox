<?php

use Livewire\Component;

new class extends Component {
    public string $text = '';

    public function getStatsProperty(): array
    {
        $text = $this->text;

        // Characters
        $charsWithSpaces = mb_strlen($text);
        $charsWithoutSpaces = mb_strlen(preg_replace('/\s+/u', '', $text));

        // Words (Bangla + English both supported)
        $words = preg_match_all('/[\p{L}\p{N}\'\-]+/u', $text, $matches) ? count($matches[0]) : 0;

        // Sentences
        $sentences = preg_match_all('/[^.!?।]+[.!?।]+/u', $text, $matches) ? count($matches[0]) : ($text !== '' ? 1 : 0);

        // Paragraphs
        $paragraphs = $text === '' ? 0 : count(array_filter(preg_split('/\n\s*\n/u', trim($text))));

        // Lines
        $lines = $text === '' ? 0 : substr_count($text, "\n") + 1;

        // Reading time (average 200 words/min)
        $readingMinutes = $words > 0 ? max(1, (int) ceil($words / 200)) : 0;

        // Speaking time (average 130 words/min)
        $speakingMinutes = $words > 0 ? max(1, (int) ceil($words / 130)) : 0;

        return [
            'chars_with_spaces' => number_format($charsWithSpaces),
            'chars_without_spaces' => number_format($charsWithoutSpaces),
            'words' => number_format($words),
            'sentences' => number_format($sentences),
            'paragraphs' => number_format($paragraphs),
            'lines' => number_format($lines),
            'reading_time' => $readingMinutes,
            'speaking_time' => $speakingMinutes,
            'has_text' => $text !== '',
        ];
    }

    public function toUpperCase(): void
    {
        $this->text = mb_strtoupper($this->text, 'UTF-8');
    }

    public function toLowerCase(): void
    {
        $this->text = mb_strtolower($this->text, 'UTF-8');
    }

    public function toSentenceCase(): void
    {
        $text = mb_strtolower($this->text, 'UTF-8');

        // Capitalize first letter of the whole text and after . ! ? ।
        $text = preg_replace_callback(
            '/(^|[.!?।]\s*)(\p{L})/u',
            function ($matches) {
                return $matches[1] . mb_strtoupper($matches[2], 'UTF-8');
            },
            $text,
        );

        $this->text = $text;
    }

    public function toTitleCase(): void
    {
        // Every word first letter uppercase (Title Case)
        $this->text = preg_replace_callback(
            '/\b(\p{L})(\p{L}*)/u',
            function ($matches) {
                return mb_strtoupper($matches[1], 'UTF-8') . mb_strtolower($matches[2], 'UTF-8');
            },
            $this->text,
        );
    }

    public function resetText(): void
    {
        $this->text = '';
    }

    public function clear(): void
    {
        $this->resetText();
    }
}; ?>

<div class="max-w-2xl mx-auto">
    <x-seo title="ওয়ার্ড অ্যান্ড ক্যারেক্টার কাউন্টার - শব্দ, অক্ষর ও লাইন হিসাব"
        description="অনলাইনে তাৎক্ষণিকভাবে শব্দ, অক্ষর (স্পেসসহ/ছাড়া), বাক্য, প্যারাগ্রাফ, লাইন এবং পড়ার সময় হিসাব করুন। বাংলা ও ইংরেজি দুই ভাষাতেই নিখুঁত কাজ করে। Uppercase, Lowercase, Title Case টুলসহ।"
        keywords="ওয়ার্ড কাউন্টার, ক্যারেক্টার কাউন্টার, word counter bangla, character counter, শব্দ গণনা, অক্ষর গণনা, অনলাইন ওয়ার্ড কাউন্টার, uppercase, lowercase, title case" />

    <div class="text-center mb-8">
        <flux:heading size="xl" level="1">ওয়ার্ড অ্যান্ড ক্যারেক্টার কাউন্টার</flux:heading>
        <flux:subheading>শব্দ, অক্ষর, বাক্য, প্যারাগ্রাফ ও পড়ার সময় এক নজরে জানুন</flux:subheading>
    </div>

    <div class="space-y-6">
        <flux:card class="space-y-4">
            <flux:textarea wire:model.live.debounce.150ms="text" label="এখানে টেক্সট লিখুন বা পেস্ট করুন" rows="8"
                placeholder="আপনার টেক্সট এখানে লিখুন..." class="font-mono text-sm" />

            {{-- Case Conversion Tools --}}
            <div class="flex flex-wrap gap-4">
                <flux:button wire:click="toUpperCase" variant="outline" size="sm"
                    :disabled="!$this->stats['has_text']">
                    UPPERCASE
                </flux:button>
                <flux:button wire:click="toLowerCase" variant="outline" size="sm"
                    :disabled="!$this->stats['has_text']">
                    lowercase
                </flux:button>
                <flux:button wire:click="toSentenceCase" variant="outline" size="sm"
                    :disabled="!$this->stats['has_text']">
                    Sentence case
                </flux:button>
                <flux:button wire:click="toTitleCase" variant="outline" size="sm"
                    :disabled="!$this->stats['has_text']">
                    Title Case
                </flux:button>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-2 pt-1">
                <flux:button x-data
                    x-on:click="
                        navigator.clipboard.writeText($wire.text).then(() => {
                            $el.innerText = 'কপি হয়েছে!';
                            setTimeout(() => $el.innerText = 'কপি করুন', 1500);
                        })
                    "
                    variant="subtle" size="sm" :disabled="!$this->stats['has_text']">
                    কপি করুন
                </flux:button>

                <flux:button wire:click="resetText" variant="ghost" size="sm"
                    :disabled="!$this->stats['has_text']">
                    রিসেট করুন
                </flux:button>
            </div>
        </flux:card>

        @if ($this->stats['has_text'])
            <div
                class="p-5 sm:p-6 bg-zinc-50 dark:bg-zinc-900 rounded-xl space-y-6 border border-zinc-200 dark:border-zinc-800">
                {{-- Main Stats --}}
                <div class="grid grid-cols-2  gap-4">
                    <div class="p-4 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                        <div class="text-xs text-zinc-500 uppercase tracking-wider">শব্দ</div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">
                            {{ $this->stats['words'] }}
                        </div>
                    </div>
                    <div class="p-4 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                        <div class="text-xs text-zinc-500 uppercase tracking-wider">অক্ষর (স্পেসসহ)</div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">
                            {{ $this->stats['chars_with_spaces'] }}
                        </div>
                    </div>
                    <div class="p-4 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                        <div class="text-xs text-zinc-500 uppercase tracking-wider">অক্ষর (স্পেসছাড়া)</div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">
                            {{ $this->stats['chars_without_spaces'] }}
                        </div>
                    </div>
                    <div class="p-4 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                        <div class="text-xs text-zinc-500 uppercase tracking-wider">বাক্য</div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">
                            {{ $this->stats['sentences'] }}
                        </div>
                    </div>
                </div>

                {{-- Secondary Stats --}}
                <div class="grid grid-cols-2  gap-4 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                    <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                        <div class="text-xs text-zinc-500">প্যারাগ্রাফ</div>
                        <div class="text-lg font-bold mt-1">{{ $this->stats['paragraphs'] }}</div>
                    </div>
                    <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                        <div class="text-xs text-zinc-500">লাইন</div>
                        <div class="text-lg font-bold mt-1">{{ $this->stats['lines'] }}</div>
                    </div>
                    <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                        <div class="text-xs text-zinc-500">পড়ার সময়</div>
                        <div class="text-lg font-bold mt-1 text-emerald-600 dark:text-emerald-400">
                            ≈ {{ $this->stats['reading_time'] }} মিনিট
                        </div>
                    </div>
                    <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                        <div class="text-xs text-zinc-500">কথার সময়</div>
                        <div class="text-lg font-bold mt-1 text-amber-600 dark:text-amber-400">
                            ≈ {{ $this->stats['speaking_time'] }} মিনিট
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div
                class="p-8 text-center text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700">
                টেক্সট লিখলেই এখানে লাইভ কাউন্ট দেখা যাবে
            </div>
        @endif
    </div>

    {{-- SEO Content Section --}}
    <div class="mt-16 pt-2 border-t border-zinc-400/25 space-y-6 text-zinc-700 dark:text-zinc-300 leading-relaxed">
        <flux:heading size="lg" level="2">ওয়ার্ড অ্যান্ড ক্যারেক্টার কাউন্টার কীভাবে ব্যবহার করবেন?
        </flux:heading>
        <p>
            আমাদের ওয়ার্ড অ্যান্ড ক্যারেক্টার কাউন্টার দিয়ে আপনি তাৎক্ষণিকভাবে যেকোনো টেক্সটের শব্দ, অক্ষর, বাক্য,
            প্যারাগ্রাফ ও লাইনের সংখ্যা জানতে পারবেন।
            শুধু টেক্সটবক্সে লিখুন বা কপি-পেস্ট করুন — সব হিসাব লাইভে আপডেট হবে। বাংলা ও ইংরেজি উভয় ভাষাতেই নিখুঁতভাবে
            কাজ করে।
        </p>

        <flux:heading size="base" level="3">কী কী হিসাব করা হয়?</flux:heading>
        <ul class="list-disc list-inside space-y-1.5 ml-1">
            <li><strong>শব্দ (Words)</strong> — বাংলা ও ইংরেজি শব্দ দুইই সঠিকভাবে গণনা করে</li>
            <li><strong>অক্ষর (Characters)</strong> — স্পেসসহ এবং স্পেস ছাড়া আলাদা আলাদা দেখায়</li>
            <li><strong>বাক্য (Sentences)</strong> — । ! ? চিহ্ন অনুযায়ী বাক্য গণনা</li>
            <li><strong>প্যারাগ্রাফ ও লাইন</strong> — খালি লাইন অনুযায়ী প্যারাগ্রাফ এবং মোট লাইন</li>
            <li><strong>পড়ার সময়</strong> — গড় ২০০ শব্দ/মিনিট হিসেবে আনুমানিক পড়ার সময়</li>
            <li><strong>কথার সময়</strong> — গড় ১৩০ শব্দ/মিনিট হিসেবে আনুমানিক বলার সময়</li>
        </ul>

        <flux:heading size="base" level="3">টেক্সট কেস কনভার্শন টুলস</flux:heading>
        <ul class="list-disc list-inside space-y-1.5 ml-1">
            <li><strong>UPPERCASE</strong> — সব অক্ষর বড় হাতের করে দেয়</li>
            <li><strong>lowercase</strong> — সব অক্ষর ছোট হাতের করে দেয়</li>
            <li><strong>Sentence case</strong> — প্রতিটি বাক্যের প্রথম অক্ষর বড় হাতের করে</li>
            <li><strong>Title Case</strong> — প্রতিটি শব্দের প্রথম অক্ষর বড় হাতের করে</li>
        </ul>

        <flux:heading size="base" level="3">কেন এই টুল ব্যবহার করবেন?</flux:heading>
        <p>
            ব্লগ লেখা, অ্যাসাইনমেন্ট, সোশ্যাল মিডিয়া পোস্ট, পরীক্ষার উত্তর বা যেকোনো লেখার ক্ষেত্রে শব্দসীমা মেনে চলতে
            এই টুল খুবই উপকারী। এছাড়া টেক্সট কেস পরিবর্তন ও এক ক্লিকে কপি করার সুবিধাও আছে।
            সম্পূর্ণ ফ্রি, কোনো রেজিস্ট্রেশন লাগে না এবং আপনার লেখা কোথাও সংরক্ষণ করা হয় না। Livewire ব্যবহার করায়
            সবকিছু তাৎক্ষণিক ও মসৃণ।
        </p>
        <p>
            এখনই চেষ্টা করে দেখুন — টেক্সট পেস্ট করুন এবং দেখুন কত শব্দ, কত অক্ষর এবং কত সময় লাগবে পড়তে বা বলতে!
        </p>
    </div>
</div>
