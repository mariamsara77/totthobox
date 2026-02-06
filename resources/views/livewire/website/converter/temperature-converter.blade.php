<?php

use Livewire\Volt\Component;

new class extends Component
{
    // Public properties that will be bound to the HTML elements
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'celsius';
    public string $outputUnit = 'fahrenheit';

    /**
     * This method runs when the component is first mounted.
     */
    public function mount()
    {
        $this->convertTemperature();
    }

    /**
     * This method is triggered whenever a bound property changes.
     * @param string $property The name of the updated property.
     */
    public function updated($property)
    {
        // We only want to run the conversion if a relevant property changes
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertTemperature();
        }
    }

    /**
     * This method handles the temperature conversion logic.
     */
    public function convertTemperature()
    {
        // Ensure the input value is a number to prevent errors
        $safeValue = is_numeric($this->inputValue) ? floatval($this->inputValue) : 0;

        // Convert input value to a common base unit (Celsius)
        $valueInCelsius = $this->convertToCelsius($safeValue, $this->inputUnit);

        // Convert the value from Celsius to the desired output unit
        $this->outputValue = round($this->convertFromCelsius($valueInCelsius, $this->outputUnit), 6);
    }

    /**
     * Converts a given value from any unit to Celsius.
     * @param float $value The value to convert.
     * @param string $unit The unit of the input value.
     * @return float The converted value in Celsius.
     */
    private function convertToCelsius(float $value, string $unit): float
    {
        switch ($unit) {
            case 'fahrenheit':
                // $C = ($F - 32) \times \frac{5}{9}$
                return ($value - 32) * (5 / 9);
            case 'kelvin':
                // $C = K - 273.15$
                return $value - 273.15;
            case 'celsius':
            default:
                return $value;
        }
    }

    /**
     * Converts a value from Celsius to the desired output unit.
     * @param float $value The value in Celsius.
     * @param string $unit The desired output unit.
     * @return float The converted value.
     */
    private function convertFromCelsius(float $value, string $unit): float
    {
        switch ($unit) {
            case 'fahrenheit':
                // $F = C \times \frac{9}{5} + 32$
                return $value * (9 / 5) + 32;
            case 'kelvin':
                // $K = C + 273.15$
                return $value + 273.15;
            case 'celsius':
            default:
                return $value;
        }
    }

    /**
     * This method swaps the units and triggers a new conversion.
     */
    public function swapTemperatureUnits()
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertTemperature();
    }
};
?>


<section class="flex items-center justify-center">
    <div class="w-full max-w-xl rounded-2xl space-y-6">
        <h2 class="text-3xl font-extrabold text-center">Temperature Converter</h2>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
            <div class="md:col-span-2">
                <label for="tempInputValue" class="sr-only">Enter value</label>
                <flux:input id="tempInputValue" type="number" wire:model.live.debounce.500ms="inputValue" placeholder="Enter value" />
            </div>

            <div class="flex justify-center md:col-span-1">
                <flux:button wire:click="swapTemperatureUnits" class="p-3 rounded-full">
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
                <label for="tempOutputValue" class="sr-only">Converted value</label>
                <flux:input id="tempOutputValue" type="number" wire:model="outputValue" disabled placeholder="Converted value" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <label for="tempInputUnit" class="block text-sm font-medium mb-2">From Unit</label>
                <flux:select id="tempInputUnit" wire:model.live="inputUnit">
                    <option value="celsius">Celsius ($^\circ C$)</option>
                    <option value="fahrenheit">Fahrenheit ($^\circ F$)</option>
                    <option value="kelvin">Kelvin (K)</option>
                </flux:select>
            </div>

            <div>
                <label for="tempOutputUnit" class="block text-sm font-medium mb-2">To Unit</label>
                <flux:select id="tempOutputUnit" wire:model.live="outputUnit">
                    <option value="celsius">Celsius ($^\circ C$)</option>
                    <option value="fahrenheit">Fahrenheit ($^\circ F$)</option>
                    <option value="kelvin">Kelvin (K)</option>
                </flux:select>
            </div>
        </div>
    </div>
</section>
