<?php

use Livewire\Volt\Component;
use App\Models\Minister;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate, On};

new class extends Component {
    use WithFileUploads, WithPagination;

    public $ministerId;

    // Form Properties
    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|string|max:255')]
    public $designation = '';

    #[Validate('required|numeric|min:1')]
    public $rank = 1;

    #[Validate('required|string')]
    public $party = '';

    #[Validate('required|date')]
    public $from_date = '';

    #[Validate('nullable|date|after_or_equal:from_date')]
    public $to_date = '';

    #[Validate('boolean')]
    public $is_current = true;

    #[Validate('nullable|string')]
    public $bio = '';

    #[Validate('nullable|exists:divisions,id')]
    public $division_id;

    #[Validate('nullable|exists:districts,id')]
    public $district_id;

    #[Validate('nullable|exists:thanas,id')]
    public $thana_id;

    #[Validate('boolean')]
    public $is_featured = false;

    #[Validate('boolean')]
    public $status = true;

    #[Validate('nullable|string|max:255')]
    public $meta_title, $meta_description, $meta_keywords;

    // Image state (Single array to match IntroBD style)
    public $images = [];

    // UI State
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
    public function ministers()
    {
        return ($this->viewType === 'trashed' ? Minister::onlyTrashed() : Minister::query())
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('designation', 'like', "%{$this->search}%");
            })
            ->orderBy('rank', 'asc')
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'divisions' => Division::all(),
            'districts' => District::all(),
            'thanas' => Thana::all(),
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['ministerId', 'name', 'designation', 'rank', 'party', 'from_date', 'to_date', 'bio', 'division_id', 'district_id', 'thana_id', 'is_featured', 'status', 'meta_title', 'meta_description', 'meta_keywords', 'images']);
        $this->dispatch('modal-show', name: 'minister-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $item = Minister::withTrashed()->findOrFail($id);

        $this->ministerId = $item->id;
        $this->name = $item->name;
        $this->designation = $item->designation;
        $this->rank = $item->rank;
        $this->party = $item->party;
        $this->from_date = $item->from_date ? $item->from_date->format('Y-m-d') : '';
        $this->to_date = $item->to_date ? $item->to_date->format('Y-m-d') : '';
        $this->is_current = (bool) $item->is_current;
        $this->bio = $item->bio;
        $this->division_id = $item->division_id;
        $this->district_id = $item->district_id;
        $this->thana_id = $item->thana_id;
        $this->is_featured = (bool) $item->is_featured;
        $this->status = (bool) $item->status;
        $this->meta_title = $item->meta_title;
        $this->meta_description = $item->meta_description;
        $this->meta_keywords = $item->meta_keywords;

        // Map media exactly like IntroBD
        $this->images = $item->getMedia('minister_images')->map(fn($m) => [
            'id' => $m->id,
            'url' => $m->getUrl('thumb') ?? $m->getUrl(),
            'is_existing' => true
        ])->toArray();

        $this->dispatch('modal-show', name: 'minister-form');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file)
            return;

        if (is_array($file) && isset($file['is_existing'])) {
            $item = Minister::withTrashed()->findOrFail($this->ministerId);
            $item->deleteMedia($file['id']);
        }

        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function save()
    {
        $this->validate();

        $item = Minister::updateOrCreate(['id' => $this->ministerId], [
            'name' => $this->name,
            'designation' => $this->designation,
            'rank' => $this->rank,
            'party' => $this->party,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'is_current' => $this->is_current,
            'bio' => $this->bio,
            'division_id' => $this->division_id,
            'district_id' => $this->district_id,
            'thana_id' => $this->thana_id,
            'is_featured' => $this->is_featured,
            'status' => $this->status,
            'slug' => Str::slug($this->name),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'user_id' => auth()->id(),
        ]);

        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                if ($image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $item->addMedia($image->getRealPath())
                        ->usingFileName(Str::random(10) . '.' . $image->getClientOriginalExtension())
                        ->toMediaCollection('minister_images');
                }
            }
        }

        $this->dispatch('modal-close', name: 'minister-form');
        $this->dispatch('toast', variant: 'success', heading: 'Saved', text: 'Minister information has been saved.');
        $this->reset(['images', 'ministerId']);
    }

    public function delete($id)
    {
        Minister::find($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'Minister moved to trash.');
    }

    public function restore($id)
    {
        Minister::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'Minister restored successfully.');
    }

    public function forceDelete($id)
    {
        $item = Minister::onlyTrashed()->findOrFail($id);
        $item->clearMediaCollection('minister_images');
        $item->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'Minister deleted permanently.');
    }
}; ?>

<div class="p-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Minister Management</flux:heading>
            <flux:subheading>Manage government officials and their profiles.</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">Add Minister</flux:button>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by name or designation..."
            icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->ministers">
        <flux:table.columns>
            <flux:table.column>Profile</flux:table.column>
            <flux:table.column>Details</flux:table.column>
            <flux:table.column>Party</flux:table.column>
            <flux:table.column>Rank</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->ministers as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>
                        @php $media = $item->getFirstMedia('minister_images'); @endphp
                        <flux:avatar src="{{ $media ? $media->getUrl() : '' }}"
                            initials="{{ substr($item->name, 0, 1) }}" />
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="font-medium text-zinc-800 dark:text-white">{{ $item->name }}</div>
                        <div class="text-xs text-zinc-500">{{ $item->designation }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="blue" inset="top bottom">{{ $item->party }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $item->rank }}</flux:table.cell>
                    <flux:table.cell align="end">
                        @if($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red" wire:confirm="Move to trash?"
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
                    <flux:table.cell colspan="5" class="text-center py-10 text-zinc-400">No ministers found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="minister-form" class="md:w-[50rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $ministerId ? 'Edit Profile' : 'Add New Minister' }}</flux:heading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="name" label="Full Name" />
                <flux:input wire:model="designation" label="Designation" />
                <flux:input wire:model="party" label="Political Party" />
                <flux:input type="number" wire:model="rank" label="Rank/Priority" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input type="date" wire:model="from_date" label="From Date" />
                <flux:input type="date" wire:model="to_date" label="To Date" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select wire:model="division_id" label="Division">
                    <option value="">Select Division</option>
                    @foreach($divisions as $div) <option value="{{ $div->id }}">{{ $div->name }}</option> @endforeach
                </flux:select>
                <flux:select wire:model="district_id" label="District">
                    <option value="">Select District</option>
                    @foreach($districts as $dis) <option value="{{ $dis->id }}">{{ $dis->name }}</option> @endforeach
                </flux:select>
                <flux:select wire:model="thana_id" label="Thana">
                    <option value="">Select Thana</option>
                    @foreach($thanas as $tha) <option value="{{ $tha->id }}">{{ $tha->name }}</option> @endforeach
                </flux:select>
            </div>

            <div class="flex gap-4">
                <flux:checkbox wire:model="is_current" label="Current Minister" />
                <flux:checkbox wire:model="is_featured" label="Featured" />
                <flux:checkbox wire:model="status" label="Active Status" />
            </div>

            <div wire:ignore>
                <flux:editor wire:model="bio" label="Biography" />
            </div>

            {{-- Media Upload --}}
            <flux:field>
                <flux:file-upload wire:model.live="images" />
            </flux:field>

            <div class="space-y-4 border-t pt-4">
                <flux:heading size="sm">SEO Settings</flux:heading>
                <flux:input wire:model="meta_title" label="Meta Title" />
                <flux:textarea wire:model="meta_description" label="Meta Description" />
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Profile</flux:button>
            </div>
        </form>
    </flux:modal>
</div>