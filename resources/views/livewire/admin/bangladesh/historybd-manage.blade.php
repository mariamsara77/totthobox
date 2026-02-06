<?php

use Livewire\Volt\Component;
use App\Models\HistoryBd;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate, On};

new class extends Component {
    use WithFileUploads, WithPagination;

    public $historyBdId;

    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|min:10')]
    public $description = '';

    #[Validate('nullable|exists:divisions,id')]
    public $division_id;

    #[Validate('nullable|exists:districts,id')]
    public $district_id;

    #[Validate('nullable|exists:thanas,id')]
    public $thana_id;

    #[Validate('boolean')]
    public $is_featured = false;

    // ইমেজ সংরক্ষণের জন্য একক অ্যারে (Standard)
    public $images = [];

    public $viewType = 'active';
    public $search = '';

    // চাইল্ড ডেটা স্টোরেজ
    public $districts = [], $thanas = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    public function updatedDivisionId($value)
    {
        $this->districts = District::where('division_id', $value)->get();
        $this->reset(['district_id', 'thana_id', 'thanas']);
    }

    public function updatedDistrictId($value)
    {
        $this->thanas = Thana::where('district_id', $value)->get();
        $this->reset('thana_id');
    }

    #[Computed]
    public function historyBds()
    {
        return ($this->viewType === 'trashed' ? HistoryBd::onlyTrashed() : HistoryBd::query())
            ->with(['division', 'district', 'thana'])
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'divisions' => Division::all()
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['historyBdId', 'title', 'description', 'division_id', 'district_id', 'thana_id', 'is_featured', 'images', 'districts', 'thanas']);
        $this->dispatch('modal-show', name: 'history-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $history = HistoryBd::withTrashed()->findOrFail($id);
        $this->historyBdId = $history->id;
        $this->title = $history->title;
        $this->description = $history->description;
        $this->division_id = $history->division_id;
        $this->district_id = $history->district_id;
        $this->thana_id = $history->thana_id;
        $this->is_featured = (bool) $history->is_featured;

        // লোড ডিপেন্ডেন্ট ডেটা
        if ($this->division_id)
            $this->districts = District::where('division_id', $this->division_id)->get();
        if ($this->district_id)
            $this->thanas = Thana::where('district_id', $this->district_id)->get();

        // Standard Media Mapping
        $this->images = $history->getMedia('images')->map(fn($m) => [
            'id' => $m->id,
            'url' => $m->getUrl('thumb') ?? $m->getUrl(),
            'is_existing' => true
        ])->toArray();

        $this->dispatch('modal-show', name: 'history-form');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;
        if (!$file)
            return;

        if (is_array($file) && isset($file['is_existing'])) {
            $history = HistoryBd::withTrashed()->findOrFail($this->historyBdId);
            $history->deleteMedia($file['id']);
        }

        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function save()
    {
        $this->validate();

        $history = HistoryBd::updateOrCreate(['id' => $this->historyBdId], [
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'description' => $this->description,
            'division_id' => $this->division_id,
            'district_id' => $this->district_id,
            'thana_id' => $this->thana_id,
            'is_featured' => $this->is_featured,
            'user_id' => auth()->id(),
        ]);

        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                if ($image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $history->addMedia($image->getRealPath())
                        ->usingFileName(Str::random(10) . '.' . $image->getClientOriginalExtension())
                        ->toMediaCollection('images');
                }
            }
        }

        $this->dispatch('modal-close', name: 'history-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'তথ্যটি সংরক্ষিত হয়েছে।');
        $this->reset(['images', 'historyBdId']);
    }

    #[On('file-uploaded')]
    public function handleFileUpload($fileInfo)
    {
        $file = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($fileInfo);
        $this->images[] = $file;
    }

    public function delete($id)
    {
        HistoryBd::find($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'Item moved to trash.');
    }

    public function restore($id)
    {
        HistoryBd::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'Item restored successfully.');
    }

    public function forceDelete($id)
    {
        $history = HistoryBd::onlyTrashed()->findOrFail($id);
        $history->clearMediaCollection('images');
        $history->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'Item deleted permanently.');
    }
}; ?>

<div class="p-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">History BD Management</flux:heading>
            <flux:subheading>Manage historical information of Bangladesh.</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">Create New</flux:button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by title..." icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->historyBds">
        <flux:table.columns>
            <flux:table.column>Media</flux:table.column>
            <flux:table.column sortable>Title</flux:table.column>
            <flux:table.column>Location</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->historyBds as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>
                        <flux:avatar.group>
                            @foreach($item->getMedia('images')->take(3) as $media)
                                <flux:avatar src="{{ $media->getUrl('thumb') }}" />
                            @endforeach
                            @if($item->getMedia('images')->count() > 3)
                                <flux:avatar initials="+{{ $item->getMedia('images')->count() - 3 }}" />
                            @endif
                        </flux:avatar.group>
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">{{ $item->title }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">
                            {{ $item->division?->name }} → {{ $item->district?->name }}
                        </flux:badge>
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
                    <flux:table.cell colspan="4" class="text-center py-10 text-zinc-400">No records found.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="history-form" class="md:w-[50rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $historyBdId ? 'Edit Information' : 'Add New Information' }}</flux:heading>
                <flux:subheading>Fill in the details for the historical record.</flux:subheading>
            </div>

            <flux:input wire:model="title" label="Title" placeholder="Enter title..." />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select wire:model.live="division_id" label="Division">
                    <option value="">Select Division</option>
                    @foreach($divisions as $div) <option value="{{ $div->id }}">{{ $div->name }}</option> @endforeach
                </flux:select>

                <flux:select wire:model.live="district_id" label="District">
                    <option value="">Select District</option>
                    @foreach($districts as $dis) <option value="{{ $dis->id }}">{{ $dis->name }}</option> @endforeach
                </flux:select>

                <flux:select wire:model="thana_id" label="Thana">
                    <option value="">Select Thana</option>
                    @foreach($thanas as $tha) <option value="{{ $tha->id }}">{{ $tha->name }}</option> @endforeach
                </flux:select>
            </div>

            <div wire:ignore>
                <flux:editor wire:model="description" label="Detailed Description" />
            </div>

            <div class="flex items-center gap-4">
                <flux:checkbox wire:model="is_featured" label="Show as Featured" />
            </div>

            {{-- Standard Media Upload Component --}}
            <flux:file-upload wire:model.live="images" multiple />

            <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    Save Changes
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>