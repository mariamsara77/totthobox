<?php

use Livewire\Volt\Component;
use App\Models\FoodCategory;
use Livewire\WithPagination;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    public $categoryId;
    public $name_bn;
    public $name_en;
    public $slug;
    public $status = 0;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;

    public $showForm = false;
    public $formType = 'create';
    public $viewType = 'active';
    public $search = '';

    public $perPage = 10;
    public $sortField = 'name_bn';
    public $sortDirection = 'asc';

    public function getCategoriesProperty()
    {
        $query = $this->viewType === 'trashed' ? FoodCategory::onlyTrashed() : FoodCategory::query();

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
        $item = $this->viewType === 'trashed' ? FoodCategory::withTrashed()->find($id) : FoodCategory::find($id);

        $this->categoryId = $item->id;
        $this->name_bn = $item->name_bn;
        $this->name_en = $item->name_en;
        $this->status = $item->status;
        $this->meta_title = $item->meta_title;
        $this->meta_description = $item->meta_description;
        $this->meta_keywords = $item->meta_keywords;

        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset(['categoryId', 'name_bn', 'name_en', 'slug', 'status', 'meta_title', 'meta_description', 'meta_keywords']);
        $this->resetErrorBag();
    }

    public function save()
    {
        $validated = $this->validate([
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'status' => 'nullable|integer',
        ]);

        $validated['slug'] = \Str::slug($this->name_bn);

        if ($this->formType === 'edit') {
            $item = FoodCategory::withTrashed()->find($this->categoryId);
            $item->update($validated);
            $message = 'Category updated successfully!';
        } else {
            FoodCategory::create($validated);
            $message = 'Category created successfully!';
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('message', $message);
        Flux::toast($message);
    }

    public function deleteItem($id)
    {
        $item = FoodCategory::find($id);
        $item->delete();
        session()->flash('message', 'Category moved to trash!');
        $this->resetPage();
    }

    public function restoreItem($id)
    {
        $item = FoodCategory::onlyTrashed()->find($id);
        $item->restore();
        session()->flash('message', 'Category restored successfully!');
        $this->resetPage();
    }

    public function forceDeleteItem($id)
    {
        $item = FoodCategory::onlyTrashed()->find($id);
        $item->forceDelete();
        session()->flash('message', 'Category permanently deleted!');
        $this->resetPage();
    }
};
?>


<section class="p-4">
    <div class="flex flex-col space-y-6">

        <!-- Form -->
        @if ($showForm)
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">
                        {{ $formType === 'create' ? 'Create New Category' : 'Edit Category' }}</h3>
                    <flux:button wire:click="$set('showForm', false)" size="sm">Back</flux:button>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <flux:input wire:model.live="name_bn" label="Category Name (BN)" required />
                    <flux:input wire:model.live="name_en" label="Category Name (EN)" />
                    <flux:input wire:model.live="meta_title" label="Meta Title" />
                    <flux:input wire:model.live="meta_description" label="Meta Description" />
                    <flux:input wire:model.live="meta_keywords" label="Meta Keywords" />

                    <div class="flex justify-end space-x-3">
                        <flux:button type="button" wire:click="$set('showForm', false)">Cancel</flux:button>
                        <flux:button type="submit">{{ $formType === 'create' ? 'Create' : 'Update' }}</flux:button>
                    </div>
                </form>
            </div>
        @else
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">Food Category Management</h2>
                <div class="flex space-x-2">
                    <flux:button wire:click="showCreateForm">Create New</flux:button>
                    <flux:button wire:click="toggleView('active')"
                        variant="{{ $viewType === 'active' ? 'primary' : 'filled' }}">Active</flux:button>
                    <flux:button wire:click="toggleView('trashed')"
                        variant="{{ $viewType === 'trashed' ? 'primary' : 'filled' }}">Trashed</flux:button>
                </div>
            </div>

            <!-- Search -->
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search category..." />

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer"
                                wire:click="sortBy('name_bn')">
                                Name (BN)
                                @if ($sortField === 'name_bn')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Name (EN)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($this->categories as $item)
                            <tr>
                                <td class="px-6 py-4">{{ $item->name_bn }}</td>
                                <td class="px-6 py-4">{{ $item->name_en }}</td>
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
                                <td colspan="3" class="px-6 py-4 text-center">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $this->categories->links() }}
            </div>
        @endif
    </div>
</section>
