<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\SignCategory;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination, WithFileUploads;

    // Properties
    public $categoryId;
    public $name;
    public $description;
    public $slug;
    public $icon;
    public $status = 1;
    public $is_featured = false;

    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $search = '';
    public $perPage = 10;
    public $showTrashed = false;
    public $activeTab = 'index';

    // Validation rules
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255|lowercase',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'is_featured' => 'boolean',
        ];
    }

    // Switch tabs
    public function showTab($tabName)
    {
        $this->activeTab = $tabName;
        $this->resetFields();
    }

    // Reset form fields
    public function resetFields()
    {
        $this->reset(['categoryId', 'name', 'icon', 'description', 'slug', 'status', 'is_featured']);
        $this->resetErrorBag();
    }

    // Load category for editing
    public function editCategory($id)
    {
        $cat = SignCategory::withTrashed()->findOrFail($id);

        $this->categoryId = $cat->id;
        $this->name = $cat->name;
        $this->icon = $cat->icon;
        $this->description = $cat->description;
        $this->slug = $cat->slug;
        $this->status = $cat->status;
        $this->is_featured = $cat->is_featured;

        $this->activeTab = 'edit';
    }

    // Create or update category
    public function saveCategory()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'icon' => $this->icon,
            'description' => $this->description,
            'slug' => $this->slug,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
        ];

        if ($this->categoryId) {
            // Update existing category
            $category = SignCategory::find($this->categoryId);
            $category->update($data);
            session()->flash('success', 'Category updated successfully.');
        } else {
            // Create new category
            SignCategory::create($data);
            session()->flash('success', 'Category created successfully.');
        }

        $this->resetFields();
        $this->activeTab = 'index';
    }

    // Delete category
    public function deleteCategory($id)
    {
        $cat = SignCategory::findOrFail($id);
        $cat->delete();
        session()->flash('success', 'Category moved to trash.');
    }

    // Restore category
    public function restoreCategory($id)
    {
        $cat = SignCategory::withTrashed()->findOrFail($id);
        $cat->restore();
        session()->flash('success', 'Category restored successfully.');
    }

    // Force delete category
    public function forceDeleteCategory($id)
    {
        $cat = SignCategory::withTrashed()->findOrFail($id);
        $cat->forceDelete();
        session()->flash('success', 'Category permanently deleted.');
    }

    // Sort function
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // Get categories for display
    public function getCategoriesProperty()
    {
        $query = SignCategory::query();

        if ($this->showTrashed) {
            $query->onlyTrashed();
        }

        return $query
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
}; ?>

<section class="p-6">
    <div class="flex flex-col space-y-6">

        @if ($activeTab === 'create' || $activeTab === 'edit')
            <div class="p-6 rounded-lg shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">
                        {{ $activeTab === 'create' ? 'Create New Category' : 'Edit Category' }}
                    </h3>
                    <flux:button wire:click="showTab('index')" size="sm">
                        Back to List
                    </flux:button>
                </div>

                <form wire:submit="saveCategory" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <div class="lg:col-span-1">
                        <flux:input type="text" wire:model="name" label="Name" />
                    </div>
                    <div class="lg:col-span-1">
                        <flux:input type="text" wire:model="icon" label="Icon" />
                    </div>
                    <div class="lg:col-span-1">
                        <flux:input type="text" wire:model="slug" label="Slug" />
                    </div>
                    <div class="lg:col-span-1">
                        <flux:select wire:model="status" label="Status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </flux:select>
                    </div>
                    <div class="flex items-center mt-6 lg:col-span-1">
                        <flux:checkbox wire:model="is_featured" label="Featured" />
                    </div>

                    <div class="md:col-span-2 lg:col-span-3 flex justify-end space-x-3 mt-4">
                        <flux:button type="button" wire:click="resetFields">
                            Reset
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            {{ $categoryId ? 'Update Category' : 'Create Category' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @else
            <div class="flex justify-between items-start items-center space-y-4 sm:space-y-0">
                <h2 class="text-2xl font-bold">Sign Category Management</h2>

                <div class="flex space-x-2">
                    <flux:button wire:click="showTab('create')" size="sm">
                        Create New
                    </flux:button>
                    <flux:button wire:click="$set('showTrashed', false)" size="sm"
                        variant="{{ !$showTrashed ? 'primary' : 'filled' }}">
                        Active
                    </flux:button>
                    <flux:button wire:click="$set('showTrashed', true)" size="sm"
                        variant="{{ $showTrashed ? 'primary' : 'filled' }}">
                        Trashed
                    </flux:button>
                </div>
            </div>

            <div>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by name or slug..."
                    class="w-full" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('name')">
                                Name
                                @if ($sortField === 'name')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('slug')">
                                Slug
                                @if ($sortField === 'slug')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('status')">
                                Status
                                @if ($sortField === 'status')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('is_featured')">
                                Featured
                                @if ($sortField === 'is_featured')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium  uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class=" divide-y divide-gray-200">
                        @forelse ($this->categories as $cat)
                            <tr class="hover:bg-zinc-400/10">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $cat->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm ">
                                    {{ $cat->slug }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($cat->deleted_at)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Trashed
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cat->status ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $cat->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm ">
                                    {{ $cat->is_featured ? 'Yes' : 'No' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if ($cat->deleted_at)
                                        <button wire:click="restoreCategory({{ $cat->id }})"
                                            class="text-green-600 hover:text-green-900 mr-3">
                                            Restore
                                        </button>
                                        <button wire:click="forceDeleteCategory({{ $cat->id }})"
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('Are you sure you want to permanently delete this category?')">
                                            Permanently Delete
                                        </button>
                                    @else
                                        <button wire:click="editCategory({{ $cat->id }})"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            Edit
                                        </button>
                                        <button wire:click="deleteCategory({{ $cat->id }})"
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('Are you sure you want to move this category to trash?')">
                                            Delete
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm ">
                                    No {{ $showTrashed ? 'trashed' : 'active' }} categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($this->categories->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $this->categories->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</section>
