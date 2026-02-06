<?php

use Livewire\Volt\Component;

new class extends Component
{
    // Public properties that will be bound to the HTML elements
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'kilogram';
    public string $outputUnit = 'gram';

    // A protected property for conversion rates, making it backend data
    protected array $weightConversionRates = [
        'kilogram' => 1,
        'gram' => 1000,
        'milligram' => 1000000,
        'metric_ton' => 0.001,
        'pound' => 2.20462,
        'ounce' => 35.274,
    ];

    // This method runs when the component is first mounted
    public function mount()
    {
        $this->convertWeight();
    }

    // This method is triggered whenever a bound property changes
    public function updated($property)
    {
        // We only want to run the conversion if a relevant property changes
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertWeight();
        }
    }

    // This method handles the conversion logic
    public function convertWeight()
    {
        // Ensure the input value is a number to prevent errors
        $safeValue = is_numeric($this->inputValue) ? floatval($this->inputValue) : 0;

        // Check for division by zero
        $inputRate = $this->weightConversionRates[$this->inputUnit] ?? null;
        if ($inputRate === null || $inputRate === 0) {
            $this->outputValue = 0;
            return;
        }

        // Convert to kilograms first
        $valueInKilograms = $safeValue / $inputRate;

        // Convert to the output unit
        $outputRate = $this->weightConversionRates[$this->outputUnit] ?? 0;
        $this->outputValue = round($valueInKilograms * $outputRate, 6);
    }

    // This method swaps the units and triggers a new conversion
    public function swapWeightUnits()
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertWeight();
    }
};
?>


<section class="flex items-center justify-center">
    <div class="w-full max-w-xl rounded-2xl space-y-6">
        <h2 class="text-3xl font-extrabold text-center">Weight Converter</h2>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
            <div class="md:col-span-2">
                <label for="weightInputValue" class="sr-only">Enter value</label>
                <flux:input id="weightInputValue" type="number" wire:model.live.debounce.500ms="inputValue" placeholder="Enter value" />
            </div>

            <div class="flex justify-center md:col-span-1">
                <flux:button wire:click="swapWeightUnits" class="p-3 rounded-full">
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
                <label for="weightOutputValue" class="sr-only">Converted value</label>
                <flux:input id="weightOutputValue" type="number" wire:model="outputValue" disabled placeholder="Converted value" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <label for="weightInputUnit" class="block text-sm font-medium mb-2">From Unit</label>
                <flux:select id="weightInputUnit" wire:model.live="inputUnit">
                    <option value="kilogram">Kilogram (kg)</option>
                    <option value="gram">Gram (g)</option>
                    <option value="milligram">Milligram (mg)</option>
                    <option value="metric_ton">Metric Ton (t)</option>
                    <option value="pound">Pound (lb)</option>
                    <option value="ounce">Ounce (oz)</option>
                </flux:select>
            </div>

            <div>
                <label for="weightOutputUnit" class="block text-sm font-medium mb-2">To Unit</label>
                <flux:select id="weightOutputUnit" wire:model.live="outputUnit">
                    <option value="kilogram">Kilogram (kg)</option>
                    <option value="gram">Gram (g)</option>
                    <option value="milligram">Milligram (mg)</option>
                    <option value="metric_ton">Metric Ton (t)</option>
                    <option value="pound">Pound (lb)</option>
                    <option value="ounce">Ounce (oz)</option>
                </flux:select>
            </div>
        </div>
    </div>
</section>
