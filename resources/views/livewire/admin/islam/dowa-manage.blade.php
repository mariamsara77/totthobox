<?php

use Livewire\Volt\Component;
use App\Models\Dowa;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\{Computed, Validate, On};

new class extends Component {
    use WithFileUploads, WithPagination;

    public $dowaId;

    #[Validate('required|min:3')]
    public $bangla_name = '';

    #[Validate('nullable|string')]
    public $arabic_name = '';

    #[Validate('nullable|string')]
    public $arabic_text = '';

    #[Validate('required|string')]
    public $bangla_text = '';

    #[Validate('nullable|string')]
    public $bangla_meaning = '';

    #[Validate('nullable|string')]
    public $bangla_fojilot = '';

    #[Validate('boolean')]
    public $is_active = true;

    #[Validate('boolean')]
    public $is_featured = false;

    // অডিও হ্যান্ডলিং
    public $audio;
    public $existingAudio;

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
    public function dowas()
    {
        return ($this->viewType === 'trashed' ? Dowa::onlyTrashed() : Dowa::query())
            ->with(['creator'])
            ->when($this->search, function ($q) {
                $q->where('bangla_name', 'like', "%{$this->search}%")
                    ->orWhere('arabic_name', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['dowaId', 'bangla_name', 'arabic_name', 'arabic_text', 'bangla_text', 'bangla_meaning', 'bangla_fojilot', 'is_active', 'is_featured', 'audio', 'existingAudio']);
        $this->dispatch('modal-show', name: 'dowa-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $item = Dowa::withTrashed()->findOrFail($id);

        $this->dowaId = $item->id;
        $this->bangla_name = $item->bangla_name;
        $this->arabic_name = $item->arabic_name;
        $this->arabic_text = $item->arabic_text;
        $this->bangla_text = $item->bangla_text;
        $this->bangla_meaning = $item->bangla_meaning;
        $this->bangla_fojilot = $item->bangla_fojilot;
        $this->is_active = (bool) $item->is_active;
        $this->is_featured = (bool) $item->is_featured;
        $this->existingAudio = $item->audio ? Storage::url($item->audio) : null;

        $this->dispatch('modal-show', name: 'dowa-form');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'bangla_name' => $this->bangla_name,
            'slug' => Str::slug($this->bangla_name),
            'arabic_name' => $this->arabic_name,
            'arabic_text' => $this->arabic_text,
            'bangla_text' => $this->bangla_text,
            'bangla_meaning' => $this->bangla_meaning,
            'bangla_fojilot' => $this->bangla_fojilot,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'updated_by' => auth()->id(),
        ];

        if (!$this->dowaId) {
            $data['created_by'] = auth()->id();
            $data['user_id'] = auth()->id();
        }

        if ($this->audio) {
            if ($this->existingAudio) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $this->existingAudio));
            }
            $data['audio'] = $this->audio->store('dowa-audios', 'public');
        }

        Dowa::updateOrCreate(['id' => $this->dowaId], $data);

        $this->dispatch('modal-close', name: 'dowa-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'দোয়াটি সংরক্ষিত হয়েছে।');
        $this->reset(['audio', 'dowaId']);
    }

    public function delete($id)
    {
        $item = Dowa::find($id);
        $item->deleted_by = auth()->id();
        $item->save();
        $item->delete();
        $this->dispatch('toast', variant: 'warning', text: 'আইটেমটি ট্র্যাশে পাঠানো হয়েছে।');
    }

    public function restore($id)
    {
        $item = Dowa::onlyTrashed()->findOrFail($id);
        $item->restore();
        $item->deleted_by = null;
        $item->save();
        $this->dispatch('toast', variant: 'success', text: 'আইটেমটি রিস্টোর করা হয়েছে।');
    }

    public function forceDelete($id)
    {
        $item = Dowa::onlyTrashed()->findOrFail($id);
        if ($item->audio) {
            Storage::disk('public')->delete($item->audio);
        }
        $item->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'আইটেমটি স্থায়ীভাবে ডিলিট করা হয়েছে।');
    }
}; ?>

<div>
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Dowa Management</flux:heading>
            <flux:subheading>Manage your supplications (Dowa) database.</flux:subheading>
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
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by name..." icon="magnifying-glass" />
    </div>

    {{-- Table Section --}}
    <flux:table :paginate="$this->dowas">
        <flux:table.columns>
            <flux:table.column>Bangla Name</flux:table.column>
            <flux:table.column>Arabic Name</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->dowas as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell class="font-medium">
                        <div class="flex flex-col">
                            <span>{{ $item->bangla_name }}</span>
                            @if($item->is_featured)
                                <span class="text-[10px] text-amber-600 font-bold uppercase tracking-tighter">Featured</span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 italic">{{ $item->arabic_name ?: 'N/A' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$item->is_active ? 'green' : 'red'" inset="top bottom">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        @if($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="showEditForm({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red" wire:confirm="Are you sure?" wire:click="delete({{ $item->id }})" />
                        @else
                            <flux:button variant="ghost" size="sm" icon="arrow-path" color="green" wire:click="restore({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red" wire:confirm="Permanent delete?" wire:click="forceDelete({{ $item->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-10 text-zinc-400">No records found.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="dowa-form" class="md:w-[50rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $dowaId ? 'Edit Dowa' : 'Add New Dowa' }}</flux:heading>
                <flux:subheading>Update supplication details, text, and audio.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="bangla_name" label="Bangla Name" required />
                <flux:input wire:model="arabic_name" label="Arabic Name" />
            </div>

            <flux:textarea wire:model="arabic_text" label="Arabic Text (Original)" rows="3" class="font-arabic text-right" dir="rtl" />
            
            <flux:textarea wire:model="bangla_text" label="Bangla Transliteration" rows="2" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:textarea wire:model="bangla_meaning" label="Bangla Meaning" rows="3" />
                <flux:textarea wire:model="bangla_fojilot" label="Fojilot / Benefits" rows="3" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:checkbox wire:model="is_active" label="Active" />
                <flux:checkbox wire:model="is_featured" label="Featured" />
            </div>

            {{-- Audio Upload --}}
            <div class="space-y-2 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                <flux:label>Audio Recitation</flux:label>
                @if($existingAudio)
                    <audio controls class="h-8 mb-2 w-full">
                        <source src="{{ $existingAudio }}" type="audio/mpeg">
                    </audio>
                @endif
                <flux:input type="file" wire:model="audio" accept="audio/*" />
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