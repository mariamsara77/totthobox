<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'liter';
    public string $outputUnit = 'cubic_foot';

    protected array $volumeConversionRates = [
        'cubic_meter' => 1.0,
        'liter' => 1000.0,
        'milliliter' => 1000000.0,
        'cubic_centimeter' => 1000000.0,
        'cubic_foot' => 35.3147,
        'gallon' => 264.172,
    ];

    public function mount(): void
    {
        $this->convertVolume();
    }

    public function updated($property): void
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertVolume();
        }
    }

    public function convertVolume(): void
    {
        $safeValue = is_numeric($this->inputValue) ? (float) $this->inputValue : 0;
        $inputRate = $this->volumeConversionRates[$this->inputUnit] ?? null;

        if (!$inputRate) {
            $this->outputValue = 0;
            return;
        }

        $valueInCubicMeters = $safeValue / $inputRate;
        $outputRate = $this->volumeConversionRates[$this->outputUnit] ?? 0;
        $this->outputValue = round($valueInCubicMeters * $outputRate, 6);
    }

    public function swapVolumeUnits(): void
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertVolume();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <x-seo title="অনলাইন আয়তন রূপান্তরকারী - লিটার, CFT, CC, গ্যালন কনভার্টার | Totthobox"
        description="সহজেই লিটার (L), সেফটি/কিউবিক ফুট (CFT), সিসি (CC), মিলিলিটার এবং গ্যালন কনভার্ট করুন। Totthobox-এর নিখুঁত Volume Converter।"
        keywords="আয়তন রূপান্তরকারী, volume converter, CFT to liter, cft calculator, সিসি থেকে লিটার, সেফটি ক্যালকুলেটর, Totthobox" />

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Volume Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                আয়তন রূপান্তরকারী — লিটার, CFT, CC, গ্যালন
            </h2>
        </div>

        <div class="space-y-6 w-full">
            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="volumeInputUnit" wire:model.live="inputUnit" label="যে ইউনিট থেকে (From)">
                        <option value="liter">লিটার (Liter - L)</option>
                        <option value="cubic_foot">সেফটি / কিউবিক ফুট (CFT)</option>
                        <option value="cubic_centimeter">সিসি / কিউবিক সেন্টিমিটার (CC)</option>
                        <option value="milliliter">মিলিলিটার (Milliliter - mL)</option>
                        <option value="cubic_meter">কিউবিক মিটার (m³)</option>
                        <option value="gallon">গ্যালন (US Gallon)</option>
                    </flux:select>
                    <flux:input id="volumeInputValue" type="number" wire:model.live.debounce.400ms="inputValue"
                        placeholder="পরিমাণ লিখুন" label="ইনপুট মান" />
                </flux:input.group>
            </div>

            <div class="w-full flex justify-center">
                <flux:button wire:click="swapVolumeUnits" icon="arrows-right-left" variant="subtle"
                    tooltip="ইউনিট অদলবদল করুন" />
            </div>

            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="volumeOutputUnit" wire:model.live="outputUnit" label="যে ইউনিটে রূপান্তর (To)">
                        <option value="liter">লিটার (Liter - L)</option>
                        <option value="cubic_foot">সেফটি / কিউবিক ফুট (CFT)</option>
                        <option value="cubic_centimeter">সিসি / কিউবিক সেন্টিমিটার (CC)</option>
                        <option value="milliliter">মিলিলিটার (Milliliter - mL)</option>
                        <option value="cubic_meter">কিউবিক মিটার (m³)</option>
                        <option value="gallon">গ্যালন (US Gallon)</option>
                    </flux:select>
                    <flux:input id="volumeOutputValue" type="number" wire:model="outputValue" disabled
                        placeholder="রূপান্তরিত ফলাফল" label="রূপান্তরিত মান" />
                </flux:input.group>
            </div>
        </div>

        <div class="mt-24 pt-12 border-t border-zinc-400/25 space-y-6 text-sm text-zinc-600 dark:text-zinc-400">
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">কীভাবে ব্যবহার করবেন?</h3>
            <ul class="list-disc list-inside space-y-2">
                <li>“From” থেকে ইউনিট সিলেক্ট করুন (যেমন লিটার বা CFT)।</li>
                <li>মান লিখুন — ফলাফল সাথে সাথে দেখাবে।</li>
                <li>মাঝের বাটনে ক্লিক করে ইউনিট অদলবদল করতে পারবেন।</li>
            </ul>

            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">গুরুত্বপূর্ণ কনভার্শন</h3>
            <ul class="list-disc list-inside space-y-1">
                <li>1 Cubic Meter = 1000 Liter</li>
                <li>1 CFT ≈ 28.3168 Liter</li>
                <li>1 Liter = 1000 CC / mL</li>
                <li>1 US Gallon ≈ 3.78541 Liter</li>
            </ul>
        </div>
    </div>
</section>
