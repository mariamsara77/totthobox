<?php

use Livewire\Volt\Component;
use App\Models\HistoryBd;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate};
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    public $historyBdId;

    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|min:3|max:255')]
    public $slug = '';

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

    #[Validate('required|in:0,1')]
    public $status = 1;

    #[Validate('nullable|string|max:100')]
    public $era = '';

    #[Validate('nullable|integer|min:0')]
    public $sort_order = 0;

    #[Validate('nullable')]
    public $start_year;

    #[Validate('nullable')]
    public $end_year;

    // Single image (because of singleFile())
    public $image;
    public $existingImage = null; // for preview on edit

    public $viewType = 'active';
    public $search = '';

    public $districts = [];
    public $thanas = [];

    public $createNewEra = false;
    public $existingEras = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    public function updatedTitle($value)
    {
        if (!$this->historyBdId) {
            $this->slug = Str::slug($value);
        }
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
            'divisions' => Division::all(),
            'existingEras' => HistoryBd::whereNotNull('era')->where('era', '!=', '')->distinct()->orderBy('era')->pluck('era')->toArray(),
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['historyBdId', 'title', 'slug', 'description', 'division_id', 'district_id', 'thana_id', 'is_featured', 'status', 'era', 'sort_order', 'start_year', 'end_year', 'image', 'existingImage', 'districts', 'thanas']);
        $this->status = 1;
        $this->sort_order = 0;
        $this->createNewEra = false;
        $this->dispatch('modal-show', name: 'history-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $history = HistoryBd::withTrashed()->findOrFail($id);

        $this->historyBdId = $history->id;
        $this->title = $history->title;
        $this->slug = $history->slug;
        $this->description = $history->description;
        $this->division_id = $history->division_id;
        $this->district_id = $history->district_id;
        $this->thana_id = $history->thana_id;
        $this->is_featured = (bool) $history->is_featured;
        $this->status = $history->status;
        $this->era = $history->era;
        $this->sort_order = $history->sort_order ?? 0;
        $this->start_year = $history->start_year;
        $this->end_year = $history->end_year;

        if ($this->division_id) {
            $this->districts = District::where('division_id', $this->division_id)->get();
        }
        if ($this->district_id) {
            $this->thanas = Thana::where('district_id', $this->district_id)->get();
        }

        // Existing image
        $media = $history->getFirstMedia('images');
        $this->existingImage = $media
            ? [
                'id' => $media->id,
                'url' => $media->getUrl('thumb') ?? $media->getUrl(),
            ]
            : null;

        $this->image = null;

        $this->createNewEra = false;

        $this->dispatch('modal-show', name: 'history-form');
    }

    public function removeImage()
    {
        if ($this->existingImage && $this->historyBdId) {
            $history = HistoryBd::withTrashed()->findOrFail($this->historyBdId);
            $history->clearMediaCollection('images');
        }

        $this->existingImage = null;
        $this->image = null;
    }

    public function save()
    {
        $uniqueRule = $this->historyBdId ? 'unique:history_bds,slug,' . $this->historyBdId : 'unique:history_bds,slug';

        $this->validate([
            'title' => 'required|min:3|max:255',
            'slug' => "required|min:3|max:255|{$uniqueRule}",
            'description' => 'required|min:10',
            'division_id' => 'nullable|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'thana_id' => 'nullable|exists:thanas,id',
            'is_featured' => 'boolean',
            'status' => 'required|in:0,1',
            'era' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'start_year' => 'nullable',
            'end_year' => 'nullable',
            'image' => 'nullable|image|max:5120', // 5MB
        ]);

        $history = HistoryBd::updateOrCreate(
            ['id' => $this->historyBdId],
            [
                'title' => $this->title,
                'slug' => $this->slug,
                'description' => $this->description,
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'thana_id' => $this->thana_id,
                'is_featured' => $this->is_featured,
                'status' => $this->status,
                'era' => $this->era,
                'sort_order' => $this->sort_order ?? 0,
                'start_year' => $this->start_year,
                'end_year' => $this->end_year,
            ],
        );

        // Process new image (only TemporaryUploadedFile)
        if ($this->image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $fileName = Str::slug($history->title) . '-totthobox-history-bd-' . Str::lower(Str::random(6)) . '.webp';

            // Strip metadata + convert to WebP
            $processed = Image::read($this->image->getRealPath());
            $processed->toWebp(85)->save($this->image->getRealPath());

            $history
                ->addMedia($this->image->getRealPath())
                ->usingFileName($fileName)
                ->usingName("{$history->title} - Totthobox History BD")
                ->toMediaCollection('images');
        }

        $this->dispatch('modal-close', name: 'history-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'তথ্যটি সংরক্ষিত হয়েছে।');
        $this->reset(['image', 'existingImage', 'historyBdId']);
    }

    public function delete($id)
    {
        HistoryBd::find($id)?->delete();
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

    public function updatedCreateNewEra($value)
    {
        $this->era = '';
    }
}; ?>

<div>
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4" mb-6">
        <div>
            <flux:heading size="xl">History BD Management</flux:heading>
            <flux:subheading>Manage historical information of Bangladesh.</flux:subheading>
        </div>
        <div class="flex items-center gap-4">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">
                Create New
            </flux:button>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by title..." icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->historyBds">
        <flux:table.columns>
            <flux:table.column>Media</flux:table.column>
            <flux:table.column>Title</flux:table.column>
            <flux:table.column>Era / Year</flux:table.column>
            <flux:table.column>Location</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->historyBds as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>
                        @if ($media = $item->getFirstMedia('images'))
                            <flux:avatar src="{{ $media->getUrl('thumb') }}" />
                        @else
                            <flux:avatar initials="?" />
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="font-medium">
                        {{ $item->title }}
                        @if ($item->is_featured)
                            <flux:badge size="sm" color="amber" class="ml-1">Featured</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="text-sm">
                            @if ($item->era)
                                <div>{{ $item->era }}</div>
                            @endif
                            @if ($item->start_year || $item->end_year)
                                <div class="text-zinc-500">
                                    {{ $item->start_year ?? '?' }} – {{ $item->end_year ?? '?' }}
                                </div>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">
                            {{ $item->division?->name }} → {{ $item->district?->name }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" :color="$item->status ? 'green' : 'zinc'">
                            {{ $item->status ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        @if ($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                wire:confirm="Are you sure?" wire:click="delete({{ $item->id }})" />
                        @else
                            @can('restore data')
                                <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                    wire:click="restore({{ $item->id }})" />
                            @endcan
                            @can('permanent delete')
                                <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                    wire:confirm="Permanent delete?" wire:click="forceDelete({{ $item->id }})" />
                            @endcan
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-10 text-zinc-400">
                        No records found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="history-form" class="md:w-[55rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $historyBdId ? 'Edit Information' : 'Add New Information' }}
                </flux:heading>
                <flux:subheading>Fill in the details for the historical record.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model.live="title" label="Title" placeholder="Enter title..." />
                <flux:input wire:model="slug" label="Slug" placeholder="auto-generated" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select wire:model.live="division_id" label="Division">
                    <option value="">Select Division</option>
                    @foreach ($divisions as $div)
                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="district_id" label="District">
                    <option value="">Select District</option>
                    @foreach ($districts as $dis)
                        <option value="{{ $dis->id }}">{{ $dis->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="thana_id" label="Thana">
                    <option value="">Select Thana</option>
                    @foreach ($thanas as $tha)
                        <option value="{{ $tha->id }}">{{ $tha->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Era + Year --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- Era with Toggle --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <flux:label>Era</flux:label>
                        <flux:switch wire:model.live="createNewEra" label="নতুন" align="right" size="sm" />
                    </div>

                    @if ($createNewEra)
                        <flux:input wire:model="era" placeholder="নতুন Era লিখুন..." />
                    @else
                        <flux:select wire:model="era">
                            <option value="">Era সিলেক্ট করুন</option>
                            @foreach ($existingEras as $existingEra)
                                <option value="{{ $existingEra }}">{{ $existingEra }}</option>
                            @endforeach
                        </flux:select>
                    @endif
                </div>

                <flux:input wire:model="start_year" label="Start Year" placeholder="e.g. 1757" />
                <flux:input wire:model="end_year" label="End Year" placeholder="e.g. 1947" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:input wire:model="sort_order" type="number" label="Sort Order" />

                <flux:select wire:model="status" label="Status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </flux:select>

                <div class="flex items-end pb-2">
                    <flux:checkbox wire:model="is_featured" label="Show as Featured" />
                </div>
            </div>

            <div wire:ignore>
                <flux:editor wire:model="description" label="Detailed Description" />
            </div>

            {{-- Single Image Upload --}}
            <div>
                <flux:label>Image</flux:label>

                <div class="mt-2">
                    <flux:file-upload wire:model="image" />
                </div>

                {{-- Preview --}}
                @if ($existingImage || $image)
                    <div class="mt-4 relative inline-block">
                        @if ($image)
                            <img src="{{ $image->temporaryUrl() }}"
                                class="h-28 w-28 object-cover rounded-lg border" />
                        @elseif ($existingImage)
                            <img src="{{ $existingImage['url'] }}"
                                class="h-28 w-28 object-cover rounded-lg border" />
                        @endif

                        <button type="button" wire:click="removeImage"
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                            <flux:icon name="x-mark" class="size-3.5" />
                        </button>
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-4" pt-6 border-t border-zinc-400/25">
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
