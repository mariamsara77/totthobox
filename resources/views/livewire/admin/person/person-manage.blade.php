<?php

use Livewire\Volt\Component;
use App\Models\Person;
use App\Models\PeopleCategory;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate};
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    public $personId;

    #[Validate('required|min:3|max:255')]
    public $name = '';

    #[Validate('required|min:3|max:255')]
    public $slug = '';

    #[Validate('nullable|string')]
    public $bio = '';

    #[Validate('array')]
    public $selectedCategories = [];

    // Multiple images
    public $images = [];

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

    public function updatedName($value)
    {
        if (!$this->personId) {
            $this->slug = Str::slug($value);
        }
    }

    #[Computed]
    public function people()
    {
        return ($this->viewType === 'trashed' ? Person::onlyTrashed() : Person::query())
            ->with(['peopleCategories', 'media'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'categories' => PeopleCategory::orderBy('name')->get(),
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['personId', 'name', 'slug', 'bio', 'selectedCategories', 'images']);
        $this->dispatch('modal-show', name: 'person-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $this->reset(['images']);

        $person = Person::withTrashed()
            ->with(['peopleCategories', 'media'])
            ->findOrFail($id);

        $this->personId = $person->id;
        $this->name = $person->name;
        $this->slug = $person->slug;
        $this->bio = $person->bio;
        $this->selectedCategories = $person->peopleCategories->pluck('id')->toArray();

        // Load existing media into $images
        $this->images = $person
            ->getMedia('images')
            ->map(
                fn($m) => [
                    'id' => $m->id,
                    'url' => $m->getUrl('thumb') ?? $m->getUrl(),
                    'is_existing' => true,
                ],
            )
            ->toArray();

        $this->dispatch('modal-show', name: 'person-form');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file) {
            return;
        }

        // Existing image হলে Media Library থেকে মুছে ফেলো
        if (is_array($file) && isset($file['is_existing']) && $file['is_existing']) {
            $person = Person::withTrashed()->findOrFail($this->personId);
            $person->deleteMedia($file['id']);
        }

        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function save()
    {
        $uniqueRule = $this->personId ? 'unique:people,slug,' . $this->personId : 'unique:people,slug';

        $this->validate([
            'name' => 'required|min:3|max:255',
            'slug' => "required|min:3|max:255|{$uniqueRule}",
            'bio' => 'nullable|string',
            'selectedCategories' => 'array',
            'selectedCategories.*' => 'exists:people_categories,id',
        ]);

        // শুধুমাত্র নতুন আপলোড হওয়া ফাইলগুলো আলাদা করা
        $newImages = array_filter($this->images, fn($img) => $img instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile);

        // নতুন ফাইল থাকলে ম্যানুয়ালি ভ্যালিডেট করা
        if (!empty($newImages)) {
            $validator = \Illuminate\Support\Facades\Validator::make(
                ['new_uploads' => $newImages],
                [
                    'new_uploads' => 'array|max:10', // সর্বোচ্চ ১০টা
                    'new_uploads.*' => 'image|max:5120', // প্রতিটা ৫MB
                ],
                [
                    'new_uploads.max' => 'You can upload maximum 10 images.',
                    'new_uploads.*.image' => 'The file must be an image.',
                    'new_uploads.*.max' => 'Each image must not exceed 5MB.',
                ],
            );

            if ($validator->fails()) {
                $this->addError('images', $validator->errors()->first());
                return;
            }
        }

        $person = Person::updateOrCreate(
            ['id' => $this->personId],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'bio' => $this->bio,
            ],
        );

        // Sync categories
        $person->peopleCategories()->sync($this->selectedCategories);

        // Upload new images
        if (!empty($newImages)) {
            foreach ($newImages as $file) {
                $fileName = Str::slug($person->name) . '-totthobox-public-figure-' . Str::lower(Str::random(6)) . '.webp';

                $processed = Image::read($file->getRealPath());
                $processed->toWebp(85)->save($file->getRealPath());

                $person
                    ->addMedia($file->getRealPath())
                    ->usingFileName($fileName)
                    ->usingName("{$person->name} - Totthobox Public Figure")
                    ->toMediaCollection('images');
            }
        }

        $this->dispatch('modal-close', name: 'person-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'তথ্যটি সংরক্ষিত হয়েছে।');
        $this->reset(['personId', 'images']);
    }

    public function delete($id)
    {
        Person::find($id)?->delete();
        $this->dispatch('toast', variant: 'warning', text: 'Item moved to trash.');
    }

    public function restore($id)
    {
        Person::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'Item restored successfully.');
    }

    public function forceDelete($id)
    {
        $person = Person::onlyTrashed()->findOrFail($id);
        $person->clearMediaCollection('images');
        $person->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'Item deleted permanently.');
    }
}; ?>

<div>
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Public Figure Management</flux:heading>
            <flux:subheading>Manage public figures and their profiles.</flux:subheading>
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
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by name..." icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->people">
        <flux:table.columns>
            <flux:table.column>Media</flux:table.column>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Categories</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->people as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>
                        @php
                            $mediaItems = $item->getMedia('images');
                        @endphp

                        @if ($mediaItems->isNotEmpty())
                            <flux:avatar.group>
                                @foreach ($mediaItems->take(3) as $media)
                                    <flux:avatar src="{{ $media->getUrl('thumb') ?? $media->getUrl() }}"
                                        size="sm" />
                                @endforeach
                                @if ($mediaItems->count() > 3)
                                    <flux:avatar initials="+{{ $mediaItems->count() - 3 }}" size="sm" />
                                @endif
                            </flux:avatar.group>
                        @else
                            <flux:avatar initials="{{ Str::initials($item->name) }}" size="sm" />
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="font-medium">
                        {{ $item->name }}
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($item->peopleCategories->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach ($item->peopleCategories as $cat)
                                    <flux:badge size="sm" color="blue" variant="subtle">
                                        {{ $cat->name }}
                                    </flux:badge>
                                @endforeach
                            </div>
                        @else
                            <span class="text-zinc-400 text-sm">—</span>
                        @endif
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
                    <flux:table.cell colspan="4" class="text-center py-10 text-zinc-400">
                        No records found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="person-form" class="md:w-[55rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $personId ? 'Edit Public Figure' : 'Add New Public Figure' }}
                </flux:heading>
                <flux:subheading>Fill in the details for the public figure.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model.live="name" label="Full Name" placeholder="Enter full name..." />
                <flux:input wire:model="slug" label="Slug" placeholder="auto-generated" />
            </div>

            <div wire:ignore>
                <flux:editor wire:model="bio" label="Biography" />
            </div>

            {{-- Categories --}}
            <div>
                <flux:label>Categories</flux:label>
                @if ($categories->isNotEmpty())
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-2">
                        @foreach ($categories as $category)
                            <flux:checkbox wire:model="selectedCategories" value="{{ $category->id }}"
                                label="{{ $category->name }}" />
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-zinc-400 mt-1">No categories available.</p>
                @endif
            </div>

            {{-- Multiple Image Upload --}}
            <div>
                <flux:label>Images (Multiple)</flux:label>
                <div class="mt-2">
                    <flux:file-upload wire:model="images" multiple />
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
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
