<?php

use Livewire\Volt\Component;
use App\Models\EstablishmentBd;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate, On};

new class extends Component {
    use WithFileUploads, WithPagination;

    // Properties
    public $establishmentBdId;
    public $viewType = 'active';
    public $search = '';

    // Form Fields
    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|min:10')]
    public $description = '';

    #[Validate('required|exists:divisions,id')]
    public $division_id;

    #[Validate('required|exists:districts,id')]
    public $district_id;

    #[Validate('required|exists:thanas,id')]
    public $thana_id;

    #[Validate('boolean')]
    public $status = 1;

    #[Validate('boolean')]
    public $is_featured = false;

    // Media Property (Single array for consistency)
    public $images = [];

    // Dependable Data
    public $districts = [], $thanas = [];

    /**
     * Lifecycle & Logic Methods
     */
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

    /**
     * Computed Data
     */
    #[Computed]
    public function rows()
    {
        return ($this->viewType === 'trashed' ? EstablishmentBd::onlyTrashed() : EstablishmentBd::query())
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

    /**
     * Action Methods
     */
    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['establishmentBdId', 'title', 'description', 'division_id', 'district_id', 'thana_id', 'status', 'is_featured', 'images', 'districts', 'thanas']);
        $this->dispatch('modal-show', name: 'establishment-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $item = EstablishmentBd::withTrashed()->findOrFail($id);

        $this->establishmentBdId = $item->id;
        $this->title = $item->title;
        $this->description = $item->description;
        $this->division_id = $item->division_id;
        $this->district_id = $item->district_id;
        $this->thana_id = $item->thana_id;
        $this->status = (int) $item->status;
        $this->is_featured = (bool) $item->is_featured;

        // Load Dependent Dropdowns
        $this->districts = District::where('division_id', $this->division_id)->get();
        $this->thanas = Thana::where('district_id', $this->district_id)->get();

        // Map Spatie Media
        $this->images = $item->getMedia('establishment_images')->map(fn($m) => [
            'id' => $m->id,
            'url' => $m->getUrl('thumb') ?? $m->getUrl(),
            'is_existing' => true
        ])->toArray();

        $this->dispatch('modal-show', name: 'establishment-form');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file)
            return;

        if (is_array($file) && isset($file['is_existing'])) {
            $item = EstablishmentBd::withTrashed()->findOrFail($this->establishmentBdId);
            $item->deleteMedia($file['id']);
        }

        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function save()
    {
        $this->validate();

        $item = EstablishmentBd::updateOrCreate(['id' => $this->establishmentBdId], [
            'title' => $this->title,
            'description' => $this->description,
            'division_id' => $this->division_id,
            'district_id' => $this->district_id,
            'thana_id' => $this->thana_id,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'user_id' => auth()->id(),
            'slug' => Str::slug($this->title),
        ]);

        // Process Spatie Media
        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                if ($image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $item->addMedia($image->getRealPath())
                        ->usingFileName(Str::random(10) . '.' . $image->getClientOriginalExtension())
                        ->toMediaCollection('establishment_images');
                }
            }
        }

        $this->dispatch('modal-close', name: 'establishment-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'তথ্যটি সফলভাবে সংরক্ষিত হয়েছে।');
        $this->reset(['images', 'establishmentBdId']);
    }

    public function delete($id)
    {
        EstablishmentBd::findOrFail($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'আইটেমটি ট্র্যাশে সরানো হয়েছে।');
    }

    public function restore($id)
    {
        EstablishmentBd::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'সফলভাবে রিস্টোর করা হয়েছে।');
    }

    public function forceDelete($id)
    {
        $item = EstablishmentBd::onlyTrashed()->findOrFail($id);
        $item->clearMediaCollection('establishment_images');
        $item->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'স্থায়ীভাবে মুছে ফেলা হয়েছে।');
    }
}; ?>

<div class="p-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Establishment BD</flux:heading>
            <flux:subheading>Manage establishments and locations across Bangladesh.</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">Create New</flux:button>
        </div>
    </div>

    {{-- Filter --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by title..." icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->rows">
        <flux:table.columns>
            <flux:table.column>Media</flux:table.column>
            <flux:table.column sortable>Title</flux:table.column>
            <flux:table.column>Location</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->rows as $row)
                <flux:table.row :key="$row->id">
                    <flux:table.cell>
                        @php $mediaItems = $row->getMedia('establishment_images'); @endphp
                        <flux:avatar.group>
                            @foreach($mediaItems->take(3) as $media)
                                <flux:avatar src="{{ $media->getUrl() }}" />
                            @endforeach
                            @if($mediaItems->count() > 3)
                                <flux:avatar initials="+{{ $mediaItems->count() - 3 }}" />
                            @endif
                        </flux:avatar.group>
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">{{ $row->title }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">{{ $row->district?->name }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$row->status ? 'green' : 'red'" variant="subtle">
                            {{ $row->status ? 'Active' : 'Inactive' }}
                        </flux:badge>
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
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red" wire:confirm="Delete permanently?"
                                wire:click="forceDelete({{ $row->id }})" />
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
    <flux:modal name="establishment-form" class="md:w-[50rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $establishmentBdId ? 'Edit Establishment' : 'Add New Establishment' }}
                </flux:heading>
                <flux:subheading>Provide details about the establishment and its location.</flux:subheading>
            </div>

            <flux:input wire:model="title" label="Title" placeholder="Establishment name..." />

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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model="status" label="Status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </flux:select>
                <div class="flex items-center pt-8">
                    <flux:checkbox wire:model="is_featured" label="Mark as Featured" />
                </div>
            </div>

            {{-- Media Section --}}
            <div>
                <flux:file-upload wire:model.live="images" multiple />
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