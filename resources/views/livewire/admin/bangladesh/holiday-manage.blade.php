<?php

use Livewire\Volt\Component;
use App\Models\Holiday;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate, On};

new class extends Component {
    use WithFileUploads, WithPagination;

    // Properties
    public $holidayId;
    public $viewType = 'active';
    public $search = '';

    // Form Fields
    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|date')]
    public $date;

    #[Validate('required')]
    public $type = '';

    #[Validate('nullable|string')]
    public $details = '';

    #[Validate('nullable|string')]
    public $tags = '';

    #[Validate('boolean')]
    public $is_featured = false;

    #[Validate('boolean')]
    public $status = true;

    // Media Property (Intro BD Style)
    public $images = [];

    // Options
    public $holidayTypes = ['National', 'Religious', 'International', 'Observance', 'Seasonal'];

    /**
     * Lifecycle & Logic
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function updatedViewType()
    {
        $this->resetPage();
    }

    #[Computed]
    public function rows()
    {
        return ($this->viewType === 'trashed' ? Holiday::onlyTrashed() : Holiday::query())
            ->when($this->search, function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('type', 'like', "%{$this->search}%");
            })
            ->orderBy('date', 'desc')
            ->paginate(10);
    }

    /**
     * Action Methods
     */
    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['holidayId', 'title', 'date', 'type', 'details', 'tags', 'is_featured', 'status', 'images']);
        $this->dispatch('modal-show', name: 'holiday-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $holiday = Holiday::withTrashed()->findOrFail($id);

        $this->holidayId = $holiday->id;
        $this->title = $holiday->title;
        $this->date = $holiday->date->format('Y-m-d');
        $this->type = $holiday->type;
        $this->details = $holiday->details;
        $this->tags = $holiday->tags;
        $this->is_featured = (bool) $holiday->is_featured;
        $this->status = (bool) $holiday->status;

        // Map Spatie Media (Intro BD Style)
        $this->images = $holiday->getMedia('holiday_images')->map(fn($m) => [
            'id' => $m->id,
            'url' => $m->getUrl('thumb') ?? $m->getUrl(),
            'is_existing' => true
        ])->toArray();

        $this->dispatch('modal-show', name: 'holiday-form');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file)
            return;

        if (is_array($file) && isset($file['is_existing'])) {
            $holiday = Holiday::withTrashed()->findOrFail($this->holidayId);
            $holiday->deleteMedia($file['id']);
        }

        // অ্যারে থেকে রিমুভ করা
        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function save()
    {
        $this->validate();

        $holiday = Holiday::updateOrCreate(['id' => $this->holidayId], [
            'title' => $this->title,
            'date' => $this->date,
            'type' => $this->type,
            'details' => $this->details,
            'tags' => $this->tags,
            'is_featured' => $this->is_featured,
            'status' => $this->status,
            'slug' => Str::slug($this->title),
            'user_id' => auth()->id(),
        ]);

        // Process Media
        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                if ($image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $holiday->addMedia($image->getRealPath())
                        ->usingFileName(Str::random(10) . '.' . $image->getClientOriginalExtension())
                        ->toMediaCollection('holiday_images');
                }
            }
        }

        $this->dispatch('modal-close', name: 'holiday-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'ছুটির তথ্য সংরক্ষিত হয়েছে।');
        $this->reset(['images', 'holidayId']);
    }

    public function delete($id)
    {
        Holiday::findOrFail($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'আইটেমটি ট্র্যাশে সরানো হয়েছে।');
    }

    public function restore($id)
    {
        Holiday::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'সফলভাবে রিস্টোর করা হয়েছে।');
    }

    public function forceDelete($id)
    {
        $holiday = Holiday::onlyTrashed()->findOrFail($id);
        $holiday->clearMediaCollection('holiday_images');
        $holiday->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'স্থায়ীভাবে মুছে ফেলা হয়েছে।');
    }
}; ?>

<div class="p-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Holiday Management</flux:heading>
            <flux:subheading>Manage national and religious holidays.</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">Add Holiday</flux:button>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search holidays..." icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->rows">
        <flux:table.columns>
            <flux:table.column>Media</flux:table.column>
            <flux:table.column>Title</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->rows as $row)
                <flux:table.row :key="$row->id">
                    <flux:table.cell>
                        @php $mediaItems = $row->getMedia('holiday_images'); @endphp
                        <flux:avatar.group>
                            @foreach($mediaItems->take(2) as $media)
                                <flux:avatar src="{{ $media->getUrl() }}" />
                            @endforeach
                            @if($mediaItems->count() > 2)
                                <flux:avatar initials="+{{ $mediaItems->count() - 2 }}" />
                            @endif
                        </flux:avatar.group>
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        {{ $row->title }}
                        @if($row->is_featured)
                        <flux:badge size="xs" color="purple" class="ml-1">Featured</flux:badge> @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $row->date->format('d M, Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">{{ $row->type }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        @if($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $row->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red" wire:confirm="Are you sure?"
                                wire:click="delete({{ $row->id }})" />
                        @else
                            <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                wire:click="restore({{ $row->id }})" />
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red" wire:confirm="Permanent delete?"
                                wire:click="forceDelete({{ $row->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-10 text-zinc-400">No holidays found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="holiday-form" class="md:w-[45rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $holidayId ? 'Edit Holiday' : 'Add New Holiday' }}</flux:heading>
                <flux:subheading>Fill in the details for the holiday event.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="title" label="Title" placeholder="Independence Day..." />
                <flux:input wire:model="date" type="date" label="Holiday Date" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model="type" label="Holiday Type">
                    <option value="">Select Type</option>
                    @foreach($holidayTypes as $t) <option value="{{ $t }}">{{ $t }}</option> @endforeach
                </flux:select>
                <flux:input wire:model="tags" label="Tags" placeholder="national, public, victory..." />
            </div>

            <div wire:ignore>
                <flux:editor wire:model="details" label="Description / Details" />
            </div>

            <div class="flex gap-4">
                <flux:checkbox wire:model="is_featured" label="Mark as Featured" />
                <flux:checkbox wire:model="status" label="Active Status" />
            </div>

            {{-- Media Upload (Intro BD Style) --}}
            <div>
                <flux:file-upload wire:model.live="images" multiple />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Holiday</flux:button>
            </div>
        </form>
    </flux:modal>
</div>