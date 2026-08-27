<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use Carbon\Carbon;

new class extends Component {
    public string $tab = 'single';

    #[Validate('nullable|date')]
    public ?string $dob = null;

    #[Validate('nullable|date')]
    public ?string $targetDate = null;

    #[Validate('nullable|date')]
    public ?string $person1Dob = null;

    #[Validate('nullable|date')]
    public ?string $person2Dob = null;

    public function mount(): void
    {
        $this->targetDate = now()->format('Y-m-d');
    }

    public function getSingleAgeProperty(): ?array
    {
        if (!$this->dob) {
            return null;
        }

        $start = Carbon::parse($this->dob)->startOfDay();
        $end = Carbon::parse($this->targetDate ?? now())->startOfDay();

        if ($start->greaterThan($end)) {
            return ['error' => 'জন্মতারিখ নির্দিষ্ট তারিখের চেয়ে বড় হতে পারবে না।'];
        }

        $diff = $start->diff($end);

        // Next Birthday
        $nextBirthday = $start->copy()->year($end->year);
        if ($nextBirthday->isPast() && !$nextBirthday->isToday()) {
            $nextBirthday->addYear();
        }
        $daysToNextBirthday = (int) ceil($end->diffInDays($nextBirthday, false));

        // Previous Birthday
        $prevBirthday = $start->copy()->year($end->year);
        if ($prevBirthday->isFuture() || $prevBirthday->isToday()) {
            $prevBirthday->subYear();
        }
        $daysSincePrevBirthday = (int) ceil($prevBirthday->diffInDays($end, false));

        // Total months (approximate precise)
        $totalMonths = $diff->y * 12 + $diff->m;

        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'total_days' => number_format($start->diffInDays($end)),
            'total_weeks' => number_format((int) $start->diffInWeeks($end)),
            'total_hours' => number_format($start->diffInHours($end)),
            'total_months' => number_format($totalMonths),
            'next_birthday_days' => $daysToNextBirthday,
            'next_birthday_date' => $nextBirthday->translatedFormat('j F, Y'),
            'prev_birthday_days' => $daysSincePrevBirthday,
            'day_of_week' => $start->translatedFormat('l'),
            'birth_date_formatted' => $start->translatedFormat('j F, Y'),
            'target_date_formatted' => $end->translatedFormat('j F, Y'),
            'is_today' => $end->isToday(),
        ];
    }

    public function getAgeDifferenceProperty(): ?array
    {
        if (!$this->person1Dob || !$this->person2Dob) {
            return null;
        }

        $p1 = Carbon::parse($this->person1Dob)->startOfDay();
        $p2 = Carbon::parse($this->person2Dob)->startOfDay();

        if ($p1->equalTo($p2)) {
            return [
                'status' => 'same',
                'message' => 'দুইজনের বয়স একদম সমান!',
            ];
        }

        $older = $p1->lessThan($p2) ? 'প্রথম ব্যক্তি' : 'দ্বিতীয় ব্যক্তি';
        $younger = $p1->lessThan($p2) ? 'দ্বিতীয় ব্যক্তি' : 'প্রথম ব্যক্তি';
        $diff = $p1->diff($p2);

        $totalMonths = $diff->y * 12 + $diff->m;

        return [
            'status' => 'different',
            'older' => $older,
            'younger' => $younger,
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'total_days' => number_format($p1->diffInDays($p2)),
            'total_weeks' => number_format((int) $p1->diffInWeeks($p2)),
            'total_months' => number_format($totalMonths),
            'person1_formatted' => $p1->translatedFormat('j F, Y'),
            'person2_formatted' => $p2->translatedFormat('j F, Y'),
        ];
    }

    public function resetSingle(): void
    {
        $this->dob = null;
        $this->targetDate = now()->format('Y-m-d');
    }

    public function resetDifference(): void
    {
        $this->person1Dob = null;
        $this->person2Dob = null;
    }
}; ?>

