<?php

use Livewire\Volt\Component;
use App\Models\Food;
use App\Models\FoodCategory;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads, WithPagination;

    // Form fields
    public $foodId;
    public $food_category_id;
    public $name_bn = '';
    public $name_en = '';
    public $description = ''; // NEW
    public $calorie = 0;
    public $carb = 0;
    public $protein = 0;
    public $fat = 0;
    public $fiber = 0;
    public $serving_size = ''; // NEW
    public $slug = '';
    public $status = 0;
    public $image;
    public $imagePreview;

    // SEO fields
    public $meta_title;
    public $meta_description;
    public $meta_keywords;

    // Audit fields
    public $user_id;
    public $created_by;
    public $updated_by;
    public $deleted_by;
    public $published_by;
    public $published_at;
    public $view_count = 0;
    public $is_featured = false;
    public $ip_address;
    public $user_agent;

    // UI states
    public $showForm = false;
    public $formType = 'create'; // create/edit
    public $viewType = 'active'; // active/trashed
    public $search = '';

    // Pagination and Sorting
    public $perPage = 10;
    public $sortField = 'name_bn';
    public $sortDirection = 'asc';

    public function getFoodsProperty()
    {
        $query = $this->viewType === 'trashed' ? Food::onlyTrashed() : Food::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name_bn', 'like', "%{$this->search}%")->orWhere('name_en', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
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
        $item = $this->viewType === 'trashed' ? Food::withTrashed()->find($id) : Food::find($id);

        $this->foodId = $item->id;
        $this->food_category_id = $item->food_category_id;
        $this->name_bn = $item->name_bn;
        $this->name_en = $item->name_en;
        $this->description = $item->description; // NEW
        $this->calorie = $item->calorie;
        $this->carb = $item->carb;
        $this->protein = $item->protein;
        $this->fat = $item->fat;
        $this->fiber = $item->fiber;
        $this->serving_size = $item->serving_size; // NEW
        $this->status = $item->status;
        $this->meta_title = $item->meta_title;
        $this->meta_description = $item->meta_description;
        $this->meta_keywords = $item->meta_keywords;
        $this->imagePreview = $item->image ? Storage::url($item->image) : null;

        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset(['foodId', 'food_category_id', 'name_bn', 'name_en', 'description', 'calorie', 'carb', 'protein', 'fat', 'fiber', 'serving_size', 'slug', 'status', 'image', 'imagePreview', 'meta_title', 'meta_description', 'meta_keywords']);
        $this->resetErrorBag();
    }

    public function save()
    {
        $validated = $this->validate([
            'food_category_id' => 'nullable|exists:food_categories,id',
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string', // NEW
            'calorie' => 'nullable|numeric',
            'carb' => 'nullable|numeric',
            'protein' => 'nullable|numeric',
            'fat' => 'nullable|numeric',
            'fiber' => 'nullable|numeric',
            'serving_size' => 'nullable|string|max:50', // NEW
            'status' => 'nullable|integer',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($this->image) {
            $imagePath = $this->image->store('foods', 'public');
            $validated['image'] = $imagePath;

            if ($this->formType === 'edit' && $this->imagePreview) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $this->imagePreview));
            }
        } elseif ($this->formType === 'edit') {
            // Keep existing image
            $validated['image'] = $this->imagePreview ? str_replace('/storage/', '', $this->imagePreview) : null;
        }

        $validated['slug'] = \Str::slug($this->name_bn);

        if ($this->formType === 'edit') {
            $item = Food::withTrashed()->find($this->foodId);
            $item->update($validated);
            $message = 'Food updated successfully!';
        } else {
            Food::create($validated);
            $message = 'Food created successfully!';
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('message', $message);
        Flux::toast($message);
    }

    public function deleteItem($id)
    {
        $item = Food::find($id);
        $item->delete();
        session()->flash('message', 'Item moved to trash!');
        $this->resetPage();
    }

    public function restoreItem($id)
    {
        $item = Food::onlyTrashed()->find($id);
        $item->restore();
        session()->flash('message', 'Item restored successfully!');
        $this->resetPage();
    }

    public function forceDeleteItem($id)
    {
        $item = Food::onlyTrashed()->find($id);

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->forceDelete();
        session()->flash('message', 'Item permanently deleted!');
        $this->resetPage();
    }

    public function categories()
    {
        return FoodCategory::all();
    }
};
?>
<section class="p-4">
    <div class="flex flex-col space-y-6">

        <!-- Form -->
        @if ($showForm)
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">{{ $formType === 'create' ? 'Create New Food' : 'Edit Food' }}</h3>
                    <flux:button wire:click="$set('showForm', false)" size="sm">Back</flux:button>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model.live="name_bn" label="Food Name (BN)" required />
                        <flux:input wire:model.live="name_en" label="Food Name (EN)" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model.live="description" label="Description" /> <!-- NEW -->
                        <flux:input wire:model.live="serving_size" label="Serving Size" /> <!-- NEW -->
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:input wire:model.live="calorie" label="Calorie" type="number" />
                        <flux:input wire:model.live="carb" label="Carb (g)" type="number" />
                        <flux:input wire:model.live="protein" label="Protein (g)" type="number" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:input wire:model.live="fat" label="Fat (g)" type="number" />
                        <flux:input wire:model.live="fiber" label="Fiber (g)" type="number" />
                        <flux:select wire:model="food_category_id" class="w-full" label="Category">
                            <option value="">Select Category</option>
                            @foreach ($this->categories() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name_bn }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    <flux:input type="file" wire:model.live="image" label="Image" accept="image/*" />
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" class="h-20 mt-2 rounded">
                    @elseif($imagePreview)
                        <img src="{{ $imagePreview }}" class="h-20 mt-2 rounded">
                    @endif

                    <div class="flex justify-end space-x-3">
                        <flux:button type="button" wire:click="$set('showForm', false)">Cancel</flux:button>
                        <flux:button type="submit">{{ $formType === 'create' ? 'Create' : 'Update' }}</flux:button>
                    </div>
                </form>
            </div>
        @else
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">Food Management</h2>
                <div class="flex space-x-2">
                    <flux:button wire:click="showCreateForm">Create New</flux:button>
                    <flux:button wire:click="toggleView('active')"
                        variant="{{ $viewType === 'active' ? 'primary' : 'filled' }}">Active</flux:button>
                    <flux:button wire:click="toggleView('trashed')"
                        variant="{{ $viewType === 'trashed' ? 'primary' : 'filled' }}">Trashed</flux:button>
                </div>
            </div>

            <!-- Search -->
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by name..." />

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer"
                                wire:click="sortBy('name_bn')">
                                Name (BN)
                                @if ($sortField === 'name_bn')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Calories</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Serving Size</th> <!-- NEW -->
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Description</th> <!-- NEW -->
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($this->foods as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    @if ($item->image)
                                        <img src="{{ Storage::url($item->image) }}" class="h-10 w-10 rounded">
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">{{ $item->name_bn }}</td>
                                <td class="px-6 py-4 text-sm">{{ $item->calorie }} kcal</td>
                                <td class="px-6 py-4 text-sm">{{ $item->category?->name_bn }}</td>
                                <td class="px-6 py-4 text-sm">{{ $item->serving_size }}</td> <!-- NEW -->
                                <td class="px-6 py-4 text-sm">{{ $item->description }}</td> <!-- NEW -->
                                <td class="px-6 py-4 text-right text-sm">
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
                                <td colspan="7" class="px-6 py-4 text-center text-sm">No {{ $viewType }} items
                                    found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if ($this->foods->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $this->foods->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
