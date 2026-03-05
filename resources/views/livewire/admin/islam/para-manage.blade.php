<?php

use Livewire\Volt\Component;
use App\Models\Para;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate};

new class extends Component {
    use WithPagination;

    public $paraId;

    #[Validate('required|integer|min:1|max:30')]
    public $para_number;

    #[Validate('required|string')]
    public $name_arabic = '';

    #[Validate('required|string')]
    public $name_english = '';

    #[Validate('required|string')]
    public $name_bangla = '';

    public $is_active = true;
    public $search = '';
    public $viewType = 'active'; // আপনার সেভড ফরম্যাট অনুযায়ী

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    #[Computed]
    public function paras()
    {
        return ($this->viewType === 'trashed' ? Para::onlyTrashed() : Para::query())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name_bangla', 'like', '%' . $this->search . '%')
                        ->orWhere('name_english', 'like', '%' . $this->search . '%')
                        ->orWhere('para_number', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('para_number', 'asc')
            ->paginate(10);
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['paraId', 'para_number', 'name_arabic', 'name_english', 'name_bangla', 'is_active']);
        $this->dispatch('modal-show', name: 'para-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $para = Para::withTrashed()->findOrFail($id);

        $this->paraId = $para->id;
        $this->para_number = $para->para_number;
        $this->name_arabic = $para->name_arabic;
        $this->name_english = $para->name_english;
        $this->name_bangla = $para->name_bangla;
        $this->is_active = (bool) $para->is_active;

        $this->dispatch('modal-show', name: 'para-form');
    }

    public function save()
    {
        $this->validate([
            'para_number' => 'unique:paras,para_number,' . $this->paraId
        ]);

        $data = [
            'para_number' => $this->para_number,
            'name_arabic' => $this->name_arabic,
            'name_english' => $this->name_english,
            'name_bangla' => $this->name_bangla,
            'slug' => Str::slug($this->name_english),
            'is_active' => $this->is_active,
        ];

        Para::updateOrCreate(['id' => $this->paraId], $data);

        $this->dispatch('modal-close', name: 'para-form');
        $this->dispatch('toast', variant: 'success', text: 'পারা সফলভাবে সংরক্ষিত হয়েছে।');
    }

    // Soft Delete (Trash)
    public function delete($id)
    {
        $para = Para::find($id);
        $para->delete();
        $this->dispatch('toast', variant: 'warning', text: 'পারাটি ট্র্যাশে পাঠানো হয়েছে।');
    }

    // Restore from Trash
    public function restore($id)
    {
        $para = Para::onlyTrashed()->findOrFail($id);
        $para->restore();
        $this->dispatch('toast', variant: 'success', text: 'পারাটি রিস্টোর করা হয়েছে।');
    }

    // Permanent Delete
    public function forceDelete($id)
    {
        $para = Para::onlyTrashed()->findOrFail($id);
        $para->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'পারাটি স্থায়ীভাবে ডিলিট করা হয়েছে।');
    }
}; ?>

<div>
    {{-- Header Section with Toggle --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Para Management</flux:heading>
            <flux:subheading>Manage the 30 Paras of Al-Quran.</flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trashed" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">Create New</flux:button>
        </div>
    </div>

    {{-- Search Filter --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="পারা নম্বর বা নাম দিয়ে খুঁজুন..."
            icon="magnifying-glass" />
    </div>

    {{-- Table Section --}}
    <flux:table :paginate="$this->paras">
        <flux:table.columns>
            <flux:table.column>No</flux:table.column>
            <flux:table.column>Name (Bangla / Arabic)</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->paras as $para)
                <flux:table.row :key="$para->id">
                    <flux:table.cell class="font-bold">
                        {{ str_pad($para->para_number, 2, '0', STR_PAD_LEFT) }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex flex-col text-sm">
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $para->name_bangla }}</span>
                            <span class="text-xs text-zinc-500 font-arabic italic">{{ $para->name_arabic }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" :color="$para->is_active ? 'green' : 'red'" inset="top bottom">
                            {{ $para->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-1">
                            @if($viewType === 'active')
                                <flux:button variant="ghost" size="sm" icon="pencil-square"
                                    wire:click="showEditForm({{ $para->id }})" />
                                <flux:button variant="ghost" size="sm" icon="trash" color="red" wire:confirm="আর ইউ সিওর?"
                                    wire:click="delete({{ $para->id }})" />
                            @else
                                <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                    wire:click="restore({{ $para->id }})" />
                                <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                    wire:confirm="স্থায়ীভাবে মুছে ফেলতে চান?" wire:click="forceDelete({{ $para->id }})" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-10 text-zinc-400">
                        @if($viewType === 'active')
                            কোনো তথ্য পাওয়া যায়নি।
                        @else
                            তথ্য পাওয়া যায়নি।
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="para-form" class="md:w-[40rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $paraId ? 'Edit Para' : 'Add New Para' }}</flux:heading>
                <flux:subheading>পারা সংক্রান্ত তথ্যগুলো সঠিকভাবে পূরণ করুন।</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input type="number" wire:model="para_number" label="পারা নম্বর (১-৩০)" required />
                <flux:input wire:model="name_arabic" label="আরবি নাম" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="name_english" label="ইংরেজি নাম" required />
                <flux:input wire:model="name_bangla" label="বাংলা নাম" required />
            </div>

            <flux:checkbox wire:model="is_active" label="পাবলিশ করুন (Active)" />

            <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">বাতিল</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">সংরক্ষণ করুন</flux:button>
            </div>
        </form>
    </flux:modal>
</div>