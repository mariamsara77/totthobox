<?php

use Livewire\Volt\Component;
use App\Models\BasicIslam;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads, WithPagination;

    // Form fields
    public $basicIslamId;
    public $title = '';
    public $description = '';
    public $image;
    public $imagePreview;
    public $type = '';
    public $tagsInput = '';
    public $status = 1;
    public $is_featured = false;
    public $meta_title = '';
    public $meta_description = '';
    public $meta_keywords = '';
    public $published_at;

    // UI states
    public $showForm = false;
    public $formType = 'create'; // 'create' or 'edit'
    public $viewType = 'active'; // 'active', 'trashed'
    public $search = '';

    // Pagination
    public $perPage = 10;
    public $sortField = 'title';
    public $sortDirection = 'asc';

    // Available types
    public $types = [];

    public function mount()
    {
        $this->published_at = now();
        $this->types = BasicIslam::TYPES;
    }

    public function getBasicIslamsProperty()
    {
        $query = $this->viewType === 'trashed' ? BasicIslam::onlyTrashed() : BasicIslam::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query
            ->with(['user', 'creator', 'editor'])
            ->orderBy($this->sortField, $this->sortDirection)
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

    public function showEditForm($basicIslamId)
    {
        $basicIslam = $this->viewType === 'trashed' ? BasicIslam::withTrashed()->find($basicIslamId) : BasicIslam::find($basicIslamId);

        $this->basicIslamId = $basicIslam->id;
        $this->title = $basicIslam->title;
        $this->description = $basicIslam->description;
        $this->type = $basicIslam->type;
        $this->tagsInput = is_array($basicIslam->tags) ? implode(', ', $basicIslam->tags) : $basicIslam->tags;
        $this->status = $basicIslam->is_active;
        $this->is_featured = $basicIslam->is_featured;
        $this->meta_title = $basicIslam->meta_title;
        $this->meta_description = $basicIslam->meta_description;
        $this->meta_keywords = is_array($basicIslam->meta_keywords) ? implode(', ', $basicIslam->meta_keywords) : $basicIslam->meta_keywords;
        $this->published_at = $basicIslam->published_at ? $basicIslam->published_at->format('Y-m-d\TH:i') : null;
        $this->imagePreview = $basicIslam->image ? Storage::url($basicIslam->image) : null;

        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset(['basicIslamId', 'title', 'description', 'image', 'imagePreview', 'type', 'tagsInput', 'status', 'is_featured', 'meta_title', 'meta_description', 'meta_keywords', 'published_at']);
        $this->resetErrorBag();
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|min:3',
            'description' => 'required',
            'status' => 'required|boolean',
            'is_featured' => 'required|boolean',
            'published_at' => 'nullable|date',
        ]);

        // Handle image upload
        if ($this->image) {
            $imagePath = $this->image->store('basic-islam-images', 'public');
            $validated['image'] = $imagePath;

            // Delete old image if exists
            if ($this->formType === 'edit' && $this->imagePreview) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $this->imagePreview));
            }
        }

        // Process tags as array
        if (!empty($validated['tagsInput'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tagsInput']));
        }
        unset($validated['tagsInput']);

        // Process meta keywords as array
        if (!empty($validated['meta_keywords'])) {
            $validated['meta_keywords'] = array_map('trim', explode(',', $validated['meta_keywords']));
        }

        $validated['user_id'] = auth()->id();
        $validated['slug'] = \Str::slug($this->title);
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        // Use the correct column name based on your database
        // Check your database table structure and adjust accordingly
        $validated['is_active'] = $validated['status'];
        unset($validated['status']);

        if ($this->formType === 'edit') {
            $basicIslam = BasicIslam::withTrashed()->find($this->basicIslamId);
            $basicIslam->update($validated);
            $message = 'Basic Islam content updated successfully!';
        } else {
            BasicIslam::create($validated);
            $message = 'Basic Islam content created successfully!';
        }

        $this->showForm = false;
        $this->resetForm();

        session()->flash('message', $message);
        Flux::toast($message);
    }

    public function deleteBasicIslam($basicIslamId)
    {
        $basicIslam = BasicIslam::find($basicIslamId);
        $basicIslam->deleted_by = auth()->id();
        $basicIslam->save();
        $basicIslam->delete();
        session()->flash('message', 'Basic Islam content moved to trash!');
        $this->resetPage();
    }

    public function restoreBasicIslam($basicIslamId)
    {
        $basicIslam = BasicIslam::onlyTrashed()->find($basicIslamId);
        $basicIslam->restore();
        $basicIslam->deleted_by = null;
        $basicIslam->save();
        session()->flash('message', 'Basic Islam content restored successfully!');
        $this->resetPage();
    }

    public function forceDeleteBasicIslam($basicIslamId)
    {
        $basicIslam = BasicIslam::onlyTrashed()->find($basicIslamId);

        // Delete associated files
        if ($basicIslam->image) {
            Storage::disk('public')->delete($basicIslam->image);
        }

        $basicIslam->forceDelete();
        session()->flash('message', 'Basic Islam content permanently deleted!');
        $this->resetPage();
    }
}; ?>

