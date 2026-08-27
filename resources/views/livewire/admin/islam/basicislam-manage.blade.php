<?php

use Livewire\Volt\Component;
use App\Models\BasicIslam;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\{Computed};
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination, WithFileUploads;

    public array $activities = [];
    public ?array $selectedActivity = null;

    public $images = [];
    public $existingImages = [];

    // Form Fields
    public $basicIslamId;

    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|min:10')]
    public $description = '';

    #[Validate('required|min:10|unique:basic_islams,slug,{{ $this->basicIslamId }}')]
    public $slug = '';

    #[Validate('boolean')]
    public $is_featured = false;

    #[Validate('required|in:1,0')]
    public $status = 1;

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
    public function basicIslams()
    {
        return ($this->viewType === 'trashed' ? BasicIslam::onlyTrashed() : BasicIslam::query())->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))->latest()->paginate(10);
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['basicIslamId', 'title', 'description', 'is_featured', 'status', 'images', 'existingImages']);
        $this->dispatch('modal-show', name: 'basic-islam-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $this->reset(['images']);
        $item = BasicIslam::withTrashed()->findOrFail($id);

        $this->basicIslamId = $item->id;
        $this->title = $item->title;
        $this->slug = $item->slug;
        $this->description = $item->description;
        $this->is_featured = (bool) $item->is_featured;
        $this->status = $item->status;

        // Load existing media
        $this->images = $item
            ->getMedia('images')
            ->map(
                fn($m) => [
                    'id' => $m->id,
                    'url' => $m->getUrl(),
                    'is_existing' => true,
                ],
            )
            ->toArray();

        $this->dispatch('modal-show', name: 'basic-islam-form');
    }

    public function save()
    {
        $this->validate(); // Title, Description ইত্যাদির ভ্যালিডেশন হবে

        // শুধুমাত্র নতুন আপলোড হওয়া ফাইলগুলো আলাদা করা
        $newImages = array_filter($this->images, fn($img) => $img instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile);

        // নতুন ফাইল থাকলে ম্যানুয়ালি ভ্যালিডেট করা
        if (!empty($newImages)) {
            $validator = \Illuminate\Support\Facades\Validator::make(
                ['new_uploads' => $newImages],
                ['new_uploads.*' => 'image|max:2048'],
                [
                    'new_uploads.*.image' => 'The file must be an image.',
                    'new_uploads.*.max' => 'Image size must not exceed 2MB.',
                ],
            );

            if ($validator->fails()) {
                $this->addError('images', $validator->errors()->first('new_uploads.*'));
                return;
            }
        }

        $data = [
            'title' => $this->title,
            'slug' => $this->slug ?: Str::slug($this->title),
            'description' => $this->description,
            'is_featured' => $this->is_featured,
            'status' => $this->status,
        ];

        $basicIslam = BasicIslam::updateOrCreate(['id' => $this->basicIslamId], $data);

        // Upload & Strip Copyright Metadata + convert to WebP (same style as Sign)
        if (!empty($newImages)) {
            foreach ($newImages as $file) {
                $slug = Str::slug($basicIslam->title);
                $fileName = "{$slug}-totthobox-basic-islam-" . Str::lower(Str::random(6)) . '.webp';

                // ১. মূল ইমেজের সব মেটাডেটা রিমুভ করে WebP করা
                $processedImage = Image::read($file->getRealPath());
                $processedImage->toWebp(85)->save($file->getRealPath());

                // ২. সম্পূর্ণ ক্লিন WebP ফাইলটি মেইন মিডিয়া হিসেবে সেভ করা
                $basicIslam
                    ->addMedia($file->getRealPath())
                    ->usingFileName($fileName)
                    ->usingName("{$basicIslam->title} - Totthobox Basic Islam")
                    ->toMediaCollection('images');
            }
        }

        $this->dispatch('modal-close', name: 'basic-islam-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'তথ্যটি সংরক্ষিত হয়েছে।');
        $this->reset(['basicIslamId', 'images', 'existingImages']);
    }
    public function forceDelete($id)
    {
        $item = BasicIslam::onlyTrashed()->findOrFail($id);
        $item->clearMediaCollection('images');
        $item->forceDelete();

        $this->dispatch('toast', variant: 'error', text: 'আইটেমটি স্থায়ীভাবে ডিলিট করা হয়েছে।');
    }

    // একটাই method – সব property-র জন্য কাজ করে
    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file) {
            return;
        }

        // Existing image হলে Media Library থেকে মুছে ফেলো
        if (is_array($file) && isset($file['is_existing']) && $file['is_existing']) {
            $item = BasicIslam::withTrashed()->findOrFail($this->basicIslamId);
            $item->deleteMedia($file['id']);
        }

        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function delete($id)
    {
        BasicIslam::find($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'আইটেমটি ট্র্যাশে পাঠানো হয়েছে।');
    }

    public function restore($id)
    {
        BasicIslam::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'আইটেমটি রিস্টোর করা হয়েছে।');
    }

    // View activity logs with detailed changes
    public function viewLogs(int $id): void
    {
        try {
            $this->activities = Activity::query()
                ->with('causer')
                ->where('subject_id', $id)
                ->where('subject_type', BasicIslam::class)
                ->latest()
                ->get()
                ->map(function ($activity) {
                    try {
                        $properties = $activity->properties ?? collect();

                        // Safely convert to array if it's a collection
                        $propertiesArray = $properties instanceof \Illuminate\Support\Collection ? $properties->toArray() : (array) $properties;

                        // Format the changes for display with null checks
                        if ($activity->event === 'updated' && isset($propertiesArray['changes'])) {
                            $activity->formatted_changes = $this->formatChanges($propertiesArray['changes']);
                        } elseif ($activity->event === 'created' && isset($propertiesArray['attributes'])) {
                            $activity->formatted_data = $propertiesArray['attributes'];
                        } elseif ($activity->event === 'deleted' && isset($propertiesArray['old'])) {
                            $activity->formatted_data = $propertiesArray['old'];
                        }

                        return $activity;
                    } catch (\Exception $e) {
                        // Return a safe version of the activity if there's an error
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
            // Skip if change is not an array with old/new structure
            if (!is_array($change) || !isset($change['old']) || !isset($change['new'])) {
                continue;
            }

            // Determine field type for display
            $type = 'text';
            if (in_array($field, ['description', 'content', 'details', 'long_description'])) {
                $type = 'textarea';
            }

            // Safely get old and new values
            $oldValue = $change['old'] ?? null;
            $newValue = $change['new'] ?? null;

            // Format arrays and objects
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
            'is_featured' => 'Featured Status',
            'status' => 'Status',
            'slug' => 'Slug',
        ];

        return $names[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    // View specific activity details
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
            <flux:heading size="xl">Basic Islam Management</flux:heading>
            <flux:subheading>Manage your Islamic basic contents with activity tracking.</flux:subheading>
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
    <flux:table :paginate="$this->basicIslams">
        <flux:table.columns>
            <flux:table.column sortable>Image</flux:table.column>
            <flux:table.column sortable>Title</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->basicIslams as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>
                        @php
                            $mediaItems = $item->getMedia('images');
                            $mediaCount = $mediaItems->count();
                        @endphp

                        <flux:avatar.group class="size-10">
                            @if ($mediaCount > 0)
                                {{-- প্রথম ছবিটি প্রদর্শন করবে --}}
                                @foreach ($mediaItems->take(1) as $media)
                                    <flux:avatar src="{{ $media->getUrl('thumb') }}" />
                                @endforeach

                                {{-- ১টির বেশি ছবি থাকলে কতটি অতিরিক্ত আছে তা দেখাবে --}}
                                @if ($mediaCount > 1)
                                    <flux:avatar initials="+{{ bn_num($mediaCount - 1) }}" />
                                @endif
                            @else
                                {{-- কোনো ছবি না থাকলে টাইটেল দিয়ে ফলব্যাক দেখাবে --}}
                                <flux:avatar initials="{{ mb_substr($item->title, 0, 2) }}" />
                            @endif
                        </flux:avatar.group>
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        <div>{{ $item->title }}</div>
                        <div class="text-xs text-zinc-500">{{ $item->status ? 'Published' : 'Draft' }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($item->is_featured)
                            <flux:badge size="sm" color="amber">Featured</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">Standard</flux:badge>
                        @endif
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
                                wire:confirm="Permanent delete?" wire:click="forceDelete({{ $item->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="text-center py-10 text-zinc-400">No records found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="basic-islam-form" class="md:w-[45rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $basicIslamId ? 'Edit Content' : 'Add New Content' }}</flux:heading>
                <flux:subheading>Manage Islamic content with activity tracking.</flux:subheading>
            </div>

            <flux:input wire:model="title" label="Title" placeholder="Enter title..." />
            <flux:input wire:model="slug" label="Slug" placeholder="Enter slug..." />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model="status" label="Status">
                    <option value="1">Published</option>
                    <option value="0">Draft</option>
                </flux:select>
                <div class="pt-6">
                    <flux:checkbox wire:model="is_featured" label="Mark as Featured" />
                </div>
            </div>

            <div wire:ignore>
                <flux:editor wire:model="description" label="Description" />
            </div>

            <div>
                <flux:file-upload wire:model="images" multiple />
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Save Changes
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
                            {{-- Header --}}
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

                            {{-- User --}}
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-zinc-600">By:</span>
                                <flux:profile :chevron="false" name="{{ $causerName }}"
                                    avatar="{{ $causerAvatar }}" />
                            </div>

                            {{-- Changes Display --}}
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
                                                    class="text-xs font-medium text-zinc-500 w-24">{{ ucfirst($key) }}:</span>
                                                <span
                                                    class="text-sm break-words">{{ $displayValue ?: '(empty)' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- View Details Button --}}
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
