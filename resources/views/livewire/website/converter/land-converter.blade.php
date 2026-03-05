<?php

use Livewire\Volt\Component;

new class extends Component {
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'decimal';
    public string $outputUnit = 'katha';

    /**
     * ১ শতাংশ (Decimal) কে বেস ধরে সব হিসাব।
     * সূত্র: ১ শতাংশ = ৪৩৫.৬ বর্গফুট
     */
    protected array $landConversionRates = [
        'sq_ft' => 435.6,      // বর্গফুট
        'sq_meter' => 40.47,      // বর্গমিটার
        'sq_yard' => 48.4,       // গজ (Sq Yard)
        'decimal' => 1,          // শতাংশ/ডেসিমেল (Base Unit)
        'katha' => 0.605,      // ১ কাঠা = ১.৬৫ শতাংশ
        'bigha' => 0.0303,     // ১ বিঘা = ৩৩ শতাংশ
        'acre' => 0.01,       // ১ একর = ১০০ শতাংশ
        'hectare' => 0.004047,   // ১ হেক্টর = ২৪৭.১১ শতাংশ প্রায়
        'ganda' => 0.5,        // ১ গণ্ডা = ২ শতাংশ (অঞ্চলভেদে ভিন্ন হয়, এটি স্ট্যান্ডার্ড)
        'kani' => 0.025,      // ১ কানি = ৪০ শতাংশ (সরকারি হিসেবে)
    ];

    public function mount()
    {
        $this->convertLand();
    }

    public function updated($property)
    {
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertLand();
        }
    }

    public function convertLand()
    {
        if (!$this->inputValue || $this->inputValue < 0) {
            $this->outputValue = 0;
            return;
        }

        $inputRate = $this->landConversionRates[$this->inputUnit] ?? 1;

        // ইনপুট থেকে শতাংশে কনভার্ট (Value / Rate)
        $valueInDecimal = $this->inputValue / $inputRate;

        // শতাংশ থেকে টার্গেট ইউনিটে কনভার্ট (Value * Rate)
        $outputRate = $this->landConversionRates[$this->outputUnit] ?? 1;

        // ৮ দশমিক স্থান পর্যন্ত নির্ভুল রাখা হয়েছে
        $this->outputValue = round($valueInDecimal * $outputRate, 6);
    }

    public function swapLandUnits()
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertLand();
    }
};
?>

<section class="max-w-2xl mx-auto">
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-800 dark:text-white">Land Area Converter</h2>
            <p class="text-sm text-gray-500 mt-2">জমি পরিমাপের সঠিক ক্যালকুলেটর</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
            <div class="md:col-span-2">
                <flux:input id="landInputValue" type="number" wire:model.live.debounce.300ms="inputValue"
                    placeholder="পরিমাণ লিখুন" class="text-lg font-bold" />
            </div>

            <div class="flex justify-center md:col-span-1">
                <flux:button wire:click="swapLandUnits" variant="ghost" icon="arrows-right-left"
                    class="hover:rotate-180 transition-transform duration-300" />
            </div>

            <div class="md:col-span-2">
                <flux:input id="landOutputValue" type="text" value="{{ $outputValue }}" disabled
                    class="text-lg font-bold bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-500">From (থেকে)</label>
                <flux:select id="landInputUnit" wire:model.live="inputUnit">
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
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-500">To (এ
                    রূপান্তর)</label>
                <flux:select id="landOutputUnit" wire:model.live="outputUnit">
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
            </div>
        </div>

        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border-l-4 border-blue-500">
            <div class="flex gap-3">
                <div class="text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="text-xs text-blue-800 dark:text-blue-300 space-y-1">
                    <p>• ১ শতাংশ = ৪৩৫.৬ বর্গফুট | ১ কাঠা = ৭২০ বর্গফুট (প্রায়)</p>
                    <p>• ১ বিঘা = ৩৩ শতাংশ | ১ একর = ১০০ শতাংশ</p>
                    <p>• ১ কানি = ২০ গণ্ডা বা ৪০ শতাংশ (প্রমিত মান)</p>
                </div>
            </div>
        </div>
    </div>
</section>