<?php

use Livewire\Volt\Component;
use App\Models\Para;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;

    // Form fields
    public $paraId;
    public $para_number;
    public $name_arabic = '';
    public $name_english = '';
    public $name_bangla = '';
    public $is_active = true;

    // UI states
    public $showForm = false;
    public $formType = 'create';
    public $search = '';

    // Pagination / Sorting
    public $perPage = 10;
    public $sortField = 'para_number';
    public $sortDirection = 'asc';

    public function getParasProperty()
    {
        return Para::query()
            ->when($this->search, function ($query) {
                $query->where('name_bangla', 'like', '%' . $this->search . '%')
                    ->orWhere('name_english', 'like', '%' . $this->search . '%')
                    ->orWhere('para_number', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function sortBy($field)
    {
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc') ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->formType = 'create';
        $this->showForm = true;
    }

    public function showEditForm(Para $para)
    {
        $this->paraId = $para->id;
        $this->para_number = $para->para_number;
        $this->name_arabic = $para->name_arabic;
        $this->name_english = $para->name_english;
        $this->name_bangla = $para->name_bangla;
        $this->is_active = $para->is_active;

        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset(['paraId', 'para_number', 'name_arabic', 'name_english', 'name_bangla', 'is_active']);
        $this->resetErrorBag();
    }

    public function save()
    {
        $validated = $this->validate([
            'para_number' => 'required|integer|unique:paras,para_number,' . $this->paraId,
            'name_arabic' => 'required|string',
            'name_english' => 'required|string',
            'name_bangla' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($this->name_english);

        if ($this->formType === 'edit') {
            Para::find($this->paraId)->update($validated);
            $message = 'Para updated successfully!';
        } else {
            Para::create($validated);
            $message = 'Para created successfully!';
        }

        $this->showForm = false;
        $this->resetForm();
        Flux::toast($message);
    }

    public function deletePara(Para $para)
    {
        $para->delete();
        Flux::toast('Para deleted successfully!');
    }
}; ?>

<section>
    <div class="flex flex-col space-y-6">
        @if ($showForm)
            <div class="">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">{{ $formType === 'create' ? 'নতুন পারা যুক্ত করুন' : 'পারা এডিট করুন' }}
                    </h3>
                    <flux:button wire:click="$set('showForm', false)" variant="ghost" size="sm">Back</flux:button>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input type="number" wire:model="para_number" label="পারা নম্বর (১-৩০)" />
                        <flux:input type="text" wire:model="name_arabic" label="আরবি নাম" />
                        <flux:input type="text" wire:model="name_english" label="ইংরেজি নাম" />
                        <flux:input type="text" wire:model="name_bangla" label="বাংলা নাম" />
                    </div>

                    <div class="flex items-center space-x-4">
                        <flux:checkbox wire:model="is_active" label="Active" />
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <flux:button type="button" wire:click="$set('showForm', false)">Cancel</flux:button>
                        <flux:button type="submit" variant="primary">
                            {{ $formType === 'create' ? 'Save Para' : 'Update Para' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @else
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">Para Management</h2>
                <flux:button wire:click="showCreateForm" variant="primary">Create New Para</flux:button>
            </div>

            <flux:input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or number..." />

            <flux:table>
                <flux:table.columns>
                    <flux:table.column wire:click="sortBy('para_number')" :sortable="true"
                        :direction="$sortField === 'para_number' ? $sortDirection : null">
                        No
                    </flux:table.column>

                    <flux:table.column>Name (BN/AR)</flux:table.column>

                    <flux:table.column>Status</flux:table.column>

                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->paras as $para)
                        <flux:table.row :key="$para->id">
                            <flux:table.cell class="font-bold text-gray-900">
                                {{ $para->para_number }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ $para->name_bangla }}</span>
                                    <span class="text-xs text-gray-500 font-arabic">{{ $para->name_arabic }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$para->is_active ? 'green' : 'red'" inset="top">
                                    {{ $para->is_active ? 'Active' : 'Inactive' }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:button wire:click="showEditForm({{ $para->id }})" variant="ghost" size="sm"
                                        icon="pencil-square" />
                                    <flux:button wire:confirm="আয়াতগুলোসহ এই পারাটি মুছে ফেলতে চান?"
                                        wire:click="deletePara({{ $para->id }})" variant="ghost" size="sm" color="red"
                                        icon="trash" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center py-10 text-gray-400">
                                কোনো ডাটা পাওয়া যায়নি।
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $this->paras->links() }}
            </div>
        @endif
    </div>
</section>