<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'kilogram';
    public string $outputUnit = 'gram';

    protected array $weightConversionRates = [
        'kilogram' => 1,
        'gram' => 1000,
        'milligram' => 1000000,
        'metric_ton' => 0.001,
        'pound' => 2.20462,
        'ounce' => 35.274,
    ];

    public function mount(): void
    {
        $this->convertWeight();
    }

    public function updated($property): void
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertWeight();
        }
    }

    public function convertWeight(): void
    {
        $safeValue = is_numeric($this->inputValue) ? (float) $this->inputValue : 0;
        $inputRate = $this->weightConversionRates[$this->inputUnit] ?? null;

        if (!$inputRate) {
            $this->outputValue = 0;
            return;
        }

        $valueInKilograms = $safeValue / $inputRate;
        $outputRate = $this->weightConversionRates[$this->outputUnit] ?? 0;

        $this->outputValue = round($valueInKilograms * $outputRate, 6);
    }

    public function swapWeightUnits(): void
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertWeight();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <x-seo title="অনলাইন ওজন রূপান্তরকারী - কেজি, গ্রাম, পাউন্ড কনভার্টার | Totthobox"
        description="সহজেই কেজি (kg), গ্রাম (g), পাউন্ড (lb), আউন্স এবং মেট্রিক টন কনভার্ট করুন। Totthobox-এর নিখুঁত Weight Converter।"
        keywords="ওজন রূপান্তরকারী, kg to lbs, gram to kg, weight converter, পাউন্ড থেকে কেজি, Totthobox" />

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Weight Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                ওজন রূপান্তরকারী — কেজি, গ্রাম, পাউন্ড
            </h2>
        </div>

        <div class="space-y-6 w-full">
            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="weightInputUnit" wire:model.live="inputUnit" label="যে ইউনিট থেকে (From)">
                        <option value="kilogram">Kilogram (kg)</option>
                        <option value="gram">Gram (g)</option>
                        <option value="milligram">Milligram (mg)</option>
                        <option value="metric_ton">Metric Ton (t)</option>
                        <option value="pound">Pound (lb)</option>
                        <option value="ounce">Ounce (oz)</option>
                    </flux:select>
                    <flux:input id="weightInputValue" type="number" wire:model.live.debounce.400ms="inputValue"
                        placeholder="মান লিখুন" label="ইনপুট মান" />
                </flux:input.group>
            </div>

            <div class="w-full flex justify-center">
                <flux:button wire:click="swapWeightUnits" icon="arrows-up-down" variant="subtle"
                    tooltip="ইউনিট অদলবদল করুন" />
            </div>

            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="weightOutputUnit" wire:model.live="outputUnit" label="যে ইউনিটে রূপান্তর (To)">
                        <option value="kilogram">Kilogram (kg)</option>
                        <option value="gram">Gram (g)</option>
                        <option value="milligram">Milligram (mg)</option>
                        <option value="metric_ton">Metric Ton (t)</option>
                        <option value="pound">Pound (lb)</option>
                        <option value="ounce">Ounce (oz)</option>
                    </flux:select>
                    <flux:input id="weightOutputValue" type="number" wire:model="outputValue" disabled
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
                <li>1 Kilogram = 1000 Gram</li>
                <li>1 Kilogram ≈ 2.20462 Pound</li>
                <li>1 Pound = 16 Ounce</li>
                <li>1 Metric Ton = 1000 Kilogram</li>
            </ul>
        </div>
    </div>
</section>
