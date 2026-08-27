<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'second';
    public string $outputUnit = 'minute';

    protected array $timeConversionRates = [
        'millisecond' => 0.001,
        'second' => 1,
        'minute' => 60,
        'hour' => 3600,
        'day' => 86400,
        'week' => 604800,
        'year' => 31536000,
    ];

    public function mount(): void
    {
        $this->convertTime();
    }

    public function updated($property): void
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertTime();
        }
    }

    public function convertTime(): void
    {
        $safeValue = is_numeric($this->inputValue) ? (float) $this->inputValue : 0;
        $inputRate = $this->timeConversionRates[$this->inputUnit] ?? null;

        if (!$inputRate) {
            $this->outputValue = 0;
            return;
        }

        $valueInSeconds = $safeValue * $inputRate;
        $outputRate = $this->timeConversionRates[$this->outputUnit] ?? 0;

        $this->outputValue = $outputRate ? round($valueInSeconds / $outputRate, 6) : 0;
    }

    public function swapTimeUnits(): void
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertTime();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <x-seo title="অনলাইন সময় রূপান্তরকারী - সেকেন্ড, ঘণ্টা, দিন, বছর কনভার্টার | Totthobox"
        description="সহজেই সেকেন্ড, মিনিট, ঘণ্টা, দিন, সপ্তাহ এবং বছর কনভার্ট করুন। Totthobox-এর নিখুঁত Time Converter।"
        keywords="সময় রূপান্তরকারী, hour to minute, time converter, দিন থেকে সেকেন্ড, Totthobox" />

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Time Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                সময় রূপান্তরকারী — সেকেন্ড, মিনিট, ঘণ্টা, দিন
            </h2>
        </div>

        <div class="space-y-6 w-full">
            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="timeInputUnit" wire:model.live="inputUnit" label="যে ইউনিট থেকে (From)">
                        <option value="millisecond">Millisecond (ms)</option>
                        <option value="second">Second (s)</option>
                        <option value="minute">Minute (min)</option>
                        <option value="hour">Hour (h)</option>
                        <option value="day">Day (d)</option>
                        <option value="week">Week (wk)</option>
                        <option value="year">Year (yr)</option>
                    </flux:select>
                    <flux:input id="timeInputValue" type="number" wire:model.live.debounce.400ms="inputValue"
                        placeholder="মান লিখুন" label="ইনপুট মান" />
                </flux:input.group>
            </div>

            <div class="w-full flex justify-center">
                <flux:button wire:click="swapTimeUnits" icon="arrows-up-down" variant="subtle"
                    tooltip="ইউনিট অদলবদল করুন" />
            </div>

            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="timeOutputUnit" wire:model.live="outputUnit" label="যে ইউনিটে রূপান্তর (To)">
                        <option value="millisecond">Millisecond (ms)</option>
                        <option value="second">Second (s)</option>
                        <option value="minute">Minute (min)</option>
                        <option value="hour">Hour (h)</option>
                        <option value="day">Day (d)</option>
                        <option value="week">Week (wk)</option>
                        <option value="year">Year (yr)</option>
                    </flux:select>
                    <flux:input id="timeOutputValue" type="number" wire:model="outputValue" disabled
                        placeholder="ফলাফল" label="রূপান্তরিত মান" />
                </flux:input.group>
            </div>
        </div>

        <div class="mt-24 pt-12 border-t border-zinc-400/25 space-y-6 text-sm text-zinc-600 dark:text-zinc-400">
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">কীভাবে ব্যবহার করবেন?</h3>
            <ul class="list-disc list-inside space-y-2">
                <li>উপরের বক্সে যে ইউনিট থেকে কনভার্ট করতে চান সেটি সিলেক্ট করুন।</li>
                <li>মান লিখুন — ফলাফল স্বয়ংক্রিয়ভাবে দেখাবে।</li>
                <li>মাঝের বাটনে ক্লিক করে ইউনিট অদলবদল করতে পারবেন।</li>
            </ul>

            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">গুরুত্বপূর্ণ কনভার্শন</h3>
            <ul class="list-disc list-inside space-y-1">
                <li>1 Minute = 60 Second</li>
                <li>1 Hour = 3600 Second</li>
                <li>1 Day = 86400 Second</li>
                <li>1 Week = 7 Day</li>
                <li>1 Year ≈ 365 Day</li>
            </ul>
        </div>
    </div>
</section>
