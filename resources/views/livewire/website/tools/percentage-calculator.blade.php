<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

new class extends Component {
    public string $tab = 'percent_of';

    #[Validate('nullable|numeric|min:0')]
    public ?string $value1 = null;

    #[Validate('nullable|numeric|min:0')]
    public ?string $value2 = null;

    #[Validate('nullable|numeric')]
    public ?string $percent = null;

    public array $history = [];

    public function updatedTab(): void
    {
        $this->resetAll();
    }

    #[Computed]
    public function result(): ?array
    {
        $v1 = $this->value1 !== null && $this->value1 !== '' ? (float) $this->value1 : null;
        $v2 = $this->value2 !== null && $this->value2 !== '' ? (float) $this->value2 : null;
        $p = $this->percent !== null && $this->percent !== '' ? (float) $this->percent : null;

        $data = match ($this->tab) {
            'percent_of' => $v1 !== null && $p !== null ? $this->build(($p / 100) * $v1, "({$p} ÷ 100) × {$v1}", "{$v1} টাকার/সংখ্যার {$p}% মানে {$p} ভাগ ১০০-এর। তাই উত্তর = ({$p} ÷ 100) × {$v1}", 'ফলাফল') : null,

            'is_what_percent' => $v1 !== null && $v2 !== null && $v2 != 0 ? $this->build(($v1 / $v2) * 100, "({$v1} ÷ {$v2}) × 100", "{$v1} হলো {$v2}-এর কত শতাংশ? হিসাব: ({$v1} ÷ {$v2}) × 100", 'শতাংশ', '%') : null,

            'percent_of_what' => $v1 !== null && $p !== null && $p != 0 ? $this->build(($v1 * 100) / $p, "({$v1} × 100) ÷ {$p}", "{$v1} যদি কোনো সংখ্যার {$p}% হয়, তাহলে সেই মূল সংখ্যা কত? হিসাব: ({$v1} × 100) ÷ {$p}", 'মূল সংখ্যা') : null,

            'increase' => $v1 !== null && $p !== null
                ? (function () use ($v1, $p) {
                    $inc = ($p / 100) * $v1;
                    $r = $v1 + $inc;
                    return $this->build($r, "{$v1} + ({$p}% of {$v1})", "{$v1}-কে {$p}% বাড়ালে নতুন মান = {$v1} + ({$p} ÷ 100 × {$v1})", 'নতুন মান', '', ['title' => 'কত বাড়ল', 'value' => $this->fmt($inc), 'color' => 'emerald']);
                })()
                : null,

            'decrease' => $v1 !== null && $p !== null
                ? (function () use ($v1, $p) {
                    $dec = ($p / 100) * $v1;
                    $r = $v1 - $dec;
                    return $this->build($r, "{$v1} − ({$p}% of {$v1})", "{$v1}-কে {$p}% কমালে নতুন মান = {$v1} − ({$p} ÷ 100 × {$v1})", 'নতুন মান', '', ['title' => 'কত কমল', 'value' => $this->fmt($dec), 'color' => 'rose']);
                })()
                : null,

            'difference' => $v1 !== null && $v2 !== null
                ? (function () use ($v1, $v2) {
                    $diff = abs($v1 - $v2);
                    $avg = ($v1 + $v2) / 2;
                    if ($avg == 0) {
                        return null;
                    }
                    $r = ($diff / $avg) * 100;
                    return $this->build($r, "|{$v1} − {$v2}| ÷ (({$v1} + {$v2}) ÷ 2) × 100", "দুটি সংখ্যার শতকরা পার্থক্য = |{$v1} − {$v2}| ÷ গড় × 100", 'পার্থক্য', '%', ['title' => 'পরম পার্থক্য', 'value' => $this->fmt($diff), 'color' => 'violet']);
                })()
                : null,

            'discount' => $v1 !== null && $p !== null
                ? (function () use ($v1, $p) {
                    $off = ($p / 100) * $v1;
                    $r = $v1 - $off;
                    return $this->build($r, "{$v1} − ({$p}% of {$v1})", "মূল্য {$v1} টাকায় {$p}% ছাড়। ছাড়ের পরিমাণ {$this->fmt($off)} টাকা। আপনি দেবেন = {$v1} − {$this->fmt($off)}", 'ছাড়ের পর দাম', '', ['title' => 'কত টাকা বাঁচবে', 'value' => $this->fmt($off) . ' ৳', 'color' => 'rose']);
                })()
                : null,

            'tip' => $v1 !== null && $p !== null
                ? (function () use ($v1, $p) {
                    $tip = ($p / 100) * $v1;
                    $r = $v1 + $tip;
                    return $this->build($r, "{$v1} + ({$p}% of {$v1})", "বিল {$v1} টাকায় {$p}% টিপ = {$this->fmt($tip)} টাকা। মোট দিতে হবে = {$v1} + {$this->fmt($tip)}", 'মোট (বিল + টিপ)', '', ['title' => 'টিপের পরিমাণ', 'value' => $this->fmt($tip) . ' ৳', 'color' => 'emerald']);
                })()
                : null,

            'margin' => $v1 !== null && $v2 !== null && $v1 != 0
                ? (function () use ($v1, $v2) {
                    $profit = $v2 - $v1;
                    $r = ($profit / $v1) * 100;
                    return $this->build($r, "({$v2} − {$v1}) ÷ {$v1} × 100", "ক্রয়মূল্য {$v1} টাকা, বিক্রয়মূল্য {$v2} টাকা। মুনাফা = {$this->fmt($profit)} টাকা। মার্জিন = (মুনাফা ÷ ক্রয়মূল্য) × 100", 'মার্জিন %', '%', [
                        'title' => 'মুনাফা',
                        'value' => $this->fmt($profit) . ' ৳',
                        'color' => $profit >= 0 ? 'emerald' : 'rose',
                    ]);
                })()
                : null,

            default => null,
        };

        if ($data && !empty($this->value1)) {
            $this->pushHistory($data);
        }

        return $data;
    }

    private function build(float $value, string $formula, string $explanation, string $badge, string $suffix = '', ?array $extra = null): array
    {
        $fmt = $this->fmt($value);

        return [
            'value' => $fmt,
            'label' => $fmt,
            'suffix' => $suffix,
            'formula' => $formula,
            'explanation' => $explanation . ' = ' . $fmt . $suffix,
            'extra' => $extra,
            'badge' => $badge,
        ];
    }

    private function fmt(float $n): string
    {
        return rtrim(rtrim(number_format($n, 4, '.', ','), '0'), '.');
    }

    private function pushHistory(array $data): void
    {
        $entry = [
            'tab' => $this->tab,
            'label' => $data['label'] . $data['suffix'],
            'time' => now()->format('H:i'),
        ];

        if (($this->history[0]['label'] ?? null) === $entry['label']) {
            return;
        }

        array_unshift($this->history, $entry);
        $this->history = array_slice($this->history, 0, 6);
    }

    public function fillExample(string $v1, ?string $v2 = null, ?string $p = null): void
    {
        $this->value1 = $v1;
        $this->value2 = $v2;
        $this->percent = $p;
    }

    public function resetAll(): void
    {
        $this->value1 = null;
        $this->value2 = null;
        $this->percent = null;
    }

    public function clearHistory(): void
    {
        $this->history = [];
        $this->js("localStorage.removeItem('pct_calc_history')");
    }
}; ?>

