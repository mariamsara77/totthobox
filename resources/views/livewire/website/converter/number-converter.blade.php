<?php

/**
 * সংখ্যা → শব্দ রূপান্তরকারী (Number → Word Converter)
 * with Decimal & Currency Support
 * Livewire Volt Component — PSR-12 compliant
 *
 * Route:  /converter/number-to-word
 *
 * Display order:
 *   1. টাকা & পয়সা (Currency)
 *   2. ইউনিকোড বাংলা
 *   3. আদর্শলিপি / প্রশিকা (Non-Unicode ANSI)
 *   4. ইংরেজি
 */

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use App\Services\NumberConverterService;

new class extends Component {
    #[Validate(['number' => ['nullable', 'numeric', 'min:0', 'max:99999999.99']])]
    public ?string $number = null;

    public string $currencyBn = '';
    public string $currencyEn = '';
    public string $bnUnicode = '';
    public string $enWords = '';
    public bool $hasResult = false;

    public function updatedNumber(): void
    {
        $this->hasResult = false;
        $this->reset(['currencyBn', 'currencyEn', 'bnUnicode', 'enWords']);

        if (blank($this->number)) {
            return;
        }

        $this->validate();

        $svc = new NumberConverterService();
        $num = floatval($this->number);

        // Separate integer and decimal parts
        $parts = explode('.', (string) $num);
        $integerPart = (int) $parts[0];
        $decimalPart = isset($parts[1]) ? (int) str_pad($parts[1], 2, '0', STR_PAD_RIGHT) : 0;

        // Currency conversion (Taka & Paisa)
        $this->currencyBn = $this->convertCurrencyToBangla($integerPart, $decimalPart);
        $this->currencyEn = $this->convertCurrencyToEnglish($integerPart, $decimalPart);

        // Word conversion
        $this->bnUnicode = $svc->toBanglaWords($integerPart);
        if ($decimalPart > 0) {
            $this->bnUnicode .= ' দশমিক ' . $svc->toBanglaWords($decimalPart);
        }

        $this->enWords = $svc->toEnglishWords($integerPart);
        if ($decimalPart > 0) {
            $this->enWords .= ' point ' . $this->digitByDigitEnglish($decimalPart);
        }

        $this->hasResult = true;
        $this->dispatch('number-updated');
    }

    private function convertCurrencyToBangla(int $taka, int $paisa): string
    {
        $svc = new NumberConverterService();

        $result = '';
        if ($taka > 0) {
            $result = $svc->toBanglaWords($taka) . ' টাকা';
        }

        if ($paisa > 0) {
            if ($result) {
                $result .= ' এবং ' . $svc->toBanglaWords($paisa) . ' পয়সা';
            } else {
                $result = $svc->toBanglaWords($paisa) . ' পয়সা';
            }
        }

        return $result ?: 'শূন্য টাকা';
    }

    private function convertCurrencyToEnglish(int $taka, int $paisa): string
    {
        $svc = new NumberConverterService();

        $result = '';
        if ($taka > 0) {
            $result = $svc->toEnglishWords($taka) . ' Taka';
        }

        if ($paisa > 0) {
            if ($result) {
                $result .= ' and ' . $svc->toEnglishWords($paisa) . ' Paisa';
            } else {
                $result = $svc->toEnglishWords($paisa) . ' Paisa';
            }
        }

        return $result ?: 'Zero Taka';
    }

    private function digitByDigitEnglish(int $num): string
    {
        $digits = [
            '0' => 'zero',
            '1' => 'one',
            '2' => 'two',
            '3' => 'three',
            '4' => 'four',
            '5' => 'five',
            '6' => 'six',
            '7' => 'seven',
            '8' => 'eight',
            '9' => 'nine',
        ];

        $str = (string) $num;
        $result = [];

        for ($i = 0; $i < strlen($str); $i++) {
            $result[] = $digits[$str[$i]];
        }

        return implode(' ', $result);
    }

    public function clear(): void
    {
        $this->reset();
        $this->hasResult = false;
    }
}; ?>

