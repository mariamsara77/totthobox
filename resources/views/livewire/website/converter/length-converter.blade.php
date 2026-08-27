<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'meter';
    public string $outputUnit = 'centimeter';

    protected array $lengthConversionRates = [
        'meter' => 1,
        'centimeter' => 100,
        'millimeter' => 1000,
        'kilometer' => 0.001,
        'mile' => 0.000621371,
        'yard' => 1.09361,
        'foot' => 3.28084,
        'inch' => 39.3701,
    ];

    public function mount(): void
    {
        $this->convertLength();
    }

    public function updated($property): void
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertLength();
        }
    }

    public function convertLength(): void
    {
        $safeValue = is_numeric($this->inputValue) ? (float) $this->inputValue : 0;
        $inputRate = $this->lengthConversionRates[$this->inputUnit] ?? null;

        if (!$inputRate) {
            $this->outputValue = 0;
            return;
        }

        $valueInMeters = $safeValue / $inputRate;
        $outputRate = $this->lengthConversionRates[$this->outputUnit] ?? 0;
        $this->outputValue = round($valueInMeters * $outputRate, 6);
    }

    public function swapLengthUnits(): void
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertLength();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <x-seo title="অনলাইন দৈর্ঘ্য রূপান্তরকারী - মিটার, কিমি, মাইল, ফুট, ইঞ্চি কনভার্টার | Totthobox"
        description="সহজেই মিটার (m), সেন্টিমিটার (cm), কিলোমিটার (km), মাইল (mi), ফুট (ft) এবং ইঞ্চি (in) কনভার্ট করুন। Totthobox-এর নিখুঁত Length Converter।"
        keywords="দৈর্ঘ্য রূপান্তরকারী, length converter, meter to feet, km to miles, inch to cm, মিটার থেকে ফুট, দৈর্ঘ্য ক্যালকুলেটর, Totthobox" />

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Length Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                দৈর্ঘ্য রূপান্তরকারী — Meter, Kilometer, Mile, Foot, Inch
            </h2>
        </div>

        <div class="space-y-6 w-full">
            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="lengthInputUnit" wire:model.live="inputUnit" label="যে ইউনিট থেকে (From)">
                        <option value="meter">Meter (m)</option>
                        <option value="centimeter">Centimeter (cm)</option>
                        <option value="millimeter">Millimeter (mm)</option>
                        <option value="kilometer">Kilometer (km)</option>
                        <option value="mile">Mile (mi)</option>
                        <option value="yard">Yard (yd)</option>
                        <option value="foot">Foot (ft)</option>
                        <option value="inch">Inch (in)</option>
                    </flux:select>
                    <flux:input id="lengthInputValue" type="number" wire:model.live.debounce.400ms="inputValue"
                        placeholder="মান লিখুন" label="ইনপুট মান" />
                </flux:input.group>
            </div>

            <div class="w-full flex justify-center">
                <flux:button wire:click="swapLengthUnits" icon="arrows-up-down" variant="subtle"
                    tooltip="ইউনিট অদলবদল করুন" />
            </div>

            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="lengthOutputUnit" wire:model.live="outputUnit" label="যে ইউনিটে রূপান্তর (To)">
                        <option value="meter">Meter (m)</option>
                        <option value="centimeter">Centimeter (cm)</option>
                        <option value="millimeter">Millimeter (mm)</option>
                        <option value="kilometer">Kilometer (km)</option>
                        <option value="mile">Mile (mi)</option>
                        <option value="yard">Yard (yd)</option>
                        <option value="foot">Foot (ft)</option>
                        <option value="inch">Inch (in)</option>
                    </flux:select>
                    <flux:input id="lengthOutputValue" type="number" wire:model="outputValue" disabled
                        placeholder="ফলাফল" label="রূপান্তরিত মান" />
                </flux:input.group>
            </div>
        </div>

        <div class="mt-24 pt-12 border-t border-zinc-400/25 space-y-6 text-sm text-zinc-600 dark:text-zinc-400">
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">কীভাবে ব্যবহার করবেন?</h3>
            <ul class="list-disc list-inside space-y-2">
                <li>প্রথমে “From” ইউনিট সিলেক্ট করুন।</li>
                <li>মান লিখুন — ফলাফল সাথে সাথে দেখাবে।</li>
                <li>মাঝের বাটনে ক্লিক করে ইউনিট অদলবদল করতে পারবেন।</li>
            </ul>

            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">গুরুত্বপূর্ণ কনভার্শন</h3>
            <ul class="list-disc list-inside space-y-1">
                <li>1 Meter = 100 Centimeter</li>
                <li>1 Meter = 3.28084 Foot</li>
                <li>1 Kilometer = 0.621371 Mile</li>
                <li>1 Inch = 2.54 Centimeter</li>
            </ul>
        </div>
    </div>
</section>
