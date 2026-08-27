<?php

use Livewire\Volt\Component;
use App\Models\ContactCategory;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    // Form Fields
    public $categoryId = null;
    public $name = '';
    public $slug = '';
    public $description = '';
    public $icon = '';
    public $is_featured = false;
    public $is_active = true;
    public $status = 'active';

    // UI State
    public $viewType = 'active';
    public $search = '';

    // Activity Logs
    public array $activities = [];
    public ?array $selectedActivity = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return ContactCategory::query()
            ->when($this->viewType === 'trashed', fn($q) => $q->onlyTrashed())
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'categories' => $this->categories,
            'totalCount' => ContactCategory::count(),
            'activeCount' => ContactCategory::where('is_active', true)->count(),
            'featuredCount' => ContactCategory::where('is_featured', true)->count(),
            'statusActiveCount' => ContactCategory::where('status', 'active')->count(),
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['categoryId', 'name', 'slug', 'description', 'icon', 'is_featured', 'is_active', 'status']);
        $this->dispatch('modal-show', name: 'category-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $category = ContactCategory::withTrashed()->findOrFail($id);

        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description;
        $this->icon = $category->icon;
        $this->is_featured = (bool) $category->is_featured;
        $this->is_active = (bool) $category->is_active;
        $this->status = $category->status;

        $this->dispatch('modal-show', name: 'category-form');
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:contact_categories,slug' . ($this->categoryId ? ',' . $this->categoryId : ''),
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'slug' => $this->slug ?: Str::slug($this->name),
            'description' => $this->description,
            'icon' => $this->icon,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'status' => $this->status,
        ];

        if ($this->categoryId) {
            $category = ContactCategory::withTrashed()->where('id', $this->categoryId)->first();
            $category->update($data);
            $message = 'Category updated successfully!';
        } else {
            $category = ContactCategory::create($data);
            $message = 'Category created successfully!';
        }

        $this->dispatch('modal-close', name: 'category-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: $message);
        $this->reset(['categoryId', 'name', 'slug', 'description', 'icon', 'is_featured', 'is_active', 'status']);
    }

    public function delete($id)
    {
        try {
            $category = ContactCategory::findOrFail($id);
            $category->delete();
            $this->dispatch('toast', variant: 'warning', text: 'Category moved to trash.');
        } catch (\Exception $e) {
            $this->dispatch('toast', variant: 'error', text: 'Error deleting category!');
        }
    }

    public function restore($id)
    {
        try {
            $category = ContactCategory::onlyTrashed()->findOrFail($id);
            $category->restore();
            $this->dispatch('toast', variant: 'success', text: 'Category restored successfully.');
        } catch (\Exception $e) {
            $this->dispatch('toast', variant: 'error', text: 'Error restoring category!');
        }
    }

    public function forceDelete($id)
    {
        try {
            $category = ContactCategory::onlyTrashed()->findOrFail($id);
            $category->forceDelete();
            $this->dispatch('toast', variant: 'error', text: 'Category deleted permanently.');
        } catch (\Exception $e) {
            $this->dispatch('toast', variant: 'error', text: 'Error deleting category permanently!');
        }
    }

    // Real-time slug generation
    public function updatedName($value)
    {
        if (!$this->slug || $this->slug === Str::slug($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    // View activity logs with detailed changes
    public function viewLogs(int $id): void
    {
        try {
            $this->activities = Activity::query()
                ->with('causer')
                ->where('subject_id', $id)
                ->where('subject_type', ContactCategory::class)
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
            if (in_array($field, ['description', 'content', 'details'])) {
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

            // Convert boolean values to readable text
            if (is_bool($oldValue)) {
                $oldValue = $oldValue ? 'Yes' : 'No';
            }
            if (is_bool($newValue)) {
                $newValue = $newValue ? 'Yes' : 'No';
            }

            $formatted[] = [
                'field' => $this->formatFieldName($field),
                'type' => $type,
                'old' => $oldValue ?? '(empty)',
                'new' => $newValue ?? '(empty)',
            ];
        }

        return $formatted;
    }

    private function formatFieldName($field): string
    {
        $names = [
            'name' => 'Category Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'icon' => 'Icon',
            'is_featured' => 'Featured Status',
            'is_active' => 'Active Status',
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
};
?>

<div class="">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Contact Categories</flux:heading>
            <flux:subheading>Manage your contact categories efficiently.</flux:subheading>
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
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by name..." icon="magnifying-glass" />
    </div>


    {{-- Table --}}
    <flux:table :paginate="$this->categories">
        <flux:table.columns>
            <flux:table.column>Icon</flux:table.column>
            <flux:table.column sortable>Name</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Featured</flux:table.column>
            <flux:table.column>Active</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->categories as $category)
                <flux:table.row :key="$category->id">
                    <flux:table.cell>
                        @if ($category->icon)
                            <div
                                class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <flux:icon :name="$category->icon" class="w-5 h-5 text-gray-600 dark:text-gray-300" />
                            </div>
                        @else
                            <div
                                class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <flux:icon name="tag" class="w-5 h-5 text-gray-400" />
                            </div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        <div>{{ $category->name }}</div>
                        @if ($category->description)
                            <div class="text-xs text-zinc-500">{{ Str::limit($category->description, 50) }}</div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <code
                            class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $category->slug }}</code>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($category->is_featured)
                            <flux:badge size="sm" color="amber">Featured</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">Standard</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$category->is_active ? 'green' : 'red'">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$category->status === 'active' ? 'green' : 'yellow'">
                            {{ ucfirst($category->status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        @if ($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="clock"
                                wire:click="viewLogs({{ $category->id }})" title="View Activity Logs" />
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $category->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                wire:confirm="Are you sure?" wire:click="delete({{ $category->id }})" />
                        @else
                            <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                wire:click="restore({{ $category->id }})" />
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                wire:confirm="This will be deleted permanently!"
                                wire:click="forceDelete({{ $category->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-10 text-zinc-400">
                        <div class="flex flex-col items-center justify-center">
                            <flux:icon name="folder-open" class="w-16 h-16 text-gray-400 mb-4" />
                            <p class="text-gray-500 dark:text-gray-400 text-lg">No categories found</p>
                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Click the "Create New" button to
                                create one</p>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="category-form" class="md:w-160">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $categoryId ? 'Edit Category' : 'Create Category' }}</flux:heading>
                <flux:subheading>Manage category details and settings.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <flux:input wire:model="name" label="Category Name" placeholder="Enter category name" required />
                </div>

                <!-- Slug -->
                <div>
                    <flux:input wire:model="slug" label="Slug" placeholder="auto-generated from name" />
                </div>

                <!-- Icon -->
                <div>
                    <flux:input wire:model="icon" label="Icon" placeholder="heroicon-name"
                        hint="Enter icon name from Heroicons" />
                </div>

                <!-- Status -->
                <div>
                    <flux:select wire:model="status" label="Status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </flux:select>
                </div>

                <!-- Featured Checkbox -->
                <div class="flex items-center mt-6">
                    <flux:checkbox wire:model="is_featured" label="Feature this category" />
                </div>

                <!-- Active Checkbox -->
                <div class="flex items-center mt-6">
                    <flux:checkbox wire:model="is_active" label="Category is active" />
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <flux:textarea wire:model="description" label="Description"
                        placeholder="Enter category description (optional)" rows="3" />
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $categoryId ? 'Update Category' : 'Create Category' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Activity Logs Modal --}}
    <flux:modal name="activity-logs" class="w-full max-w-4xl">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <flux:heading size="lg">Activity History</flux:heading>
                    <flux:subheading>Detailed change log with before/after values</flux:subheading>
                </div>
            </div>

            <div class="space-y-6 pr-2 max-h-[60vh] overflow-y-auto">
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

                                        <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                                {{ $field }}
                                            </div>

                                            @if ($type === 'textarea')
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-red-500 mb-2">Before:</div>
                                                        <div
                                                            class="text-xs bg-white dark:bg-zinc-900 p-2 rounded border border-zinc-400/25 max-h-32 overflow-y-auto">
                                                            {!! nl2br(e($oldValue)) !!}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs text-green-500 mb-2">After:</div>
                                                        <div
                                                            class="text-xs bg-white dark:bg-zinc-900 p-2 rounded border border-zinc-400/25 max-h-32 overflow-y-auto">
                                                            {!! nl2br(e($newValue)) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-red-500 mb-2">Before:</div>
                                                        <div
                                                            class="text-sm bg-white dark:bg-zinc-900 p-2 rounded border border-zinc-400/25 break-words">
                                                            {{ $oldValue }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs text-green-500 mb-2">After:</div>
                                                        <div
                                                            class="text-sm bg-white dark:bg-zinc-900 p-2 rounded border border-zinc-400/25 break-words">
                                                            {{ $newValue }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(in_array($event, ['created', 'deleted', 'force_deleted']) && !empty($formattedData))
                                <div class="mt-2 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-3">
                                    <div class="grid grid-cols-1 gap-4">
                                        @foreach ($formattedData as $key => $value)
                                            @php
                                                $displayValue = is_array($value)
                                                    ? implode(', ', $value)
                                                    : (string) $value;
                                            @endphp
                                            <div class="flex">
                                                <span
                                                    class="text-xs font-medium text-zinc-500 dark:text-zinc-400 w-42">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
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
                $eventColor =
                    $event === 'created'
                        ? 'green'
                        : ($event === 'updated'
                            ? 'blue'
                            : ($event === 'restored'
                                ? 'yellow'
                                : 'red'));
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
                        <flux:input readonly value="{{ $description }}" />
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
                            class="bg-zinc-950 dark:bg-zinc-900 p-4 rounded-lg text-emerald-400 text-xs font-mono overflow-auto max-h-60 border border-zinc-800 whitespace-pre-wrap">
                            {{ json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                        </div>
                    </flux:field>
                @endif
            </div>
        @endif
    </flux:modal>

    {{-- Toast Notifications --}}
    <flux:toast />
</div>
