<?php

use Livewire\Volt\Component;
use App\Models\IntroBd;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate, On};

new class extends Component {
    use WithFileUploads, WithPagination;

    public $temp_upload;
    public $introBdId;

    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|min:10')]
    public $description = '';

    #[Validate('nullable|string')]
    public $intro_category = '', $new_category = '';

    #[Validate('boolean')]
    public $is_featured = false;

    // ইমেজ সংরক্ষণের জন্য একক অ্যারে
    public $images = [];

    public $viewType = 'active';
    public $search = '';
    public $categoryInputType = 'select';

    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function updatedViewType()
    {
        $this->resetPage();
    }

    #[Computed]
    public function introBds()
    {
        return ($this->viewType === 'trashed' ? IntroBd::onlyTrashed() : IntroBd::query())
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'availableCategories' => IntroBd::distinct()->whereNotNull('intro_category')->pluck('intro_category')
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['introBdId', 'title', 'description', 'intro_category', 'new_category', 'is_featured', 'images']);
        $this->dispatch('modal-show', name: 'intro-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $intro = IntroBd::withTrashed()->findOrFail($id);
        $this->introBdId = $intro->id;
        $this->title = $intro->title;
        $this->description = $intro->description;
        $this->intro_category = $intro->intro_category;
        $this->is_featured = (bool) $intro->is_featured;

        // গ্লোবাল আপলোডারের জন্য মিডিয়া ম্যাপ করা
        $this->images = $intro->getMedia('intro_images')->map(fn($m) => [
            'id' => $m->id,
            'url' => $m->getUrl('thumb') ?? $m->getUrl(),
            'is_existing' => true // এটি দেখে ব্লেড চিনবে যে এটি ডাটাবেজে আছে
        ])->toArray();

        $this->dispatch('modal-show', name: 'intro-form');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file)
            return;

        // যদি এটি ডাটাবেজের ইমেজ হয়, তবে মিডিয়া লাইব্রেরি থেকে ডিলিট করো
        if (is_array($file) && isset($file['is_existing'])) {
            $intro = IntroBd::withTrashed()->findOrFail($this->introBdId);
            $intro->deleteMedia($file['id']);
        }

        // অ্যারে থেকে রিমুভ করা
        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function save()
    {
        $this->validate();
        $category = $this->categoryInputType === 'create' ? $this->new_category : $this->intro_category;

        $intro = IntroBd::updateOrCreate(['id' => $this->introBdId], [
            'title' => $this->title,
            'description' => $this->description,
            'intro_category' => $category,
            'is_featured' => $this->is_featured,
            'slug' => Str::slug($this->title),
        ]);

        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                // শুধুমাত্র নতুন আপলোড করা ফাইলগুলো সেভ হবে
                if ($image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $intro->addMedia($image->getRealPath())
                        ->usingFileName(Str::random(10) . '.' . $image->getClientOriginalExtension())
                        ->toMediaCollection('intro_images');
                }
            }
        }

        $this->dispatch('modal-close', name: 'intro-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'তথ্যটি সংরক্ষিত হয়েছে।');
        $this->reset(['images', 'introBdId']);
    }

    #[On('file-uploaded')]
    public function handleFileUpload($fileInfo)
    {
        $file = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($fileInfo);
        $this->images[] = $file;
    }

    // বাকী ডিলিট, রিস্টোর ফাংশনগুলো আগের মতোই থাকবে...
    public function delete($id)
    {
        IntroBd::find($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'Item moved to trash.');
    }
    public function restore($id)
    {
        IntroBd::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'Item restored successfully.');
    }
    public function forceDelete($id)
    {
        $intro = IntroBd::onlyTrashed()->findOrFail($id);
        $intro->clearMediaCollection('intro_images');
        $intro->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'Item deleted permanently.');
    }
}; ?>

<div class="p-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Intro BD Management</flux:heading>
            <flux:subheading>Manage your site intro sections from here.</flux:subheading>
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
    <flux:table :paginate="$this->introBds">
        <flux:table.columns>
            <flux:table.column>Media</flux:table.column>
            <flux:table.column sortable>Title</flux:table.column>
            <flux:table.column>Category</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->introBds as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>
                      
                            @php 
                                $images = $item->getMedia('intro_images'); 
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
                    <flux:table.cell class="font-medium">{{ $item->title }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">{{ $item->intro_category ?: 'General' }}
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
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                wire:confirm="This will be deleted permanently!" wire:click="forceDelete({{ $item->id }})" />
                    @endif
                        </flux:table.cell>
                </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="4" class="text-center py-10 text-zinc-400">No records found.
                    </flux:table.cell>
                </flux:table.row>
        @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="intro-form" class="md:w-[45rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $introBdId ? 'Edit Information' : 'Add New Information' }}</flux:heading>
                <flux:subheading>Please fill in all the fields correctly.</flux:subheading>
            </div>

            <flux:input wire:model="title" label="Title" placeholder="Enter title..." />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                <flux:field>
                    <flux:label>Category</flux:label>
                <div class="flex gap-2">
                    @if($categoryInputType === 'select')
                        <flux:select wire:model="intro_category" class="flex-1">
                            <option value="">Select Category</option>
                            @foreach($availableCategories as $cat) <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </flux:select>
                    @else
                            <flux:input wire:model="new_category" class="flex-1" placeholder="New category name..." />
                        @endif
                        <flux:button variant="subtle" size="sm"
                            wire:click="$set('categoryInputType', '{{ $categoryInputType === 'select' ? 'create' : 'select' }}')">
                            {{ $categoryInputType === 'select' ? 'New' : 'List' }}
                        </flux:button>
                    </div>
                </flux:field>
                <div class="pb-2">
                    <flux:checkbox wire:model="is_featured" label="Show as Featured" />
                </div>
            </div>

            <div wire:ignore>
                <flux:editor wire:model="description" label="Detailed Description" />
            </div>

            {{-- Media Upload --}}
            <flux:file-upload wire:model.live="images" multiple />

            <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>