<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use App\Models\ClassLevel;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    // Properties
    public $classLevelId;
    public $name;
    public $slug;
    public $order;
    public $is_active = true;
    public $status = 'draft';
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $is_featured = false;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'order';
    public $sortDirection = 'asc';
    public $showTrashed = false;
    public $activeTab = 'index';

    // Validation
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:class_levels,slug,' . $this->classLevelId,
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|string|max:50',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'is_featured' => 'nullable|boolean',
        ];
    }

    // Tabs
    public function showTab($tabName)
    {
        $this->activeTab = $tabName;
        $this->resetFields();
    }

    // Reset
    public function resetFields()
    {
        $this->reset([
            'classLevelId', 'name', 'slug', 'order', 'is_active', 'status',
            'meta_title', 'meta_description', 'meta_keywords', 'is_featured'
        ]);
        $this->is_active = true;
        $this->status = 'draft';
        $this->is_featured = false;
        $this->resetErrorBag();
    }

    // Edit
    public function editClassLevel($id)
    {
        $classLevel = ClassLevel::withTrashed()->findOrFail($id);

        $this->classLevelId = $classLevel->id;
        $this->name = $classLevel->name;
        $this->slug = $classLevel->slug;
        $this->order = $classLevel->order;
        $this->is_active = $classLevel->is_active;
        $this->status = $classLevel->status;
        $this->meta_title = $classLevel->meta_title;
        $this->meta_description = $classLevel->meta_description;
        $this->meta_keywords = $classLevel->meta_keywords;
        $this->is_featured = $classLevel->is_featured;

        $this->activeTab = 'edit';
    }

    // Save
    public function saveClassLevel()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug ?: Str::slug($this->name) . '-' . Str::random(5),
            'order' => $this->order,
            'is_active' => $this->is_active,
            'status' => $this->status,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'is_featured' => $this->is_featured,
            'user_id' => Auth::id(),
        ];

        if ($this->classLevelId) {
            $classLevel = ClassLevel::findOrFail($this->classLevelId);
            $data['updated_by'] = Auth::id();
            $classLevel->update($data);
            session()->flash('success', 'Class Level updated successfully.');
        } else {
            $data['created_by'] = Auth::id();
            ClassLevel::create($data);
            session()->flash('success', 'Class Level created successfully.');
        }

        $this->resetFields();
        $this->activeTab = 'index';
    }

    // Delete
    public function deleteClassLevel($id)
    {
        $classLevel = ClassLevel::findOrFail($id);
        $classLevel->update(['deleted_by' => Auth::id()]);
        $classLevel->delete();
        session()->flash('success', 'Class Level moved to trash.');
    }

    // Restore
    public function restoreClassLevel($id)
    {
        $classLevel = ClassLevel::withTrashed()->findOrFail($id);
        $classLevel->restore();
        session()->flash('success', 'Class Level restored successfully.');
    }

    // Force Delete
    public function forceDeleteClassLevel($id)
    {
        $classLevel = ClassLevel::withTrashed()->findOrFail($id);
        $classLevel->forceDelete();
        session()->flash('success', 'Class Level permanently deleted.');
    }

    // Sort
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // Data
    public function getClassLevelsProperty()
    {
        $query = ClassLevel::query();

        if ($this->showTrashed) {
            $query->onlyTrashed();
        }

        return $query->when($this->search, function ($query) {
            return $query->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('slug', 'like', '%' . $this->search . '%');
        })
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);
    }
};
?>




<section class="p-6">
    <div class="flex flex-col space-y-6">

        {{-- Create/Edit Form --}}
        @if($activeTab === 'create' || $activeTab === 'edit')
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">
                    {{ $activeTab === 'create' ? 'Create New Class Level' : 'Edit Class Level' }}
                </h3>
                <flux:button wire:click="showTab('index')" size="sm">Back</flux:button>
            </div>

            <form wire:submit="saveClassLevel">
                <div class="grid grid-cols-1 gap-6">
                    <flux:input wire:model="name" label="Name" />
                    <flux:input wire:model="slug" label="Slug (auto if blank)" />
                    <flux:input type="number" wire:model="order" label="Order" />

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:checkbox wire:model="is_active" label="Active" />
                        <flux:checkbox wire:model="is_featured" label="Featured" />
                        <flux:select wire:model="status" label="Status">
                            <option value="">Set Status</option>
                            <option value="active">Active</option>
                            <option value="deactive">Deactive</option>
                            <option value="archived">Archived</option>
                        </flux:select>
                    </div>

                    {{-- SEO Info --}}
                    <div>
                        <h4 class="text-lg font-medium">SEO Information</h4>
                        <flux:input wire:model="meta_title" label="Meta Title" />
                        <flux:textarea wire:model="meta_description" label="Meta Description" />
                        <flux:input wire:model="meta_keywords" label="Meta Keywords" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <flux:button type="button" wire:click="showTab('index')">Cancel</flux:button>
                    <flux:button type="submit">{{ $activeTab === 'create' ? 'Create' : 'Update' }}</flux:button>
                </div>
            </form>
        </div>
        @else

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <h2 class="text-2xl font-bold">Class Levels Management</h2>
            <div class="flex space-x-2">
                <flux:button wire:click="showTab('create')">Create New</flux:button>
                <flux:button wire:click="$set('showTrashed', false)" variant="{{ !$showTrashed ? 'primary' : 'filled' }}">Active</flux:button>
                <flux:button wire:click="$set('showTrashed', true)" variant="{{ $showTrashed ? 'primary' : 'filled' }}">Trashed</flux:button>
            </div>
        </div>

        {{-- Search --}}
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by name or slug..." />

        {{-- Table --}}
        <div class="overflow-x-auto rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer" wire:click="sortBy('name')">
                            Name {!! $sortField === 'name' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer" wire:click="sortBy('slug')">
                            Slug {!! $sortField === 'slug' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Active</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->classLevels as $level)
                    <tr>
                        <td class="px-6 py-4">{{ $level->name }}</td>
                        <td class="px-6 py-4">{{ $level->slug }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $level->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($level->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            {{ $level->is_active ? 'Yes' : 'No' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($level->deleted_at)
                            <button wire:click="restoreClassLevel({{ $level->id }})" class="text-green-600 mr-2">Restore</button>
                            <button wire:click="forceDeleteClassLevel({{ $level->id }})" class="text-red-600" onclick="return confirm('Permanently delete?')">Delete Permanently</button>
                            @else
                            <button wire:click="editClassLevel({{ $level->id }})" class="text-blue-600 mr-2">Edit</button>
                            <button wire:click="deleteClassLevel({{ $level->id }})" class="text-red-600" onclick="return confirm('Move to trash?')">Delete</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center">No {{ $showTrashed ? 'trashed' : 'active' }} class levels found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($this->classLevels->hasPages())
            <div class="px-6 py-3 border-t">
                {{ $this->classLevels->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
</section>
