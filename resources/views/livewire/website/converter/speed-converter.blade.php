<?php

use Livewire\Volt\Component;

new class extends Component
{
    // Public properties that will be bound to the HTML elements
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'meters_per_second';
    public string $outputUnit = 'kilometers_per_hour';

    // A protected property for conversion rates, making it backend data
    protected array $velocityConversionRates = [
        'meters_per_second' => 1,
        'kilometers_per_hour' => 3.6,
        'miles_per_hour' => 2.23694,
        'knots' => 1.94384,
        'feet_per_second' => 3.28084,
    ];

    /**
     * This method runs when the component is first mounted.
     */
    public function mount()
    {
        $this->convertVelocity();
    }

    /**
     * This method is triggered whenever a bound property changes.
     * @param string $property The name of the updated property.
     */
    public function updated($property)
    {
        // We only want to run the conversion if a relevant property changes
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertVelocity();
        }
    }

    /**
     * This method handles the conversion logic.
     */
    public function convertVelocity()
    {
        // Ensure the input value is a number to prevent errors
        $safeValue = is_numeric($this->inputValue) ? floatval($this->inputValue) : 0;

        // Check for division by zero
        $inputRate = $this->velocityConversionRates[$this->inputUnit] ?? null;
        if ($inputRate === null || $inputRate === 0) {
            $this->outputValue = 0;
            return;
        }

        // Convert to meters per second first
        $valueInMetersPerSecond = $safeValue / $inputRate;

        // Convert to the output unit
        $outputRate = $this->velocityConversionRates[$this->outputUnit] ?? 0;
        $this->outputValue = round($valueInMetersPerSecond * $outputRate, 6);
    }

    /**
     * This method swaps the units and triggers a new conversion.
     */
    public function swapVelocityUnits()
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertVelocity();
    }
};
?>


<section class="flex items-center justify-center">
    <div class="w-full max-w-xl rounded-2xl space-y-6">
        <h2 class="text-3xl font-extrabold text-center">Velocity Converter</h2>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
            <div class="md:col-span-2">
                <label for="velocityInputValue" class="sr-only">Enter value</label>
                <flux:input id="velocityInputValue" type="number" wire:model.live.debounce.500ms="inputValue" placeholder="Enter value" />
            </div>

            <div class="flex justify-center md:col-span-1">
                <flux:button wire:click="swapVelocityUnits" class="p-3 rounded-full">
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
                <label for="velocityOutputValue" class="sr-only">Converted value</label>
                <flux:input id="velocityOutputValue" type="number" wire:model="outputValue" disabled placeholder="Converted value" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <label for="velocityInputUnit" class="block text-sm font-medium mb-2">From Unit</label>
                <flux:select id="velocityInputUnit" wire:model.live="inputUnit">
                    <option value="meters_per_second">Meters/second ($m/s$)</option>
                    <option value="kilometers_per_hour">Kilometers/hour ($km/h$)</option>
                    <option value="miles_per_hour">Miles/hour ($mph$)</option>
                    <option value="knots">Knots (kn)</option>
                    <option value="feet_per_second">Feet/second ($ft/s$)</option>
                </flux:select>
            </div>

            <div>
                <label for="velocityOutputUnit" class="block text-sm font-medium mb-2">To Unit</label>
                <flux:select id="velocityOutputUnit" wire:model.live="outputUnit">
                    <option value="meters_per_second">Meters/second ($m/s$)</option>
                    <option value="kilometers_per_hour">Kilometers/hour ($km/h$)</option>
                    <option value="miles_per_hour">Miles/hour ($mph$)</option>
                    <option value="knots">Knots (kn)</option>
                    <option value="feet_per_second">Feet/second ($ft/s$)</option>
                </flux:select>
            </div>
        </div>
    </div>
</section>