<div class="max-w-2xl mx-auto" x-data x-init="(() => {
    let saved = localStorage.getItem('pct_calc_history');
    if (saved) {
        try {
            let parsed = JSON.parse(saved);
            if (Array.isArray(parsed) && parsed.length) {
                $wire.set('history', parsed);
            }
        } catch (e) {}
    }
    $wire.$watch('history', value => {
        localStorage.setItem('pct_calc_history', JSON.stringify(value ?? []));
    });
})()">
    <x-seo title="পার্সেন্টেজ ক্যালকুলেটর — সহজ বাংলায় শতকরা হিসাব | Free Percentage Calculator"
        description="সহজ বাংলায় পার্সেন্টেজ ক্যালকুলেটর। কোনো সংখ্যার শতকরা, বৃদ্ধি-হ্রাস, ছাড়, টিপ, মার্জিন ও পার্থক্য — সূত্র ও ব্যাখ্যাসহ তাৎক্ষণিক ফলাফল। রেজিস্ট্রেশন লাগবে না।"
        keywords="percentage calculator, শতকরা ক্যালকুলেটর, percent calculator, percentage increase, percentage decrease, discount calculator, tip calculator, margin calculator, reverse percentage, অনলাইন শতকরা ক্যালকুলেটর, বাংলা পার্সেন্টেজ ক্যালকুলেটর, ছাড় ক্যালকুলেটর, টিপ ক্যালকুলেটর" />

    {{-- Hero --}}
    <div class="text-center mb-8">
        <flux:badge color="lime" size="sm" class="mb-3">বিনামূল্যে · রেজিস্ট্রেশন লাগবে না</flux:badge>
        <flux:heading size="xl" class="tracking-tight">পার্সেন্টেজ ক্যালকুলেটর</flux:heading>
        <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400 max-w-xl mx-auto">
            যেকোনো শতকরা হিসাব এক জায়গায়। সংখ্যা লিখুন — সাথে সাথে সূত্র ও সহজ বাংলা ব্যাখ্যাসহ উত্তর পাবেন।
        </flux:text>
    </div>

    {{-- Tabs --}}
    <flux:tab.group>
        <flux:tabs wire:model="tab" scrollable scrollable:fade class="mb-6">
            <flux:tab name="percent_of" icon="calculator">কোনো সংখ্যার %</flux:tab>
            <flux:tab name="is_what_percent" icon="percent-badge">কত শতাংশ?</flux:tab>
            <flux:tab name="percent_of_what" icon="arrow-uturn-left">মূল সংখ্যা বের করুন</flux:tab>
            <flux:tab name="increase" icon="arrow-trending-up">বাড়ান</flux:tab>
            <flux:tab name="decrease" icon="arrow-trending-down">কমান</flux:tab>
            <flux:tab name="discount" icon="tag">ছাড় হিসাব</flux:tab>
            <flux:tab name="tip" icon="banknotes">টিপ হিসাব</flux:tab>
            <flux:tab name="margin" icon="chart-bar">মুনাফা / মার্জিন</flux:tab>
            <flux:tab name="difference" icon="arrows-right-left">পার্থক্য %</flux:tab>
        </flux:tabs>

        {{-- Input Card --}}
        <flux:card class="space-y-5">
            @php
                $configs = [
                    'percent_of' => [
                        [
                            'label' => 'মূল সংখ্যা',
                            'model' => 'value1',
                            'placeholder' => 'যেমন: ৫০০',
                            'icon' => 'hashtag',
                        ],
                        [
                            'label' => 'শতকরা (%)',
                            'model' => 'percent',
                            'placeholder' => 'যেমন: ২০',
                            'icon' => 'percent-badge',
                        ],
                    ],
                    'is_what_percent' => [
                        [
                            'label' => 'ছোট সংখ্যা',
                            'model' => 'value1',
                            'placeholder' => 'যেমন: ২৫',
                            'icon' => 'hashtag',
                        ],
                        [
                            'label' => 'বড় সংখ্যা',
                            'model' => 'value2',
                            'placeholder' => 'যেমন: ১০০',
                            'icon' => 'hashtag',
                        ],
                    ],
                    'percent_of_what' => [
                        [
                            'label' => 'আপনি যা জানেন',
                            'model' => 'value1',
                            'placeholder' => 'যেমন: ৪০',
                            'icon' => 'hashtag',
                        ],
                        [
                            'label' => 'এটা কত শতাংশ?',
                            'model' => 'percent',
                            'placeholder' => 'যেমন: ২০',
                            'icon' => 'percent-badge',
                        ],
                    ],
                    'increase' => [
                        [
                            'label' => 'বর্তমান মান',
                            'model' => 'value1',
                            'placeholder' => 'যেমন: ১০০০',
                            'icon' => 'hashtag',
                        ],
                        [
                            'label' => 'কত % বাড়াবেন?',
                            'model' => 'percent',
                            'placeholder' => 'যেমন: ১৫',
                            'icon' => 'arrow-trending-up',
                        ],
                    ],
                    'decrease' => [
                        [
                            'label' => 'বর্তমান মান',
                            'model' => 'value1',
                            'placeholder' => 'যেমন: ৮০০',
                            'icon' => 'hashtag',
                        ],
                        [
                            'label' => 'কত % কমবে?',
                            'model' => 'percent',
                            'placeholder' => 'যেমন: ২৫',
                            'icon' => 'arrow-trending-down',
                        ],
                    ],
                    'discount' => [
                        [
                            'label' => 'মূল দাম (৳)',
                            'model' => 'value1',
                            'placeholder' => 'যেমন: ২৫০০',
                            'icon' => 'banknotes',
                        ],
                        [
                            'label' => 'ছাড় কত %?',
                            'model' => 'percent',
                            'placeholder' => 'যেমন: ৩০',
                            'icon' => 'tag',
                        ],
                    ],
                    'tip' => [
                        [
                            'label' => 'বিলের পরিমাণ (৳)',
                            'model' => 'value1',
                            'placeholder' => 'যেমন: ১৮০০',
                            'icon' => 'banknotes',
                        ],
                        [
                            'label' => 'টিপ কত %?',
                            'model' => 'percent',
                            'placeholder' => 'যেমন: ১০',
                            'icon' => 'heart',
                        ],
                    ],
                    'margin' => [
                        [
                            'label' => 'কেনা দাম / Cost (৳)',
                            'model' => 'value1',
                            'placeholder' => 'যেমন: ৮০০',
                            'icon' => 'shopping-cart',
                        ],
                        [
                            'label' => 'বিক্রির দাম (৳)',
                            'model' => 'value2',
                            'placeholder' => 'যেমন: ১২০০',
                            'icon' => 'currency-bangladeshi',
                        ],
                    ],
                    'difference' => [
                        [
                            'label' => 'প্রথম সংখ্যা',
                            'model' => 'value1',
                            'placeholder' => 'যেমন: ৮০',
                            'icon' => 'hashtag',
                        ],
                        [
                            'label' => 'দ্বিতীয় সংখ্যা',
                            'model' => 'value2',
                            'placeholder' => 'যেমন: ১০০',
                            'icon' => 'hashtag',
                        ],
                    ],
                ];
                $fields = $configs[$tab] ?? [];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($fields as $field)
                    <flux:input type="number" step="any" wire:model.live="{{ $field['model'] }}"
                        label="{{ $field['label'] }}" placeholder="{{ $field['placeholder'] }}"
                        icon="{{ $field['icon'] }}" clearable />
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-1">
                <flux:button variant="subtle" size="sm" icon="arrow-path" wire:click="resetAll">
                    মুছে ফেলুন
                </flux:button>

                {{-- One-click examples --}}
                <flux:tab.panel name="percent_of">
                    <flux:button size="sm" variant="ghost" wire:click="fillExample('500','','20')">৫০০-এর ২০%
                    </flux:button>
                    <flux:button size="sm" variant="ghost" wire:click="fillExample('1000','','15')">১০০০-এর ১৫%
                    </flux:button>
                </flux:tab.panel>

                <flux:tab.panel name="discount">
                    <flux:button size="sm" variant="ghost" wire:click="fillExample('2500','','30')">২৫০০ টাকায় ৩০%
                        ছাড়</flux:button>
                    <flux:button size="sm" variant="ghost" wire:click="fillExample('4999','','40')">৪৯৯৯ টাকায় ৪০%
                        ছাড়</flux:button>
                </flux:tab.panel>

                <flux:tab.panel name="tip">
                    <flux:button size="sm" variant="ghost" wire:click="fillExample('1800','','10')">১৮০০ টাকায় ১০%
                        টিপ</flux:button>
                    <flux:button size="sm" variant="ghost" wire:click="fillExample('2500','','15')">২৫০০ টাকায় ১৫%
                        টিপ</flux:button>
                </flux:tab.panel>

                <flux:tab.panel name="increase">
                    <flux:button size="sm" variant="ghost" wire:click="fillExample('40000','','12')">৪০,০০০ + ১২%
                    </flux:button>
                </flux:tab.panel>

                <flux:tab.panel name="margin">
                    <flux:button size="sm" variant="ghost" wire:click="fillExample('800','1200')">৮০০ → ১২০০
                    </flux:button>
                </flux:tab.panel>
            </div>
        </flux:card>

        {{-- Result --}}
        @if ($this->result)
            @php $res = $this->result; @endphp
            <flux:card class="mt-4 space-y-4 border-emerald-500/30 dark:border-emerald-500/20">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <flux:badge color="emerald" size="sm">{{ $res['badge'] }}</flux:badge>
                        <div class="mt-2 text-3xl sm:text-4xl font-bold tracking-tight tabular-nums">
                            {{ $res['label'] }}<span class="text-emerald-500 text-2xl">{{ $res['suffix'] }}</span>
                        </div>
                    </div>
                    <flux:button size="sm" variant="subtle" icon="clipboard-document" x-data
                        x-on:click="
                                    navigator.clipboard.writeText('{{ $res['label'] }}{{ $res['suffix'] }}');
                                    $flux.toast('কপি হয়েছে!');
                                ">
                        কপি
                    </flux:button>
                </div>

                @if ($res['extra'])
                    <div class="flex items-center gap-2 text-sm">
                        <flux:badge :color="$res['extra']['color']" size="sm">
                            {{ $res['extra']['title'] }}: {{ $res['extra']['value'] }}
                        </flux:badge>
                    </div>
                @endif

                <flux:separator />

                <div class="space-y-3 text-sm">
                    <div class="flex sm:gap-4">
                        <span class="text-zinc-500 dark:text-zinc-400 shrink-0 w-16">সূত্র</span>
                        <code
                            class="font-mono text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded">
                            {{ $res['formula'] }}
                        </code>
                    </div>
                    <div class="flex sm:gap-4">
                        <span class="text-zinc-500 dark:text-zinc-400 shrink-0 w-16">ব্যাখ্যা</span>
                        <span class="text-zinc-700 dark:text-zinc-300 leading-relaxed">{{ $res['explanation'] }}</span>
                    </div>
                </div>
            </flux:card>
        @else
            <div
                class="mt-4 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 bg-zinc-400/10 py-12 text-center">
                <flux:icon name="calculator" class="mx-auto size-8 text-zinc-400 mb-3" />
                <flux:text class="text-zinc-500">উপরে সংখ্যা লিখুন — ফলাফল এখানে দেখাবে</flux:text>
            </div>
        @endif
    </flux:tab.group>

    {{-- History --}}
    @if (count($history) > 0)
        <div class="mt-8">
            <div class="flex items-center justify-between mb-3">
                <flux:heading size="sm">সাম্প্রতিক হিসাব</flux:heading>
                <flux:button size="xs" variant="ghost" wire:click="clearHistory">সব মুছে ফেলুন</flux:button>
            </div>
            <div class="flex flex-wrap gap-4">
                @foreach ($history as $h)
                    <flux:badge color="zinc" size="sm">
                        {{ $h['label'] }}
                        <span class="opacity-50 ml-1">{{ $h['time'] }}</span>
                    </flux:badge>
                @endforeach
            </div>
        </div>
    @endif

    {{-- SEO + Educational content --}}
    <div class="mt-16 space-y-10 prose prose-zinc dark:prose-invert max-w-none">
        <section>
            <flux:heading size="lg">কীভাবে ব্যবহার করবেন?</flux:heading>
            <flux:text class="mt-3">
                উপরের ট্যাব থেকে আপনার প্রয়োজনীয় হিসাব বেছে নিন। ঘরে সংখ্যা লিখুন — সাথে সাথে সূত্র ও সহজ বাংলা
                ব্যাখ্যাসহ উত্তর দেখাবে।
                কোনো বাটন চাপতে হবে না, রেজিস্ট্রেশনও লাগবে না। ফলাফল এক ক্লিকে কপি করে নিতে পারবেন।
            </flux:text>
        </section>

        <section>
            <flux:heading size="lg">৯টি হিসাবের ধরন (সহজ ভাষায়)</flux:heading>
            <ul class="mt-3 space-y-2 text-sm">
                <li><strong>কোনো সংখ্যার %</strong> — যেমন: ৫০০ টাকার ২০% কত?</li>
                <li><strong>কত শতাংশ?</strong> — যেমন: ২৫ হলো ১০০-এর কত শতাংশ?</li>
                <li><strong>মূল সংখ্যা বের করুন</strong> — যেমন: ৪০ টাকা যদি ২০% হয়, তাহলে মূল সংখ্যা কত?</li>
                <li><strong>বাড়ান</strong> — বেতন বা দাম কত % বাড়লে নতুন মান কত হবে</li>
                <li><strong>কমান</strong> — কোনো সংখ্যা কত % কমালে কত থাকবে</li>
                <li><strong>ছাড় হিসাব</strong> — শপিংয়ে কত টাকা বাঁচবে + চূড়ান্ত দাম</li>
                <li><strong>টিপ হিসাব</strong> — রেস্টুরেন্ট বিল + টিপ মিলিয়ে মোট কত</li>
                <li><strong>মুনাফা / মার্জিন</strong> — কেনা ও বিক্রির দাম থেকে মুনাফার শতাংশ</li>
                <li><strong>পার্থক্য %</strong> — দুটি সংখ্যার মধ্যে শতকরা পার্থক্য</li>
            </ul>
        </section>

        <section>
            <flux:heading size="lg">বাস্তব জীবনের উদাহরণ</flux:heading>
            <ul class="mt-3 space-y-2 text-sm">
                <li><strong>শপিং:</strong> ২৫০০ টাকার পণ্যে ৩০% ছাড় → আপনি কত টাকা দেবেন?</li>
                <li><strong>বেতন:</strong> এখন ৪০,০০০ টাকা, ১২% বাড়লে নতুন বেতন কত?</li>
                <li><strong>পরীক্ষা:</strong> ১০০-তে ৭৫ পেলে কত পারসেন্ট?</li>
                <li><strong>বিনিয়োগ:</strong> ৫০,০০০ টাকায় ১৮% লাভ হলে মোট কত?</li>
                <li><strong>রেস্টুরেন্ট:</strong> ১৮০০ টাকার বিলে ১০% টিপ কত?</li>
                <li><strong>ব্যবসা:</strong> ৮০০ টাকায় কিনে ১২০০ টাকায় বিক্রি করলে মার্জিন কত?</li>
            </ul>
        </section>

        <section>
            <flux:heading size="lg">মূল সূত্রগুলো মনে রাখুন</flux:heading>
            <ul class="mt-3 space-y-1 text-sm font-mono">
                <li>কোনো সংখ্যার % = (শতকরা ÷ ১০০) × মূল সংখ্যা</li>
                <li>কত শতাংশ = (ছোট সংখ্যা ÷ বড় সংখ্যা) × ১০০</li>
                <li>মূল সংখ্যা = (জানা মান × ১০০) ÷ শতকরা</li>
                <li>বাড়ানো = মূল + (মূল × শতকরা ÷ ১০০)</li>
                <li>কমানো / ছাড় = মূল − (মূল × শতকরা ÷ ১০০)</li>
                <li>মার্জিন % = (বিক্রি − কেনা) ÷ কেনা × ১০০</li>
                <li>পার্থক্য % = |A − B| ÷ ((A + B) ÷ ২) × ১০০</li>
            </ul>
        </section>

        <section>
            <flux:heading size="lg">কেন এই ক্যালকুলেটর?</flux:heading>
            <flux:text class="mt-3">
                সম্পূর্ণ ফ্রি, সহজ বাংলায় ব্যাখ্যাসহ, মোবাইলেও সুন্দর দেখায়, ডার্ক মোড সাপোর্টেড।
                শুধু উত্তর নয় — সূত্রও দেওয়া থাকে, তাই বুঝে নিতে পারবেন।
                সাম্প্রতিক হিসাবের হিস্ট্রি রাখে, এক ক্লিকে উদাহরণ ভরে দেয়, ফলাফল কপি করা যায়।
                আপনার কোনো ডেটা সংরক্ষণ করা হয় না। বুকমার্ক করে রাখুন, যেকোনো সময় ব্যবহার করুন।
            </flux:text>
        </section>

        <section>
            <flux:heading size="lg">সচরাচর জিজ্ঞাসা (FAQ)</flux:heading>
            <div class="mt-4 space-y-4 text-sm">
                <div>
                    <strong>পার্সেন্টেজ আর পারসেন্টেজ পয়েন্টের পার্থক্য কী?</strong>
                    <p class="text-zinc-500 dark:text-zinc-400 mt-1">
                        ১০% থেকে ১৫% হলে পারসেন্টেজ পয়েন্ট বেড়েছে ৫, কিন্তু আপেক্ষিক বৃদ্ধি ৫০%।
                    </p>
                </div>
                <div>
                    <strong>মূল সংখ্যা বের করা কখন লাগে?</strong>
                    <p class="text-zinc-500 dark:text-zinc-400 mt-1">
                        যখন আপনি জানেন “৪০ টাকা হলো ২০%”, তখন মূল সংখ্যা বের করতে এই টুল ব্যবহার করুন।
                    </p>
                </div>
                <div>
                    <strong>মার্জিন আর মার্কআপ কি একই?</strong>
                    <p class="text-zinc-500 dark:text-zinc-400 mt-1">
                        না। মার্জিন = মুনাফা ÷ বিক্রির দাম, মার্কআপ = মুনাফা ÷ কেনার দাম।
                        এই ক্যালকুলেটরে কেনার দামের উপর ভিত্তি করে (মার্কআপ স্টাইল) দেখানো হয়।
                    </p>
                </div>
            </div>
        </section>
    </div>
</div>
