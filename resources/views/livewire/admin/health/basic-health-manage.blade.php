<?php

use Livewire\Volt\Component;
use App\Models\BasicHealth;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

new class extends Component {
    use WithFileUploads, WithPagination;

    // Form fields
    public $basicHealthId;
    public $title = '';
    public $description = '';
    public $type = '';
    public $summary = '';
    public $image;
    public $imagePreview;
    public $tags = '';
    public $slug = '';

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
    public $formType = 'create'; // create / edit
    public $viewType = 'active'; // active / trashed
    public $search = '';

    // Pagination and Sorting
    public $perPage = 10;
    public $sortField = 'title';
    public $sortDirection = 'asc';

    public function getBasicHealthsProperty()
    {
        $query = $this->viewType === 'trashed' ? BasicHealth::onlyTrashed() : BasicHealth::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('type', 'like', "%{$this->search}%")
                    ->orWhere('summary', 'like', "%{$this->search}%");
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
        $item = $this->viewType === 'trashed' ? BasicHealth::withTrashed()->find($id) : BasicHealth::find($id);

        $this->basicHealthId = $item->id;
        $this->title = $item->title;
        $this->description = $item->description;
        $this->type = $item->type;
        $this->summary = $item->summary;
        $this->tags = is_array($item->tags) ? implode(',', $item->tags) : $item->tags;
        $this->imagePreview = $item->image ? Storage::url($item->image) : null;

        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset(['basicHealthId', 'title', 'description', 'type', 'summary', 'tags', 'image', 'imagePreview', 'slug']);
        $this->resetErrorBag();
    }
    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'tags' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($this->image) {
            // New image uploaded
            $imagePath = $this->image->store('basic-health', 'public');
            $validated['image'] = $imagePath;

            if ($this->formType === 'edit' && $this->imagePreview) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $this->imagePreview));
            }
        } elseif ($this->formType === 'edit') {
            // Keep old image if no new file uploaded
            $validated['image'] = str_replace('/storage/', '', $this->imagePreview);
        }

        $validated['slug'] = \Str::slug($this->title);

        if ($this->formType === 'edit') {
            $item = BasicHealth::withTrashed()->find($this->basicHealthId);
            $item->update($validated);
            $message = 'Health content updated successfully!';
        } else {
            BasicHealth::create($validated);
            $message = 'Health content created successfully!';
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('message', $message);
        Flux::toast($message);
    }

    public function deleteItem($id)
    {
        $item = BasicHealth::find($id);
        $item->delete();
        session()->flash('message', 'Item moved to trash!');
        $this->resetPage();
    }

    public function restoreItem($id)
    {
        $item = BasicHealth::onlyTrashed()->find($id);
        $item->restore();
        session()->flash('message', 'Item restored successfully!');
        $this->resetPage();
    }

    public function forceDeleteItem($id)
    {
        $item = BasicHealth::onlyTrashed()->find($id);

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->forceDelete();
        session()->flash('message', 'Item permanently deleted!');
        $this->resetPage();
    }
}; ?>

<section class="p-4">
    <div class="flex flex-col space-y-6">

        <!-- Form -->
        @if ($showForm)
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">{{ $formType === 'create' ? 'Create New Health' : 'Edit Health' }}
                    </h3>
                    <flux:button wire:click="$set('showForm', false)" size="sm">Back</flux:button>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model.live="title" label="Title" required />
                        <flux:input wire:model.live="type" label="Type" />
                    </div>

                    <flux:textarea wire:model.live="summary" label="Summary" rows="2" />
                    <flux:textarea wire:model.live="description" label="Description" rows="3" />
                    <flux:input type="file" wire:model.live="image" label="Image" accept="image/*" />
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" class="h-20 mt-2 rounded">
                    @elseif($imagePreview)
                        <img src="{{ $imagePreview }}" class="h-20 mt-2 rounded">
                    @endif

                    <flux:input wire:model.live="tags" label="Tags (comma separated)" />

                    <div class="flex justify-end space-x-3">
                        <flux:button type="button" wire:click="$set('showForm', false)">Cancel</flux:button>
                        <flux:button type="submit">{{ $formType === 'create' ? 'Create' : 'Update' }}</flux:button>
                    </div>
                </form>
            </div>
        @else
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">Basic Health Management</h2>
                <div class="flex space-x-2">
                    <flux:button wire:click="showCreateForm">Create New</flux:button>
                    <flux:button wire:click="toggleView('active')"
                        variant="{{ $viewType === 'active' ? 'primary' : 'filled' }}">Active</flux:button>
                    <flux:button wire:click="toggleView('trashed')"
                        variant="{{ $viewType === 'trashed' ? 'primary' : 'filled' }}">Trashed</flux:button>
                </div>
            </div>

            <!-- Search -->
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by title or type..." />

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer"
                                wire:click="sortBy('title')">
                                Title
                                @if ($sortField === 'title')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($this->basicHealths as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    @if ($item->image)
                                        <img src="{{ Storage::url($item->image) }}" class="h-10 w-10 rounded">
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">{{ $item->title }}</td>
                                <td class="px-6 py-4 text-sm">{{ $item->type_name }}</td>
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
                                <td colspan="4" class="px-6 py-4 text-center text-sm">No {{ $viewType }} items
                                    found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if ($this->basicHealths->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $this->basicHealths->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
