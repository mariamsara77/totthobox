<?php

use Livewire\Volt\Component;

new class extends Component
{
    // Public properties that will be bound to the HTML elements
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'cubic_meter';
    public string $outputUnit = 'liter';

    // A protected property for conversion rates, making it backend data
    protected array $volumeConversionRates = [
        'cubic_meter' => 1,
        'cubic_kilometer' => 0.000000001,
        'liter' => 1000,
        'milliliter' => 1000000,
        'cubic_centimeter' => 1000000,
        'cubic_foot' => 35.3147,
        'cubic_inch' => 61023.7,
        'gallon' => 264.172,
    ];

    // This method runs when the component is first mounted
    public function mount()
    {
        $this->convertVolume();
    }

    // This method is triggered whenever a bound property changes
    public function updated($property)
    {
        // We only want to run the conversion if a relevant property changes
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertVolume();
        }
    }

    // This method handles the conversion logic
    public function convertVolume()
    {
        // Ensure the input value is a number to prevent errors
        $safeValue = is_numeric($this->inputValue) ? floatval($this->inputValue) : 0;

        // Check for division by zero
        $inputRate = $this->volumeConversionRates[$this->inputUnit] ?? null;
        if ($inputRate === null || $inputRate === 0) {
            $this->outputValue = 0;
            return;
        }

        // Convert to cubic meters first
        $valueInCubicMeters = $safeValue / $inputRate;

        // Convert to the output unit
        $outputRate = $this->volumeConversionRates[$this->outputUnit] ?? 0;
        $this->outputValue = round($valueInCubicMeters * $outputRate, 6);
    }

    // This method swaps the units and triggers a new conversion
    public function swapVolumeUnits()
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertVolume();
    }
};
?>


<section class="flex items-center justify-center">
    <div class="w-full max-w-xl rounded-2xl space-y-6">
        <h2 class="text-3xl font-extrabold text-center">Volume Converter</h2>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
            <div class="md:col-span-2">
                <label for="volumeInputValue" class="sr-only">Enter value</label>
                <flux:input id="volumeInputValue" type="number" wire:model.live.debounce.500ms="inputValue" placeholder="Enter value" />
            </div>

            <div class="flex justify-center md:col-span-1">
                <flux:button wire:click="swapVolumeUnits" class="p-3 rounded-full">
                    <span wire:loading.remove>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v12m0 0l4 4m-4-4l-4 4" />
                        </svg>
                    </span>
                    <span wire:loading>
                        <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </flux:button>
            </div>

            <div class="md:col-span-2">
                <label for="volumeOutputValue" class="sr-only">Converted value</label>
                <flux:input id="volumeOutputValue" type="number" wire:model="outputValue" disabled placeholder="Converted value" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <label for="volumeInputUnit" class="block text-sm font-medium mb-2">From Unit</label>
                <flux:select id="volumeInputUnit" wire:model.live="inputUnit">
                    <option value="cubic_meter">Cubic Meter ($m^3$)</option>
                    <option value="cubic_kilometer">Cubic Kilometer ($km^3$)</option>
                    <option value="liter">Liter (L)</option>
                    <option value="milliliter">Milliliter (mL)</option>
                    <option value="cubic_centimeter">Cubic Centimeter ($cm^3$)</option>
                    <option value="cubic_foot">Cubic Foot ($ft^3$)</option>
                    <option value="cubic_inch">Cubic Inch ($in^3$)</option>
                    <option value="gallon">Gallon (gal)</option>
                </flux:select>
            </div>

            <div>
                <label for="volumeOutputUnit" class="block text-sm font-medium mb-2">To Unit</label>
                <flux:select id="volumeOutputUnit" wire:model.live="outputUnit">
                    <option value="cubic_meter">Cubic Meter ($m^3$)</option>
                    <option value="cubic_kilometer">Cubic Kilometer ($km^3$)</option>
                    <option value="liter">Liter (L)</option>
                    <option value="milliliter">Milliliter (mL)</option>
                    <option value="cubic_centimeter">Cubic Centimeter ($cm^3$)</option>
                    <option value="cubic_foot">Cubic Foot ($ft^3$)</option>
                    <option value="cubic_inch">Cubic Inch ($in^3$)</option>
                    <option value="gallon">Gallon (gal)</option>
                </flux:select>
            </div>
        </div>
    </div>
</section>
