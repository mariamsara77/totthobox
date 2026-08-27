<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use Carbon\Carbon;

new class extends Component {
    #[Validate('nullable|date')]
    public ?string $dob = null;

    #[Validate('nullable|date')]
    public ?string $person1Dob = null;

    #[Validate('nullable|date')]
    public ?string $person2Dob = null;

    /**
     * ১০০% নির্ভুল রাশি এবং তার বিস্তারিত তথ্য (Standard Western Astrology)
     */
    private function getZodiacData(int $month, int $day): ?array
    {
        $zodiacs = [
            ['name' => 'মকর', 'en' => 'Capricorn', 'emoji' => '♑', 'element' => 'পৃথিবী (Earth)', 'planet' => 'শনি (Saturn)', 'color' => 'কালো, বাদামী', 'number' => '4, 8', 'start_m' => 12, 'start_d' => 22, 'end_m' => 1, 'end_d' => 19, 'trait' => 'উচ্চাকাঙ্ক্ষী, শৃঙ্খলাপরায়ণ এবং বাস্তববাদী।'],
            ['name' => 'কুম্ভ', 'en' => 'Aquarius', 'emoji' => '♒', 'element' => 'বায়ু (Air)', 'planet' => 'শনি ও ইউরেনাস', 'color' => 'হালকা নীল, সিলভার', 'number' => '4, 7, 11', 'start_m' => 1, 'start_d' => 20, 'end_m' => 2, 'end_d' => 18, 'trait' => 'স্বাধীনচেতা, সৃজনশীল এবং মানবতাবাদী।'],
            ['name' => 'মীন', 'en' => 'Pisces', 'emoji' => '♓', 'element' => 'জল (Water)', 'planet' => 'বৃহস্পতি ও নেপচুন', 'color' => 'সমুদ্র নীল, সবুজ', 'number' => '3, 9, 12', 'start_m' => 2, 'start_d' => 19, 'end_m' => 3, 'end_d' => 20, 'trait' => 'সহানুভূতিশীল, কল্পনাপ্রবণ এবং সংবেদনশীল।'],
            ['name' => 'মেষ', 'en' => 'Aries', 'emoji' => '♈', 'element' => 'অগ্নি (Fire)', 'planet' => 'মঙ্গল (Mars)', 'color' => 'লাল, গাঢ় কমলা', 'number' => '1, 8, 17', 'start_m' => 3, 'start_d' => 21, 'end_m' => 4, 'end_d' => 19, 'trait' => 'সাহসী, উদ্যমী এবং আত্মবিশ্বাসী।'],
            ['name' => 'বৃষ', 'en' => 'Taurus', 'emoji' => '♉', 'element' => 'পৃথিবী (Earth)', 'planet' => 'শুক্র (Venus)', 'color' => 'সবুজ, গোলাপি', 'number' => '2, 6, 9', 'start_m' => 4, 'start_d' => 20, 'end_m' => 5, 'end_d' => 20, 'trait' => 'ধৈর্যশীল, বিশ্বস্ত এবং সৌন্দর্যপ্রেমী।'],
            ['name' => 'মিথুন', 'en' => 'Gemini', 'emoji' => '♊', 'element' => 'বায়ু (Air)', 'planet' => 'বুধ (Mercury)', 'color' => 'হলুদ, হালকা সবুজ', 'number' => '5, 7, 14', 'start_m' => 5, 'start_d' => 21, 'end_m' => 6, 'end_d' => 20, 'trait' => 'বুদ্ধিমান, কৌতূহলী এবং মিশুক।'],
            ['name' => 'কর্কট', 'en' => 'Cancer', 'emoji' => '♋', 'element' => 'জল (Water)', 'planet' => 'চন্দ্র (Moon)', 'color' => 'সাদা, রূপালী', 'number' => '2, 3, 15', 'start_m' => 6, 'start_d' => 21, 'end_m' => 7, 'end_d' => 22, 'trait' => 'আবেগপ্রবণ, যত্নশীল এবং প্রতিরক্ষামূলক।'],
            ['name' => 'সিংহ', 'en' => 'Leo', 'emoji' => '♌', 'element' => 'অগ্নি (Fire)', 'planet' => 'সূর্য (Sun)', 'color' => 'সোনালী, হলুদ', 'number' => '1, 3, 10', 'start_m' => 7, 'start_d' => 23, 'end_m' => 8, 'end_d' => 22, 'trait' => 'উদার, প্রফুল্ল এবং জন্মগত নেতা।'],
            ['name' => 'কন্যা', 'en' => 'Virgo', 'emoji' => '♍', 'element' => 'পৃথিবী (Earth)', 'planet' => 'বুধ (Mercury)', 'color' => 'ধূসর, হালকা হলুদ', 'number' => '5, 14, 15', 'start_m' => 8, 'start_d' => 23, 'end_m' => 9, 'end_d' => 22, 'trait' => 'বিশ্লেষণী, দয়ালু এবং পরিশ্রমী।'],
            ['name' => 'তুলা', 'en' => 'Libra', 'emoji' => '♎', 'element' => 'বায়ু (Air)', 'planet' => 'শুক্র (Venus)', 'color' => 'গোলাপি, হালকা নীল', 'number' => '4, 6, 13', 'start_m' => 9, 'start_d' => 23, 'end_m' => 10, 'end_d' => 22, 'trait' => 'ন্যায়পরায়ণ, শান্তিকামী এবং সামাজিক।'],
            ['name' => 'বৃশ্চিক', 'en' => 'Scorpio', 'emoji' => '♏', 'element' => 'জল (Water)', 'planet' => 'মঙ্গল ও প্লুটো', 'color' => 'গাঢ় লাল, কালো', 'number' => '8, 11, 18', 'start_m' => 10, 'start_d' => 23, 'end_m' => 11, 'end_d' => 21, 'trait' => 'আবেগপূর্ণ, সাহসী এবং দৃঢ়প্রতিজ্ঞ।'],
            ['name' => 'ধনু', 'en' => 'Sagittarius', 'emoji' => '♐', 'element' => 'অগ্নি (Fire)', 'planet' => 'বৃহস্পতি (Jupiter)', 'color' => 'বেগুনী, গাঢ় নীল', 'number' => '3, 7, 9', 'start_m' => 11, 'start_d' => 22, 'end_m' => 12, 'end_d' => 21, 'trait' => 'আশাবাদী, স্বাধীনতা-প্রেমী এবং দার্শনিক।'],
        ];

        foreach ($zodiacs as $z) {
            if (($month == $z['start_m'] && $day >= $z['start_d']) || ($month == $z['end_m'] && $day <= $z['end_d'])) {
                return $z;
            }
        }

        return null;
    }

    public function getSingleZodiacProperty(): ?array
    {
        if (!$this->dob) {
            return null;
        }

        $date = Carbon::parse($this->dob);
        $zodiac = $this->getZodiacData($date->month, $date->day);

        if (!$zodiac) {
            return ['error' => 'সঠিক রাশি খুঁজে পাওয়া যায়নি।'];
        }

        $zodiac['formatted_date'] = $date->translatedFormat('j F, Y');
        return $zodiac;
    }

    /**
     * অ্যাস্ট্রোলজিক্যাল এলিমেন্ট (Fire, Water, Earth, Air) অনুযায়ী রাশির মিল বিচার
     */
    public function getCompatibilityProperty(): ?array
    {
        if (!$this->person1Dob || !$this->person2Dob) {
            return null;
        }

        $p1 = Carbon::parse($this->person1Dob);
        $p2 = Carbon::parse($this->person2Dob);

        $z1 = $this->getZodiacData($p1->month, $p1->day);
        $z2 = $this->getZodiacData($p2->month, $p2->day);

        if (!$z1 || !$z2) {
            return null;
        }

        // Extract raw element name (e.g. অগ্নি, জল)
        $el1 = explode(' ', $z1['element'])[0];
        $el2 = explode(' ', $z2['element'])[0];

        $score = 50;
        $message = 'মাঝারি মিল';
        $status = 'medium';

        // Astrological Compatibility Logic
        if ($el1 === $el2) {
            $score = 95;
            $message = 'চমৎকার মিল! আপনাদের স্বভাব ও চিন্তাধারায় দারুণ সামঞ্জস্য রয়েছে।';
            $status = 'excellent';
        } elseif (($el1 === 'অগ্নি' && $el2 === 'বায়ু') || ($el2 === 'অগ্নি' && $el1 === 'বায়ু') || ($el1 === 'পৃথিবী' && $el2 === 'জল') || ($el2 === 'পৃথিবী' && $el1 === 'জল')) {
            $score = 85;
            $message = 'খুব ভালো মিল! আপনারা একে অপরকে দারুণভাবে পরিপূরক করেন।';
            $status = 'good';
        } elseif (($el1 === 'অগ্নি' && $el2 === 'জল') || ($el2 === 'অগ্নি' && $el1 === 'জল') || ($el1 === 'পৃথিবী' && $el2 === 'বায়ু') || ($el2 === 'পৃথিবী' && $el1 === 'বায়ু')) {
            $score = 45;
            $message = 'পার্থক্য রয়েছে! আপনাদের সম্পর্ক টিকিয়ে রাখতে ভালো বোঝাপড়া জরুরি।';
            $status = 'poor';
        } else {
            $score = 65;
            $message = 'মোটামুটি মিল। আপনাদের সম্পর্কে নতুনত্ব ও বৈচিত্র্য থাকতে পারে।';
            $status = 'average';
        }

        return [
            'p1' => $z1,
            'p2' => $z2,
            'score' => $score,
            'message' => $message,
            'status' => $status,
        ];
    }

    public function resetSingle(): void
    {
        $this->dob = null;
    }

    public function resetCompatibility(): void
    {
        $this->person1Dob = null;
        $this->person2Dob = null;
    }
}; ?>

