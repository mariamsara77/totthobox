<?php

use Livewire\Volt\Component;
use App\Models\Dowa;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\{Computed, Validate};
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    public $dowaId;
    public $viewType = 'active';
    public $search = '';
    public $audioFile;

    #[Validate('required|string|max:255')]
    public $bangla_name = '';

    #[Validate('nullable|string')]
    public $arabic_name;
    #[Validate('nullable|string')]
    public $slug;
    #[Validate('nullable|string')]
    public $arabic_text = '';
    #[Validate('nullable|string')]
    public $bangla_text = '';
    #[Validate('nullable|string')]
    public $bangla_meaning = '';
    #[Validate('nullable|string')]
    public $bangla_fojilot = '';
    #[Validate('nullable|string')]
    public $others = '';
    #[Validate('nullable|string')]
    public $type = '';

    #[Validate('boolean')]
    public $is_featured = false;

    public array $activities = [];

    #[Computed]
    public function dowas()
    {
        return ($this->viewType === 'trashed' ? Dowa::onlyTrashed() : Dowa::query())->when($this->search, fn($q) => $q->where('bangla_name', 'like', "%{$this->search}%"))->latest()->paginate(10);
    }

    public function save()
    {
        $this->validate();

        $dowa = Dowa::updateOrCreate(
            ['id' => $this->dowaId],
            [
                'bangla_name' => $this->bangla_name,
                'arabic_name' => $this->arabic_name,
                'arabic_text' => $this->arabic_text,
                'bangla_text' => $this->bangla_text,
                'bangla_meaning' => $this->bangla_meaning,
                'bangla_fojilot' => $this->bangla_fojilot,
                'others' => $this->others,
                'type' => $this->type,
                'slug' => $this->slug ?? Str::slug($this->bangla_name),
                'status' => 1,
                'is_featured' => $this->is_featured,
            ],
        );

        if ($this->audioFile) {
            $dowa->addMedia($this->audioFile)->toMediaCollection('audio');
        }

        $this->dispatch('modal-close', name: 'dowa-form');
        $this->reset(['dowaId', 'bangla_name', 'arabic_name', 'audioFile']);
    }

    public function edit($id)
    {
        $dowa = Dowa::findOrFail($id);
        $this->fill($dowa->toArray());
        $this->dowaId = $dowa->id;
        $this->dispatch('modal-show', name: 'dowa-form');
    }

    public function viewLogs($id)
    {
        $this->activities = Activity::where('subject_id', $id)->where('subject_type', Dowa::class)->latest()->get()->toArray();
        $this->dispatch('modal-show', name: 'activity-logs');
    }

    public function delete($id)
    {
        Dowa::find($id)->delete();
    }
}; ?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">Dowa Management</flux:heading>
        <flux:button wire:click="$dispatch('modal-show', { name: 'dowa-form' })" icon="plus" variant="primary">Create
            New</flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Bangla Name</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->dowas as $item)
                <flux:table.row>
                    <flux:table.cell>{{ $item->bangla_name }}</flux:table.cell>
                    <flux:table.cell>{{ $item->type }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button variant="ghost" icon="clock" wire:click="viewLogs({{ $item->id }})" />
                        <flux:button variant="ghost" icon="pencil-square" wire:click="edit({{ $item->id }})" />
                        <flux:button variant="ghost" icon="trash" color="red"
                            wire:click="delete({{ $item->id }})" />
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Dowa Modal --}}
    <flux:modal name="dowa-form" class="md:w-180">
        <form wire:submit="save" class="space-y-4 p-6">
            <flux:input wire:model="bangla_name" label="Bangla Name" />
            <flux:input wire:model="slug" label="Slug" />
            <flux:input wire:model="arabic_name" label="Arabic Name" />
            <flux:input wire:model="type" label="Type" />
            <flux:textarea wire:model="arabic_text" label="Arabic Text" />
            <flux:textarea wire:model="bangla_text" label="Bangla Pronunciation" />
            <flux:textarea wire:model="bangla_meaning" label="Bangla Meaning" />
            <flux:file-upload wire:model="audioFile" label="Audio File" />
            <flux:checkbox wire:model="is_featured" label="Is Featured" />
            <flux:button type="submit" variant="primary">Save</flux:button>
        </form>
    </flux:modal>
</div>
