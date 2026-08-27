<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'square_meter';
    public string $outputUnit = 'square_foot';

    protected array $areaConversionRates = [
        'square_meter' => 1,
        'square_kilometer' => 0.000001,
        'square_centimeter' => 10000,
        'square_millimeter' => 1000000,
        'square_mile' => 0.0000003861,
        'acre' => 0.000247105,
        'hectare' => 0.0001,
        'square_yard' => 1.19599,
        'square_foot' => 10.7639,
        'square_inch' => 1550,
    ];

    public function mount(): void
    {
        $this->convertArea();
    }

    public function updated($property): void
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertArea();
        }
    }

    public function convertArea(): void
    {
        $safeValue = is_numeric($this->inputValue) ? (float) $this->inputValue : 0;
        $inputRate = $this->areaConversionRates[$this->inputUnit] ?? null;

        if (!$inputRate) {
            $this->outputValue = 0;
            return;
        }

        $valueInSquareMeters = $safeValue / $inputRate;
        $outputRate = $this->areaConversionRates[$this->outputUnit] ?? 0;
        $this->outputValue = round($valueInSquareMeters * $outputRate, 6);
    }

    public function swapAreaUnits(): void
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertArea();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <x-seo title="অনলাইন ক্ষেত্রফল রূপান্তরকারী - বর্গমিটার, বর্গফুট, একর, হেক্টর কনভার্টার | Totthobox"
        description="সহজেই বর্গমিটার, বর্গফুট, একর, হেক্টর, বর্গকিলোমিটার কনভার্ট করুন। জমি বা স্থানের নিখুঁত ক্ষেত্রফল পরিমাপের জন্য Totthobox Area Converter।"
        keywords="ক্ষেত্রফল রূপান্তরকারী, area converter, square foot to square meter, একর থেকে বর্গফুট, hectare to acre, জমি মাপার ক্যালকুলেটর, Totthobox" />

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Area Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                ক্ষেত্রফল রূপান্তরকারী — Square Meter, Square Foot, Acre, Hectare
            </h2>
        </div>

        <div class="space-y-6 w-full">
            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="areaInputUnit" wire:model.live="inputUnit" label="যে ইউনিট থেকে (From)">
                        <option value="square_meter">Square Meter (m²)</option>
                        <option value="square_kilometer">Square Kilometer (km²)</option>
                        <option value="square_centimeter">Square Centimeter (cm²)</option>
                        <option value="square_millimeter">Square Millimeter (mm²)</option>
                        <option value="square_mile">Square Mile (mi²)</option>
                        <option value="acre">Acre (ac)</option>
                        <option value="hectare">Hectare (ha)</option>
                        <option value="square_yard">Square Yard (yd²)</option>
                        <option value="square_foot">Square Foot (ft²)</option>
                        <option value="square_inch">Square Inch (in²)</option>
                    </flux:select>
                    <flux:input id="areaInputValue" type="number" wire:model.live.debounce.400ms="inputValue"
                        placeholder="মান লিখুন" label="ইনপুট মান" />
                </flux:input.group>
            </div>

            <div class="w-full flex justify-center">
                <flux:button wire:click="swapAreaUnits" icon="arrows-right-left" variant="subtle"
                    tooltip="ইউনিট অদলবদল করুন" />
            </div>

            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="areaOutputUnit" wire:model.live="outputUnit" label="যে ইউনিটে রূপান্তর (To)">
                        <option value="square_meter">Square Meter (m²)</option>
                        <option value="square_kilometer">Square Kilometer (km²)</option>
                        <option value="square_centimeter">Square Centimeter (cm²)</option>
                        <option value="square_millimeter">Square Millimeter (mm²)</option>
                        <option value="square_mile">Square Mile (mi²)</option>
                        <option value="acre">Acre (ac)</option>
                        <option value="hectare">Hectare (ha)</option>
                        <option value="square_yard">Square Yard (yd²)</option>
                        <option value="square_foot">Square Foot (ft²)</option>
                        <option value="square_inch">Square Inch (in²)</option>
                    </flux:select>
                    <flux:input id="areaOutputValue" type="number" wire:model="outputValue" disabled
                        placeholder="ফলাফল" label="রূপান্তরিত মান" />
                </flux:input.group>
            </div>
        </div>

        <div class="mt-24 pt-12 border-t border-zinc-400/25 space-y-6 text-sm text-zinc-600 dark:text-zinc-400">
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">কীভাবে ব্যবহার করবেন?</h3>
            <ul class="list-disc list-inside space-y-2">
                <li>“From” থেকে ইউনিট সিলেক্ট করুন।</li>
                <li>মান লিখুন — ফলাফল সাথে সাথে দেখাবে।</li>
                <li>মাঝের বাটনে ক্লিক করে ইউনিট অদলবদল করতে পারবেন।</li>
            </ul>

            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">গুরুত্বপূর্ণ কনভার্শন</h3>
            <ul class="list-disc list-inside space-y-1">
                <li>1 Square Meter = 10.7639 Square Foot</li>
                <li>1 Acre ≈ 4046.86 Square Meter</li>
                <li>1 Hectare = 2.47105 Acre</li>
                <li>1 Square Kilometer = 100 Hectare</li>
            </ul>
        </div>
    </div>
</section>
