<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'meters_per_second';
    public string $outputUnit = 'kilometers_per_hour';

    protected array $velocityConversionRates = [
        'meters_per_second' => 1,
        'kilometers_per_hour' => 3.6,
        'miles_per_hour' => 2.23694,
        'knots' => 1.94384,
        'feet_per_second' => 3.28084,
    ];

    public function mount(): void
    {
        $this->convertVelocity();
    }

    public function updated($property): void
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertVelocity();
        }
    }

    public function convertVelocity(): void
    {
        $safeValue = is_numeric($this->inputValue) ? (float) $this->inputValue : 0;
        $inputRate = $this->velocityConversionRates[$this->inputUnit] ?? null;

        if (!$inputRate) {
            $this->outputValue = 0;
            return;
        }

        $valueInMetersPerSecond = $safeValue / $inputRate;
        $outputRate = $this->velocityConversionRates[$this->outputUnit] ?? 0;
        $this->outputValue = round($valueInMetersPerSecond * $outputRate, 6);
    }

    public function swapVelocityUnits(): void
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertVelocity();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <x-seo title="অনলাইন গতিবেগ রূপান্তরকারী - m/s, km/h, mph, Knots কনভার্টার | Totthobox"
        description="সহজেই মিটার/সেকেন্ড (m/s), কিলোমিটার/ঘণ্টা (km/h), মাইল/ঘণ্টা (mph), নট (kn) এবং ফুট/সেকেন্ড কনভার্ট করুন। Totthobox-এর নিখুঁত Velocity Converter।"
        keywords="গতিবেগ রূপান্তরকারী, velocity converter, km/h to m/s, mph to km/h, m/s to km/h, knots converter, গতি পরিমাপ ক্যালকুলেটর, Totthobox" />

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Velocity Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                গতিবেগ রূপান্তরকারী — m/s, km/h, mph, Knots
            </h2>
        </div>

        <div class="space-y-6">
            {{-- From --}}
            <div class="w-full flex text-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="velocityInputUnit" wire:model.live="inputUnit" label="যে ইউনিট থেকে (From)">
                        <option value="meters_per_second">Meters / second (m/s)</option>
                        <option value="kilometers_per_hour">Kilometers / hour (km/h)</option>
                        <option value="miles_per_hour">Miles / hour (mph)</option>
                        <option value="knots">Knots (kn)</option>
                        <option value="feet_per_second">Feet / second (ft/s)</option>
                    </flux:select>
                    <flux:input id="velocityInputValue" type="number" wire:model.live.debounce.400ms="inputValue"
                        placeholder="মান লিখুন" label="ইনপুট মান" />
                </flux:input.group>
            </div>

            {{-- Swap Button --}}
            <div class="w-full flex justify-center">
                <flux:button wire:click="swapVelocityUnits" icon="arrows-up-down" variant="subtle"
                    tooltip="ইউনিট অদলবদল করুন" />
            </div>

            {{-- To --}}
            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="velocityOutputUnit" wire:model.live="outputUnit" label="যে ইউনিটে রূপান্তর (To)">
                        <option value="meters_per_second">Meters / second (m/s)</option>
                        <option value="kilometers_per_hour">Kilometers / hour (km/h)</option>
                        <option value="miles_per_hour">Miles / hour (mph)</option>
                        <option value="knots">Knots (kn)</option>
                        <option value="feet_per_second">Feet / second (ft/s)</option>
                    </flux:select>
                    <flux:input id="velocityOutputValue" type="number" wire:model="outputValue" disabled
                        placeholder="ফলাফল" label="রূপান্তরিত মান" />
                </flux:input.group>
            </div>
        </div>

        {{-- Extra Content for SEO & User Value --}}
        <div class="mt-24 pt-12 border-t border-zinc-400/25 space-y-6 text-sm text-zinc-600 dark:text-zinc-400">
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">কীভাবে ব্যবহার করবেন?</h3>
            <ul class="list-disc list-inside space-y-2">
                <li>উপরের বক্সে যে ইউনিট থেকে কনভার্ট করতে চান সেটি সিলেক্ট করুন।</li>
                <li>মান লিখুন — ফলাফল স্বয়ংক্রিয়ভাবে দেখাবে।</li>
                <li>মাঝের বাটনে ক্লিক করে ইউনিট অদলবদল করতে পারবেন।</li>
            </ul>

            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">গুরুত্বপূর্ণ কনভার্শন ফর্মুলা</h3>
            <ul class="list-disc list-inside space-y-1">
                <li>1 m/s = 3.6 km/h</li>
                <li>1 km/h ≈ 0.621371 mph</li>
                <li>1 knot ≈ 1.852 km/h</li>
                <li>1 m/s ≈ 3.28084 ft/s</li>
            </ul>
        </div>
    </div>
</section>
