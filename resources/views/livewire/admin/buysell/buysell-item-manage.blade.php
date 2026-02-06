<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\BuySellItem;
use App\Models\BuySellCategory;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination, WithFileUploads;

    // Properties (Updated to match the new Model structure)
    public $itemId, $title, $slug, $description, $buy_sell_category_id;
    public $status = 'draft';
    public $is_featured = false;
    public $thumbnail; // Renamed from $images
    public $currentThumbnail; // For displaying existing image
    public $note, $meta_title, $meta_description, $meta_keywords;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showTrashed = false;
    public $activeTab = 'index';

    public $categories, $users;

    // Validation rules (Updated for new fields and single thumbnail)
    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:buy_sell_items,slug,' . $this->itemId,
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'buy_sell_category_id' => 'required|exists:buy_sell_categories,id',
            'status' => 'nullable|in:draft,published',
            'is_featured' => 'boolean',
            'thumbnail' => $this->itemId ? 'nullable|image|max:2048' : 'nullable|image|max:2048', // Allow nullable on edit if current image exists
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ];
    }

    // Mount
    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->categories = BuySellCategory::where('status', 'published')->get();
        // Assuming User model has a similar status field (from original code)
        $this->users = User::where('status', 1)->get();
    }

    // Reset fields (Updated)
    public function resetFields()
    {
        $this->reset(['itemId', 'title', 'slug', 'description', 'buy_sell_category_id', 'status', 'is_featured', 'thumbnail', 'currentThumbnail', 'note', 'meta_title', 'meta_description', 'meta_keywords']);
        $this->resetErrorBag();
        $this->status = 'draft';
        $this->is_featured = false;
    }

    // Generate slug
    public function generateSlug()
    {
        if (!$this->slug && $this->title) {
            $this->slug = Str::slug($this->title);
        }
    }

    // Edit (Updated for new fields)
    public function editItem($id)
    {
        $item = BuySellItem::withTrashed()->findOrFail($id);

        $this->itemId = $item->id;
        $this->title = $item->title;
        $this->slug = $item->slug;
        $this->description = $item->description;
        $this->note = $item->note;
        $this->buy_sell_category_id = $item->buy_sell_category_id;
        $this->status = $item->status;
        $this->is_featured = $item->is_featured;
        $this->currentThumbnail = $item->thumbnail;
        $this->meta_title = $item->meta_title;
        $this->meta_description = $item->meta_description;
        $this->meta_keywords = $item->meta_keywords;

        $this->activeTab = 'edit';
    }

    // Save (Create/Update) (Updated for new fields and single thumbnail)
    public function saveItem()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'slug' => $this->slug ?: Str::slug($this->title),
            'description' => $this->description,
            'note' => $this->note,
            'buy_sell_category_id' => $this->buy_sell_category_id,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'updated_by' => auth()->id(),
        ];

        // Handle Thumbnail Upload
        if ($this->thumbnail) {
            // Delete old thumbnail if exists
            if ($this->itemId) {
                $item = BuySellItem::find($this->itemId);
                if ($item && $item->thumbnail) {
                    Storage::disk('public')->delete($item->thumbnail);
                }
            }

            // Create image manager
            $manager = new ImageManager(new Driver());

            // Read and process the uploaded image
            $image = $manager->read($this->thumbnail->getRealPath());

            // Resize the image (example: 800x600 with cropping)
            $image->cover(800, 600);

            // Store the processed image
            $path = 'buy_sell_items/thumbnails/' . Str::slug($this->title) . '_' . now()->format('Ymd_His') . '.' . $this->thumbnail->getClientOriginalExtension();
            // Ensure directory exists
            $fullDir = storage_path('app/public/buy_sell_items/thumbnails');
            if (!file_exists($fullDir)) {
                mkdir($fullDir, 0755, true); // recursive mkdir
            }

            // Save the processed image
            $image->toWebp(80)->save($fullDir . '/' . Str::slug($this->title) . '_' . now()->format('Ymd_His') . '.' . $this->thumbnail->getClientOriginalExtension());

            // Set path for database
            $data['thumbnail'] = env('APP_URL') . '/storage/buy_sell_items/thumbnails/' . Str::slug($this->title) . '_' . now()->format('Ymd_His') . '.' . $this->thumbnail->getClientOriginalExtension();
        } elseif ($this->currentThumbnail) {
            // Keep existing thumbnail if no new upload
            $data['thumbnail'] = $this->currentThumbnail;
        }

        // Save or Update
        if ($this->itemId) {
            $item = BuySellItem::find($this->itemId);

            // Handle publishing logic only if status is changing to 'published'
            if ($item->status !== 'published' && $this->status === 'published') {
                $data['published_at'] = now();
                $data['published_by'] = auth()->id();
            }

            $item->fill($data)->save();
            session()->flash('success', 'Item updated successfully.');
        } else {
            $item = new BuySellItem();
            $item->fill($data);
            $item->created_by = auth()->id();

            // Set published info on creation if status is 'published'
            if ($this->status === 'published') {
                $item->published_at = now();
                $item->published_by = auth()->id();
            }

            $item->save();
            session()->flash('success', 'Item created successfully.');
        }

        $this->resetFields();
        $this->activeTab = 'index';
    }

    // Delete (Soft Delete)
    public function deleteItem($id)
    {
        $item = BuySellItem::findOrFail($id);
        $item->deleted_by = auth()->id();
        $item->save();
        $item->delete();
        session()->flash('success', 'Item moved to trash.');
    }

    // Restore
    public function restoreItem($id)
    {
        $item = BuySellItem::withTrashed()->findOrFail($id);
        $item->restore();
        session()->flash('success', 'Item restored successfully.');
    }

    // Force Delete (Updated for single thumbnail)
    public function forceDeleteItem($id)
    {
        $item = BuySellItem::withTrashed()->findOrFail($id);
        if ($item->thumbnail) {
            Storage::disk('public')->delete($item->thumbnail);
        }
        $item->forceDelete();
        session()->flash('success', 'Item permanently deleted.');
    }

    // Remove current thumbnail (when editing)
    public function removeCurrentThumbnail()
    {
        if ($this->currentThumbnail) {
            Storage::disk('public')->delete($this->currentThumbnail);
            $this->currentThumbnail = null;

            // Update the database to remove the thumbnail reference if it's an existing item
            if ($this->itemId) {
                BuySellItem::find($this->itemId)->update(['thumbnail' => null]);
            }
        }
    }

    // Get items (using model scopes)
    public function getItemsProperty()
    {
        $query = BuySellItem::with(['category', 'user']);

        if ($this->showTrashed) {
            $query->onlyTrashed();
        }

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%')->orWhere('slug', 'like', '%' . $this->search . '%');
        }

        // Use the scope to filter active/inactive if needed, but the soft delete handles 'trashed'
        // $query->when(!$this->showTrashed, fn ($q) => $q->active());

        return $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
    }

    // Sorting
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // Switch tabs
    public function showTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'create') {
            $this->resetFields();
        }
    }
}; ?>

