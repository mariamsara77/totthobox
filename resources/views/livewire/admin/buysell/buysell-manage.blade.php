<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\BuySellPost;
use App\Models\BuySellCategory;
use App\Models\BuySellItem;

new class extends Component {
    use WithPagination, WithFileUploads;

    // Form fields
    public $postId;
    public $title;
    public $slug;
    public $description;
    public $note;
    public $buy_sell_category_id;
    public $buy_sell_item_id;
    public $condition;
    public $price;
    public $discount_price;
    public $currency = 'BDT';
    public $is_negotiable = false;
    public $sku;
    public $stock;
    public $is_available = true;
    public $division_id;
    public $district_id;
    public $thana_id;
    public $address;
    public $phone;
    public $whatsapp;
    public $email;
    public $status = 'draft';
    public $is_active = true;
    public $is_featured = false;
    public $images = [];
    public $existingImages = [];
    public $primaryImageId;

    // Table & UI
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showTrashed = false;
    public $activeTab = 'index';

    public $categories = [];
    public $items = [];

    public function mount()
    {
        $this->categories = BuySellCategory::all();
        $this->items = BuySellItem::all();
    }

    // Validation
    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:buy_sell_posts,slug,' . $this->postId,
            'buy_sell_category_id' => 'required|exists:buy_sell_categories,id',
            'buy_sell_item_id' => 'nullable|exists:buy_sell_items,id',
            'price' => 'nullable|numeric',
            'discount_price' => 'nullable|numeric',
            'images.*' => 'nullable|image|max:2048',
            'status' => 'required|string',
        ];
    }

    // Reset form
    public function resetFields()
    {
        $this->reset(['postId', 'title', 'slug', 'description', 'note', 'buy_sell_category_id', 'buy_sell_item_id', 'condition', 'price', 'discount_price', 'currency', 'is_negotiable', 'sku', 'stock', 'is_available', 'division_id', 'district_id', 'thana_id', 'address', 'phone', 'whatsapp', 'email', 'status', 'is_active', 'is_featured', 'images', 'existingImages', 'primaryImageId']);

        $this->status = 'draft';
        $this->is_active = true;
        $this->is_featured = false;
        $this->is_available = true;
        $this->is_negotiable = false;
        $this->resetErrorBag();
    }

    // Switch tab
    public function showTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetFields();
    }

    // Edit
    public function editPost($id)
    {
        $post = BuySellPost::withTrashed()->findOrFail($id);
        $this->postId = $post->id;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->description = $post->description;
        $this->note = $post->note;
        $this->buy_sell_category_id = $post->buy_sell_category_id;
        $this->buy_sell_item_id = $post->buy_sell_item_id;
        $this->condition = $post->condition;
        $this->price = $post->price;
        $this->discount_price = $post->discount_price;
        $this->currency = $post->currency;
        $this->is_negotiable = $post->is_negotiable;
        $this->sku = $post->sku;
        $this->stock = $post->stock;
        $this->is_available = $post->is_available;
        $this->division_id = $post->division_id;
        $this->district_id = $post->district_id;
        $this->thana_id = $post->thana_id;
        $this->address = $post->address;
        $this->phone = $post->phone;
        $this->whatsapp = $post->whatsapp;
        $this->email = $post->email;
        $this->status = $post->status;
        $this->is_active = $post->is_active;
        $this->is_featured = $post->is_featured;
        $this->existingImages = $post->images()->get()->toArray();
        $this->primaryImageId = optional($post->images()->where('is_primary', true)->first())->id;
        $this->activeTab = 'edit';
    }

    // Save/Create
    public function savePost()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'slug' => $this->slug ?: Str::slug($this->title . '-' . Str::random(6)),
            'description' => $this->description,
            'note' => $this->note,
            'buy_sell_category_id' => $this->buy_sell_category_id,
            'buy_sell_item_id' => $this->buy_sell_item_id,
            'condition' => $this->condition,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'currency' => $this->currency,
            'is_negotiable' => $this->is_negotiable,
            'sku' => $this->sku,
            'stock' => $this->stock,
            'is_available' => $this->is_available,
            'division_id' => $this->division_id,
            'district_id' => $this->district_id,
            'thana_id' => $this->thana_id,
            'address' => $this->address,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'user_id' => Auth::id(),
        ];

        if ($this->postId) {
            $post = BuySellPost::findOrFail($this->postId);
            $data['updated_by'] = Auth::id();
            $post->update($data);
        } else {
            $data['created_by'] = Auth::id();
            $post = BuySellPost::create($data);
        }

        // Handle images
        if ($this->images) {
            foreach ($this->images as $img) {
                $path = $img->store('buy_sell_posts', 'public');
                $post->images()->create(['path' => $path]);
            }
        }

        // Set primary image
        if ($this->primaryImageId) {
            $post->images()->update(['is_primary' => false]);
            $post
                ->images()
                ->where('id', $this->primaryImageId)
                ->update(['is_primary' => true]);
        }

        $this->resetFields();
        $this->activeTab = 'index';
        session()->flash('success', $this->postId ? 'Post updated successfully.' : 'Post created successfully.');
    }

    // Delete / Trash
    public function deletePost($id)
    {
        $post = BuySellPost::findOrFail($id);
        $post->update(['deleted_by' => Auth::id()]);
        $post->delete();
        session()->flash('success', 'Post moved to trash.');
    }

    // Restore
    public function restorePost($id)
    {
        $post = BuySellPost::withTrashed()->findOrFail($id);
        $post->restore();
        session()->flash('success', 'Post restored successfully.');
    }

    // Force delete
    public function forceDeletePost($id)
    {
        $post = BuySellPost::withTrashed()->findOrFail($id);
        foreach ($post->images as $img) {
            Storage::disk('public')->delete($img->path);
            $img->delete();
        }
        $post->forceDelete();
        session()->flash('success', 'Post permanently deleted.');
    }

    // Sorting
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // Table data
    public function getPostsProperty()
    {
        $query = BuySellPost::query();
        if ($this->showTrashed) {
            $query->onlyTrashed();
        }

        return $query->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%'))->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
    }
};
?>


