<?php

use Livewire\Volt\Component;
use App\Models\ContactCategory;
use Illuminate\Support\Str;

new class extends Component {

    // Properties
    public $categories;
    public $categoryId;

    public $name;
    public $slug;
    public $description;
    public $icon;
    public $is_featured = false;
    public $is_active = true;
    public $status = 'inactive';
    public $extra_attributes = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->categories = ContactCategory::latest()->get();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:contact_categories,slug,' . $this->categoryId,
            'status' => 'required|in:active,inactive',
        ]);

        ContactCategory::updateOrCreate(
            ['id' => $this->categoryId],
            [
                'name' => $this->name,
                'slug' => $this->slug ?: Str::slug($this->name),
                'description' => $this->description,
                'icon' => $this->icon,
                'is_featured' => $this->is_featured,
                'is_active' => $this->is_active,
                'status' => $this->status,
                'extra_attributes' => json_encode($this->extra_attributes),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );

        $this->resetForm();
        $this->loadData();
    }

    public function edit($id)
    {
        $cat = ContactCategory::findOrFail($id);
        $this->categoryId = $cat->id;
        $this->name = $cat->name;
        $this->slug = $cat->slug;
        $this->description = $cat->description;
        $this->icon = $cat->icon;
        $this->is_featured = $cat->is_featured;
        $this->is_active = $cat->is_active;
        $this->status = $cat->status;
        $this->extra_attributes = $cat->extra_attributes ? json_decode($cat->extra_attributes, true) : [];
    }

    public function delete($id)
    {
        ContactCategory::findOrFail($id)->delete();
        $this->loadData();
    }

    public function resetForm()
    {
        $this->reset(['categoryId','name','slug','description','icon','is_featured','is_active','status','extra_attributes']);
    }

    public function addExtraAttribute()
    {
        $this->extra_attributes[] = ['key'=>'','value'=>''];
    }

    public function removeExtraAttribute($index)
    {
        unset($this->extra_attributes[$index]);
        $this->extra_attributes = array_values($this->extra_attributes);
    }
};
 ?>

<div class="p-6 space-y-6">
    <h2 class="text-xl font-bold">📂 Contact Category Management</h2>

    <!-- Form -->
    <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

        <!-- Name -->
        <div>
            <flux:input type="text" wire:model="name" label="Name" />
        </div>

        <!-- Slug -->
        <div>
            <flux:input type="text" wire:model="slug" label="Slug" />
        </div>

        <!-- Icon -->
        <div>
            <flux:input type="text" wire:model="icon" label="Icon" />
        </div>

        <!-- Status -->
        <div>
            <flux:select wire:model="status" label="Status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </flux:select>
        </div>

        <!-- Featured -->
        <div class="flex items-center space-x-2 mt-6">
            <flux:checkbox wire:model="is_featured" label="Featured" />
        </div>

        <!-- Active -->
        <div class="flex items-center space-x-2 mt-6">
            <flux:checkbox wire:model="is_active" label="Active" />
        </div>

        <!-- Description -->
        <div class="md:col-span-3">
            <flux:textarea wire:model="description" label="Description (Optional)" />
        </div>

        <!-- Buttons -->
        <div class="md:col-span-2 flex space-x-2 justify-start">
            <flux:button type="submit" variant="primary">
                {{ $categoryId ? 'Update' : 'Create' }}
            </flux:button>
            <flux:button type="button" wire:click="resetForm">Reset</flux:button>
        </div>

    </form>


    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full text-center">
            <thead class="border-b border-zinc-400/10">
                <tr>
                    <th class="py-3 px-2">Icon</th>
                    <th class="py-3 px-2">Name</th>
                    <th class="py-3 px-2">Slug</th>
                    <th class="py-3 px-2">Featured</th>
                    <th class="py-3 px-2">Active</th>
                    <th class="py-3 px-2">Status</th>
                    <th class="py-3 px-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr class="hover:bg-zinc-400/10 border-b border-zinc-400/10">
                    <td class="py-3 px-2">{{ $cat->icon ?? '-' }}</td>
                    <td class="py-3 px-2">{{ $cat->name }}</td>
                    <td class="py-3 px-2">{{ $cat->slug }}</td>
                    <td class="py-3 px-2">{{ $cat->is_featured ? 'Yes' : 'No' }}</td>
                    <td class="py-3 px-2">{{ $cat->is_active ? 'Yes' : 'No' }}</td>
                    <td class="py-3 px-2">{{ $cat->status }}</td>
                    <td class="py-3 px-2 space-x-2">
                        <button wire:click="edit({{ $cat->id }})" class="text-blue-600">Edit</button>
                        <button wire:click="delete({{ $cat->id }})" class="text-red-600">Delete</button>
                    </td>
                </tr>
                @empty
                <tr class="hover:bg-zinc-400/10 border-b border-zinc-400/10">
                    <td colspan="7" class="text-center py-3 px-2">No categories found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