<div class="max-w-2xl mx-auto">
    <x-seo title="স্মার্ট রাশিফল ক্যালকুলেটর - সঠিক রাশি ও রাশির মিল জানুন"
        description="অনলাইনে জন্মতারিখ দিয়ে আপনার সঠিক রাশি (Zodiac Sign), বৈশিষ্ট্য, শুভ সংখ্যা এবং দুইজনের রাশির মধ্যে কতটা মিল রয়েছে তা নিখুঁতভাবে হিসেব করুন।"
        keywords="রাশিফল ক্যালকুলেটর, রাশি নির্ণয়, zodiac sign calculator bangla, রাশির মিল, জোটক বিচার, অনলাইন রাশিফল" />

    <div class="text-center mb-8">
        <flux:heading size="xl" level="1">স্মার্ট রাশিফল ক্যালকুলেটর</flux:heading>
        <flux:subheading>জন্মতারিখ দিয়ে আপনার সঠিক রাশি এবং দুইজনের রাশির মিল নিখুঁতভাবে হিসেব করুন</flux:subheading>
    </div>


    <div class="space-y-6">
        <flux:card class="space-y-4">
            <div class="grid grid-cols-1 gap-4">
                <flux:input type="date" label="আপনার জন্মতারিখ দিন" wire:model.live="dob"
                    max="{{ now()->format('Y-m-d') }}" />
            </div>
            <div class="flex flex-wrap gap-4">
                <flux:button wire:click="resetSingle" variant="ghost" size="sm">
                    রিসেট করুন
                </flux:button>
            </div>
        </flux:card>

        @if ($this->singleZodiac)
            @if (isset($this->singleZodiac['error']))
                <flux:badge color="red" size="lg" class="w-full justify-center">
                    {{ $this->singleZodiac['error'] }}
                </flux:badge>
            @else
                <div
                    class="p-5 sm:p-6 bg-zinc-50 dark:bg-zinc-900 rounded-xl space-y-6 border border-zinc-200 dark:border-zinc-800 animate-fade-in-up">
                    {{-- Main Zodiac Display --}}
                    <div class="text-center">
                        <flux:subheading class="uppercase tracking-wider text-xs">আপনার রাশিফল
                        </flux:subheading>
                        <div class="mt-4 mb-2">
                            <span class="text-7xl">{{ $this->singleZodiac['emoji'] }}</span>
                        </div>
                        <h3 class="text-3xl sm:text-4xl font-extrabold text-indigo-600 dark:text-indigo-400">
                            {{ $this->singleZodiac['name'] }}
                        </h3>
                        <p class="text-lg font-medium text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ $this->singleZodiac['en'] }}
                        </p>
                        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                            জন্মতারিখ: {{ $this->singleZodiac['formatted_date'] }}
                        </p>
                    </div>

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                        <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                            <div class="text-xs text-zinc-500">উপাদান (Element)</div>
                            <div class="text-base sm:text-lg font-bold mt-1 text-emerald-600 dark:text-emerald-400">
                                {{ $this->singleZodiac['element'] }}
                            </div>
                        </div>
                        <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                            <div class="text-xs text-zinc-500">অধিপতি গ্রহ</div>
                            <div class="text-base sm:text-lg font-bold mt-1 text-blue-600 dark:text-blue-400">
                                {{ $this->singleZodiac['planet'] }}
                            </div>
                        </div>
                        <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                            <div class="text-xs text-zinc-500">শুভ রং</div>
                            <div class="text-sm font-bold mt-1 text-pink-600 dark:text-pink-400">
                                {{ $this->singleZodiac['color'] }}
                            </div>
                        </div>
                        <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                            <div class="text-xs text-zinc-500">শুভ সংখ্যা</div>
                            <div class="text-base sm:text-lg font-bold mt-1 text-purple-600 dark:text-purple-400">
                                {{ $this->singleZodiac['number'] }}
                            </div>
                        </div>
                    </div>

                    {{-- Traits Highlight --}}
                    <div
                        class="p-4 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 text-center">
                        <div class="text-sm font-medium text-indigo-800 dark:text-indigo-300 mb-2">
                            ব্যক্তিত্ব ও বৈশিষ্ট্য
                        </div>
                        <div class="text-base font-medium text-indigo-700 dark:text-indigo-400 leading-relaxed">
                            "{{ $this->singleZodiac['trait'] }}"
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
    {{-- SEO Content Section --}}
    <div class="mt-16 pt-6 border-t border-zinc-400/25 space-y-6 text-zinc-700 dark:text-zinc-300 leading-relaxed">
        <flux:heading size="lg" level="2">স্মার্ট রাশিফল ক্যালকুলেটর কীভাবে ব্যবহার করবেন?</flux:heading>

        <p>
            আমাদের স্মার্ট রাশিফল ক্যালকুলেটর দিয়ে আপনি খুব সহজে এবং নিখুঁতভাবে আপনার বা আপনার প্রিয়জনের রাশি (Zodiac
            Sign) জানতে পারবেন।
            পাশ্চাত্য জ্যোতিষশাস্ত্র (Western Astrology) অনুযায়ী আপনার জন্মতারিখ দিলেই আপনার রাশি, বৈশিষ্ট্য, শুভ রং ও
            শুভ সংখ্যা স্বয়ংক্রিয়ভাবে চলে আসবে।
        </p>

        <flux:heading size="base" level="3">একক রাশি হিসেবের সুবিধা</flux:heading>
        <ul class="list-disc list-inside space-y-1.5 ml-1">
            <li>জন্মতারিখ থেকে ১০০% সঠিক রাশি নির্ণয় (মেষ থেকে মীন)।</li>
            <li>আপনার রাশির উপাদান (Fire, Water, Earth, Air) এবং অধিপতি গ্রহ সম্পর্কে ধারণা।</li>
            <li>আপনার ব্যক্তিত্বের মূল বৈশিষ্ট্য ও স্বভাব।</li>
            <li>আপনার জন্য ভাগ্যবান রং এবং শুভ সংখ্যা এক নজরে।</li>
        </ul>

        <flux:heading size="base" level="3">রাশির মিল বা জোটক বিচার (Zodiac Compatibility)</flux:heading>
        <p>
            "রাশির মিল" ট্যাবে গিয়ে আপনি আপনার এবং আপনার পার্টনারের জন্মতারিখ দিলে, আমাদের অ্যাডভান্সড অ্যালগরিদম
            আপনাদের রাশির উপাদানের (Elements)
            উপর ভিত্তি করে একটি নিখুঁত স্কোর প্রদান করবে। এর মাধ্যমে বুঝতে পারবেন আপনাদের চিন্তাধারা এবং স্বভাবের মধ্যে
            কতটা মিল বা অমিল রয়েছে।
            বন্ধুত্ব, প্রেম বা বৈবাহিক সম্পর্কের ক্ষেত্রে এটি একটি দারুণ গাইডলাইন হতে পারে।
        </p>

        <flux:heading size="base" level="3">কেন এই টুলটি ব্যবহার করবেন?</flux:heading>
        <p>
            অনেকেই নিজের সঠিক রাশি নিয়ে বিভ্রান্তিতে থাকেন। এই টুলটি Carbon লাইব্রেরি এবং স্ট্রিক্ট ডেট রেঞ্জিং লজিক
            ব্যবহার করে তৈরি,
            তাই এখানে ভুল হওয়ার কোনো সুযোগ নেই। টুলটি Livewire ও Flux UI দিয়ে তৈরি হওয়ায় এটি দারুণ দ্রুত, সম্পূর্ণ
            ফ্রি এবং আপনার কোনো ব্যক্তিগত ডেটা সংরক্ষণ করে না।
        </p>

        <p class="font-medium text-zinc-800 dark:text-zinc-200">
            আজই আপনার জন্মতারিখ দিয়ে নিজের ভাগ্য ও স্বভাব সম্পর্কে মজার ও গুরুত্বপূর্ণ তথ্যগুলো জেনে নিন!
        </p>
    </div>
</div>
