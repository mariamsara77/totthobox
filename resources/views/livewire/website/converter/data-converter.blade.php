<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'gigabyte';
    public string $outputUnit = 'megabyte';

    protected array $dataStorageConversionRates = [
        'bit' => 0.125,
        'byte' => 1,
        'kilobyte' => 1024,
        'megabyte' => 1048576,
        'gigabyte' => 1073741824,
        'terabyte' => 1099511627776,
        'petabyte' => 1125899906842624,
    ];

    public function mount(): void
    {
        $this->convertDataStorage();
    }

    public function updated($property): void
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertDataStorage();
        }
    }

    public function convertDataStorage(): void
    {
        $safeValue = is_numeric($this->inputValue) ? (float) $this->inputValue : 0;
        $inputRate = $this->dataStorageConversionRates[$this->inputUnit] ?? null;

        if (!$inputRate) {
            $this->outputValue = 0;
            return;
        }

        $valueInBytes = $safeValue * $inputRate;
        $outputRate = $this->dataStorageConversionRates[$this->outputUnit] ?? 0;

        $this->outputValue = $outputRate ? round($valueInBytes / $outputRate, 6) : 0;
    }

    public function swapDataStorageUnits(): void
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertDataStorage();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <x-seo title="অনলাইন ডাটা স্টোরেজ কনভার্টার - MB, GB, TB, PB রূপান্তর | Totthobox"
        description="সহজেই বিট, বাইট, কিলোবাইট (KB), মেগাবাইট (MB), গিগাবাইট (GB), টেরাবাইট (TB) এবং পেটাবাইট (PB) কনভার্ট করুন। Totthobox-এর নিখুঁত Data Storage Converter।"
        keywords="ডাটা কনভার্টার, MB to GB, KB to MB, GB to TB, data storage converter, স্টোরেজ ক্যালকুলেটর, Totthobox" />

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                Data Storage Converter
            </h1>
            <h2 class="text-lg text-zinc-600 dark:text-zinc-400">
                ডাটা স্টোরেজ রূপান্তরকারী — MB, GB, TB, PB
            </h2>
        </div>

        <div class="space-y-6 w-full">
            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="dataStorageInputUnit" wire:model.live="inputUnit" label="যে ইউনিট থেকে (From)">
                        <option value="bit">Bit (b)</option>
                        <option value="byte">Byte (B)</option>
                        <option value="kilobyte">Kilobyte (KB)</option>
                        <option value="megabyte">Megabyte (MB)</option>
                        <option value="gigabyte">Gigabyte (GB)</option>
                        <option value="terabyte">Terabyte (TB)</option>
                        <option value="petabyte">Petabyte (PB)</option>
                    </flux:select>
                    <flux:input id="dataStorageInputValue" type="number" wire:model.live.debounce.400ms="inputValue"
                        placeholder="মান লিখুন" label="ইনপুট মান" />
                </flux:input.group>
            </div>

            <div class="w-full flex justify-center">
                <flux:button wire:click="swapDataStorageUnits" icon="arrows-up-down" variant="subtle"
                    tooltip="ইউনিট অদলবদল করুন" />
            </div>

            <div class="w-full flex justify-center">
                <flux:input.group class="w-full flex justify-center">
                    <flux:select id="dataStorageOutputUnit" wire:model.live="outputUnit"
                        label="যে ইউনিটে রূপান্তর (To)">
                        <option value="bit">Bit (b)</option>
                        <option value="byte">Byte (B)</option>
                        <option value="kilobyte">Kilobyte (KB)</option>
                        <option value="megabyte">Megabyte (MB)</option>
                        <option value="gigabyte">Gigabyte (GB)</option>
                        <option value="terabyte">Terabyte (TB)</option>
                        <option value="petabyte">Petabyte (PB)</option>
                    </flux:select>
                    <flux:input id="dataStorageOutputValue" type="number" wire:model="outputValue" disabled
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
                <li>1 Byte = 8 Bit</li>
                <li>1 KB = 1024 Byte</li>
                <li>1 MB = 1024 KB</li>
                <li>1 GB = 1024 MB</li>
                <li>1 TB = 1024 GB</li>
            </ul>
        </div>
    </div>
</section>
