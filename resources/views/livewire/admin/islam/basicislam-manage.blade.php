<?php

use Livewire\Volt\Component;
use App\Models\BasicIslam;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\{Computed, Validate, On};

new class extends Component {
    use WithFileUploads, WithPagination;

    public $basicIslamId;

    #[Validate('required|min:3')]
    public $title = '';

    #[Validate('required|min:10')]
    public $description = '';

    #[Validate('boolean')]
    public $is_active = true;

    #[Validate('boolean')]
    public $is_featured = false;

    // ইমেজ হ্যান্ডলিং
    // public $image;
    // public $existingImage;

    public $viewType = 'active';
    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    #[Computed]
    public function basicIslams()
    {
        return ($this->viewType === 'trashed' ? BasicIslam::onlyTrashed() : BasicIslam::query())
            ->with(['creator'])
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['basicIslamId', 'title', 'description', 'is_active', 'is_featured']);
        $this->dispatch('modal-show', name: 'basic-islam-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $item = BasicIslam::withTrashed()->findOrFail($id);

        $this->basicIslamId = $item->id;
        $this->title = $item->title;
        $this->description = $item->description;
        $this->is_active = (bool) $item->is_active;
        $this->is_featured = (bool) $item->is_featured;
        // $this->existingImage = $item->image ? Storage::url($item->image) : null;

        $this->dispatch('modal-show', name: 'basic-islam-form');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'description' => $this->description,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'updated_by' => auth()->id(),
        ];

        if (!$this->basicIslamId) {
            $data['created_by'] = auth()->id();
            $data['user_id'] = auth()->id();
        }

        // if ($this->image) {
        //     // পুরাতন ইমেজ ডিলিট (এডিট মোডে)
        //     if ($this->existingImage) {
        //         Storage::disk('public')->delete(str_replace('/storage/', '', $this->existingImage));
        //     }
        //     $data['image'] = $this->image->store('basic-islam', 'public');
        // }

        BasicIslam::updateOrCreate(['id' => $this->basicIslamId], $data);

        $this->dispatch('modal-close', name: 'basic-islam-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'তথ্যটি সংরক্ষিত হয়েছে।');
        $this->reset(['basicIslamId']);
    }

    public function delete($id)
    {
        $item = BasicIslam::find($id);
        $item->deleted_by = auth()->id();
        $item->save();
        $item->delete();
        $this->dispatch('toast', variant: 'warning', text: 'আইটেমটি ট্র্যাশে পাঠানো হয়েছে।');
    }

    public function restore($id)
    {
        $item = BasicIslam::onlyTrashed()->findOrFail($id);
        $item->restore();
        $item->deleted_by = null;
        $item->save();
        $this->dispatch('toast', variant: 'success', text: 'আইটেমটি রিস্টোর করা হয়েছে।');
    }

    public function forceDelete($id)
    {
        $item = BasicIslam::onlyTrashed()->findOrFail($id);
        // if ($item->image) {
        //     Storage::disk('public')->delete($item->image);
        // }
        $item->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'আইটেমটি স্থায়ীভাবে ডিলিট করা হয়েছে।');
    }
}; ?>

<div>
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Basic Islam Management</flux:heading>
            <flux:subheading>Manage your islamic basic contents from here.</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">Create New</flux:button>
        </div>
    </div>

    {{-- Search Filter --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by title..." icon="magnifying-glass" />
    </div>

    {{-- Table Section --}}
    <flux:table :paginate="$this->basicIslams">
        <flux:table.columns>
            {{-- <flux:table.column>Media</flux:table.column> --}}
            <flux:table.column>Title</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Creator</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->basicIslams as $item)
                <flux:table.row :key="$item->id">
                    {{-- <flux:table.cell>
                        @if($item->image)
                        <flux:avatar src="{{ Storage::url($item->image) }}" />
                        @else
                        <flux:icon.photo class="text-zinc-400" />
                        @endif
                    </flux:table.cell> --}}
                    <flux:table.cell class="font-medium">
                        <div class="flex flex-col">
                            <span>{{ $item->title }}</span>
                            @if($item->is_featured)
                                <span class="text-[10px] text-amber-600 font-bold uppercase">Featured</span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$item->is_active ? 'green' : 'red'" inset="top bottom">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $item->creator?->name ?: 'Unknown' }}
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        @if($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red" wire:confirm="Are you sure?"
                                wire:click="delete({{ $item->id }})" />
                        @else
                            <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                wire:click="restore({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red" wire:confirm="Permanent delete?"
                                wire:click="forceDelete({{ $item->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-10 text-zinc-400">No records found.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="basic-islam-form" class="md:w-[45rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $basicIslamId ? 'Edit Content' : 'Add New Content' }}</flux:heading>
                <flux:subheading>Fill in the details for basic islam section.</flux:subheading>
            </div>

            <flux:input wire:model="title" label="Title" placeholder="Enter title..." />

            <div class="grid grid-cols-2 gap-4">
                <flux:checkbox wire:model="is_active" label="Mark as Active" />
                <flux:checkbox wire:model="is_featured" label="Mark as Featured" />
            </div>

            <div wire:ignore>
                <flux:editor wire:model="description" label="Description" />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
            </div>
        </form>
    </flux:modal>
</div>