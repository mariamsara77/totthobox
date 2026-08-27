<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'decimal';
    public string $outputUnit = 'katha';

    protected array $landConversionRates = [
        'sq_ft' => 435.6,
        'sq_meter' => 40.47,
        'sq_yard' => 48.4,
        'decimal' => 1,
        'katha' => 0.605,
        'bigha' => 0.0303,
        'acre' => 0.01,
        'hectare' => 0.004047,
        'ganda' => 0.5,
        'kani' => 0.025,
    ];

    public function mount(): void
    {
        $this->convertLand();
    }

    public function updated($property): void
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertLand();
        }
    }

    public function convertLand(): void
    {
        if (!is_numeric($this->inputValue) || $this->inputValue < 0) {
            $this->outputValue = 0;
            return;
        }

        $inputRate = $this->landConversionRates[$this->inputUnit] ?? 1;
        $valueInDecimal = $this->inputValue / $inputRate;
        $outputRate = $this->landConversionRates[$this->outputUnit] ?? 1;

        $this->outputValue = round($valueInDecimal * $outputRate, 6);
    }

    public function swapLandUnits(): void
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertLand();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <x-seo title="জমি পরিমাপের ক্যালকুলেটর - শতাংশ, কাঠা, বিঘা কনভার্টার | Totthobox"
        description="সহজেই শতাংশ, কাঠা, বিঘা, একর, বর্গফুট এবং হেক্টর কনভার্ট করুন। Totthobox-এর নিখুঁত ভূমি পরিমাপ ক্যালকুলেটর।"
        keywords="জমি পরিমাপ ক্যালকুলেটর, শতাংশ থেকে কাঠা, বিঘা থেকে শতাংশ, land area converter, katha to decimal, Totthobox" />

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Land Area Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                জমি পরিমাপের ক্যালকুলেটর — শতাংশ, কাঠা, বিঘা
            </h2>
        </div>

        <div class="space-y-6 w-full">
            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="landInputUnit" wire:model.live="inputUnit" label="যে ইউনিট থেকে (From)">
                        <option value="decimal">শতাংশ / ডেসিমেল</option>
                        <option value="sq_ft">বর্গফুট (Sq. Ft)</option>
                        <option value="sq_meter">বর্গমিটার</option>
                        <option value="katha">কাঠা</option>
                        <option value="bigha">বিঘা</option>
                        <option value="acre">একর</option>
                        <option value="kani">কানি (৪০ শতাংশ)</option>
                        <option value="ganda">গণ্ডা</option>
                        <option value="hectare">হেক্টর</option>
                    </flux:select>
                    <flux:input id="landInputValue" type="number" wire:model.live.debounce.400ms="inputValue"
                        placeholder="পরিমাণ লিখুন" label="ইনপুট মান" />
                </flux:input.group>
            </div>

            <div class="w-full flex justify-center">
                <flux:button wire:click="swapLandUnits" icon="arrows-up-down" variant="subtle"
                    tooltip="ইউনিট অদলবদল করুন" />
            </div>

            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="landOutputUnit" wire:model.live="outputUnit" label="যে ইউনিটে রূপান্তর (To)">
                        <option value="katha">কাঠা</option>
                        <option value="decimal">শতাংশ / ডেসিমেল</option>
                        <option value="sq_ft">বর্গফুট (Sq. Ft)</option>
                        <option value="sq_meter">বর্গমিটার</option>
                        <option value="bigha">বিঘা</option>
                        <option value="acre">একর</option>
                        <option value="kani">কানি (৪০ শতাংশ)</option>
                        <option value="ganda">গণ্ডা</option>
                        <option value="hectare">হেক্টর</option>
                    </flux:select>
                    <flux:input id="landOutputValue" type="number" wire:model="outputValue" disabled
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

            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">গুরুত্বপূর্ণ মান</h3>
            <ul class="list-disc list-inside space-y-1">
                <li>১ শতাংশ = ৪৩৫.৬ বর্গফুট</li>
                <li>১ কাঠা ≈ ১.৬৫ শতাংশ</li>
                <li>১ বিঘা = ৩৩ শতাংশ</li>
                <li>১ একর = ১০০ শতাংশ</li>
                <li>১ কানি = ৪০ শতাংশ</li>
            </ul>
        </div>
    </div>
</section>
