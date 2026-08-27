<?php

use Livewire\Volt\Component;
use App\Models\TourismBd;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\{Computed, Validate, On};
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    public array $activities = [];
    public ?array $selectedActivity = null;

    // Collections
    public $divisions = [];

    // Form Fields
    public $tourismBdId;

    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|min:3|unique:tourism_bds,slug|max:255')]
    public $slug = '';

    #[Validate('required|string|max:100')]
    public $tourism_type = '';

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
            'thanas' => $this->thanas,
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['tourismBdId', 'title', 'slug', 'description', 'division_id', 'district_id', 'thana_id', 'is_featured', 'status', 'images', 'map']);
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
        $this->slug = $tourism->slug;
        $this->tourism_type = $tourism->tourism_type;
        $this->description = $tourism->description;
        $this->division_id = $tourism->division_id;
        $this->district_id = $tourism->district_id;
        $this->thana_id = $tourism->thana_id;
        $this->is_featured = (bool) $tourism->is_featured;
        $this->status = $tourism->status;

        // Load dependent dropdowns
        $this->districts = District::where('division_id', $this->division_id)->get();
        $this->thanas = Thana::where('district_id', $this->district_id)->get();

        // Load existing media
        $this->images = $tourism
            ->getMedia('tourism_images')
            ->map(
                fn($m) => [
                    'id' => $m->id,
                    'url' => $m->getUrl(),
                    'is_existing' => true,
                ],
            )
            ->toArray();

        $this->dispatch('modal-show', name: 'tourism-form');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file) {
            return;
        }

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
            'slug' => [
                'required',
                'min:3',
                'max:255',
                // Ignore current record when editing
                \Illuminate\Validation\Rule::unique('tourism_bds', 'slug')->ignore($this->tourismBdId),
            ],
            'tourism_type' => 'required',
            'description' => 'required|min:10',
            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'thana_id' => 'nullable|exists:thanas,id',
            'is_featured' => 'boolean',
            'status' => 'required|in:1,0',
        ]);

        $tourism = TourismBd::updateOrCreate(
            ['id' => $this->tourismBdId],
            [
                'title' => $this->title,
                'tourism_type' => $this->tourism_type,
                'description' => $this->description,
                'slug' => $this->slug ?: Str::slug($this->title),
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'thana_id' => $this->thana_id,
                'is_featured' => $this->is_featured,
                'status' => $this->status,
            ],
        );

        // ===== Process Gallery Images =====
        if (!empty($this->images)) {
            foreach ($this->images as $file) {
                if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $slug = Str::slug($tourism->slug ?: $tourism->title);
                    $fileName = "{$slug}-totthobox-tourism-bd-" . Str::lower(Str::random(6)) . '.webp';

                    // ১. মেটাডেটা রিমুভ করে WebP এ কনভার্ট
                    $processedImage = Image::read($file->getRealPath());
                    $processedImage->toWebp(85)->save($file->getRealPath());

                    // ২. ক্লিন WebP ফাইল সেভ
                    $tourism
                        ->addMedia($file->getRealPath())
                        ->usingFileName($fileName)
                        ->usingName("{$tourism->title} - Totthobox Tourism BD")
                        ->toMediaCollection('tourism_images');
                }
            }
        }

        // ===== Process Map =====
        if ($this->map instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $slug = Str::slug($tourism->title);
            $fileName = "{$slug}-totthobox-tourism-map-" . Str::lower(Str::random(6)) . '.webp';

            // Map-ও WebP + metadata free করা
            $processedMap = Image::read($this->map->getRealPath());
            $processedMap->toWebp(85)->save($this->map->getRealPath());

            $tourism
                ->addMedia($this->map->getRealPath())
                ->usingFileName($fileName)
                ->usingName("{$tourism->title} - Totthobox Tourism Map")
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

    // Helper method to safely get nested array values
    private function safeGet($array, $key, $default = null)
    {
        if (!is_array($array) && !is_object($array)) {
            return $default;
        }

        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $segment) {
            if (is_array($value) && isset($value[$segment])) {
                $value = $value[$segment];
            } elseif (is_object($value) && isset($value->$segment)) {
                $value = $value->$segment;
            } else {
                return $default;
            }
        }

        return $value;
    }

    // View activity logs with detailed changes
    public function viewLogs(int $id): void
    {
        try {
            $this->activities = Activity::query()
                ->with('causer')
                ->where('subject_id', $id)
                ->where('subject_type', TourismBd::class)
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
                        } elseif ($activity->event === 'created' && isset($propertiesArray['created_data'])) {
                            $activity->formatted_data = $propertiesArray['created_data'];
                        } elseif ($activity->event === 'deleted' && isset($propertiesArray['deleted_data'])) {
                            $activity->formatted_data = $propertiesArray['deleted_data'];
                        } elseif ($activity->event === 'force_deleted' && isset($propertiesArray['permanently_deleted_data'])) {
                            $activity->formatted_data = $propertiesArray['permanently_deleted_data'];
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
            } elseif (in_array($field, ['categories', 'tags', 'types'])) {
                $type = 'categories';
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
            'slug' => 'Slug',
            'description' => 'Description',
            'tourism_type' => 'Tourism Type',
            'division_id' => 'Division',
            'district_id' => 'District',
            'thana_id' => 'Thana',
            'is_featured' => 'Featured Status',
            'status' => 'Status',
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

<div class="">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Tourism BD Management</flux:heading>
            <flux:subheading>Manage tourism destinations in Bangladesh.</flux:subheading>
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
                            @foreach ($images->take(3) as $media)
                                <flux:avatar src="{{ $media->getUrl() }}" />
                            @endforeach
                            @if ($images->count() > 3)
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
                            @can('restore data')
                                <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                    wire:click="restore({{ $item->id }})" />
                            @endcan
                            @can('permanent delete')
                                <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                    wire:confirm="This will be deleted permanently!"
                                    wire:click="forceDelete({{ $item->id }})" />
                            @endcan
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
    <flux:modal name="tourism-form" class="md:w-240">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $tourismBdId ? 'Edit Destination' : 'Add New Destination' }}
                </flux:heading>
                <flux:subheading>Manage tourism spot details and location information.</flux:subheading>
            </div>

            <flux:input wire:model="title" label="Destination Title" placeholder="Enter destination title..." />
            <flux:input wire:model="slug" label="Slug" placeholder="Enter slug..." />

            <flux:select wire:model="tourism_type" label="পর্যটনের ধরন (Tourism Type)" placeholder="ধরন নির্বাচন করুন">
                <option value="">ধরন নির্বাচন করুন</option>

                {{-- ঐতিহাসিক ক্যাটাগরি --}}
                <option value="historical">ঐতিহাসিক ও প্রত্নতাত্ত্বিক (Historical & Archaeological)</option>
                <option value="heritage">রাজপ্রাসাদ ও জমিদার বাড়ি (Palaces & Heritage)</option>

                {{-- প্রাকৃতিক ক্যাটাগরি --}}
                <option value="natural">প্রাকৃতিক সৌন্দর্য (Natural Beauty)</option>
                <option value="waterfall">ঝর্ণা ও জলপ্রপাত (Waterfalls)</option>
                <option value="beach">সমুদ্র সৈকত (Sea Beach)</option>
                <option value="hill_station">পাহাড় ও পার্বত্য এলাকা (Hills & Mountains)</option>
                <option value="forest">বন ও বন্যপ্রাণী (Forest & Wildlife)</option>

                {{-- ধর্মীয় ও সাংস্কৃতিক --}}
                <option value="religious">ধর্মীয় ও পবিত্র স্থান (Religious Sites)</option>
                <option value="cultural">সাংস্কৃতিক ও জাদুঘর (Cultural & Museums)</option>

                {{-- আধুনিক ও বিনোদন --}}
                <option value="adventure">অ্যাডভেঞ্চার ও ট্র্যাকিং (Adventure & Trekking)</option>
                <option value="resort">রিসোর্ট ও বিনোদন কেন্দ্র (Resorts & Amusement)</option>
                <option value="riverine">হাওর ও নদীকেন্দ্রিক (Riverine & Haor)</option>
                <option value="picnic">পিকনিক স্পট (Picnic Spot)</option>
            </flux:select>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select wire:model.live="division_id" label="Division" placeholder="Select Division">
                    <option value="">Select Division</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="district_id" label="District" placeholder="Select District"
                    :disabled="!$division_id">
                    <option value="">Select District</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="thana_id" label="Thana" placeholder="Select Thana"
                    :disabled="!$district_id">
                    <option value="">Select Thana</option>
                    @foreach ($thanas as $thana)
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
                    <flux:file-upload wire:model.live="map" multiple />
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

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Save Destination
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
                                            @elseif($type === 'categories')
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-red-500 mb-2">Before:</div>
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach (explode(', ', $oldValue) as $cat)
                                                                @if ($cat && $cat !== 'None' && $cat !== '(empty)')
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
                                                            @foreach (explode(', ', $newValue) as $cat)
                                                                @if ($cat && $cat !== 'None' && $cat !== '(empty)')
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
                        <flux:badge>
                            {!! $description !!}
                        </flux:badge>
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
