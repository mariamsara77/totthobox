<?php

use Livewire\Volt\Component;
use App\Models\TourismBd;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate, On};

new class extends Component {
    use WithFileUploads, WithPagination;

    // Collections
    public $divisions = [];

    // Form Fields
    public $tourismBdId;

    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|min:10')]
    public $description = '';

    #[Validate('required|exists:divisions,id')]
    public $division_id = '';

    #[Validate('nullable|exists:districts,id')]
    public $district_id = '';

    #[Validate('nullable|exists:thanas,id')]
    public $thana_id = '';

    #[Validate('boolean')]
    public $is_featured = false;

    #[Validate('required|in:1,0')]
    public $status = 1;

    // SEO Fields
    public $meta_title = '';
    public $meta_description = '';
    public $meta_keywords = '';

    // Media
    public $images = [];
    public $map;

    // UI State
    public $districts = [];
    public $thanas = [];
    public $viewType = 'active';
    public $search = '';

    public function mount()
    {
        $this->divisions = Division::select('id', 'name')->get();
    }

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
        $this->districts = $value ? District::where('division_id', $value)->select('id', 'name')->get() : [];
        $this->reset(['district_id', 'thana_id']);
        $this->thanas = [];
    }

    public function updatedDistrictId($value)
    {
        $this->thanas = $value ? Thana::where('district_id', $value)->select('id', 'name')->get() : [];
        $this->thana_id = null;
    }

    #[Computed]
    public function tourismBds()
    {
        return ($this->viewType === 'trashed' ? TourismBd::onlyTrashed() : TourismBd::query())
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->with(['division', 'district', 'thana'])
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'divisions' => $this->divisions,
            'districts' => $this->districts,
            'thanas' => $this->thanas
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset([
            'tourismBdId',
            'title',
            'description',
            'division_id',
            'district_id',
            'thana_id',
            'is_featured',
            'status',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'images',
            'map'
        ]);
        $this->districts = [];
        $this->thanas = [];
        $this->dispatch('modal-show', name: 'tourism-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $tourism = TourismBd::withTrashed()->findOrFail($id);

        $this->tourismBdId = $tourism->id;
        $this->title = $tourism->title;
        $this->description = $tourism->description;
        $this->division_id = $tourism->division_id;
        $this->district_id = $tourism->district_id;
        $this->thana_id = $tourism->thana_id;
        $this->is_featured = (bool) $tourism->is_featured;
        $this->status = $tourism->status;
        $this->meta_title = $tourism->meta_title;
        $this->meta_description = $tourism->meta_description;
        $this->meta_keywords = $tourism->meta_keywords;

        // Load dependent dropdowns
        $this->districts = District::where('division_id', $this->division_id)->get();
        $this->thanas = Thana::where('district_id', $this->district_id)->get();

        // Load existing media
        $this->images = $tourism->getMedia('tourism_images')->map(fn($m) => [
            'id' => $m->id,
            'url' => $m->getUrl(),
            'is_existing' => true
        ])->toArray();

        $this->dispatch('modal-show', name: 'tourism-form');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file)
            return;

        // If it's an existing database image, delete from media library
        if (is_array($file) && isset($file['is_existing'])) {
            $tourism = TourismBd::withTrashed()->findOrFail($this->tourismBdId);
            $tourism->deleteMedia($file['id']);
        }

        // Remove from array
        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:10',
            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'thana_id' => 'nullable|exists:thanas,id',
            'is_featured' => 'boolean',
            'status' => 'required|in:1,0',
        ]);

        $tourism = TourismBd::updateOrCreate(['id' => $this->tourismBdId], [
            'title' => $this->title,
            'description' => $this->description,
            'slug' => Str::slug($this->title),
            'division_id' => $this->division_id,
            'district_id' => $this->district_id,
            'thana_id' => $this->thana_id,
            'is_featured' => $this->is_featured,
            'status' => $this->status,
            'meta_title' => $this->meta_title ?: $this->title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'user_id' => auth()->id(),
        ]);

        // Save images
        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                if ($image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $tourism->addMedia($image->getRealPath())
                        ->usingFileName(Str::random(10) . '.' . $image->getClientOriginalExtension())
                        ->toMediaCollection('tourism_images');
                }
            }
        }

        // Save map if provided
        if ($this->map instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $tourism->addMedia($this->map->getRealPath())
                ->usingFileName(Str::random(10) . '.' . $this->map->getClientOriginalExtension())
                ->toMediaCollection('tourism_maps');
        }

        $this->dispatch('modal-close', name: 'tourism-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'তথ্যটি সংরক্ষিত হয়েছে।');
        $this->reset(['images', 'map', 'tourismBdId']);
    }

    #[On('file-uploaded')]
    public function handleFileUpload($fileInfo)
    {
        $file = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($fileInfo);
        $this->images[] = $file;
    }

    #[On('map-uploaded')]
    public function handleMapUpload($fileInfo)
    {
        $this->map = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($fileInfo);
    }

    public function delete($id)
    {
        TourismBd::find($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'Item moved to trash.');
    }

    public function restore($id)
    {
        TourismBd::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'Item restored successfully.');
    }

    public function forceDelete($id)
    {
        $tourism = TourismBd::onlyTrashed()->findOrFail($id);
        $tourism->clearMediaCollection('tourism_images');
        $tourism->clearMediaCollection('tourism_maps');
        $tourism->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'Item deleted permanently.');
    }
}; ?>