<section class="p-6">
    <div class="flex flex-col space-y-6">
        {{-- Create/Edit Form --}}
        @if ($activeTab === 'create' || $activeTab === 'edit')
            <div class="rounded-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">{{ $activeTab === 'create' ? 'Create New Post' : 'Edit Post' }}
                    </h3>
                    <flux:button wire:click="showTab('index')" size="sm">Back</flux:button>
                </div>

                <form wire:submit.prevent="savePost" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Title & Slug --}}
                    <div>
                        <flux:input wire:model="title" label="Title" />
                    </div>
                    <div>
                        <flux:input wire:model="slug" label="Slug (auto if blank)" />
                    </div>

                    {{-- Category & Item --}}
                    <div>
                        <flux:select wire:model="buy_sell_category_id" label="Category">
                            <option value="">-- Select --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model="buy_sell_item_id" label="Item">
                            <option value="">-- Select --</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    {{-- Condition & Price --}}
                    <div>
                        <flux:input wire:model="condition" label="Condition (New/Used)" />
                    </div>
                    <div>
                        <flux:input type="number" wire:model="price" label="Price" />
                    </div>
                    <div>
                        <flux:input type="number" wire:model="discount_price" label="Discount Price (Optional)" />
                    </div>
                    <div>
                        <flux:input wire:model="currency" label="Currency" />
                    </div>

                    {{-- Stock & Negotiable --}}
                    <div>
                        <flux:input type="number" wire:model="stock" label="Stock" />
                    </div>
                    <div class="flex items-center gap-2 mt-6">
                        <flux:checkbox wire:model="is_negotiable" label="Negotiable" />
                        <flux:checkbox wire:model="is_available" label="Available" />
                    </div>

                    {{-- Location --}}
                    <div>
                        <flux:input wire:model="division_id" label="Division ID" />
                    </div>
                    <div>
                        <flux:input wire:model="district_id" label="District ID" />
                    </div>
                    <div>
                        <flux:input wire:model="thana_id" label="Thana ID" />
                    </div>
                    <div class="col-span-2">
                        <flux:input wire:model="address" label="Address" />
                    </div>

                    {{-- Contact --}}
                    <div>
                        <flux:input wire:model="phone" label="Phone" />
                    </div>
                    <div>
                        <flux:input wire:model="whatsapp" label="Whatsapp" />
                    </div>
                    <div>
                        <flux:input wire:model="email" label="Email" />
                    </div>

                    {{-- Description & Note --}}
                    <div class="col-span-2">
                        <flux:textarea wire:model="description" label="Description" />
                        <flux:textarea wire:model="note" label="Note (Optional)" />
                    </div>

                    {{-- Status & Flags --}}
                    <div>
                        <flux:select wire:model="status" label="Status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </flux:select>
                    </div>
                    <div class="flex gap-4 mt-6">
                        <flux:checkbox wire:model="is_active" label="Active" />
                        <flux:checkbox wire:model="is_featured" label="Featured" />
                    </div>

                    {{-- Images --}}
                    <div class="col-span-2">
                        <flux:input type="file" wire:model="images" label="Upload Images" multiple />
                        @if ($existingImages)
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($existingImages as $img)
                                    <div class="relative">
                                        <img src="{{ Storage::url($img['path']) }}"
                                            class="w-24 h-24 object-cover rounded">
                                        <button type="button" wire:click="$set('primaryImageId', {{ $img['id'] }})"
                                            class="absolute top-0 right-0 bg-white rounded-full p-1 text-green-600">
                                            {{ $primaryImageId == $img['id'] ? '★' : '☆' }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Buttons --}}
                    <div class="col-span-2 flex gap-2 justify-end mt-4">
                        <flux:button type="button" wire:click="showTab('index')">Cancel</flux:button>
                        <flux:button type="submit">{{ $postId ? 'Update Post' : 'Create Post' }}</flux:button>
                    </div>

                </form>
            </div>
        @else
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <h2 class="text-2xl font-bold">Buy & Sell Posts Management</h2>
                <div class="flex space-x-2 mt-4 sm:mt-0">
                    <flux:button wire:click="showTab('create')">Create New Post</flux:button>
                    <flux:button wire:click="$set('showTrashed', false)"
                        variant="{{ !$showTrashed ? 'primary' : 'filled' }}">
                        Active</flux:button>
                    <flux:button wire:click="$set('showTrashed', true)"
                        variant="{{ $showTrashed ? 'primary' : 'filled' }}">
                        Trashed</flux:button>
                </div>
            </div>

            {{-- Search --}}
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by title..." />

            <div class="overflow-x-auto rounded-xl">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="">
                        <tr>
                            <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('title')">Title
                                {!! $sortField === 'title' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}</th>
                            <th class="px-4 py-2">Category</th>
                            <th class="px-4 py-2">Price</th>
                            <th class="px-4 py-2">Stock</th>
                            <th class="px-4 py-2">Active</th>
                            <th class="px-4 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($this->posts as $post)
                            <tr class="hover:bg-zinc-400/10">
                                <td class="px-4 py-2">{{ $post->title }}</td>
                                <td class="px-4 py-2">{{ $post->category->name ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $post->formatted_price }}</td>
                                <td class="px-4 py-2">{{ $post->stock }}</td>
                                <td class="px-4 py-2">{{ $post->is_active ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    @if ($post->deleted_at)
                                        <button wire:click="restorePost({{ $post->id }})"
                                            class="text-green-600">Restore</button>
                                        <button wire:click="forceDeletePost({{ $post->id }})"
                                            class="text-red-600"
                                            onclick="return confirm('Permanently delete?')">Delete</button>
                                    @else
                                        <button wire:click="editPost({{ $post->id }})"
                                            class="text-blue-600">Edit</button>
                                        <button wire:click="deletePost({{ $post->id }})" class="text-red-600"
                                            onclick="return confirm('Move to trash?')">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-2 text-center">No
                                    {{ $showTrashed ? 'trashed' : 'active' }} posts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if ($this->posts->hasPages())
                    <div class="px-4 py-2 border-t">
                        {{ $this->posts->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
