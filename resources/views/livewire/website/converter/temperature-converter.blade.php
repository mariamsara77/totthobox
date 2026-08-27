<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'celsius';
    public string $outputUnit = 'fahrenheit';

    public function mount(): void
    {
        $this->convertTemperature();
    }

    public function updated($property): void
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertTemperature();
        }
    }

    public function convertTemperature(): void
    {
        $safeValue = is_numeric($this->inputValue) ? (float) $this->inputValue : 0;
        $valueInCelsius = $this->convertToCelsius($safeValue, $this->inputUnit);
        $this->outputValue = round($this->convertFromCelsius($valueInCelsius, $this->outputUnit), 6);
    }

    private function convertToCelsius(float $value, string $unit): float
    {
        return match ($unit) {
            'fahrenheit' => ($value - 32) * (5 / 9),
            'kelvin' => $value - 273.15,
            default => $value,
        };
    }

    private function convertFromCelsius(float $value, string $unit): float
    {
        return match ($unit) {
            'fahrenheit' => $value * (9 / 5) + 32,
            'kelvin' => $value + 273.15,
            default => $value,
        };
    }

    public function swapTemperatureUnits(): void
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertTemperature();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <x-seo title="অনলাইন তাপমাত্রা রূপান্তরকারী - সেলসিয়াস, ফারেনহাইট, কেলভিন | Totthobox"
        description="সহজেই সেলসিয়াস (°C), ফারেনহাইট (°F) এবং কেলভিন (K) কনভার্ট করুন। Totthobox-এর নিখুঁত Temperature Converter।"
        keywords="তাপমাত্রা রূপান্তরকারী, celsius to fahrenheit, fahrenheit to celsius, temperature converter, Totthobox" />

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Temperature Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                তাপমাত্রা রূপান্তরকারী — °C, °F, Kelvin
            </h2>
        </div>

        <div class="space-y-6 w-full">
            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="tempInputUnit" wire:model.live="inputUnit" label="যে ইউনিট থেকে (From)">
                        <option value="celsius">Celsius (°C)</option>
                        <option value="fahrenheit">Fahrenheit (°F)</option>
                        <option value="kelvin">Kelvin (K)</option>
                    </flux:select>
                    <flux:input id="tempInputValue" type="number" wire:model.live.debounce.400ms="inputValue"
                        placeholder="মান লিখুন" label="ইনপুট মান" />
                </flux:input.group>
            </div>

            <div class="w-full flex justify-center">
                <flux:button wire:click="swapTemperatureUnits" icon="arrows-up-down" variant="subtle"
                    tooltip="ইউনিট অদলবদল করুন" />
            </div>

            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="tempOutputUnit" wire:model.live="outputUnit" label="যে ইউনিটে রূপান্তর (To)">
                        <option value="celsius">Celsius (°C)</option>
                        <option value="fahrenheit">Fahrenheit (°F)</option>
                        <option value="kelvin">Kelvin (K)</option>
                    </flux:select>
                    <flux:input id="tempOutputValue" type="number" wire:model="outputValue" disabled
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

            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">গুরুত্বপূর্ণ ফর্মুলা</h3>
            <ul class="list-disc list-inside space-y-1">
                <li>°C → °F : (°C × 9/5) + 32</li>
                <li>°F → °C : (°F − 32) × 5/9</li>
                <li>°C → K : °C + 273.15</li>
                <li>K → °C : K − 273.15</li>
            </ul>
        </div>
    </div>
</section>