<section class="">
    <div class="flex flex-col space-y-6">

        @if ($activeTab === 'create' || $activeTab === 'edit')
            <div class="rounded-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">{{ $activeTab === 'create' ? 'Create New Item' : 'Edit Item' }}</h3>
                    <flux:button wire:click="showTab('index')" size="sm">← Back</flux:button>
                </div>

                <form wire:submit="saveItem" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    <div class="lg:col-span-1">
                        <flux:input type="text" wire:model.live="title" label="Title" wire:blur="generateSlug" />
                    </div>
                    <div class="lg:col-span-1">
                        <flux:input type="text" wire:model="slug" label="Slug" />
                    </div>

                    <div class="lg:col-span-1">
                        <flux:select wire:model="buy_sell_category_id" label="Category">
                            <option value="">Select</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="lg:col-span-3">
                        <flux:textarea wire:model="description" label="Description" rows="3" />
                    </div>
                    {{-- <div class="lg:col-span-1">
                        <flux:textarea wire:model="note" label="Note (Internal)" rows="4" />
                    </div> --}}

                    <div class="md:col-span-1 lg:col-span-1">
                        <flux:input type="file" wire:model="thumbnail" label="Thumbnail Image (Max 2MB)" />
                        @error('thumbnail')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror

                        @if ($thumbnail)
                            <div class="mt-2 text-sm text-gray-500">New thumbnail preview:</div>
                            <img src="{{ $thumbnail->temporaryUrl() }}" class="h-20 w-20 object-cover rounded mt-1" />
                        @elseif ($currentThumbnail)
                            <div class="flex flex-wrap gap-2 mt-2">
                                <div class="relative">
                                    <img src="{{ $currentThumbnail }}" class="h-20 w-20 object-cover rounded" />
                                    <flux:button size="xs" variant="primary" color="red"
                                        wire:click="removeCurrentThumbnail">×</flux:button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- <div class="lg:col-span-3 mt-4 hidden">
                        <h4 class="font-semibold mb-2">SEO / Meta Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <flux:input type="text" wire:model="meta_title" label="Meta Title" />
                            <div class="md:col-span-2">
                                <flux:input type="text" wire:model="meta_keywords"
                                    label="Meta Keywords (comma-separated)" />
                            </div>
                            <div class="md:col-span-3">
                                <flux:textarea wire:model="meta_description" label="Meta Description" rows="2" />
                            </div>
                        </div>
                    </div> --}}

                    <div class="lg:col-span-1 mt-4">
                        <flux:select wire:model="status" label="Status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </flux:select>
                    </div>
                    <div class="flex items-center lg:col-span-1 mt-4">
                        <flux:checkbox wire:model="is_featured" label="Featured Item" />
                    </div>

                    <div class="md:col-span-1 lg:col-span-3 flex justify-end space-x-3 mt-4">
                        <flux:button type="button" wire:click="resetFields" variant="outline">Reset</flux:button>
                        <flux:button type="submit" variant="primary">{{ $itemId ? 'Update Item' : 'Create Item' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @else
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <h2 class="text-2xl font-bold">Buy & Sell Items</h2>
                <div class="flex space-x-2">
                    <flux:button wire:click="showTab('create')" size="sm">Create New</flux:button>
                    <flux:button wire:click="$set('showTrashed', false)" size="sm"
                        variant="{{ !$showTrashed ? 'primary' : 'outline' }}">Active</flux:button>
                    <flux:button wire:click="$set('showTrashed', true)" size="sm"
                        variant="{{ $showTrashed ? 'primary' : 'outline' }}">Trashed</flux:button>
                </div>
            </div>

            <div>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by title or slug..."
                    class="w-full" />
            </div>

            {{-- @if (session()->has('success'))
                <div class="bg-green-100 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif --}}
            @include('partials.toast')


            <div class="overflow-x-auto mt-2">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('title')">Title
                                @if ($sortField === 'title')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </th>
                            <th class="px-4 py-2">Category</th>

                            <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('status')">Status
                                @if ($sortField === 'status')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </th>
                            <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('is_featured')">Featured
                                @if ($sortField === 'is_featured')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->items as $item)
                            <tr class="hover:bg-zinc-400/10">
                                <td class="px-4 py-2 flex items-center space-x-2">
                                    @if ($item->thumbnail)
                                        <img src="{{ $item->thumbnail }}" alt="{{ $item->title }}"
                                            class="h-8 w-8 object-cover rounded">
                                    @endif
                                    <span>{{ $item->title }}</span>
                                </td>
                                <td class="px-4 py-2">{{ $item->category->name ?? 'N/A' }}</td>

                                <td class="px-4 py-2">{{ ucfirst($item->status) }}</td>
                                <td class="px-4 py-2">{{ $item->is_featured ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-2 flex space-x-2">
                                    @if ($item->deleted_at)
                                        <button wire:click="restoreItem({{ $item->id }})"
                                            class="text-green-600">Restore</button>
                                        <button wire:click="forceDeleteItem({{ $item->id }})"
                                            class="text-red-600">Delete</button>
                                    @else
                                        <button wire:click="editItem({{ $item->id }})"
                                            class="text-blue-600">Edit</button>
                                        <button wire:click="deleteItem({{ $item->id }})"
                                            class="text-red-600">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($this->items->hasPages())
                    <div class="mt-2">{{ $this->items->links() }}</div>
                @endif
            </div>
        @endif

    </div>
</section>
