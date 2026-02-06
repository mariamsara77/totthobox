<?php

use Livewire\Volt\Component;

new class extends Component
{
    // Public properties that will be bound to the HTML elements
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'square_meter';
    public string $outputUnit = 'square_foot';

    // A protected property for conversion rates, making it backend data
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

    // This method runs when the component is first mounted
    public function mount()
    {
        $this->convertArea();
    }

    // This method is triggered whenever a bound property changes
    public function updated($property)
    {
        // We only want to run the conversion if a relevant property changes
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertArea();
        }
    }

    // This method handles the conversion logic
    public function convertArea()
    {
        // Ensure the input value is a number to prevent errors
        $safeValue = is_numeric($this->inputValue) ? floatval($this->inputValue) : 0;

        // Check for division by zero
        $inputRate = $this->areaConversionRates[$this->inputUnit] ?? null;
        if ($inputRate === null || $inputRate === 0) {
            $this->outputValue = 0;
            return;
        }

        // Convert to square meters first
        $valueInSquareMeters = $safeValue / $inputRate;

        // Convert to the output unit
        $outputRate = $this->areaConversionRates[$this->outputUnit] ?? 0;
        $this->outputValue = round($valueInSquareMeters * $outputRate, 6);
    }

    // This method swaps the units and triggers a new conversion
    public function swapAreaUnits()
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertArea();
    }
};
?>


<section class="flex items-center justify-center">
    <div class="w-full max-w-xl rounded-2xl space-y-6">
        <h2 class="text-3xl font-extrabold text-center">Area Converter</h2>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
            <div class="md:col-span-2">
                <label for="areaInputValue" class="sr-only">Enter value</label>
                <flux:input id="areaInputValue" type="number" wire:model.live.debounce.500ms="inputValue" placeholder="Enter value" />
            </div>

            <div class="flex justify-center md:col-span-1">
                <flux:button wire:click="swapAreaUnits" class="p-3 rounded-full">
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
                <label for="areaOutputValue" class="sr-only">Converted value</label>
                <flux:input id="areaOutputValue" type="number" wire:model="outputValue" disabled placeholder="Converted value" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <label for="areaInputUnit" class="block text-sm font-medium mb-2">From Unit</label>
                <flux:select id="areaInputUnit" wire:model.live="inputUnit">
                    <option value="square_meter">Square Meter (sq m)</option>
                    <option value="square_kilometer">Square Kilometer (sq km)</option>
                    <option value="square_centimeter">Square Centimeter (sq cm)</option>
                    <option value="square_millimeter">Square Millimeter (sq mm)</option>
                    <option value="square_mile">Square Mile (sq mi)</option>
                    <option value="acre">Acre (ac)</option>
                    <option value="hectare">Hectare (ha)</option>
                    <option value="square_yard">Square Yard (sq yd)</option>
                    <option value="square_foot">Square Foot (sq ft)</option>
                    <option value="square_inch">Square Inch (sq in)</option>
                </flux:select>
            </div>

            <div>
                <label for="areaOutputUnit" class="block text-sm font-medium mb-2">To Unit</label>
                <flux:select id="areaOutputUnit" wire:model.live="outputUnit">
                    <option value="square_meter">Square Meter (sq m)</option>
                    <option value="square_kilometer">Square Kilometer (sq km)</option>
                    <option value="square_centimeter">Square Centimeter (sq cm)</option>
                    <option value="square_millimeter">Square Millimeter (sq mm)</option>
                    <option value="square_mile">Square Mile (sq mi)</option>
                    <option value="acre">Acre (ac)</option>
                    <option value="hectare">Hectare (ha)</option>
                    <option value="square_yard">Square Yard (sq yd)</option>
                    <option value="square_foot">Square Foot (sq ft)</option>
                    <option value="square_inch">Square Inch (sq in)</option>
                </flux:select>
            </div>
        </div>
    </div>
</section>