<div class="p-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Tourism BD Management</flux:heading>
            <flux:subheading>Manage tourism destinations in Bangladesh.</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">Create New
            </flux:button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by title..." icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->tourismBds">
        <flux:table.columns>
            <flux:table.column>Image</flux:table.column>
            <flux:table.column sortable>Title</flux:table.column>
            <flux:table.column>Location</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->tourismBds as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>
                           @php 
                            $images = $item->getMedia('tourism_images'); 
                        @endphp

                        <flux:avatar.group>
                                @foreach($images->take(3) as $media)
                                    <flux:avatar src="{{ $media->getUrl() }}" />
                                @endforeach
                                @if($images->count() > 3)
                                    <flux:avatar initials="+{{ $images->count() - 3 }}" />
                                @endif
                        </flux:avatar.group>
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        <div>{{ $item->title }}</div>
                        <div class="text-xs text-zinc-500">{{ $item->status ? 'Published' : 'Draft' }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm">{{ $item->district->name ?? 'N/A' }}</div>
                        <div class="text-xs text-zinc-500">{{ $item->division->name ?? '' }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($item->is_featured)
                            <flux:badge size="sm" color="amber">Featured</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">Standard</flux:badge>
                        @endif
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
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                wire:confirm="This will be deleted permanently!" wire:click="forceDelete({{ $item->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-10 text-zinc-400">No records found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="tourism-form" class="md:w-[60rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $tourismBdId ? 'Edit Destination' : 'Add New Destination' }}</flux:heading>
                <flux:subheading>Manage tourism spot details and location information.</flux:subheading>
            </div>

            <flux:input wire:model="title" label="Destination Title" placeholder="Enter destination title..." />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select wire:model.live="division_id" label="Division" placeholder="Select Division">
                    <option value="">Select Division</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="district_id" label="District" placeholder="Select District"
                    :disabled="!$division_id">
                    <option value="">Select District</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="thana_id" label="Thana" placeholder="Select Thana"
                    :disabled="!$district_id">
                    <option value="">Select Thana</option>
                    @foreach($thanas as $thana)
                        <option value="{{ $thana->id }}">{{ $thana->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div wire:ignore>
                <flux:editor wire:model="description" label="Detailed Description" />
            </div>

            <div class="grid gap-6">
                <div class="space-y-4">
                    <flux:heading size="sm">Images Gallery</flux:heading>
                    <flux:file-upload wire:model.live="images" multiple />

                </div>

                <div class="space-y-4">
                    <flux:heading size="sm">Map Upload</flux:heading>
                    <flux:file-upload wire:model.live="map" accept="image/*,.pdf" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model="status" label="Status">
                    <option value="1">Published</option>
                    <option value="0">Draft</option>
                </flux:select>
                <div class="pt-6">
                    <flux:checkbox wire:model="is_featured" label="Show as Featured" />
                </div>
            </div>

            {{-- <flux:accordion>
                <flux:accordion.item title="SEO Settings">
                    <div class="space-y-4 pt-4">
                        <flux:input wire:model="meta_title" label="SEO Title" />
                        <flux:textarea wire:model="meta_description" label="Meta Description" />
                        <flux:input wire:model="meta_keywords" label="Keywords"
                            placeholder="keyword1, keyword2, keyword3" />
                    </div>
                </flux:accordion.item>
            </flux:accordion> --}}

            <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Save Destination
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>