<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\SignCategory;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

new class extends Component {
    use WithPagination, WithFileUploads;

    // Properties
    public $categoryId;
    public $name;
    public $title;
    public $short_title;
    public $short_description;
    public $long_description;
    public $description;
    public $image;
    public $slug;
    public $icon;
    public $status = 1; // Default to active
    public $is_featured = false;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $showTrashed = false;
    public $activeTab = 'index';
    public $imagePreview;
    public $currentImage;

    // Validation rules
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255|lowercase',
            'title' => 'nullable|string|max:255',
            'short_title' => 'nullable|string|max:255',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'slug' => 'nullable|string|max:255|unique:sign_categories,slug,' . $this->categoryId,
            'status' => 'required|boolean',
            'is_featured' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
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
        $this->reset(['categoryId', 'name', 'icon', 'title', 'short_title', 'short_description', 'long_description', 'description', 'image', 'slug', 'status', 'is_featured', 'meta_title', 'meta_description', 'meta_keywords', 'imagePreview', 'currentImage']);
        $this->resetErrorBag();
    }

    // Load category for editing
    public function editCategory($id)
    {
        $cat = SignCategory::withTrashed()->findOrFail($id);

        $this->categoryId = $cat->id;
        $this->name = $cat->name;
        $this->icon = $cat->icon;
        $this->title = $cat->title;
        $this->short_title = $cat->short_title;
        $this->short_description = $cat->short_description;
        $this->long_description = $cat->long_description;
        $this->description = $cat->description;
        $this->slug = $cat->slug;
        $this->status = $cat->status;
        $this->is_featured = $cat->is_featured;
        $this->meta_title = $cat->meta_title;
        $this->meta_description = $cat->meta_description;
        $this->meta_keywords = $cat->meta_keywords;
        $this->currentImage = $cat->image;

        $this->activeTab = 'edit';
    }

    // Create or update category
    public function saveCategory()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'icon' => $this->icon,
            'title' => $this->title,
            'short_title' => $this->short_title,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'description' => $this->description,
            'slug' => $this->slug ?: Str::slug($this->name),
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        // Handle image upload
        if ($this->image) {
            if ($this->currentImage) {
                Storage::disk('public')->delete('sign_categories/' . basename($this->currentImage));
            }

            $baseName = Str::slug($this->name);
            $imageName = $baseName . '-' . time() . '.webp';
            $savePath = storage_path('app/public/sign_categories/' . $imageName);

            // Intervention with GD
            $manager = new ImageManager(\Intervention\Image\Drivers\Gd\Driver::class);
            $manager
                ->read($this->image->getRealPath())
                ->toWebp(80)
                ->save($savePath);

            $data['image'] = 'sign_categories/' . $imageName;
        }

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
        if ($cat->image) {
            Storage::disk('public')->delete($cat->image);
        }
        $cat->forceDelete();
        session()->flash('success', 'Category permanently deleted.');
    }

    // Remove image
    public function removeImage()
    {
        if ($this->categoryId && $this->currentImage) {
            Storage::disk('public')->delete($this->currentImage);
            SignCategory::find($this->categoryId)->update(['image' => null]);
            $this->currentImage = null;
        }
        $this->image = null;
        $this->imagePreview = null;
    }

    // Updated image preview
    public function updatedImage()
    {
        $this->validate(['image' => 'nullable|image|max:2048']);
        $this->imagePreview = $this->image->temporaryUrl();
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
                        <flux:input type="text" wire:model="title" label="Title" />
                    </div>
                    <div class="lg:col-span-1">
                        <flux:input type="text" wire:model="short_title" label="Short Title" />
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

                    <div class="md:col-span-2 lg:col-span-3">
                        <flux:textarea wire:model="short_description" label="Short Description" rows="3" />
                    </div>

                    <div class="md:col-span-2 lg:col-span-3">
                        <flux:textarea wire:model="description" label="Description" rows="5" />
                    </div>

                    <div class="md:col-span-2 lg:col-span-3">
                        <flux:textarea wire:model="long_description" label="Long Description" rows="5" />
                    </div>

                    <div class="md:col-span-2 lg:col-span-3">
                        <flux:input type="file" wire:model="image" accept="image/*" label="Image" />
                        @error('image')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                        <div class="mt-2">
                            @if ($imagePreview)
                                <img src="{{ $imagePreview }}" alt="Image Preview" class="h-20 w-auto rounded">
                            @elseif ($currentImage)
                                <img src="{{ asset('storage/' . $currentImage) }}" alt="Current Image"
                                    class="h-20 w-auto rounded">
                                <button type="button" wire:click="removeImage"
                                    class="mt-2 text-red-600 text-sm hover:text-red-800">
                                    Remove Image
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="md:col-span-2 lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <flux:input type="text" wire:model="meta_title" label="Meta Title" />
                        </div>
                        <div class="md:col-span-1">
                            <flux:input type="text" wire:model="meta_keywords" label="Meta Keywords" />
                        </div>
                        <div class="md:col-span-1">
                            <flux:textarea wire:model="meta_description" label="Meta Description" rows="3" />
                        </div>
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
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <h2 class="text-2xl font-bold">Sign Category Management</h2>

                <div class="flex space-x-2">
                    <flux:button wire:click="showTab('create')" size="sm">
                        Create New
                    </flux:button>
                    <flux:button wire:click="$set('showTrashed', false)" size="sm"
                        variant="{{ !$showTrashed ? 'primary' : 'outline' }}">
                        Active
                    </flux:button>
                    <flux:button wire:click="$set('showTrashed', true)" size="sm"
                        variant="{{ $showTrashed ? 'primary' : 'outline' }}">
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
                                    <div class="flex items-center">
                                        @if ($cat->image)
                                            <img src="{{ asset('storage/' . $cat->image) }}"
                                                alt="{{ $cat->name }}" class="h-10 w-10 rounded-full mr-3">
                                        @else
                                            <div
                                                class="h-10 w-10 rounded-full bg-gray-500 flex items-center justify-center mr-3">
                                                <span class=" text-sm">{{ substr($cat->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div class="text-sm font-medium">{{ $cat->name }}</div>
                                    </div>
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
