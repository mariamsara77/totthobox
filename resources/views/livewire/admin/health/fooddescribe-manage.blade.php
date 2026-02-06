<?php

use Livewire\Volt\Component;
use App\Models\FoodDescribe;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads, WithPagination;

    // Form fields
    public $foodId;
    public $bangla_name = '';
    public $english_name = '';
    public $category = '';
    public $sub_category = '';
    public $description = '';
    public $health_benefits = '';
    public $nutrients = '';
    public $medical_info = '';
    public $combinations = '';
    public $others = '';
    public $Benefits = '';
    public $References = '';
    public $image;
    public $imagePreview;
    public $slug;

    // UI states
    public $showForm = false;
    public $formType = 'create'; // create / edit
    public $viewType = 'active'; // active / trashed
    public $search = '';

    // Pagination
    public $perPage = 10;
    public $sortField = 'bangla_name';
    public $sortDirection = 'asc';

    public function getFoodsProperty()
    {
        $query = $this->viewType === 'trashed'
            ? FoodDescribe::onlyTrashed()
            : FoodDescribe::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('bangla_name', 'like', "%{$this->search}%")
                  ->orWhere('english_name', 'like', "%{$this->search}%")
                  ->orWhere('category', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy($this->sortField, $this->sortDirection)
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
        $food = $this->viewType === 'trashed'
            ? FoodDescribe::withTrashed()->find($id)
            : FoodDescribe::find($id);

        $this->foodId = $food->id;
        $this->bangla_name = $food->bangla_name;
        $this->english_name = $food->english_name;
        $this->category = $food->category;
        $this->sub_category = $food->sub_category;
        $this->description = $food->description;
        $this->health_benefits = $food->health_benefits;
        $this->nutrients = $food->nutrients;
        $this->medical_info = $food->medical_info;
        $this->combinations = $food->combinations;
        $this->others = $food->others;
        $this->Benefits = $food->Benefits;
        $this->References = $food->References;
        $this->imagePreview = $food->image ? Storage::url($food->image) : null;

        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset([
            'foodId','bangla_name','english_name','category','sub_category',
            'description','health_benefits','nutrients','medical_info',
            'combinations','others','Benefits','References','image','imagePreview'
        ]);
        $this->resetErrorBag();
    }

    public function save()
    {
        $validated = $this->validate([
            'bangla_name' => 'required|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'sub_category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'health_benefits' => 'nullable|string',
            'nutrients' => 'nullable|string',
            'medical_info' => 'nullable|string',
            'combinations' => 'nullable|string',
            'others' => 'nullable|string',
            'Benefits' => 'nullable|string',
            'References' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($this->image) {
            $imagePath = $this->image->store('foods', 'public');
            $validated['image'] = $imagePath;

            if ($this->formType === 'edit' && $this->imagePreview) {
                Storage::disk('public')->delete(
                    str_replace('/storage/', '', $this->imagePreview)
                );
            }
        }

        $validated['slug'] = \Str::slug($this->english_name ?: $this->bangla_name);

        if ($this->formType === 'edit') {
            $food = FoodDescribe::withTrashed()->find($this->foodId);
            $food->update($validated);
            $message = 'Food updated successfully!';
        } else {
            FoodDescribe::create($validated);
            $message = 'Food created successfully!';
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('message', $message);
        Flux::toast($message);
    }

    public function deleteFood($id)
    {
        $food = FoodDescribe::find($id);
        $food->delete();
        session()->flash('message', 'Food moved to trash!');
        $this->resetPage();
    }

    public function restoreFood($id)
    {
        $food = FoodDescribe::onlyTrashed()->find($id);
        $food->restore();
        session()->flash('message', 'Food restored successfully!');
        $this->resetPage();
    }

    public function forceDeleteFood($id)
    {
        $food = FoodDescribe::onlyTrashed()->find($id);

        if ($food->image) {
            Storage::disk('public')->delete($food->image);
        }

        $food->forceDelete();
        session()->flash('message', 'Food permanently deleted!');
        $this->resetPage();
    }
}; ?>

<section class="">
    <div class="flex flex-col space-y-6">

        <!-- Food Form -->
        @if($showForm)
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold ">
                    {{ $formType === 'create' ? 'Create New Food' : 'Edit Food' }}
                </h3>
                <flux:button wire:click="$set('showForm', false)" size="sm">Back</flux:button>
            </div>

            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model.live="bangla_name" label="Bangla Name" required />
                    <flux:input wire:model.live="english_name" label="English Name" />
                </div>

                <flux:textarea wire:model.live="description" label="Description" rows="3" />
                <flux:textarea wire:model.live="health_benefits" label="Health Benefits" rows="3" />
                <flux:textarea wire:model.live="nutrients" label="Nutrients" rows="3" />
                <flux:textarea wire:model.live="medical_info" label="Medical Info" rows="3" />
                <flux:textarea wire:model.live="combinations" label="Combinations" rows="3" />

                <div>
                    <flux:input type="file" wire:model.live="image" accept="image/*" label="Image" />
                    @if($image)
                    <img src="{{ $image->temporaryUrl() }}" class="h-20 mt-2 rounded">
                    @elseif($imagePreview)
                    <img src="{{ $imagePreview }}" class="h-20 mt-2 rounded">
                    @endif
                </div>

                <div class="flex justify-end space-x-3">
                    <flux:button type="button" wire:click="$set('showForm', false)">Cancel</flux:button>
                    <flux:button type="submit">{{ $formType === 'create' ? 'Create' : 'Update' }}</flux:button>
                </div>
            </form>
        </div>
        @else

        <!-- Header -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Food Describe Management</h2>
            <div class="flex space-x-2">
                <flux:button wire:click="showCreateForm">Create New</flux:button>
                <flux:button wire:click="toggleView('active')" variant="{{ $viewType === 'active' ? 'primary' : 'filled' }}">Active</flux:button>
                <flux:button wire:click="toggleView('trashed')" variant="{{ $viewType === 'trashed' ? 'primary' : 'filled' }}">Trashed</flux:button>
            </div>
        </div>

        <!-- Search -->
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by name or category..." />

        <!-- Food Table -->
        <div class="overflow-x-auto rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Image</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer" wire:click="sortBy('bangla_name')">
                            Name
                            @if($sortField === 'bangla_name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($this->foods as $food)
                    <tr>
                        <td class="px-6 py-4">
                            @if($food->image)
                            <img src="{{ Storage::url($food->image) }}" class="h-10 w-10 rounded">
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium">{{ $food->bangla_name }}</div>
                            <div class="text-xs text-gray-500">{{ $food->english_name }}</div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if($viewType === 'active')
                            <button wire:click="showEditForm({{ $food->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                            <button wire:click="deleteFood({{ $food->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                            @else
                            <button wire:click="restoreFood({{ $food->id }})" class="text-green-600 hover:text-green-900 mr-3">Restore</button>
                            <button wire:click="forceDeleteFood({{ $food->id }})" class="text-red-600 hover:text-red-900">Delete Permanently</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm">No {{ $viewType }} foods found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($this->foods->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $this->foods->links() }}
            </div>
            @endif
        </div>
        @endif

    </div>
</section>
