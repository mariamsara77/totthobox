<?php

use Livewire\Volt\Component;
use App\Models\Dowa;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads, WithPagination;

    // Form fields
    public $dowaId;
    public $bangla_name = '';
    public $arabic_name = '';
    public $arabic_text = '';
    public $bangla_text = '';
    public $bangla_meaning = '';
    public $bangla_fojilot = '';
    public $audio;
    public $audioPreview;
    public $others = '';
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
    public $sortField = 'bangla_name';
    public $sortDirection = 'asc';

    // Available types
    public $types = [];

    public function mount()
    {
        $this->published_at = now();
        $this->types = Dowa::TYPES;
    }

    public function getDowasProperty()
    {
        $query = $this->viewType === 'trashed' 
            ? Dowa::onlyTrashed() 
            : Dowa::query();
            
        if ($this->search) {
            $query->where(function($q) {
                $q->where('bangla_name', 'like', '%'.$this->search.'%')
                  ->orWhere('arabic_name', 'like', '%'.$this->search.'%')
                  ->orWhere('bangla_text', 'like', '%'.$this->search.'%')
                  ->orWhere('bangla_meaning', 'like', '%'.$this->search.'%');
            });
        }
        
        return $query->with(['user', 'creator', 'editor'])
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

    public function showEditForm($dowaId)
    {
        $dowa = $this->viewType === 'trashed' 
            ? Dowa::withTrashed()->find($dowaId) 
            : Dowa::find($dowaId);
        
        $this->dowaId = $dowa->id;
        $this->bangla_name = $dowa->bangla_name;
        $this->arabic_name = $dowa->arabic_name;
        $this->arabic_text = $dowa->arabic_text;
        $this->bangla_text = $dowa->bangla_text;
        $this->bangla_meaning = $dowa->bangla_meaning;
        $this->bangla_fojilot = $dowa->bangla_fojilot;
        $this->others = $dowa->others;
        $this->type = $dowa->type;
        $this->tagsInput = is_array($dowa->tags) ? implode(', ', $dowa->tags) : $dowa->tags;
        $this->status = $dowa->is_active;
        $this->is_featured = $dowa->is_featured;
        $this->meta_title = $dowa->meta_title;
        $this->meta_description = $dowa->meta_description;
        $this->meta_keywords = is_array($dowa->meta_keywords) 
            ? implode(', ', $dowa->meta_keywords) 
            : $dowa->meta_keywords;
        $this->published_at = $dowa->published_at ? $dowa->published_at->format('Y-m-d\TH:i') : null;
        $this->audioPreview = $dowa->audio ? Storage::url($dowa->audio) : null;
        
        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset([
            'dowaId', 'bangla_name', 'arabic_name', 'arabic_text', 'bangla_text', 
            'bangla_meaning', 'bangla_fojilot', 'audio', 'audioPreview', 'others',
            'type', 'tagsInput', 'status', 'is_featured', 'meta_title', 
            'meta_description', 'meta_keywords', 'published_at'
        ]);
        $this->resetErrorBag();
    }

    public function save()
    {
        $validated = $this->validate([
            'bangla_name' => 'required|min:3',
            'arabic_name' => 'nullable|string',
            'arabic_text' => 'nullable|string',
            'bangla_text' => 'required|string',
            'bangla_meaning' => 'nullable|string',
            'bangla_fojilot' => 'nullable|string',
            'status' => 'nullable|in:0,1',
            'is_featured' => 'nullable|in:0,1',
            'published_at' => 'nullable|date',
        ]);

        // Handle audio upload
        if ($this->audio) {
            $audioPath = $this->audio->store('dowa-audio', 'public');
            $validated['audio'] = $audioPath;

            // Delete old audio if exists
            if ($this->formType === 'edit' && $this->audioPreview) {
                Storage::disk('public')->delete(
                    str_replace('/storage/', '', $this->audioPreview)
                );
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
        $validated['slug'] = \Str::slug($this->bangla_name);
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        
        // Use the correct column name based on your database
        $validated['status'] = $validated['status'];
        unset($validated['status']);

        if ($this->formType === 'edit') {
            $dowa = Dowa::withTrashed()->find($this->dowaId);
            $dowa->update($validated);
            $message = 'Dowa updated successfully!';
        } else {
            Dowa::create($validated);
            $message = 'Dowa created successfully!';
        }

        $this->showForm = false;
        $this->resetForm();
        
        session()->flash('message', $message);
        Flux::toast($message);
    }

    public function deleteDowa($dowaId)
    {
        $dowa = Dowa::find($dowaId);
        $dowa->deleted_by = auth()->id();
        $dowa->save();
        $dowa->delete();
        session()->flash('message', 'Dowa moved to trash!');
        $this->resetPage();
    }

    public function restoreDowa($dowaId)
    {
        $dowa = Dowa::onlyTrashed()->find($dowaId);
        $dowa->restore();
        $dowa->deleted_by = null;
        $dowa->save();
        session()->flash('message', 'Dowa restored successfully!');
        $this->resetPage();
    }

    public function forceDeleteDowa($dowaId)
    {
        $dowa = Dowa::onlyTrashed()->find($dowaId);
        
        // Delete associated files
        if ($dowa->audio) {
            Storage::disk('public')->delete($dowa->audio);
        }
        
        $dowa->forceDelete();
        session()->flash('message', 'Dowa permanently deleted!');
        $this->resetPage();
    }
}; ?>

<section class="">
    <div class="flex flex-col space-y-6">

        <!-- Dowa Form -->
        @if($showForm)
        <div class="">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold ">
                    {{ $formType === 'create' ? 'Create New Dowa' : 'Edit Dowa' }}
                </h3>
                <flux:button wire:click="$set('showForm', false)" size="sm">
                    Back
                </flux:button>
            </div>

            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Basic Info -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:input type="text" wire:model.live="bangla_name" label="Bangla Name" required />
                                @error('bangla_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <flux:input type="text" wire:model.live="arabic_name" label="Arabic Name" />
                                @error('arabic_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <flux:textarea wire:model.live="arabic_text" label="Arabic Text" rows="3" />
                            @error('arabic_text') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <flux:textarea wire:model.live="bangla_text" label="Bangla Text" rows="3" required />
                            @error('bangla_text') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <flux:textarea wire:model.live="bangla_meaning" label="Bangla Meaning" rows="3" />
                            @error('bangla_meaning') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <flux:textarea wire:model.live="bangla_fojilot" label="Bangla Fojilot (Benefits)" rows="3" />
                            @error('bangla_fojilot') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>



                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <flux:select wire:model.live="status" label="Status" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </flux:select>
                                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center">
                                <flux:checkbox wire:model.live="is_featured" label="Featured Dowa" />
                                @error('is_featured') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <flux:input type="datetime-local" wire:model.live="published_at" label="Publish Date/Time" />
                                @error('published_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
            <h2 class="text-2xl font-bold ">Dowa Management</h2>

            <div class="flex space-x-2">
                <flux:button wire:click="showCreateForm">
                    Create New
                </flux:button>

                <flux:button wire:click="toggleView('active')" variant="{{ $viewType === 'active' ? 'primary' : 'filled' }}">
                    Active
                </flux:button>

                <flux:button wire:click="toggleView('trashed')" variant="{{ $viewType === 'trashed' ? 'primary' : 'filled' }}">
                    Trashed
                </flux:button>
            </div>
        </div>

        <!-- Search -->
        <div class="">
            <flux:input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or text..." class="" />
        </div>

        <!-- Dowa Table - Simplified View -->
        <div class="overflow-x-auto rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider cursor-pointer" wire:click="sortBy('bangla_name')">
                            Bangla Name
                            @if($sortField === 'bangla_name')
                            @if($sortDirection === 'asc')
                            ↑
                            @else
                            ↓
                            @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                            Arabic Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                            Bangla Text
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider cursor-pointer" wire:click="sortBy('is_active')">
                            Status
                            @if($sortField === 'is_active')
                            @if($sortDirection === 'asc')
                            ↑
                            @else
                            ↓
                            @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-200 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class=" divide-y divide-gray-200">
                    @forelse ($this->dowas as $dowa)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-200">{{ $dowa->bangla_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-200">{{ $dowa->arabic_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-200 max-w-xs">{{ Str::limit($dowa->bangla_text, 100) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $dowa->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $dowa->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($viewType === 'active')
                            <button wire:click="showEditForm({{ $dowa->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                Edit
                            </button>
                            <button wire:click="deleteDowa({{ $dowa->id }})" class="text-red-600 hover:text-red-900">
                                Delete
                            </button>
                            @else
                            <button wire:click="restoreDowa({{ $dowa->id }})" class="text-green-600 hover:text-green-900 mr-3">
                                Restore
                            </button>
                            <button wire:click="forceDeleteDowa({{ $dowa->id }})" class="text-red-600 hover:text-red-900">
                                Permanently Delete
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-200">
                            No {{ $viewType === 'trashed' ? 'trashed' : 'active' }} dowas found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($this->dowas->hasPages())
            <div class="px-6 py-3 bg-white border-t border-gray-200">
                {{ $this->dowas->links() }}
            </div>
            @endif
        </div>
        @endif

    </div>
</section>
