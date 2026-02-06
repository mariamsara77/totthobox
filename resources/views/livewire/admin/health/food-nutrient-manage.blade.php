<?php

use Livewire\Volt\Component;
use App\Models\Food;
use App\Models\Nutrient;
use App\Models\FoodNutrient;
use Livewire\WithPagination;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    public $foodNutrientId;
    public $food_id;
    public $nutrient_id;
    public $amount;

    public $showForm = false;
    public $formType = 'create';
    public $viewType = 'active';
    public $search = '';

    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'asc';

    public function getFoodNutrientsProperty()
    {
        $query = $this->viewType === 'trashed' ? FoodNutrient::onlyTrashed() : FoodNutrient::query();

        if ($this->search) {
            $query->whereHas('food', fn($q) => $q->where('name_bn', 'like', "%{$this->search}%"))->orWhereHas('nutrient', fn($q) => $q->where('name_bn', 'like', "%{$this->search}%"));
        }

        return $query
            ->with(['food', 'nutrient'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleView($type)
    {
        $this->viewType = $type;
        $this->resetPage();
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->formType = 'create';
        $this->showForm = true;
    }

    public function showEditForm($id)
    {
        $item = $this->viewType === 'trashed' ? FoodNutrient::withTrashed()->find($id) : FoodNutrient::find($id);

        $this->foodNutrientId = $item->id;
        $this->food_id = $item->food_id;
        $this->nutrient_id = $item->nutrient_id;
        $this->amount = $item->amount;

        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset(['foodNutrientId', 'food_id', 'nutrient_id', 'amount']);
        $this->resetErrorBag();
    }

    public function save()
    {
        $validated = $this->validate([
            'food_id' => 'required|exists:foods,id',
            'nutrient_id' => 'required|exists:nutrients,id',
            'amount' => 'numeric|min:0',
        ]);

        if ($this->formType === 'edit') {
            $item = FoodNutrient::withTrashed()->find($this->foodNutrientId);
            $item->update($validated);
            $message = 'Food nutrient updated successfully!';
        } else {
            FoodNutrient::create($validated);
            $message = 'Food nutrient created successfully!';
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('message', $message);
        Flux::toast($message);
    }

    public function deleteItem($id)
    {
        $item = FoodNutrient::find($id);
        $item->delete();
        session()->flash('message', 'Food nutrient moved to trash!');
        $this->resetPage();
    }

    public function restoreItem($id)
    {
        $item = FoodNutrient::onlyTrashed()->find($id);
        $item->restore();
        session()->flash('message', 'Food nutrient restored successfully!');
        $this->resetPage();
    }

    public function forceDeleteItem($id)
    {
        $item = FoodNutrient::onlyTrashed()->find($id);
        $item->forceDelete();
        session()->flash('message', 'Food nutrient permanently deleted!');
        $this->resetPage();
    }

    public function getFoodsProperty()
    {
        return Food::orderBy('name_bn')->get();
    }

    public function getNutrientsProperty()
    {
        return Nutrient::orderBy('name_bn')->get();
    }
};
?>


<section class="p-4">
    <div class="flex flex-col space-y-6">

        @if ($showForm)
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">
                        {{ $formType === 'create' ? 'Attach Nutrient to Food' : 'Edit Food Nutrient' }}
                    </h3>
                    <flux:button wire:click="$set('showForm', false)" size="sm">Back</flux:button>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:select wire:model="food_id" label="Select Food">
                                <option value="">-- Select Food --</option>
                                @foreach ($this->foods as $food)
                                    <option value="{{ $food->id }}">{{ $food->name_bn }}</option>
                                @endforeach
                            </flux:select>
                            @error('food_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <flux:select wire:model="nutrient_id" label="Select Nutrient">
                                <option value="">-- Select Nutrient --</option>
                                @foreach ($this->nutrients as $nutrient)
                                    <option value="{{ $nutrient->id }}">{{ $nutrient->name_bn }} ({{ $nutrient->unit }})
                                    </option>
                                @endforeach
                            </flux:select>
                            @error('nutrient_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <flux:input wire:model.live="amount" label="Amount" type="number" step="0.01" />

                    <div class="flex justify-end space-x-3">
                        <flux:button type="button" wire:click="$set('showForm', false)">Cancel</flux:button>
                        <flux:button type="submit">{{ $formType === 'create' ? 'Attach' : 'Update' }}</flux:button>
                    </div>
                </form>
            </div>
        @else
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">Food Nutrient Management</h2>
                <div class="flex space-x-2">
                    <flux:button wire:click="showCreateForm">Attach Nutrient</flux:button>
                    <flux:button wire:click="toggleView('active')"
                        variant="{{ $viewType === 'active' ? 'primary' : 'filled' }}">Active</flux:button>
                    <flux:button wire:click="toggleView('trashed')"
                        variant="{{ $viewType === 'trashed' ? 'primary' : 'filled' }}">Trashed</flux:button>
                </div>
            </div>

            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search food or nutrient..." />

            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Food</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Nutrient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($this->foodNutrients as $item)
                            <tr>
                                <td class="px-6 py-4">{{ $item->food->name_bn ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $item->nutrient->name_bn ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $item->amount }} {{ $item->nutrient->unit ?? '' }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if ($viewType === 'active')
                                        <button wire:click="showEditForm({{ $item->id }})"
                                            class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button wire:click="deleteItem({{ $item->id }})"
                                            class="text-red-600 hover:text-red-900">Delete</button>
                                    @else
                                        <button wire:click="restoreItem({{ $item->id }})"
                                            class="text-green-600 hover:text-green-900 mr-3">Restore</button>
                                        <button wire:click="forceDeleteItem({{ $item->id }})"
                                            class="text-red-600 hover:text-red-900">Delete Permanently</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $this->foodNutrients->links() }}
            </div>
        @endif
    </div>
</section>