<section class="">
    <div class="flex flex-col space-y-6">

        <!-- BasicIslam Form -->
        @if ($showForm)
            <div class="">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold ">
                        {{ $formType === 'create' ? 'Create New Basic Islam Content' : 'Edit Basic Islam Content' }}
                    </h3>
                    <flux:button wire:click="$set('showForm', false)" size="sm">
                        Back
                    </flux:button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Basic Info -->
                        <div class="space-y-4">
                            <div>
                                <flux:input type="text" wire:model.live="title" label="Title" required />
                                @error('title')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <flux:textarea wire:model.live="description" label="Description" rows="5"
                                    required />
                                @error('description')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <flux:select wire:model.live="status" label="Status" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </flux:select>
                                    @error('status')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="flex items-center">
                                    <flux:checkbox wire:model.live="is_featured" label="Featured Content" />
                                    @error('is_featured')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <flux:input type="datetime-local" wire:model.live="published_at"
                                        label="Publish Date/Time" />
                                    @error('published_at')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <flux:button type="button" wire:click="$set('showForm', false)">
                            Cancel
                        </flux:button>
                        <flux:button type="submit">
                            {{ $formType === 'create' ? 'Create' : 'Update' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @else
            <!-- Header and Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <h2 class="text-2xl font-bold ">Basic Islam Content Management</h2>

                <div class="flex space-x-2">
                    <flux:button wire:click="showCreateForm">
                        Create New
                    </flux:button>

                    <flux:button wire:click="toggleView('active')"
                        variant="{{ $viewType === 'active' ? 'primary' : 'filled' }}">
                        Active
                    </flux:button>

                    <flux:button wire:click="toggleView('trashed')"
                        variant="{{ $viewType === 'trashed' ? 'primary' : 'filled' }}">
                        Trashed
                    </flux:button>
                </div>
            </div>

            <!-- Search -->
            <div class="">
                <flux:input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search by title or description..." class="" />
            </div>

            <!-- BasicIslam Table - Simplified View -->
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('title')">
                                Title
                                @if ($sortField === 'title')
                                    @if ($sortDirection === 'asc')
                                        ↑
                                    @else
                                        ↓
                                    @endif
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                                Description
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('is_active')">
                                Status
                                @if ($sortField === 'is_active')
                                    @if ($sortDirection === 'asc')
                                        ↑
                                    @else
                                        ↓
                                    @endif
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-200 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class=" divide-y divide-gray-200">
                        @forelse ($this->basicIslams as $basicIslam)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if ($basicIslam->image)
                                            <img src="{{ Storage::url($basicIslam->image) }}"
                                                alt="{{ $basicIslam->title }}" class="h-10 w-10 rounded-full mr-3">
                                        @endif
                                        <div class="text-sm font-medium text-gray-200">{{ $basicIslam->title }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-200 max-w-xs">
                                        {{ Str::limit($basicIslam->description, 100) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $basicIslam->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $basicIslam->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if ($viewType === 'active')
                                        <button wire:click="showEditForm({{ $basicIslam->id }})"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            Edit
                                        </button>
                                        <button wire:click="deleteBasicIslam({{ $basicIslam->id }})"
                                            class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    @else
                                        <button wire:click="restoreBasicIslam({{ $basicIslam->id }})"
                                            class="text-green-600 hover:text-green-900 mr-3">
                                            Restore
                                        </button>
                                        <button wire:click="forceDeleteBasicIslam({{ $basicIslam->id }})"
                                            class="text-red-600 hover:text-red-900">
                                            Permanently Delete
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No {{ $viewType === 'trashed' ? 'trashed' : 'active' }} basic islam content found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if ($this->basicIslams->hasPages())
                    <div class="px-6 py-3 bg-white border-t border-gray-200">
                        {{ $this->basicIslams->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</section>