{{-- ═══════════════════════════════════════════════════════════════
NUMBER TO WORD CONVERTER – Livewire Volt Component
SEO Optimized · AdSense Policy Compliant · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<x-seo title="সংখ্যা থেকে শব্দ রূপান্তরকারী (দশমিক + টাকা-পয়সা) | বাংলা ইউনিকোড ও আদর্শলিপি কনভার্টার"
    description="যেকোনো সংখ্যা ও দশমিকসহ টাকা-পয়সাকে সহজেই ইউনিকোড বাংলা, ইংরেজি এবং আদর্শলিপি (ANSI) ফরম্যাটে রূপান্তর করুন। বাংলাদেশের স্ট্যান্ডার্ড অনুসারে ১০০% সঠিক, দ্রুত ও ফ্রি অনলাইন নাম্বার টু ওয়ার্ড কনভার্টার টুল। প্রিন্টিং ও অফিসিয়াল কাজে ব্যবহারযোগ্য।"
    keywords="সংখ্যা থেকে শব্দ, সংখ্যা থেকে কথা, number to word converter bangla, number to words bangla, বাংলা সংখ্যা রূপান্তর, সংখ্যা থেকে বাংলা শব্দ, টাকা পয়সা রূপান্তর, টাকা পয়সা শব্দে, currency to words bangla, টাকা থেকে কথা, পয়সা থেকে শব্দ, দশমিক সংখ্যা রূপান্তর, ইউনিকোড বাংলা সংখ্যা, আদর্শলিপি সংখ্যা, adorsholipi number converter, ansi bangla number, বাংলা নাম্বার টু ওয়ার্ড, number to bangla words online, free number to word converter, online number to word converter bangladesh, টাকা পয়সা কনভার্টার, bangla currency converter, সংখ্যা থেকে ইংরেজি শব্দ, english number to words, number to words with decimal, decimal number to bangla words, ১০০ টাকা শব্দে, এক হাজার টাকা কথায়, বাংলাদেশ টাকা পয়সা, taka paisa to words, number converter bangla unicode, adorsho lipi converter, প্রিন্টিং এর জন্য আদর্শলিপি, official bangla number converter" />

<main class="max-w-2xl mx-auto space-y-6">

    {{-- ══════════════════════════════════════
    Page Header (Only one H1)
    ══════════════════════════════════════ --}}
    <header class="text-center space-y-1">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100" lang="bn">
            সংখ্যা → শব্দ রূপান্তরকারী
        </h1>
        <p class="text-base text-zinc-500 dark:text-zinc-400" lang="bn">
            দশমিক সহ · টাকা-পয়সা · বাংলা ও ইংরেজি
        </p>
    </header>

    {{-- ══════════════════════════════════════
    Input Section
    ══════════════════════════════════════ --}}
    <section aria-labelledby="input-heading">
        <h2 id="input-heading" class="sr-only" lang="bn">সংখ্যা ইনপুট</h2>

        <flux:card class="space-y-4">
            <flux:input wire:model.live.debounce.400ms="number" label="সংখ্যা লিখুন"
                placeholder="যেমন: ১২৫০০.৫০ অথবা 125000.50"
                description="দশমিক সহ ০.০০ থেকে ৯,৯৯,৯৯,৯৯.৯৯ পর্যন্ত সমর্থিত" min="0" max="99999999.99"
                step="0.01" icon="hashtag" inputmode="decimal" lang="en" clearable aria-label="সংখ্যা লিখুন" />
        </flux:card>
    </section>

    {{-- ══════════════════════════════════════
    Results Section
    ══════════════════════════════════════ --}}
    <section class="space-y-4" wire:key="results-{{ $number }}" aria-labelledby="results-heading"
        aria-live="polite">
        <h2 id="results-heading" class="sr-only" lang="bn">রূপান্তরের ফলাফল</h2>

        {{-- ১. মুদ্রা (টাকা ও পয়সা) --}}
        @if ($hasResult && $currencyBn)
            <article class="space-y-3" x-data="{
                currencyUnicode: $wire.entangle('currencyBn'),
                convertCurrency() {
                    if (!this.currencyUnicode) {
                        $refs.currencyAdarshaDisplay.innerText = '';
                        return;
                    }
                    $refs.currencyAdarshaDisplay.innerText = convertToUnicode(this.currencyUnicode);
                }
            }" x-init="$watch('currencyUnicode', value => convertCurrency());
            convertCurrency();"
                @number-updated.window="convertCurrency()">
                <flux:card class="space-y-3 border-2 border-amber-300 bg-amber-50 dark:bg-amber-950/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <flux:icon name="banknotes" class="size-5 text-amber-600" aria-hidden="true" />
                            <h3 class="text-base font-semibold text-zinc-800 dark:text-zinc-200" lang="bn">মুদ্রা
                                রূপান্তর</h3>
                        </div>
                        <flux:badge color="amber" size="sm" lang="bn">টাকা ও পয়সা</flux:badge>
                    </div>

                    <div class="space-y-3">
                        {{-- বাংলা ইউনিকোড --}}
                        <div class="bg-white dark:bg-zinc-800 rounded p-3 space-y-2">
                            <p class="text-sm text-zinc-500" lang="bn">বাংলা (ইউনিকোড)</p>
                            <p class="text-2xl text-amber-700 dark:text-amber-400 font-medium" lang="bn"
                                dir="ltr">
                                {{ $currencyBn }}
                            </p>
                            <flux:button variant="ghost" size="xs" icon="clipboard"
                                aria-label="বাংলা মুদ্রা কপি করুন"
                                x-on:click="
                                                        navigator.clipboard.writeText('{{ addslashes($currencyBn) }}');
                                                        $el.querySelector('span').textContent = 'কপি হয়েছে!';
                                                        setTimeout(() => $el.querySelector('span').textContent = 'কপি', 1500);
                                                    ">
                                <span lang="bn">কপি</span>
                            </flux:button>
                        </div>

                        {{-- বাংলা আদর্শলিপি / ANSI --}}
                        <div class="bg-white dark:bg-zinc-800 rounded p-3 space-y-2">
                            <p class="text-sm text-zinc-500" lang="bn">টাকা-পয়সা (আদর্শলিপি আউটপুট)</p>
                            <p x-ref="currencyAdarshaDisplay"
                                class="adorsholipi-exp text-2xl text-orange-600 font-medium">
                            </p>
                            <flux:button variant="ghost" size="xs" icon="clipboard"
                                aria-label="আদর্শলিপি মুদ্রা কপি করুন"
                                x-on:click="
                                                        const text = $refs.currencyAdarshaDisplay.innerText;
                                                        navigator.clipboard.writeText(text);
                                                        $el.querySelector('span').textContent = 'কপি হয়েছে!';
                                                        setTimeout(() => $el.querySelector('span').textContent = 'আদর্শলিপি কপি', 1500);
                                                    ">
                                <span lang="bn">আদর্শলিপি কপি</span>
                            </flux:button>
                        </div>

                        {{-- ইংরেজি --}}
                        <div class="bg-white dark:bg-zinc-800 rounded p-3 space-y-2">
                            <p class="text-sm text-zinc-500">English</p>
                            <p class="text-2xl text-amber-700 dark:text-amber-400 font-medium">
                                {{ $currencyEn }}
                            </p>
                            <flux:button variant="ghost" size="xs" icon="clipboard"
                                aria-label="Copy English currency"
                                x-on:click="
                                                        navigator.clipboard.writeText('{{ addslashes($currencyEn) }}');
                                                        $el.querySelector('span').textContent = 'Copied!';
                                                        setTimeout(() => $el.querySelector('span').textContent = 'Copy', 1500);
                                                    ">
                                Copy
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
            </article>
        @endif

        {{-- ২. ইউনিকোড বাংলা --}}
        @if ($hasResult && $bnUnicode)
            <article>
                <flux:card class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <flux:icon name="language" class="size-4 text-zinc-400" aria-hidden="true" />
                            <h3 class="font-medium text-zinc-800 dark:text-zinc-200" lang="bn">ইউনিকোড বাংলা</h3>
                        </div>
                        <flux:badge color="green" size="sm" lang="bn">বাংলা</flux:badge>
                    </div>

                    <p class="text-4xl text-green-600 tracking-tight leading-snug" lang="bn" dir="ltr">
                        {{ $bnUnicode }}
                    </p>

                    <flux:separator />

                    <flux:button variant="ghost" size="xs" icon="clipboard" aria-label="ইউনিকোড বাংলা কপি করুন"
                        x-on:click="
                                                navigator.clipboard.writeText('{{ addslashes($bnUnicode) }}');
                                                $el.querySelector('span').textContent = 'কপি হয়েছে!';
                                                setTimeout(() => $el.querySelector('span').textContent = 'কপি করুন', 1800);
                                            ">
                        <span lang="bn">কপি করুন</span>
                    </flux:button>
                </flux:card>
            </article>
        @endif

        {{-- ৩. আদর্শলিপি / প্রশিকা --}}
        @if ($hasResult && $bnUnicode)
            <article x-data="{
                unicode: $wire.entangle('bnUnicode'),
                convert() {
                    if (!this.unicode) {
                        $refs.adarshaDisplay.innerText = '';
                        return;
                    }
                    let result = convertToUnicode(this.unicode);
                    $refs.adarshaDisplay.innerText = result;
                }
            }" x-init="$watch('unicode', value => convert());
            convert();" @number-updated.window="convert()">
                <flux:card class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <flux:icon name="printer" class="size-4 text-zinc-400" aria-hidden="true" />
                            <h3 class="font-medium text-zinc-800 dark:text-zinc-200" lang="bn">আদর্শলিপি আউটপুট
                                (প্রিন্টিং-এর জন্য)</h3>
                        </div>
                        <flux:badge color="orange" size="sm" variant="subtle">ANSI / AdorshoLipi</flux:badge>
                    </div>

                    <p x-ref="adarshaDisplay"
                        class="AdarshaLipiNormal text-xl tracking-tight text-orange-600 leading-snug adorsholipi-exp">
                    </p>

                    <flux:separator variant="subtle" />

                    <div class="flex gap-4">
                        <flux:button variant="ghost" size="xs" icon="clipboard" aria-label="আদর্শলিপি কপি করুন"
                            x-on:click="
                                                    const text = $refs.adarshaDisplay.innerText;
                                                    navigator.clipboard.writeText(text);
                                                    $el.querySelector('span').textContent = 'কপি হয়েছে!';
                                                    setTimeout(() => $el.querySelector('span').textContent = 'আদর্শলিপি কপি', 1800);
                                                ">
                            <span lang="bn">আদর্শলিপি কপি</span>
                        </flux:button>
                    </div>
                </flux:card>
            </article>
        @endif

        {{-- ৪. ইংরেজি --}}
        @if ($hasResult && $enWords)
            <article>
                <flux:card class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <flux:icon name="language" class="size-4 text-zinc-400" aria-hidden="true" />
                            <h3 class="font-medium text-zinc-800 dark:text-zinc-200" lang="bn">ইংরেজি</h3>
                        </div>
                        <flux:badge color="blue" size="sm">English</flux:badge>
                    </div>

                    <p class="text-4xl tracking-tight text-sky-600 leading-snug">
                        {{ $enWords }}
                    </p>

                    <flux:separator />

                    <flux:button variant="ghost" size="xs" icon="clipboard" aria-label="Copy English words"
                        x-on:click="
                                                navigator.clipboard.writeText('{{ addslashes($enWords) }}');
                                                $el.querySelector('span').textContent = 'Copied!';
                                                setTimeout(() => $el.querySelector('span').textContent = 'Copy', 1800);
                                            ">
                        Copy
                    </flux:button>
                </flux:card>
            </article>
        @endif
    </section>

    {{-- ══════════════════════════════════════
    Reference Table
    ══════════════════════════════════════ --}}
    <section aria-labelledby="reference-heading">
        <flux:card x-data="{ open: false }">
            <flux:button variant="ghost" size="sm" icon="table-cells" x-on:click="open = !open"
                class="w-full justify-start" aria-expanded="false" x-bind:aria-expanded="open.toString()"
                aria-controls="reference-table">
                <span lang="bn" x-text="open ? 'রেফারেন্স লুকান' : 'রেফারেন্স দেখুন'">
                    রেফারেন্স দেখুন
                </span>
            </flux:button>

            <div id="reference-table" x-show="open" x-collapse class="mt-4 overflow-x-auto">
                <h2 id="reference-heading" class="sr-only" lang="bn">রেফারেন্স টেবিল</h2>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column lang="bn">সংখ্যা</flux:table.column>
                        <flux:table.column lang="bn">টাকা-পয়সা</flux:table.column>
                        <flux:table.column>English</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ([['০.৫০', 'পঞ্চাশ পয়সা', 'Fifty Paisa'], ['১', 'এক টাকা', 'One Taka'], ['১০.৭৫', 'দশ টাকা ও পঁচাত্তর পয়সা', 'Ten Taka and Seventy-Five Paisa'], ['১০০', 'এক শত টাকা', 'One Hundred Taka'], ['১,০০০.৫০', 'এক হাজার টাকা ও পঞ্চাশ পয়সা', 'One Thousand Taka and Fifty Paisa']] as [$num, $bn, $en])
                            <flux:table.row>
                                <flux:table.cell class="font-mono">{{ $num }}</flux:table.cell>
                                <flux:table.cell lang="bn">{{ $bn }}</flux:table.cell>
                                <flux:table.cell>{{ $en }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    </section>

    {{-- ══════════════════════════════════════
    Informative Content (AdSense + SEO)
    ══════════════════════════════════════ --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-converter">
        <h2 id="about-converter" class="text-lg font-bold text-zinc-800 dark:text-zinc-200" lang="bn">
            সংখ্যা থেকে শব্দ রূপান্তরকারী সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p lang="bn">
                এই অনলাইন টুলটি যেকোনো সংখ্যাকে সহজেই <strong>বাংলা ও ইংরেজি শব্দে</strong> রূপান্তর করে।
                দশমিকসহ টাকা-পয়সা ফরম্যাটেও রূপান্তর করা যায়। ইউনিকোড বাংলা এবং আদর্শলিপি (ANSI) উভয় ফরম্যাট সমর্থিত,
                যা প্রিন্টিং ও অফিসিয়াল কাজে ব্যবহার করা যায়।
            </p>
            <p lang="bn">
                বাংলাদেশের স্ট্যান্ডার্ড অনুযায়ী ১ টাকা = ১০০ পয়সা ধরে হিসাব করা হয়।
                সর্বোচ্চ ৯,৯৯,৯৯,৯৯.৯৯ পর্যন্ত সংখ্যা সাপোর্ট করে।
            </p>
        </div>
    </section>

    {{-- ══════════════════════════════════════
    FAQ Section
    ══════════════════════════════════════ --}}
    <section class="space-y-3" aria-labelledby="faq-heading">
        <h2 id="faq-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200" lang="bn">
            প্রায়শই জিজ্ঞাসিত প্রশ্ন
        </h2>

        <div class="space-y-2">
            <details class="group rounded-xl border border-zinc-400/25 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span lang="bn">কীভাবে সংখ্যা থেকে শব্দে রূপান্তর করব?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed" lang="bn">
                    উপরের ইনপুট বক্সে সংখ্যা লিখুন (যেমন: 1250.50)। স্বয়ংক্রিয়ভাবে বাংলা, ইংরেজি এবং টাকা-পয়সা ফরম্যাটে
                    ফলাফল দেখাবে।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span lang="bn">আদর্শলিপি কী এবং কেন প্রয়োজন?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed" lang="bn">
                    আদর্শলিপি একটি ANSI ফন্ট ভিত্তিক বাংলা লেখা পদ্ধতি। অনেক পুরনো সফটওয়্যার ও প্রিন্টারে ইউনিকোড
                    সাপোর্ট না থাকলে
                    আদর্শলিপি ফরম্যাট ব্যবহার করা হয়। এই টুলে এক ক্লিকেই আদর্শলিপি আউটপুট পাওয়া যায়।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span lang="bn">দশমিক সংখ্যা সাপোর্ট করে কি?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed" lang="bn">
                    হ্যাঁ, দশমিকসহ সংখ্যা সম্পূর্ণ সাপোর্ট করে। টাকা ও পয়সা আলাদাভাবে দেখানো হয় এবং সাধারণ সংখ্যায়
                    “দশমিক” শব্দসহ রূপান্তর করা হয়।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span lang="bn">সর্বোচ্চ কত বড় সংখ্যা রূপান্তর করা যায়?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed" lang="bn">
                    এই টুলটি ০ থেকে ৯,৯৯,৯৯,৯৯.৯৯ পর্যন্ত সংখ্যা সাপোর্ট করে। এর বেশি সংখ্যা দিলে ভ্যালিডেশন এরর দেখাবে।
                </div>
            </details>
        </div>
    </section>

    {{-- Footer note --}}
    <p class="text-center text-xs text-zinc-400 dark:text-zinc-600" lang="bn">
        বাংলাদেশের স্ট্যান্ডার্ড: টাকা ও পয়সা (১ টাকা = ১০০ পয়সা)
    </p>

</main>

@assets
    @push('scripts')
        <script>
            function convertToUnicode(sample) {
                if (!sample) return "";
                var compareData = ['\n', ' ', '	', '!', '\"', '#', '\$', '%', '&', '\'', '\(', '\)', '\*', '\+', ',', '-', '.',
                    '\/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '\?', '@', 'A', 'B',
                    'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',
                    'X', 'Y', 'Z', '\[', '\\', '\]', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
                    'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '\{', '|', '\}', '~', '‚',
                    'ƒ', '„', '…', '†', '‡', 'ˆ', '‰', 'Š', '‹', 'Œ', '‘', '’', '“', '”', '•', '–', '—', '˜', '™', 'š', '›',
                    'œ', 'Ÿ', '¡', '¢', '£', '¤', '¥', '¦', '§', '¨', '©', 'ª', '«', '¬', '®', '®', '¯', '°', '±', '²', '³',
                    '´', 'µ', '¶', '·', '¸', '¹', 'º', '»', '¼', '½', '¾', '¿', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È',
                    'É', 'Ê', 'Ë', 'Ì', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', '×', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'Þ', 'ß', 'à',
                    'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ',
                    'ö', '÷', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'þ', '®¡', '®±'
                ];
                var unicodeData = ['\n', ' ', '	', '!', '\"', '#', '\$', '%', '&', '\'', '\(', '\)', '\*', '\+', ',', '-', '.',
                    '\/', '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', ':', ';', '<', '=', '>', '\?', '@', 'অ', 'আ',
                    'ই', 'ঈ', 'উ', 'ঊ', 'ঋ', 'এ', 'ঐ', 'ও', 'ঔ', 'ক', 'খ', 'গ', 'ঘ', 'ঙ', 'চ', 'ছ', 'জ', 'ঝ', 'ঞ', 'ট', 'ঠ',
                    'ড', 'ঢ', 'ণ', '\[', '\\', '\]', '×', 'e0', '÷', 'ত', 'থ', 'দ', 'ধ', 'ন', 'প', 'ফ', 'ব', 'ভ', 'ম', 'য',
                    'র', 'ল', 'শ', 'ষ', 'স', 'হ', 'ক্ষ', 'ড়', 'ঢ়', 'য়', 'ৎ', 'ং', 'ঃ', 'ঁ', '।', '\{', '|', '\}', '~',
                    'ক্ক', 'ক্ট', 'ক্স', 'গু', 'গ্‌গ', 'গ্ধ', 'ঙ্ক', 'ঙ্গ', 'চ্‌ঞ', 'জ্জ', 'জ্ঝ', 'জ্ঞ', 'ঞ্চ', 'ঞ্ছ',
                    'ঞ্জ', 'ঞ্ঝ', 'ট্ট', 'ড্ড', 'ণ্ঠ', 'ণ্ড', 'ত্ত', 'ত্থ', 'ত্র', 'দ্দ', 'া', 'ি', 'ী', 'ু', 'ু', 'ু', 'ূ',
                    'ূ', 'ূ', 'ৃ', 'ৃ', '×', 'ে', 'ে', 'ৈ', 'ৈ', 'ৗ', '্‌ক', 'গ্‌', 'ঙ্‌', 'চ্‌', 'জ', '্‌ঞ', 'ণ্‌', '্‌ত',
                    '্‌ত্ত', '্‌ত্র', '্‌দ', '্‌ধ', 'ন্', 'ঙ্‌', '্‌ন', '্‌ন', '×', 'প্‌', '্ব', '্‌ব', '্‌ব', 'ম্', '্‌ম',
                    '্য', '্র', '্র', '্র', '~', 'র্', 'ল্‌', '্‌ল', '্ল', 'শ্‌', 'ষ্‌', 'ষ্‌', 'স্‌', 'স্‌', '্‌', '্‌থ',
                    'দ্ধ', '×', 'দ্ধ্ব', '×', 'দ্ব', 'দ্ভ', 'দ্র', 'ন্ঠ', 'ন্ড', 'ন্ধ', 'ন্ন', 'ন্ধ', 'প্প', 'ফ্র', 'ব্জ',
                    'ব্দ', 'ব্ধ', '×', 'ব্ব', 'ভ্র', 'ম্ব', 'ম্ভ', 'ম্ভ্র', 'ল্ক', 'ল্ড', 'ল্ল', 'শু', 'শ্ত', 'ষ্ট', 'ষ্ঠ',
                    'স্ক', 'স্ক্র', 'স্ব', 'হু', 'ক্ষ্ম', 'ো', 'ৌ'
                ];
                var uniJukto = ["এ্য", "অ্য", "ক্ক", "ক্ট", "ক্ট্র", "ক্ত", "ক্ত্র", "ক্ন", "ক্ব", "ক্ম", "ক্য", "ক্র", "ক্ল",
                    "ক্ষ", "ক্ষ্ণ", "ক্ষ্ব", "ক্ষ্ম", "ক্ষ্ম্য", "ক্ষ্য", "ক্স", "খ্য", "খ্র", "গ্ণ", "গ্ধ", "গ্ধ্য",
                    "গ্ধ্র", "গ্ন", "গ্ন্য", "গ্ব", "গ্ম", "গ্য", "গ্র", "গ্র্য", "গ্ল", "ঘ্ন", "ঘ্য", "ঘ্র", "ঙ্ক",
                    "ঙ্ক্ত", "ঙ্ক্য", "ঙ্ক্ষ", "ঙ্খ", "ঙ্খ্য", "ঙ্গ", "ঙ্গ্য", "ঙ্ঘ", "ঙ্ঘ্য", "ঙ্ঘ্র", "ঙ্ম", "চ্চ", "চ্ছ",
                    "চ্ছ্ব", "চ্ছ্র", "চ্ঞ", "চ্ব", "চ্য", "জ্জ", "জ্জ্ব", "জ্ঝ", "জ্ঞ", "জ্ব", "জ্য", "জ্র", "ঞ্চ", "ঞ্ছ",
                    "ঞ্জ", "ঞ্ঝ", "ট্ট", "ট্ব", "ট্ম", "ট্য", "ট্র", "ড্ড", "ড্ব", "ড্য", "ড্র", "ঢ্য", "ঢ্র", "ণ্ট", "ণ্ঠ",
                    "ণ্ঠ্য", "ণ্ড", "ণ্ড্য", "ণ্ড্র", "ণ্ঢ", "ণ্ণ", "ণ্ব", "ণ্ম", "ণ্য", "ত্ত", "ত্ত্ব", "ত্ত্য", "ত্থ",
                    "ত্ন", "ত্ব", "ত্ম", "ত্ম্য", "ত্য", "ত্র", "ত্র্য", "থ্ব", "থ্য", "থ্র", "দ্গ", "দ্ঘ", "দ্দ", "দ্দ্ব",
                    "দ্ধ", "দ্ব", "দ্ভ", "দ্ভ্র", "দ্ম", "দ্য", "দ্র", "দ্র্য", "ধ্ন", "ধ্ব", "ধ্ম", "ধ্য", "ধ্র", "র্ধ্ব",
                    "ন্ট", "ন্ট্র", "ন্ঠ", "ন্ড", "ন্ড্র", "ন্ত", "ন্ত্ব", "ন্ত্য", "ন্ত্র", "ন্ত্র্য", "ন্থ", "ন্থ্র",
                    "ন্দ", "ন্দ্য", "ন্দ্ব", "ন্দ্র", "ন্ধ", "ন্ধ্য", "ন্ধ্র", "ন্ন", "ন্ব", "ন্ম", "ন্য", "প্ট", "প্ত",
                    "প্ন", "প্প", "প্য", "প্র", "প্র্য", "প্ল", "প্স", "ফ্র", "ফ্ল", "ব্জ", "ব্দ", "ব্ধ", "ব্ব", "ব্য",
                    "ব্র", "ব্ল", "ভ্ব", "ভ্য", "ভ্র", "ম্ন", "ম্প", "ম্প্র", "ম্ফ", "ম্ব", "ম্ব্র", "ম্ভ", "ম্ভ্র", "ম্ম",
                    "ম্য", "ম্র", "ম্ল", "য্য", "র্ক", "র্ক্য", "র্গ্য", "র্ঘ্য", "র্চ্য", "র্জ্য", "র্জ্ঞ", "র্ণ্য",
                    "র্ত্য", "র্থ্য", "র্ব্য", "র্ম্য", "র্শ্য", "র্ষ্য", "র্হ্য", "র্খ", "র্গ", "র্গ্র", "র্ঘ", "র্চ",
                    "র্ছ", "র্জ", "র্ঝ", "র্ট", "র্ড", "র্ণ", "র্ত", "র্ত্ম", "র্ত্র", "র্ৎ", "র্থ", "র্দ", "র্দ্ব",
                    "র্দ্র", "র্ধ", "র্ধ্ব", "র্ন", "র্প", "র্ফ", "র্ব", "র্ভ", "র্ম", "র্য", "র্ল", "র্শ", "র্শ্ব", "র্ষ",
                    "র্স", "র্হ", "র্হ্য", "র্ঢ্য", "ল্ক", "ল্ক্য", "ল্গ", "ল্ট", "ল্ড", "ল্প", "ল্ফ", "ল্ব", "ল্ভ", "ল্ম",
                    "ল্য", "ল্ল", "শ্চ", "শ্ছ", "শ্ন", "শ্ব", "শ্ম", "শ্য", "শ্র", "শ্ল", "ষ্ক", "ষ্ক্র", "ষ্ট", "ষ্ট্য",
                    "ষ্ট্র", "ষ্ঠ", "ষ্ঠ্য", "ষ্ণ", "ষ্প", "ষ্প্র", "ষ্ফ", "ষ্ব", "ষ্ম", "ষ্য", "স্ক", "স্ক্র", "স্খ",
                    "স্ট", "স্ট্র", "স্ত", "স্ত্ব", "স্ত্য", "স্ত্র", "স্থ", "স্থ্য", "স্ন", "স্প", "স্প্র", "স্প্ল", "স্ফ",
                    "স্ব", "স্ম", "স্য", "স্র", "স্ল", "হ্ণ", "হ্ন", "হ্ব", "হ্ম", "হ্য", "হ্র", "হ্ল", "ড়্গ", "স্ন্য",
                    "র্জ্জ", "র্গ", "ভ্ল"
                ];
                var adorshoJukto = ["HÉ", "AÉ", "‚", "ƒ", "ƒÊ", "š²", "šÊ²", "LÁ", "LÅ", "LÈ", "LÉ", "œ²", "LÓ", "r", "rÁ",
                    "rÅ", "rÈ", "rÈÉ", "rÉ", "„", "MÉ", "MË", "NÀ", "‡", "‡É", "‡Ê", "NÀ", "NÀÉ", "NÄ", "NÈ", "NÉ", "NË",
                    "NËÉ", "NÔ", "OÀ", "OÉ", "OË", "ˆ", "ˆa", "ˆÉ", "´r", "´M", "´MÉ", "‰", "‰É", "´O", "´OÉ", "´OÊ", "´j",
                    "µQ", "µR", "µRÆ", "µRÊ", "Š", "QÄ", "QÉ", "‹", "‹Æ", "Œ", "‘", "SÅ", "SÉ", "SÊ", "’", "“", "”", "•",
                    "–", "VÄ", "VÈ", "VÉ", "VÊ", "—", "Xh", "XÉ", "XÊ", "YÉ", "YÊ", "¸V", "˜", "˜É", "™", "™É", "™Ê", "¸Y",
                    "ZZ", "ZÄ", "ZÈ", "ZÉ", "š", "šÆ", "šÉ", "›", "aÁ", "aÅ", "aÈ", "aÈÉ", "aÉ", "œ", "œÉ", "bÄ", "bÉ",
                    "bË", "cN", "cO", "Ÿ", "ŸÅ", "Ü", "à", "á", "áÊ", "cÈ", "cÉ", "â", "âÉ", "dÀ", "dÄ", "dÈ", "dÉ", "dË",
                    "dÄÑ", "¾V", "¾VÌ", "ã", "ä", "äÊ", "¿¹", "¿¹Æ", "¿¹É", "¿»", "¿»É", "¿Û", "¿ÛÊ", "¾c", "¾cÉ", "¾à",
                    "¾cÐ", "å", "åÉ", "åÌ", "æ", "eÄ", "¾j", "eÉ", "ÃV", "ç", "fÀ", "è", "fÉ", "fÐ", "fÐÉ", "fÔ", "Ãp", "é",
                    "gÓ", "ê", "ë", "ì", "î", "hÉ", "hÐ", "hÔ", "ih", "iÉ", "ï", "jÀ", "Çf", "ÇfÐ", "Çg", "ð", "ðÊ", "ñ",
                    "ò", "Çj", "jÉ", "jË", "jÔ", "kÉ", "LÑ", "LÑÉ", "NÑÉ", "OÑÉ", "QÑÉ", "SÑÉ", "‘Ñ", "ZÑÉ", "aÑÉ", "bÑÉ",
                    "hÑÉ", "jÑÉ", "nÑÉ", "oÑÉ", "qÑÉ", "MÑ", "NÑ", "NÑÉ", "OÑ", "QÑ", "RÑ", "SÑ", "TÑ", "VÑ", "XÑ", "ZÑ",
                    "aÑ", "aÈÑ", "œÑ", "vÑ", "bÑ", "cÑ", "àÑ", "âÑ", "dÑ", "dÄÑ", "eÑ", "fÑ", "gÑ", "hÑ", "iÑ", "jÑ", "kÑ",
                    "mÑ", "nÑ", "nÄÑ", "oÑ", "pÑ", "qÑ", "qÑÉ", "YÑÉ", "ó", "óÉ", "ÒN", "ÒV", "ô", "Òf", "Òg", "mÄ", "mi",
                    "mÈ", "mÉ", "õ", "ÕQ", "ÕR", "nÀ", "nÄ", "nÈ", "nÉ", "nÐ", "nÔ", "×L", "×œ²", "ø", "øÉ", "øÌ", "ù",
                    "ùÉ", "o·", "Öf", "ÖfÐ", "Ög", "×h", "oÈ", "oÉ", "ú", "û", "ØM", "ØV", "ØVÌ", "Ù¹", "ÙaÅ", "Ù¹É", "Ù»",
                    "ÙÛ", "ÙÛÉ", "pÀ", "Øf", "ØfÊ", "ØfÔ", "Øg", "ü", "pÈ", "pÉ", "pË", "pÔ", "qÁ", "q²", "qÆ", "þ", "qÉ",
                    "qÊ", "qÔ", "sN", "pÀÉ", "‹Ñ", "NÑ", "iÔ"
                ];

                input = sample;
                input = input.replace(/য়/g, "য়").replace(/ড়/g, "ড়");
                var outputText = '';
                var correctingAlpha = ['¢', '­', '®', '¯', '°', 'Ñ'];

                var i2 = '',
                    i3 = '',
                    i4 = '';

                for (i = 0; i < input.length; i++) {
                    if (input[i + 1] == '্') {
                        for (k = 0; k < uniJukto.length; k++) {
                            if (input[i + 1] == '্' && input[i + 3] == '্' && input[i + 5] == '্') {
                                if (uniJukto[k] == input[i] + input[i + 1] + input[i + 2] + input[i + 3] + input[i + 4] + input[
                                        i + 5] + input[i + 6]) {
                                    var temp = adorshoJukto[k];
                                    let temp2 = '';
                                    i += 6;
                                    if (input[i + 1] == 'ি' || input[i + 1] == 'ে' || input[i + 1] == 'ৈ' || input[i + 1] ==
                                        'ো' || input[i + 1] == 'ৌ') {
                                        switch (input[i + 1]) {
                                            case 'ি':
                                                outputText += '¢';
                                                break;
                                            case 'ে':
                                                outputText += '®';
                                                break;
                                            case 'ৈ':
                                                outputText += '¯';
                                                break;
                                            case 'ো':
                                                outputText += '®';
                                                temp2 = '¡';
                                                break;
                                            case 'ৌ':
                                                outputText += '®';
                                                temp2 = '±';
                                                break;
                                        }
                                        i++;
                                    }
                                    outputText += temp + temp2;
                                    break;
                                }
                            } else if (input[i + 1] == '্' && input[i + 3] == '্') {
                                if (uniJukto[k] == input[i] + input[i + 1] + input[i + 2] + input[i + 3] + input[i + 4]) {
                                    var temp = adorshoJukto[k];
                                    let temp2 = '';
                                    i += 4;
                                    if (input[i + 1] == 'ি' || input[i + 1] == 'ে' || input[i + 1] == 'ৈ' || input[i + 1] ==
                                        'ো' || input[i + 1] == 'ৌ') {
                                        switch (input[i + 1]) {
                                            case 'ি':
                                                outputText += '¢';
                                                break;
                                            case 'ে':
                                                outputText += '®';
                                                break;
                                            case 'ৈ':
                                                outputText += '¯';
                                                break;
                                            case 'ো':
                                                outputText += '®';
                                                temp2 = '¡';
                                                break;
                                            case 'ৌ':
                                                outputText += '®';
                                                temp2 = '±';
                                                break;
                                        }
                                        i++;
                                    }
                                    outputText += temp + temp2;
                                    break;
                                }
                            } else if (input[i + 1] == '্') {
                                if (uniJukto[k] == input[i] + input[i + 1] + input[i + 2]) {
                                    var temp = adorshoJukto[k];
                                    let temp2 = '';
                                    i += 2;
                                    if (input[i + 1] == 'ি' || input[i + 1] == 'ে' || input[i + 1] == 'ৈ' || input[i + 1] ==
                                        'ো' || input[i + 1] == 'ৌ') {
                                        switch (input[i + 1]) {
                                            case 'ি':
                                                outputText += '¢';
                                                break;
                                            case 'ে':
                                                outputText += '®';
                                                break;
                                            case 'ৈ':
                                                outputText += '¯';
                                                break;
                                            case 'ো':
                                                outputText += '®';
                                                temp2 = '¡';
                                                break;
                                            case 'ৌ':
                                                outputText += '®';
                                                temp2 = '±';
                                                break;
                                        }
                                        i++;
                                    }
                                    outputText += temp + temp2;
                                    break;
                                }
                            }
                        }
                    } else {
                        for (j = 0; j < compareData.length; j++) {
                            if (input[i] == unicodeData[j]) {
                                var temp = compareData[j];
                                let temp2 = '';
                                if (input[i + 1] == 'ি' || input[i + 1] == 'ে' || input[i + 1] == 'ৈ' || input[i + 1] == 'ো' ||
                                    input[i + 1] == 'ৌ') {
                                    switch (input[i + 1]) {
                                        case 'ি':
                                            outputText += '¢';
                                            break;
                                        case 'ে':
                                            outputText += '®';
                                            break;
                                        case 'ৈ':
                                            outputText += '¯';
                                            break;
                                        case 'ো':
                                            outputText += '®';
                                            temp2 = '¡';
                                            break;
                                        case 'ৌ':
                                            outputText += '®';
                                            temp2 = '±';
                                            break;
                                    }
                                    i++;
                                }
                                outputText += temp + temp2;
                                break;
                            }
                        }
                    }
                }

                for (i = 0; i < outputText.length; i++) {
                    if (outputText[i] == '®') {
                        i2 += '­';
                    } else {
                        i2 += outputText[i];
                    }
                }
                copyTo = i2;

                return outputText;
            }

            function converter(dir) {
                dir === 'u2a' ?
                    document.getElementById("adarshalipiInput").value = convertToUnicode(document.getElementById("output")
                        .value) :
                    document.getElementById("output").value = convertToAdarshalipi(document.getElementById("adarshalipiInput")
                        .value);
            }

            window.autoConvert = function(dir) {
                const unicodeEl = document.getElementById("output");
                const adarshaEl = document.getElementById("adarshalipiInput");

                if (dir === 'u2a') {
                    if (unicodeEl && adarshaEl) {
                        adarshaEl.value = convertToUnicode(unicodeEl.value);
                    }
                } else if (dir === 'a2u') {
                    if (typeof convertToAdarshalipi === "function") {
                        unicodeEl.value = convertToAdarshalipi(adarshaEl.value);
                    }
                }
            }

            function initializeConverter() {
                autoConvert('u2a');
            }

            window.onload = initializeConverter;
            document.addEventListener('livewire:load', initializeConverter);
            document.addEventListener('livewire:navigated', initializeConverter);
        </script>
    @endpush
@endassets
