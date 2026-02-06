<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\BuySellCategory;
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
    public $note;
    public $image;
    public $icon;
    public $slug;
    public $status = 'draft';
    public $is_active = true;
    public $is_featured = false;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $published_at;

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
            'title' => 'nullable|string|max:255',
            'short_title' => 'nullable|string|max:255',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'icon' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:buy_sell_categories,slug,' . $this->categoryId,
            'status' => 'required|in:draft,published,archived',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ];
    }

    // Mount
    public function mount()
    {
        $this->published_at = now()->format('Y-m-d\TH:i');
    }

    // Switch tabs
    public function showTab($tabName)
    {
        $this->activeTab = $tabName;
        if ($tabName === 'create') {
            $this->resetFields();
        }
    }

    // Reset form fields
    public function resetFields()
    {
        $this->reset(['categoryId', 'name', 'title', 'short_title', 'short_description', 'long_description', 'description', 'note', 'image', 'slug', 'status', 'is_active', 'is_featured', 'meta_title', 'meta_description', 'meta_keywords', 'published_at', 'imagePreview', 'currentImage']);
        $this->resetErrorBag();
        $this->status = 'draft';
        $this->is_active = true;
        $this->is_featured = false;
        $this->published_at = now()->format('Y-m-d\TH:i');
    }

    // Generate slug
    public function generateSlug()
    {
        if (!$this->slug && $this->name) {
            $this->slug = Str::slug($this->name);
        }
    }

    // Load category for editing
    public function editCategory($id)
    {
        $cat = BuySellCategory::withTrashed()->findOrFail($id);

        $this->categoryId = $cat->id;
        $this->name = $cat->name;
        $this->title = $cat->title;
        $this->icon = $cat->icon;
        $this->short_title = $cat->short_title;
        $this->short_description = $cat->short_description;
        $this->long_description = $cat->long_description;
        $this->description = $cat->description;
        $this->note = $cat->note;
        $this->slug = $cat->slug;
        $this->status = $cat->status;
        $this->is_active = $cat->is_active;
        $this->is_featured = $cat->is_featured;
        $this->meta_title = $cat->meta_title;
        $this->meta_description = $cat->meta_description;
        $this->meta_keywords = $cat->meta_keywords;
        $this->published_at = $cat->published_at ? $cat->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
        $this->currentImage = $cat->image;

        $this->activeTab = 'edit';
    }

    // Create or update category
    public function saveCategory()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'title' => $this->title,
            'short_title' => $this->short_title,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'description' => $this->description,
            'note' => $this->note,
            'icon' => $this->icon,
            'slug' => $this->slug ?: Str::slug($this->name),
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'published_at' => $this->status === 'published' ? ($this->published_at ?: now()) : null,
            'published_by' => $this->status === 'published' ? auth()->id() : null,
            'updated_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Handle image upload
        if ($this->image) {
            if ($this->currentImage) {
                Storage::disk('public')->delete('buy_sell_categories/' . basename($this->currentImage));
            }

            $baseName = Str::slug($this->name);
            $imageName = $baseName . '-' . time() . '.webp';
            $savePath = storage_path('app/public/buy_sell_categories/' . $imageName);

            // Intervention with GD
            $manager = new ImageManager(\Intervention\Image\Drivers\Gd\Driver::class);
            $manager
                ->read($this->image->getRealPath())
                ->toWebp(80)
                ->save($savePath);

            $data['image'] = 'buy_sell_categories/' . $imageName;
        }

        if ($this->categoryId) {
            // Update existing category
            $category = BuySellCategory::find($this->categoryId);
            $category->update($data);
            session()->flash('success', 'Category updated successfully.');
        } else {
            // Create new category
            $data['created_by'] = auth()->id();
            BuySellCategory::create($data);
            session()->flash('success', 'Category created successfully.');
        }

        $this->resetFields();
        $this->activeTab = 'index';
    }

    // Delete category
    public function deleteCategory($id)
    {
        $cat = BuySellCategory::findOrFail($id);

        // Check if category has associated items
        if ($cat->items()->count() > 0) {
            session()->flash('error', 'Cannot delete category with associated items.');
            return;
        }

        $cat->update(['deleted_by' => auth()->id()]);
        $cat->delete();
        session()->flash('success', 'Category moved to trash.');
    }

    // Restore category
    public function restoreCategory($id)
    {
        $cat = BuySellCategory::withTrashed()->findOrFail($id);
        $cat->restore();
        session()->flash('success', 'Category restored successfully.');
    }

    // Force delete category
    public function forceDeleteCategory($id)
    {
        $cat = BuySellCategory::withTrashed()->findOrFail($id);
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
            BuySellCategory::find($this->categoryId)->update(['image' => null]);
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
        $query = BuySellCategory::query();

        if ($this->showTrashed) {
            $query->onlyTrashed();
        }

        return $query
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%')
                        ->orWhere('short_title', 'like', '%' . $this->search . '%');
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
                        {{ $activeTab === 'create' ? 'Create New Buy & Sell Category' : 'Edit Buy & Sell Category' }}
                    </h3>
                    <flux:button wire:click="showTab('index')" size="sm">
                        ← Back to List
                    </flux:button>
                </div>

                <form wire:submit="saveCategory" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <!-- Basic Information -->
                    <div class="lg:col-span-1">
                        <flux:input wire:model="name" label="Category Name" required placeholder="e.g. Electronics"
                            wire:blur="generateSlug" />
                    </div>
                    <div class="lg:col-span-1">
                        <flux:input wire:model="slug" label="Slug" placeholder="auto-generated-or-custom" />
                    </div>
                    <div class="lg:col-span-1">
                        <flux:input wire:model="icon" label="Icon" />
                    </div>
                    <div class="lg:col-span-1">
                        <flux:input wire:model="title" label="Title" />
                    </div>
                    <div class="lg:col-span-1">
                        <flux:input wire:model="short_title" label="Short Title" />
                    </div>

                    <!-- Status & Settings -->
                    <div class="lg:col-span-1">
                        <flux:select wire:model="status" label="Status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </flux:select>
                    </div>
                    <div class="lg:col-span-1">
                        <flux:input type="datetime-local" wire:model="published_at" label="Publish Date" />
                    </div>

                    <!-- Checkboxes -->
                    <div class="flex items-center lg:col-span-1">
                        <flux:checkbox wire:model="is_active" label="Active" />
                    </div>
                    <div class="flex items-center lg:col-span-1">
                        <flux:checkbox wire:model="is_featured" label="Featured" />
                    </div>

                    <!-- Descriptions -->
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
                        <flux:textarea wire:model="note" label="Internal Note" rows="3"
                            placeholder="Internal remarks and notes" />
                    </div>

                    <!-- Image Upload -->
                    <div class="md:col-span-2 lg:col-span-3">
                        <flux:input type="file" wire:model="image" accept="image/*" label="Category Image" />
                        @error('image')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                        <div class="mt-2">
                            @if ($imagePreview)
                                <img src="{{ $imagePreview }}" alt="Image Preview" class="h-20 w-auto rounded shadow">
                            @elseif ($currentImage)
                                <img src="{{ asset('storage/' . $currentImage) }}" alt="Current Image"
                                    class="h-20 w-auto rounded shadow">
                                <button type="button" wire:click="removeImage"
                                    class="mt-2 text-red-600 text-sm hover:text-red-800">
                                    Remove Image
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- SEO Section -->
                    <div class="md:col-span-2 lg:col-span-3 border-t pt-4">
                        <h4 class="text-lg font-semibold mb-4">SEO Settings</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <flux:input wire:model="meta_title" label="Meta Title" />
                            </div>
                            <div class="md:col-span-1">
                                <flux:input wire:model="meta_keywords" label="Meta Keywords" />
                            </div>
                            <div class="md:col-span-1">
                                <flux:textarea wire:model="meta_description" label="Meta Description" rows="3" />
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="md:col-span-2 lg:col-span-3 flex justify-end space-x-3 mt-4">
                        <flux:button type="button" wire:click="resetFields" variant="outline">
                            Reset
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            {{ $categoryId ? 'Update Category' : 'Create Category' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @else
            <!-- Index View -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <h2 class="text-2xl font-bold flex items-center gap-2">
                    Buy & Sell Category Management
                </h2>

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

            <!-- Search -->
            <div>
                <flux:input wire:model.live.debounce.300ms="search"
                    placeholder="Search by name, slug, or short title..." class="w-full" />
            </div>

            <!-- Success/Error Messages -->
            {{-- @if (session()->has('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif --}}
            @include('partials.toast')

            <!-- Categories Table -->
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
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('slug')">
                                Slug
                                @if ($sortField === 'slug')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('status')">
                                Status
                                @if ($sortField === 'status')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('is_featured')">
                                Featured
                                @if ($sortField === 'is_featured')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('published_at')">
                                Published
                                @if ($sortField === 'published_at')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($this->categories as $cat)
                            <tr class="hover:bg-zinc-400/10">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if ($cat->image)
                                            <img src="{{ asset('storage/' . $cat->image) }}"
                                                alt="{{ $cat->name }}"
                                                class="h-10 w-10 rounded-full mr-3 object-cover">
                                        @else
                                            <div
                                                class="h-10 w-10 rounded-full bg-gray-500 flex items-center justify-center mr-3">
                                                <span
                                                    class="text-white text-sm font-semibold">{{ substr($cat->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium">{{ $cat->name }}</div>
                                            @if ($cat->short_title)
                                                <div class="text-xs text-gray-500">{{ $cat->short_title }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $cat->slug }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($cat->deleted_at)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Trashed
                                        </span>
                                    @else
                                        @php
                                            $statusClasses = [
                                                'published' => 'bg-green-100 text-green-800',
                                                'draft' => 'bg-yellow-100 text-yellow-800',
                                                'archived' => 'bg-gray-100 text-gray-800',
                                            ];
                                            $statusClass = $statusClasses[$cat->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ ucfirst($cat->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($cat->is_featured)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                            Yes
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            No
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $cat->published_at ? $cat->published_at->format('M j, Y') : '—' }}
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
                                            Delete Forever
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
                                <td colspan="6" class="px-6 py-4 text-center text-sm">
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
