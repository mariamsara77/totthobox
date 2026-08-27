<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'joule';
    public string $outputUnit = 'kilowatt_hour';

    protected array $energyConversionRates = [
        'joule' => 1,
        'kilojoule' => 1000,
        'calorie' => 4.184,
        'kilocalorie' => 4184,
        'watt_hour' => 3600,
        'kilowatt_hour' => 3600000,
        'electronvolt' => 1.60218e-19,
    ];

    public function mount(): void
    {
        $this->convertEnergy();
    }

    public function updated($property): void
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertEnergy();
        }
    }

    public function convertEnergy(): void
    {
        $safeValue = is_numeric($this->inputValue) ? (float) $this->inputValue : 0;
        $inputRate = $this->energyConversionRates[$this->inputUnit] ?? null;

        if (!$inputRate) {
            $this->outputValue = 0;
            return;
        }

        $valueInJoules = $safeValue * $inputRate;
        $outputRate = $this->energyConversionRates[$this->outputUnit] ?? 0;

        $this->outputValue = $outputRate ? round($valueInJoules / $outputRate, 6) : 0;
    }

    public function swapEnergyUnits(): void
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertEnergy();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <x-seo title="অনলাইন এনার্জি কনভার্টার - Joule, Calorie, kWh রূপান্তর | Totthobox"
        description="সহজেই জুল (J), ক্যালরি (cal), কিলোক্যালরি (kcal) এবং কিলোওয়াট-আওয়ার (kWh) কনভার্ট করুন। Totthobox-এর নিখুঁত Energy Converter।"
        keywords="এনার্জি কনভার্টার, joule to calorie, kWh to joule, energy converter, শক্তি পরিমাপ, Totthobox" />

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Energy Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                এনার্জি রূপান্তরকারী — Joule, Calorie, kWh
            </h2>
        </div>

        <div class="space-y-6 w-full">
            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="energyInputUnit" wire:model.live="inputUnit" label="যে ইউনিট থেকে (From)">
                        <option value="joule">Joule (J)</option>
                        <option value="kilojoule">Kilojoule (kJ)</option>
                        <option value="calorie">Calorie (cal)</option>
                        <option value="kilocalorie">Kilocalorie (kcal)</option>
                        <option value="watt_hour">Watt-hour (Wh)</option>
                        <option value="kilowatt_hour">Kilowatt-hour (kWh)</option>
                        <option value="electronvolt">Electronvolt (eV)</option>
                    </flux:select>
                    <flux:input id="energyInputValue" type="number" wire:model.live.debounce.400ms="inputValue"
                        placeholder="মান লিখুন" label="ইনপুট মান" />
                </flux:input.group>
            </div>

            <div class="w-full flex justify-center">
                <flux:button wire:click="swapEnergyUnits" icon="arrows-up-down" variant="subtle"
                    tooltip="ইউনিট অদলবদল করুন" />
            </div>

            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="energyOutputUnit" wire:model.live="outputUnit" label="যে ইউনিটে রূপান্তর (To)">
                        <option value="joule">Joule (J)</option>
                        <option value="kilojoule">Kilojoule (kJ)</option>
                        <option value="calorie">Calorie (cal)</option>
                        <option value="kilocalorie">Kilocalorie (kcal)</option>
                        <option value="watt_hour">Watt-hour (Wh)</option>
                        <option value="kilowatt_hour">Kilowatt-hour (kWh)</option>
                        <option value="electronvolt">Electronvolt (eV)</option>
                    </flux:select>
                    <flux:input id="energyOutputValue" type="number" wire:model="outputValue" disabled
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
                <li>1 Calorie = 4.184 Joule</li>
                <li>1 kWh = 3,600,000 Joule</li>
                <li>1 kcal = 4184 Joule</li>
                <li>1 Wh = 3600 Joule</li>
            </ul>
        </div>
    </div>
</section>
