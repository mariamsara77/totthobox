<?php

use Livewire\Volt\Component;

new class extends Component
{
    // Public properties that will be bound to the HTML elements
    public ?float $inputValue = null;
    public float $outputValue = 0.0;
    public string $inputUnit = 'joule';
    public string $outputUnit = 'kilowatt_hour';

    // A protected property for conversion rates to a base unit (joule)
    protected array $energyConversionRates = [
        'joule' => 1,
        'kilojoule' => 1000,
        'calorie' => 4.184,
        'kilocalorie' => 4184,
        'watt_hour' => 3600,
        'kilowatt_hour' => 3600000,
        'electronvolt' => 1.60218e-19,
    ];

    /**
     * This method runs when the component is first mounted.
     */
    public function mount()
    {
        $this->convertEnergy();
    }

    /**
     * This method is triggered whenever a bound property changes.
     * @param string $property The name of the updated property.
     */
    public function updated($property)
    {
        // We only want to run the conversion if a relevant property changes
        if (in_array($property, ['inputValue', 'inputUnit', 'outputUnit'])) {
            $this->convertEnergy();
        }
    }

    /**
     * This method handles the conversion logic.
     */
    public function convertEnergy()
    {
        // Ensure the input value is a number to prevent errors
        $safeValue = is_numeric($this->inputValue) ? floatval($this->inputValue) : 0;

        // Check for division by zero
        $inputRate = $this->energyConversionRates[$this->inputUnit] ?? null;
        if ($inputRate === null || $inputRate === 0) {
            $this->outputValue = 0;
            return;
        }

        // Convert to joules first
        $valueInJoules = $safeValue * $inputRate;

        // Convert to the output unit
        $outputRate = $this->energyConversionRates[$this->outputUnit] ?? 0;
        if ($outputRate === 0) {
            $this->outputValue = 0;
            return;
        }

        $this->outputValue = round($valueInJoules / $outputRate, 6);
    }

    /**
     * This method swaps the units and triggers a new conversion.
     */
    public function swapEnergyUnits()
    {
        [$this->inputUnit, $this->outputUnit] = [$this->outputUnit, $this->inputUnit];
        $this->convertEnergy();
    }
};
?>


<section class="flex items-center justify-center">
    <div class="w-full max-w-xl rounded-2xl space-y-6">
        <h2 class="text-3xl font-extrabold text-center">Energy Converter</h2>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
            <div class="md:col-span-2">
                <label for="energyInputValue" class="sr-only">Enter value</label>
                <flux:input id="energyInputValue" type="number" wire:model.live.debounce.500ms="inputValue" placeholder="Enter value" />
            </div>

            <div class="flex justify-center md:col-span-1">
                <flux:button wire:click="swapEnergyUnits" class="p-3 rounded-full">
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
                <label for="energyOutputValue" class="sr-only">Converted value</label>
                <flux:input id="energyOutputValue" type="number" wire:model="outputValue" disabled placeholder="Converted value" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <label for="energyInputUnit" class="block text-sm font-medium mb-2">From Unit</label>
                <flux:select id="energyInputUnit" wire:model.live="inputUnit">
                    <option value="joule">Joule ($J$)</option>
                    <option value="kilojoule">Kilojoule ($kJ$)</option>
                    <option value="calorie">Calorie (cal)</option>
                    <option value="kilocalorie">Kilocalorie (kcal)</option>
                    <option value="watt_hour">Watt-hour (Wh)</option>
                    <option value="kilowatt_hour">Kilowatt-hour (kWh)</option>
                    <option value="electronvolt">Electronvolt (eV)</option>
                </flux:select>
            </div>

            <div>
                <label for="energyOutputUnit" class="block text-sm font-medium mb-2">To Unit</label>
                <flux:select id="energyOutputUnit" wire:model.live="outputUnit">
                    <option value="joule">Joule ($J$)</option>
                    <option value="kilojoule">Kilojoule ($kJ$)</option>
                    <option value="calorie">Calorie (cal)</option>
                    <option value="kilocalorie">Kilocalorie (kcal)</option>
                    <option value="watt_hour">Watt-hour (Wh)</option>
                    <option value="kilowatt_hour">Kilowatt-hour (kWh)</option>
                    <option value="electronvolt">Electronvolt (eV)</option>
                </flux:select>
            </div>
        </div>
    </div>
</section>
