<?php

use Livewire\Volt\Component;
use App\Models\BasicHealth;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\{Computed, Validate, On};
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    public array $activities = [];
    public ?array $selectedActivity = null;

    // Form fields
    public $basicHealthId;

    #[Validate('required|string|max:255')]
    public $title = '';

    #[Validate('nullable|string')]
    public $description = '';

    #[Validate('nullable|string|max:255')]
    public $type = '';

    #[Validate('nullable|string')]
    public $summary = '';

    #[Validate('nullable|image|max:2048')]
    public $image;

    #[Validate('nullable|string')]
    public $tags = '';

    #[Validate('boolean')]
    public $is_featured = false;

    #[Validate('required|in:1,0')]
    public $status = 1;

    // UI states
    public $viewType = 'active';
    public $search = '';

    // Pagination and Sorting
    public $perPage = 10;
    public $sortField = 'title';
    public $sortDirection = 'asc';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    #[Computed]
    public function basicHealths()
    {
        return ($this->viewType === 'trashed' ? BasicHealth::onlyTrashed() : BasicHealth::query())
            ->when($this->search, function ($query) {
                $query
                    ->where('title', 'like', "%{$this->search}%")
                    ->orWhere('type', 'like', "%{$this->search}%")
                    ->orWhere('summary', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['basicHealthId', 'title', 'description', 'type', 'summary', 'tags', 'image', 'is_featured', 'status']);
        $this->dispatch('modal-show', name: 'health-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $item = BasicHealth::withTrashed()->findOrFail($id);

        $this->basicHealthId = $item->id;
        $this->title = $item->title;
        $this->description = $item->description;
        $this->type = $item->type;
        $this->summary = $item->summary;
        $this->tags = is_array($item->tags) ? implode(',', $item->tags) : $item->tags;
        $this->is_featured = (bool) $item->is_featured;
        $this->status = $item->status;

        $this->dispatch('modal-show', name: 'health-form');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'description' => $this->description,
            'type' => $this->type,
            'summary' => $this->summary,
            'tags' => $this->tags ? explode(',', $this->tags) : [],
            'is_featured' => $this->is_featured,
            'status' => $this->status,
        ];

        $health = BasicHealth::updateOrCreate(['id' => $this->basicHealthId], $data);

        // Upload & Strip Copyright Metadata + convert to WebP (same style as BasicIslam)
        if ($this->image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $slug = Str::slug($health->title);
            $fileName = "{$slug}-totthobox-basic-health-" . Str::lower(Str::random(6)) . '.webp';

            // ১. মূল ইমেজের সব মেটাডেটা রিমুভ করে WebP করা
            $processedImage = Image::read($this->image->getRealPath());
            $processedImage->toWebp(85)->save($this->image->getRealPath());

            // ২. আগের ইমেজ মুছে নতুনটা সেভ
            $health->clearMediaCollection('images');

            $health
                ->addMedia($this->image->getRealPath())
                ->usingFileName($fileName)
                ->usingName("{$health->title} - Totthobox Basic Health")
                ->toMediaCollection('images');
        }

        $this->dispatch('modal-close', name: 'health-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'তথ্যটি সংরক্ষিত হয়েছে।');
        $this->reset(['image', 'basicHealthId']);
    }

    #[On('image-uploaded')]
    public function handleImageUpload($fileInfo)
    {
        $this->image = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($fileInfo);
    }

    public function delete($id)
    {
        BasicHealth::find($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'আইটেমটি ট্র্যাশে পাঠানো হয়েছে।');
    }

    public function restore($id)
    {
        BasicHealth::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'আইটেমটি রিস্টোর করা হয়েছে।');
    }

    public function forceDelete($id)
    {
        $health = BasicHealth::onlyTrashed()->findOrFail($id);
        $health->clearMediaCollection('images');
        $health->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'আইটেমটি স্থায়ীভাবে ডিলিট করা হয়েছে।');
    }

    // View activity logs with detailed changes
    public function viewLogs(int $id): void
    {
        try {
            $this->activities = Activity::query()
                ->with('causer')
                ->where('subject_id', $id)
                ->where('subject_type', BasicHealth::class)
                ->latest()
                ->get()
                ->map(function ($activity) {
                    try {
                        $properties = $activity->properties ?? collect();

                        $propertiesArray = $properties instanceof \Illuminate\Support\Collection ? $properties->toArray() : (array) $properties;

                        if ($activity->event === 'updated' && isset($propertiesArray['changes'])) {
                            $activity->formatted_changes = $this->formatChanges($propertiesArray['changes']);
                        } elseif ($activity->event === 'created' && isset($propertiesArray['attributes'])) {
                            $activity->formatted_data = $propertiesArray['attributes'];
                        } elseif ($activity->event === 'deleted' && isset($propertiesArray['old'])) {
                            $activity->formatted_data = $propertiesArray['old'];
                        }

                        return $activity;
                    } catch (\Exception $e) {
                        $activity->formatted_changes = [];
                        $activity->formatted_data = [];
                        return $activity;
                    }
                })
                ->toArray();
        } catch (\Exception $e) {
            $this->activities = [];
            $this->dispatch('toast', variant: 'error', text: 'Error loading activity logs.');
        }

        $this->dispatch('modal-show', name: 'activity-logs');
    }

    private function formatChanges($changes): array
    {
        $formatted = [];

        if (!is_array($changes) || empty($changes)) {
            return $formatted;
        }

        foreach ($changes as $field => $change) {
            if (!is_array($change) || !isset($change['old']) || !isset($change['new'])) {
                continue;
            }

            $type = 'text';
            if (in_array($field, ['description', 'summary'])) {
                $type = 'textarea';
            } elseif ($field === 'tags') {
                $type = 'tags';
            }

            $oldValue = $change['old'] ?? null;
            $newValue = $change['new'] ?? null;

            if (is_array($oldValue) || is_object($oldValue)) {
                $oldValue = json_encode($oldValue, JSON_UNESCAPED_UNICODE);
            }
            if (is_array($newValue) || is_object($newValue)) {
                $newValue = json_encode($newValue, JSON_UNESCAPED_UNICODE);
            }

            $formatted[] = [
                'field' => $this->formatFieldName($field),
                'type' => $type,
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $formatted;
    }

    private function formatFieldName($field): string
    {
        $names = [
            'title' => 'Title',
            'description' => 'Description',
            'type' => 'Type',
            'summary' => 'Summary',
            'tags' => 'Tags',
            'image' => 'Image',
            'is_featured' => 'Featured Status',
            'status' => 'Status',
            'slug' => 'Slug',
        ];

        return $names[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    public function viewActivityDetails(int $activityId): void
    {
        try {
            $activity = Activity::with('causer')->find($activityId);

            if ($activity) {
                $properties = $activity->properties ?? collect();

                $this->selectedActivity = [
                    'id' => $activity->id,
                    'event' => $activity->event ?? 'unknown',
                    'description' => $activity->description ?? '',
                    'causer' => $activity->causer?->name ?? 'System',
                    'created_at' => $activity->created_at ? $activity->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A'),
                    'properties' => $properties instanceof \Illuminate\Support\Collection ? $properties->toArray() : (array) $properties,
                ];

                $this->dispatch('modal-show', name: 'activity-detail');
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', variant: 'error', text: 'Error loading activity details.');
        }
    }
}; ?>

<div>
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Basic Health Management</flux:heading>
            <flux:subheading>Manage health articles, tips, and medical information.</flux:subheading>
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
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by title, type or summary..."
            icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->basicHealths">
        <flux:table.columns>
            <flux:table.column>Image</flux:table.column>
            <flux:table.column wire:click="sortBy('title')" :sortable="true"
                :direction="$sortField === 'title' ? $sortDirection : null">
                Title
            </flux:table.column>
            <flux:table.column wire:click="sortBy('type')" :sortable="true"
                :direction="$sortField === 'type' ? $sortDirection : null">
                Type
            </flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->basicHealths as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>
                        @if ($item->image)
                            <flux:avatar src="{{ Storage::url($item->image) }}" />
                        @else
                            <flux:avatar initials="N/A" />
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        <div>{{ $item->title }}</div>
                        @if ($item->summary)
                            <div class="text-xs text-zinc-500 mt-1">{{ Str::limit($item->summary, 60) }}</div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($item->type)
                            <flux:badge size="sm" color="blue" variant="subtle">{{ $item->type }}</flux:badge>
                        @else
                            <span class="text-xs text-zinc-400">Not specified</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-col gap-1">
                            @if ($item->is_featured)
                                <flux:badge size="sm" color="amber">Featured</flux:badge>
                            @endif
                            <flux:badge size="sm" :color="$item->status ? 'green' : 'red'">
                                {{ $item->status ? 'Published' : 'Draft' }}
                            </flux:badge>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        @if ($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="clock"
                                wire:click="viewLogs({{ $item->id }})" title="View Activity Logs" />

                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                wire:confirm="Are you sure?" wire:click="delete({{ $item->id }})" />
                        @else
                            <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                wire:click="restore({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                wire:confirm="This will be deleted permanently!"
                                wire:click="forceDelete({{ $item->id }})" />
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
    <flux:modal name="health-form" class="md:w-240">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $basicHealthId ? 'Edit Health Content' : 'Add New Health Content' }}
                </flux:heading>
                <flux:subheading>Manage health articles, tips, and medical information.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="title" label="Title" placeholder="Enter title..." required />
                <flux:input wire:model="type" label="Type" placeholder="e.g., Nutrition, Exercise, Disease" />
            </div>

            <flux:textarea wire:model="summary" label="Summary" placeholder="Brief summary of the content..."
                rows="2" />

            <div wire:ignore>
                <flux:editor wire:model="description" label="Description" placeholder="Detailed description..." />
            </div>

            <flux:input wire:model="tags" label="Tags" placeholder="health, wellness, nutrition (comma separated)" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <flux:heading size="sm">Featured Image</flux:heading>
                    <flux:file-upload wire:model.live="image" accept="image/*" />
                    @if ($image)
                        <div class="mt-2">
                            <img src="{{ $image->temporaryUrl() }}"
                                class="h-32 w-auto object-cover rounded-lg border border-zinc-200" alt="Preview">
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <flux:select wire:model="status" label="Status">
                        <option value="1">Published</option>
                        <option value="0">Draft</option>
                    </flux:select>

                    <div>
                        <flux:checkbox wire:model="is_featured" label="Mark as Featured" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Save Content
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Activity Logs Modal --}}
    <flux:modal name="activity-logs" class="w-full">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <flux:heading size="lg">Activity History</flux:heading>
                    <flux:subheading>Detailed change log with before/after values</flux:subheading>
                </div>
            </div>

            <div class="space-y-6 pr-2">
                @forelse($activities as $log)
                    @php
                        $event = $log['event'] ?? 'unknown';
                        $createdAt = isset($log['created_at'])
                            ? \Carbon\Carbon::parse($log['created_at'])->format('d M Y, h:i A')
                            : '';
                        $causer = $log['causer'] ?? null;
                        $causerName = $causer['name'] ?? 'System';
                        $causerAvatar = $causer['avatar_url'] ?? null;
                        $formattedChanges = $log['formatted_changes'] ?? [];
                        $formattedData = $log['formatted_data'] ?? [];
                    @endphp

                    <div
                        class="relative pl-4 border-l-2 {{ $event === 'created'
                            ? 'border-green-500'
                            : ($event === 'updated'
                                ? 'border-blue-500'
                                : ($event === 'restored'
                                    ? 'border-yellow-500'
                                    : 'border-red-500')) }}">
                        <div class="flex flex-col gap-4">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <flux:badge size="sm"
                                        color="{{ $event === 'created'
                                            ? 'green'
                                            : ($event === 'updated'
                                                ? 'blue'
                                                : ($event === 'restored'
                                                    ? 'yellow'
                                                    : 'red')) }}">
                                        {{ ucfirst($event) }}
                                    </flux:badge>
                                    <span class="text-sm font-medium text-zinc-700">
                                        {{ $log['description'] ?? 'No description' }}
                                    </span>
                                </div>
                                <span class="text-xs text-zinc-500">
                                    {{ $createdAt }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-zinc-600">By:</span>
                                <flux:profile :chevron="false" name="{{ $causerName }}"
                                    avatar="{{ $causerAvatar }}" />
                            </div>

                            @if ($event === 'updated' && !empty($formattedChanges))
                                <div class="mt-2 space-y-3">
                                    @foreach ($formattedChanges as $change)
                                        @php
                                            $field = $change['field'] ?? 'Unknown Field';
                                            $type = $change['type'] ?? 'text';
                                            $oldValue = $change['old'] ?? '(empty)';
                                            $newValue = $change['new'] ?? '(empty)';
                                        @endphp

                                        <div class="bg-zinc-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-zinc-700 mb-2">
                                                {{ $field }}
                                            </div>

                                            @if ($type === 'textarea')
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-red-500 mb-2">Before:</div>
                                                        <div
                                                            class="text-xs bg-white p-2 rounded border border-zinc-200 max-h-32 overflow-y-auto">
                                                            {!! nl2br(e($oldValue)) !!}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs text-green-500 mb-2">After:</div>
                                                        <div
                                                            class="text-xs bg-white p-2 rounded border border-zinc-200 max-h-32 overflow-y-auto">
                                                            {!! nl2br(e($newValue)) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($type === 'tags')
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-red-500 mb-2">Before:</div>
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach (explode(',', $oldValue) as $tag)
                                                                @if ($tag && $tag !== 'None' && $tag !== '(empty)')
                                                                    <flux:badge size="sm" color="red"
                                                                        variant="subtle">{{ trim($tag) }}
                                                                    </flux:badge>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs text-green-500 mb-2">After:</div>
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach (explode(',', $newValue) as $tag)
                                                                @if ($tag && $tag !== 'None' && $tag !== '(empty)')
                                                                    <flux:badge size="sm" color="green"
                                                                        variant="subtle">{{ trim($tag) }}
                                                                    </flux:badge>
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
                                                            class="text-sm bg-white p-2 rounded border border-zinc-200 break-words">
                                                            {{ $oldValue }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs text-green-500 mb-2">After:</div>
                                                        <div
                                                            class="text-sm bg-white p-2 rounded border border-zinc-200 break-words">
                                                            {{ $newValue }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(in_array($event, ['created', 'deleted', 'force_deleted']) && !empty($formattedData))
                                <div class="mt-2 bg-zinc-50 rounded-lg p-3">
                                    <div class="grid grid-cols-1 gap-4">
                                        @foreach ($formattedData as $key => $value)
                                            @php
                                                $displayValue = is_array($value)
                                                    ? implode(', ', $value)
                                                    : (string) $value;
                                            @endphp
                                            <div class="flex">
                                                <span
                                                    class="text-xs font-medium text-zinc-500 w-42">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                <span
                                                    class="text-sm break-words">{{ $displayValue ?: '(empty)' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="flex justify-end">
                                <flux:button size="xs" variant="ghost" icon="eye"
                                    wire:click="viewActivityDetails({{ $log['id'] ?? 0 }})">
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

    {{-- Activity Detail Modal --}}
    <flux:modal name="activity-detail" class="w-full max-w-3xl">
        @if ($selectedActivity)
            @php
                $event = $selectedActivity['event'] ?? 'unknown';
                $eventColor = $event === 'created' ? 'green' : ($event === 'updated' ? 'blue' : 'yellow');
                $description = $selectedActivity['description'] ?? '';
                $causer = $selectedActivity['causer'] ?? 'System';
                $createdAt = $selectedActivity['created_at'] ?? '';
                $properties = $selectedActivity['properties'] ?? null;
            @endphp

            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <flux:heading size="lg">Activity Details</flux:heading>
                    <flux:badge color="{{ $eventColor }}">
                        {{ ucfirst($event) }}
                    </flux:badge>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <flux:label>Description</flux:label>
                        <div class="text-sm bg-zinc-50 p-3 rounded-lg border border-zinc-200">
                            {!! $description !!}
                        </div>
                    </div>

                    <flux:field>
                        <flux:label>Performed by</flux:label>
                        <flux:input readonly value="{{ $causer }}" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Date & Time</flux:label>
                        <flux:input readonly value="{{ $createdAt }}" />
                    </flux:field>
                </div>

                @if ($properties)
                    <flux:field>
                        <flux:label>Full Properties</flux:label>
                        <div
                            class="bg-zinc-950 p-4 rounded-lg text-emerald-400 text-xs font-mono overflow-auto max-h-60 border border-zinc-800 whitespace-pre-wrap">
                            {{ json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                        </div>
                    </flux:field>
                @endif
            </div>
        @endif
    </flux:modal>
</div>
