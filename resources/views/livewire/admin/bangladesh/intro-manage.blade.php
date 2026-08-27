<?php

use Livewire\Volt\Component;
use App\Models\IntroBd;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\{Computed, Validate, On};
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    public $temp_upload;
    public $introBdId;

    public array $activities = [];

    public ?array $selectedActivity = null;

    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|min:3|unique:intro_bds,slug|max:255')]
    public $slug = '';

    #[Validate('required|min:10')]
    public $description = '';

    public $intro_category = '',
        $new_category = '';

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
        $this->reset(['search']); // চাইলে সার্চও ক্লিয়ার করতে পারেন
    }

    #[Computed]
    public function introBds()
    {
        return ($this->viewType === 'trashed' ? IntroBd::onlyTrashed() : IntroBd::query())->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))->latest()->paginate(10);
    }
    public function with(): array
    {
        return [
            'availableCategories' => IntroBd::distinct()->whereNotNull('intro_category')->pluck('intro_category'),
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
        if ($intro->trashed()) {
            $this->dispatch('toast', variant: 'warning', text: 'You are editing a trashed item.');
        }
        $this->introBdId = $intro->id;
        $this->title = $intro->title;
        $this->slug = $intro->slug;
        $this->description = $intro->description;
        $this->intro_category = $intro->intro_category;
        $this->is_featured = (bool) $intro->is_featured;

        // গ্লোবাল আপলোডারের জন্য মিডিয়া ম্যাপ করা
        $this->images = $intro
            ->getMedia('intro_images')
            ->map(
                fn($m) => [
                    'id' => $m->id,
                    'url' => $m->getUrl('thumb') ?? $m->getUrl(),
                    'is_existing' => true, // এটি দেখে ব্লেড চিনবে যে এটি ডাটাবেজে আছে
                ],
            )
            ->toArray();

        $this->dispatch('modal-show', name: 'intro-form');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file) {
            return;
        }

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

        $intro = IntroBd::updateOrCreate(
            ['id' => $this->introBdId],
            [
                'title' => $this->title,
                'description' => $this->description,
                'intro_category' => $category,
                'is_featured' => $this->is_featured,
                'slug' => $this->slug ?? Str::slug($this->title),
            ],
        );

        // Upload & process new images (strip metadata + convert to WebP)
        if (!empty($this->images)) {
            foreach ($this->images as $file) {
                // শুধুমাত্র নতুন TemporaryUploadedFile প্রসেস করবে
                if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $slug = Str::slug($intro->title);
                    $fileName = "{$slug}-totthobox-introduction-bangladesh-" . Str::lower(Str::random(6)) . '.webp';

                    // ১. মেটাডেটা রিমুভ করে WebP এ কনভার্ট
                    $processedImage = Image::read($file->getRealPath());
                    $processedImage->toWebp(85)->save($file->getRealPath());

                    // ২. ক্লিন WebP ফাইল মিডিয়া কালেকশনে সেভ
                    $intro
                        ->addMedia($file->getRealPath())
                        ->usingFileName($fileName)
                        ->usingName("{$intro->title} - Totthobox Intro BD")
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

    // View activity logs with detailed changes
    public function viewLogs(int $id): void
    {
        $this->activities = Activity::query()
            ->with('causer')
            ->where('subject_id', $id)
            ->where('subject_type', introBd::class)
            ->latest()
            ->get()
            ->map(function ($activity) {
                $properties = $activity->properties;

                // Format the changes for display
                if ($activity->event === 'updated' && isset($properties['changes'])) {
                    $activity->formatted_changes = $this->formatChanges($properties['changes']);
                } elseif ($activity->event === 'created' && isset($properties['created_data'])) {
                    $activity->formatted_data = $properties['created_data'];
                } elseif ($activity->event === 'deleted' && isset($properties['deleted_data'])) {
                    $activity->formatted_data = $properties['deleted_data'];
                } elseif ($activity->event === 'force_deleted' && isset($properties['permanently_deleted_data'])) {
                    $activity->formatted_data = $properties['permanently_deleted_data'];
                }

                return $activity;
            })
            ->toArray();

        $this->dispatch('modal-show', name: 'activity-logs');
    }

    // View specific activity details
    public function viewActivityDetails(int $activityId): void
    {
        $activity = Activity::with('causer')->find($activityId);

        if ($activity) {
            $this->selectedActivity = [
                'id' => $activity->id,
                'event' => $activity->event,
                'description' => $activity->description,
                'causer' => $activity->causer?->name ?? 'System',
                'created_at' => $activity->created_at->format('d M Y, h:i A'),
                'properties' => $activity->properties,
            ];

            $this->dispatch('modal-show', name: 'activity-detail');
        }
    }
}; ?>

<div class="">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Intro BD Management</flux:heading>
            <flux:subheading>Manage your site intro sections from here.</flux:subheading>
        </div>
        <div class="flex items-center gap-4">
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
                            @if ($images->isEmpty())
                                {{-- ইমেজ না থাকলে ডিফল্ট ইমেজ --}}
                                <flux:icon.photo />
                            @else
                                {{-- ইমেজ থাকলে লুপ চলবে --}}
                                @foreach ($images->take(3) as $media)
                                    <flux:avatar src="{{ $media->getUrl('thumb') }}" />
                                @endforeach

                                @if ($images->count() > 3)
                                    <flux:avatar initials="+{{ $images->count() - 3 }}" />
                                @endif
                            @endif
                        </flux:avatar.group>
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">{{ $item->title }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">
                            {{ $item->intro_category ?: 'General' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        @if ($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="clock"
                                wire:click="viewLogs({{ $item->id }})" title="View Activity Logs" />

                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $item->id }})" />

                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                wire:confirm="Are you sure you want to move this to trash?"
                                wire:click="delete({{ $item->id }})" />
                        @else
                            {{-- শুধু Trash view-এ --}}
                            @can('restore data')
                                <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                    wire:click="restore({{ $item->id }})" />
                            @endcan

                            @can('permanent delete')
                                <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                    wire:confirm="This will be permanently deleted and cannot be recovered!"
                                    wire:click="forceDelete({{ $item->id }})" />
                            @endcan
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
    <flux:modal name="intro-form" class="md:w-180">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $introBdId ? 'Edit Information' : 'Add New Information' }}
                </flux:heading>
                <flux:subheading>Please fill in all the fields correctly.</flux:subheading>
            </div>

            <flux:input wire:model="title" label="Title" placeholder="Enter title..." />
            <flux:input wire:model="slug" label="Slug" placeholder="Enter slug..." />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                <flux:field>
                    <flux:label>Category</flux:label>
                    <div class="flex gap-4">
                        @if ($categoryInputType === 'select')
                            <flux:select wire:model="intro_category" class="flex-1">
                                <option value="">Select Category</option>
                                @foreach ($availableCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
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

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Activity Logs Modal --}}
    <flux:modal name="activity-logs" class="w-full h-full">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <flux:heading size="lg">Activity History</flux:heading>
                    <flux:subheading>Detailed change log with before/after values</flux:subheading>
                </div>
            </div>

            <div class="space-y-6 overflow-y-auto mt-4 pr-2">
                @forelse($activities as $log)
                    <div
                        class="relative pl-4 border-l-2 {{ $log['event'] === 'created'
                            ? 'border-green-500'
                            : ($log['event'] === 'updated'
                                ? 'border-blue-500'
                                : ($log['event'] === 'restored'
                                    ? 'border-yellow-500'
                                    : 'border-red-500')) }}">
                        <div class="flex flex-col gap-4">
                            {{-- Header --}}
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <flux:badge size="sm"
                                        color="{{ $log['event'] === 'created'
                                            ? 'green'
                                            : ($log['event'] === 'updated'
                                                ? 'blue'
                                                : ($log['event'] === 'restored'
                                                    ? 'yellow'
                                                    : 'red')) }}">
                                        {{ ucfirst($log['event']) }}
                                    </flux:badge>
                                    <span class="text-sm font-medium text-zinc-700">
                                        {{ $log['description'] }}
                                    </span>
                                </div>
                                <span class="text-xs text-zinc-500">
                                    {{ \Carbon\Carbon::parse($log['created_at'])->format('d M Y, h:i A') }}
                                </span>
                            </div>

                            {{-- User --}}
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-zinc-600">By:</span>
                                <flux:profile :chevron="false" name="{{ $log['causer']['name'] ?? 'System' }}"
                                    avatar="{{ $log['causer']['avatar_url'] }}" />
                            </div>

                            {{-- Changes Display --}}
                            @if ($log['event'] === 'updated' && isset($log['formatted_changes']))
                                <div class="mt-2 space-y-3">
                                    @foreach ($log['formatted_changes'] as $change)
                                        <div class="bg-zinc-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-zinc-700 mb-2">
                                                {{ $change['field'] }}
                                            </div>

                                            @if ($change['type'] === 'textarea')
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-red-500 mb-2">Before:</div>
                                                        <div
                                                            class="text-xs bg-white p-2 rounded border border-zinc-200 max-h-32 overflow-y-auto">
                                                            {{ $change['old'] ?: '(empty)' }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs text-green-500 mb-2">After:</div>
                                                        <div
                                                            class="text-xs bg-white p-2 rounded border border-zinc-200 max-h-32 overflow-y-auto">
                                                            {{ $change['new'] ?: '(empty)' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($change['type'] === 'categories')
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-red-500 mb-2">Before:</div>
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach (explode(', ', $change['old']) as $cat)
                                                                @if ($cat !== 'None')
                                                                    <flux:badge size="sm" color="red"
                                                                        variant="subtle">{{ $cat }}
                                                                    </flux:badge>
                                                                @else
                                                                    <span class="text-xs text-zinc-400">None</span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs text-green-500 mb-2">After:</div>
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach (explode(', ', $change['new']) as $cat)
                                                                @if ($cat !== 'None')
                                                                    <flux:badge size="sm" color="green"
                                                                        variant="subtle">{{ $cat }}
                                                                    </flux:badge>
                                                                @else
                                                                    <span class="text-xs text-zinc-400">None</span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-red-500 mb-2">Before:</div>
                                                        <div
                                                            class="text-sm bg-white p-2 rounded border border-zinc-200">
                                                            {{ $change['old'] ?: '(empty)' }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs text-green-500 mb-2">After:</div>
                                                        <div
                                                            class="text-sm bg-white p-2 rounded border border-zinc-200">
                                                            {{ $change['new'] ?: '(empty)' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(in_array($log['event'], ['created', 'deleted', 'force_deleted']) && isset($log['formatted_data']))
                                <div class="mt-2 bg-zinc-50 rounded-lg p-3">
                                    <div class="grid grid-cols-1 gap-4">
                                        @foreach ($log['formatted_data'] as $key => $value)
                                            <div class="flex">
                                                <span
                                                    class="text-xs font-medium text-zinc-500 w-24">{{ ucfirst($key) }}:</span>
                                                <span class="text-sm">
                                                    @if (is_array($value))
                                                        {{ implode(', ', $value) }}
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- View Details Button --}}
                            <div class="flex justify-end">
                                <flux:button size="xs" variant="ghost" icon="eye"
                                    wire:click="viewActivityDetails({{ $log['id'] }})">
                                    View Full Details
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-400">
                        <flux:icon name="clock" class="w-12 h-12 mx-auto mb-2" />
                        <p>No activity logs found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </flux:modal>

    <flux:modal name="activity-detail" class="w-full max-w-3xl">
        @if ($selectedActivity)
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <flux:heading size="lg">Activity Details</flux:heading>
                    <flux:badge
                        color="{{ $selectedActivity['event'] === 'created' ? 'green' : ($selectedActivity['event'] === 'updated' ? 'blue' : 'yellow') }}">
                        {{ ucfirst($selectedActivity['event']) }}
                    </flux:badge>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <flux:label>Description</flux:label>
                        <flux:badge>
                            {!! $selectedActivity['description'] !!}
                        </flux:badge>
                    </div>

                    <flux:field>
                        <flux:label>Performed by</flux:label>
                        <flux:input readonly value="{{ $selectedActivity['causer'] }}" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Date & Time</flux:label>
                        <flux:input readonly value="{{ $selectedActivity['created_at'] }}" />
                    </flux:field>
                </div>

                @if ($properties = $selectedActivity['properties'] ?? null)
                    <flux:field>
                        <flux:label>Full Properties</flux:label>
                        <div
                            class="bg-zinc-950 p-4 rounded-lg text-emerald-400 text-xs font-mono overflow-auto max-h-60 border border-zinc-800 whitespace-pre-wrap">
                            {{ strip_tags(json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}
                        </div>
                    </flux:field>
                @endif
            </div>
        @endif
    </flux:modal>
</div>