<div class="max-w-2xl mx-auto">
    <x-seo title="স্মার্ট এজ ক্যালকুলেটর - সঠিক বয়স ও বয়সের পার্থক্য হিসাব"
        description="অনলাইনে নিখুঁতভাবে আপনার বয়স, পরবর্তী জন্মদিন, দুইজনের বয়সের পার্থক্য এবং আরও অনেক কিছু হিসাব করুন। বাংলায় সহজ ও দ্রুত এজ ক্যালকুলেটর।"
        keywords="এজ ক্যালকুলেটর, বয়স হিসাব, age calculator bangla, বয়সের পার্থক্য, জন্মদিন কাউন্টডাউন, অনলাইন বয়স ক্যালকুলেটর" />

    <div class="text-center mb-8">
        <flux:heading size="xl" level="1">স্মার্ট এজ ক্যালকুলেটর</flux:heading>
        <flux:subheading>সঠিক বয়স, পরবর্তী জন্মদিন এবং দুইজনের বয়সের পার্থক্য নিখুঁতভাবে হিসেব করুন</flux:subheading>
    </div>

    <div class="space-y-6">
        <flux:tab.group>
            <flux:tabs wire:model.live="tab">
                <flux:tab name="single">একক বয়স হিসেব</flux:tab>
                <flux:tab name="difference">বয়সের পার্থক্য</flux:tab>
            </flux:tabs>

            <flux:tab.panel name="single">
                {{-- Single Age Calculator --}}

                <div class="space-y-6">
                    <flux:card class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <flux:input type="date" label="জন্মতারিখ" wire:model.live="dob"
                                max="{{ now()->format('Y-m-d') }}" />
                            <flux:input type="date" label="কোন তারিখ পর্যন্ত হিসেব করবেন?"
                                wire:model.live="targetDate" />
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <flux:button wire:click="resetSingle" variant="ghost" size="sm">
                                রিসেট করুন
                            </flux:button>
                        </div>
                    </flux:card>

                    @if ($this->singleAge)
                        @if (isset($this->singleAge['error']))
                            <flux:badge color="red" size="lg" class="w-full justify-center">
                                {{ $this->singleAge['error'] }}
                            </flux:badge>
                        @else
                            <div
                                class="p-5 sm:p-6 bg-zinc-50 dark:bg-zinc-900 rounded-xl space-y-6 border border-zinc-200 dark:border-zinc-800">
                                {{-- Main Age Display --}}
                                <div class="text-center">
                                    <flux:subheading class="uppercase tracking-wider text-xs">
                                        {{ $this->singleAge['is_today'] ? 'আপনার বর্তমান বয়স' : 'নির্দিষ্ট তারিখে আপনার বয়স' }}
                                    </flux:subheading>
                                    <div class="flex flex-wrap justify-center items-baseline gap-x-2 gap-y-1 mt-3">
                                        <span
                                            class="text-3xl sm:text-4xl font-extrabold text-indigo-600 dark:text-indigo-400">
                                            {{ $this->singleAge['years'] }}
                                        </span>
                                        <span
                                            class="text-base sm:text-lg font-medium text-zinc-600 dark:text-zinc-400">বছর</span>
                                        <span
                                            class="text-3xl sm:text-4xl font-extrabold text-indigo-600 dark:text-indigo-400">
                                            {{ $this->singleAge['months'] }}
                                        </span>
                                        <span
                                            class="text-base sm:text-lg font-medium text-zinc-600 dark:text-zinc-400">মাস</span>
                                        <span
                                            class="text-3xl sm:text-4xl font-extrabold text-indigo-600 dark:text-indigo-400">
                                            {{ $this->singleAge['days'] }}
                                        </span>
                                        <span
                                            class="text-base sm:text-lg font-medium text-zinc-600 dark:text-zinc-400">দিন</span>
                                    </div>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $this->singleAge['birth_date_formatted'] }} →
                                        {{ $this->singleAge['target_date_formatted'] }}
                                    </p>
                                </div>

                                {{-- Stats Grid --}}
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                                    <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                                        <div class="text-xs text-zinc-500">মোট দিন</div>
                                        <div class="text-base sm:text-lg font-bold mt-1">
                                            {{ $this->singleAge['total_days'] }}</div>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                                        <div class="text-xs text-zinc-500">মোট সপ্তাহ</div>
                                        <div class="text-base sm:text-lg font-bold mt-1">
                                            {{ $this->singleAge['total_weeks'] }}</div>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                                        <div class="text-xs text-zinc-500">মোট মাস</div>
                                        <div class="text-base sm:text-lg font-bold mt-1">
                                            {{ $this->singleAge['total_months'] }}</div>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                                        <div class="text-xs text-zinc-500">মোট ঘণ্টা</div>
                                        <div class="text-base sm:text-lg font-bold mt-1">
                                            {{ $this->singleAge['total_hours'] }}</div>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                                        <div class="text-xs text-zinc-500">জন্মদিনের দিন</div>
                                        <div class="text-base sm:text-lg font-bold mt-1">
                                            {{ $this->singleAge['day_of_week'] }}</div>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm text-center">
                                        <div class="text-xs text-zinc-500">পূর্ববর্তী জন্মদিন</div>
                                        <div
                                            class="text-base sm:text-lg font-bold mt-1 text-amber-600 dark:text-amber-400">
                                            {{ $this->singleAge['prev_birthday_days'] }} দিন আগে
                                        </div>
                                    </div>
                                </div>

                                {{-- Next Birthday Highlight --}}
                                <div
                                    class="p-4 rounded-lg bg-zinc-400/10 border border-emerald-200 dark:border-emerald-800 text-center">
                                    <div class="text-sm font-medium text-emerald-800 dark:text-emerald-300">
                                        পরবর্তী জন্মদিন
                                    </div>
                                    <div class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">
                                        {{ $this->singleAge['next_birthday_days'] }} দিন পর
                                    </div>
                                    <div class="text-sm text-green-600 mt-0.5">
                                        {{ $this->singleAge['next_birthday_date'] }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </flux:tab.panel>
            <flux:tab.panel name="difference">
                {{-- Age Difference Calculator --}}

                <div class="space-y-6">
                    <flux:card class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <flux:input type="date" label="প্রথম ব্যক্তির জন্মতারিখ" wire:model.live="person1Dob"
                                max="{{ now()->format('Y-m-d') }}" />
                            <flux:input type="date" label="দ্বিতীয় ব্যক্তির জন্মতারিখ" wire:model.live="person2Dob"
                                max="{{ now()->format('Y-m-d') }}" />
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <flux:button wire:click="resetDifference" variant="ghost" size="sm">
                                রিসেট করুন
                            </flux:button>
                        </div>
                    </flux:card>

                    @if ($this->ageDifference)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-900  rounded-xl text-center space-y-5">
                            @if ($this->ageDifference['status'] === 'same')
                                <flux:badge color="green" size="lg" class="mx-auto">
                                    {{ $this->ageDifference['message'] }}
                                </flux:badge>
                            @else
                                <flux:badge color="indigo" size="lg" class="mx-auto">
                                    {{ $this->ageDifference['older'] }} বড়
                                </flux:badge>

                                <div>
                                    <flux:subheading class="uppercase tracking-wider text-xs">বয়সের পার্থক্য
                                    </flux:subheading>
                                    <div class="flex flex-wrap justify-center items-baseline gap-x-2 gap-y-1 mt-2">
                                        <span
                                            class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">
                                            {{ $this->ageDifference['years'] }}
                                        </span>
                                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">বছর</span>
                                        <span
                                            class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">
                                            {{ $this->ageDifference['months'] }}
                                        </span>
                                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">মাস</span>
                                        <span
                                            class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">
                                            {{ $this->ageDifference['days'] }}
                                        </span>
                                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">দিন</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-4 pt-2">
                                    <div class="p-2.5 bg-zinc-400/10 rounded-lg text-center">
                                        <flux:text>মোট দিন</flux:text>
                                        <flux:heading>{{ $this->ageDifference['total_days'] }}
                                        </flux:heading>
                                    </div>
                                    <div class="p-2.5 bg-white dark:bg-zinc-800 rounded-lg text-center">
                                        <flux:text>মোট সপ্তাহ</flux:text>
                                        <flux:heading>{{ $this->ageDifference['total_weeks'] }}
                                        </flux:heading>
                                    </div>
                                    <div class="p-2.5 bg-white dark:bg-zinc-800 rounded-lg text-center">
                                        <flux:text>মোট মাস</flux:text>
                                        <flux:heading>{{ $this->ageDifference['total_months'] }}
                                        </flux:heading>
                                    </div>
                                </div>

                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $this->ageDifference['person1_formatted'] }} ও
                                    {{ $this->ageDifference['person2_formatted'] }} এর মধ্যে
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

            </flux:tab.panel>

        </flux:tab.group>
    </div>

    {{-- SEO Content Section --}}
    <div class="mt-16 pt-2 border-t border-zinc-400/25 space-y-6 text-zinc-700 dark:text-zinc-300 leading-relaxed">
        <flux:heading size="lg" level="2">স্মার্ট এজ ক্যালকুলেটর কীভাবে ব্যবহার করবেন?</flux:heading>

        <p>
            আমাদের স্মার্ট এজ ক্যালকুলেটর দিয়ে আপনি খুব সহজে এবং নিখুঁতভাবে যেকোনো ব্যক্তির বয়স হিসাব করতে পারবেন।
            শুধু জন্মতারিখ দিলেই বছর, মাস ও দিন অনুযায়ী সম্পূর্ণ বয়স দেখাবে। এছাড়া আপনি চাইলে ভবিষ্যতের বা অতীতের কোনো
            নির্দিষ্ট তারিখ পর্যন্ত বয়সও বের করতে পারবেন।
            টুলটি Livewire ও Flux UI ব্যবহার করে তৈরি, তাই এটি দ্রুত, রেসপন্সিভ এবং মোবাইল-ফ্রেন্ডলি।
        </p>

        <flux:heading size="base" level="3">একক বয়স হিসেবের সুবিধা</flux:heading>
        <ul class="list-disc list-inside space-y-1.5 ml-1">
            <li>জন্মতারিখ থেকে আজকের বা যেকোনো তারিখ পর্যন্ত সঠিক বয়স (বছর-মাস-দিন)</li>
            <li>মোট কত দিন, সপ্তাহ, মাস ও ঘণ্টা পার হয়েছে তা এক নজরে</li>
            <li>পরবর্তী জন্মদিন কত দিন পরে এবং কোন তারিখে</li>
            <li>পূর্ববর্তী জন্মদিন কত দিন আগে পার হয়েছে</li>
            <li>কোন দিনে জন্ম হয়েছিল (সোমবার, মঙ্গলবার ইত্যাদি)</li>
            <li>রিসেট বাটন দিয়ে সহজে নতুন হিসাব শুরু করা</li>
        </ul>

        <flux:heading size="base" level="3">বয়সের পার্থক্য হিসেব</flux:heading>
        <p>
            দুইজনের জন্মতারিখ দিয়ে কে কত বছর-মাস-দিন বড় বা ছোট তা মুহূর্তেই জানতে পারবেন।
            এছাড়া মোট দিন, সপ্তাহ ও মাসের পার্থক্যও দেখাবে। বন্ধু, ভাইবোন বা পরিবারের সদস্যদের মধ্যে বয়সের তুলনা করতে
            এটি খুবই উপযোগী।
        </p>

        <flux:heading size="base" level="3">কেন এই ক্যালকুলেটর ব্যবহার করবেন?</flux:heading>
        <p>
            অনেক সময় সরকারি ফর্ম, চাকরির আবেদন, স্কুল-কলেজের ভর্তি বা ব্যক্তিগত প্রয়োজনে সঠিক বয়স জানার প্রয়োজন হয়।
            এই টুলটি Carbon লাইব্রেরি ব্যবহার করে তারিখের পার্থক্য নিখুঁতভাবে গণনা করে, তাই ভুল হওয়ার সম্ভাবনা নেই।
            সম্পূর্ণ ফ্রি, কোনো রেজিস্ট্রেশন লাগে না এবং আপনার ডেটা কোথাও সংরক্ষণ করা হয় না।
            বাংলা ভাষায় ইন্টারফেস থাকায় যেকোনো বয়সের ব্যবহারকারী সহজেই ব্যবহার করতে পারবেন।
        </p>

        <p>
            আজই চেষ্টা করে দেখুন — আপনার জন্মতারিখ দিন এবং দেখুন আপনি ঠিক কত বছর, কত মাস ও কত দিন পুরনো হয়েছেন।
            পরবর্তী জন্মদিনের কাউন্টডাউন দেখে উত্তেজিত হয়ে উঠুন!
        </p>
    </div>
</div>
