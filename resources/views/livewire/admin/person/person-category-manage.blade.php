<?php

use Livewire\Volt\Component;
use App\Models\PeopleCategory;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    // Properties with proper typing
    public ?int $categoryId = null;
    public string $name = '';
    public string $search = '';
    public array $activities = [];
    public ?array $selectedActivity = null; // For detailed view

    // Rules for validation
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255', 'unique:people_categories,name,' . $this->categoryId],
        ];
    }

    // Custom validation messages
    protected function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.min' => 'The name must be at least 2 characters.',
            'name.unique' => 'This category name already exists.',
        ];
    }

    // Reset form
    public function resetForm(): void
    {
        $this->reset(['categoryId', 'name']);
        $this->resetValidation();
    }

    // Show create form
    public function showCreateForm(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'category-form');
    }

    // Edit category
    public function edit(int $id): void
    {
        try {
            $category = PeopleCategory::findOrFail($id);

            $this->categoryId = $category->id;
            $this->name = $category->name;

            $this->dispatch('modal-show', name: 'category-form');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Category not found.', type: 'error');
        }
    }

    // Save category with activity logging
    public function save(): void
    {
        $this->validate();

        // Get old data before update for logging
        $oldData = null;
        if ($this->categoryId) {
            $oldCategory = PeopleCategory::find($this->categoryId);
            if ($oldCategory) {
                $oldData = [
                    'name' => $oldCategory->name,
                ];
            }
        }

        // Create or update category
        $category = PeopleCategory::updateOrCreate(['id' => $this->categoryId], ['name' => $this->name]);

        // Log the changes with detailed information
        if ($this->categoryId) {
            // This is an update - log what changed
            $changes = [];

            if ($oldData && $oldData['name'] !== $this->name) {
                $changes['name'] = [
                    'old' => $oldData['name'],
                    'new' => $this->name,
                ];
            }

            // Log the changes if any
            if (!empty($changes)) {
                activity()
                    ->performedOn($category)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'changes' => $changes,
                        'old_data' => $oldData,
                        'new_data' => ['name' => $this->name],
                    ])
                    ->event('updated')
                    ->log('updated category');
            }
        } else {
            // This is a creation
            activity()
                ->performedOn($category)
                ->causedBy(Auth::user())
                ->withProperties([
                    'created_data' => [
                        'name' => $this->name,
                    ],
                ])
                ->event('created')
                ->log('created category');
        }

        $this->dispatch('modal-close', name: 'category-form');
        $this->dispatch('toast', message: $this->categoryId ? 'Category updated successfully.' : 'Category created successfully.', type: 'success');

        $this->resetForm();
    }

    // Delete with logging
    public function delete(int $id): void
    {
        $category = PeopleCategory::findOrFail($id);

        // Store data before deletion for logging
        $deletedData = [
            'name' => $category->name,
        ];

        // Log before deletion
        activity()
            ->performedOn($category)
            ->causedBy(Auth::user())
            ->withProperties([
                'deleted_data' => $deletedData,
            ])
            ->event('deleted')
            ->log('deleted category');

        // Delete the category
        $category->delete();

        $this->dispatch('toast', message: 'Category deleted successfully.', type: 'warning');
    }

    // View activity logs
    public function viewLogs(int $id): void
    {
        $this->activities = Activity::query()
            ->with('causer')
            ->where('subject_id', $id)
            ->where('subject_type', PeopleCategory::class)
            ->latest()
            ->get()
            ->map(function ($activity) {
                $properties = $activity->properties;

                // Format the changes for display
                if ($activity->event === 'updated' && isset($properties['changes'])) {
                    $activity->formatted_changes = $this->formatChanges($properties['changes']);
                } elseif ((in_array($activity->event, ['created', 'deleted']) && isset($properties['created_data'])) || isset($properties['deleted_data'])) {
                    $data = $properties['created_data'] ?? ($properties['deleted_data'] ?? []);
                    $activity->formatted_data = $data;
                }

                return $activity;
            })
            ->toArray();

        $this->dispatch('modal-show', name: 'activity-logs');
    }

    // Format changes for better display
    private function formatChanges(array $changes): array
    {
        $formatted = [];

        foreach ($changes as $field => $change) {
            if ($field === 'name') {
                $formatted[] = [
                    'field' => 'Category Name',
                    'old' => $change['old'],
                    'new' => $change['new'],
                    'type' => 'text',
                ];
            }
        }

        return $formatted;
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

    // Clear logs modal
    public function clearLogs(): void
    {
        $this->activities = [];
        $this->selectedActivity = null;
        $this->dispatch('modal-close', name: 'activity-logs');
        $this->dispatch('modal-close', name: 'activity-detail');
    }

    // With method for data passing to view
    public function with(): array
    {
        $tableExists = Schema::hasTable('people_categories');

        $query = PeopleCategory::query()->when($this->search, function (Builder $query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        });

        return [
            'categories' => $tableExists ? $query->latest()->paginate(10) : collect(),
            'tableExists' => $tableExists,
        ];
    }

    // Listeners
    protected function getListeners(): array
    {
        return [
            'refresh-categories' => '$refresh',
        ];
    }
}; ?>

<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl">Categories Management</flux:heading>
            <flux:subheading>Manage categories for people and profiles.</flux:subheading>
        </div>
        <div class="flex items-center gap-4">
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary">
                Add New Category
            </flux:button>
        </div>
    </div>

    {{-- Table Missing Warning --}}
    @if (!$tableExists)
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-center gap-2 text-amber-700">
                <flux:icon name="exclamation-triangle" class="w-5 h-5" />
                <span class="text-sm">Warning: The 'people_categories' table does not exist in the database. Please run
                    migrations.</span>
            </div>
        </div>
    @endif

    {{-- Search --}}
    <div>
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by name..." icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$categories">
        <flux:table.columns>
            <flux:table.column>Category Name</flux:table.column>
            <flux:table.column>Created At</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($categories as $category)
                <flux:table.row :key="$category->id">
                    {{-- Name --}}
                    <flux:table.cell class="font-medium">
                        <div class="flex items-center gap-4">
                            <span>{{ $category->name }}</span>
                        </div>
                    </flux:table.cell>

                    {{-- Created At --}}
                    <flux:table.cell>
                        {{ $category->created_at?->format('d M, Y') }}
                    </flux:table.cell>

                    {{-- Actions --}}
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-1">
                            <flux:button variant="ghost" size="sm" icon="clock"
                                wire:click="viewLogs({{ $category->id }})" title="View Activity Logs" />
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="edit({{ $category->id }})" title="Edit" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                wire:click="delete({{ $category->id }})"
                                wire:confirm="Are you sure you want to delete this category?" title="Delete" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="text-center py-12">
                        <div class="flex flex-col items-center gap-2 text-zinc-400">
                            <flux:icon name="folder" class="w-12 h-12" />
                            <p>No categories found.</p>
                            @if ($search)
                                <flux:button size="sm" variant="ghost" wire:click="$set('search', '')">
                                    Clear search
                                </flux:button>
                            @else
                                <flux:button size="sm" variant="primary" wire:click="showCreateForm">
                                    Add your first category
                                </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Activity Logs Modal --}}
    <flux:modal name="activity-logs" class="md:w-[50rem] max-h-[80vh]">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <flux:heading size="lg">Activity History</flux:heading>
                    <flux:subheading>Detailed change log with before/after values</flux:subheading>
                </div>
            </div>

            <div class="space-y-6 overflow-y-auto max-h-[60vh] pr-2">
                @forelse($activities as $log)
                    <div
                        class="relative pl-4 border-l-2 {{ $log['event'] === 'created'
                            ? 'border-green-500'
                            : ($log['event'] === 'updated'
                                ? 'border-blue-500'
                                : 'border-red-500') }}">
                        <div class="flex flex-col gap-4">
                            {{-- Header --}}
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <flux:badge size="sm"
                                        color="{{ $log['event'] === 'created' ? 'green' : ($log['event'] === 'updated' ? 'blue' : 'red') }}">
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
                                <flux:profile :chevron="false" :name="$log['causer']['name'] ?? 'System'" />
                            </div>

                            {{-- Changes Display --}}
                            @if ($log['event'] === 'updated' && isset($log['formatted_changes']))
                                <div class="mt-2 space-y-3">
                                    @foreach ($log['formatted_changes'] as $change)
                                        <div class="bg-zinc-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-zinc-700 mb-2">
                                                {{ $change['field'] }}
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <div class="text-xs text-red-500 mb-2">Before:</div>
                                                    <div class="text-sm bg-white p-2 rounded border border-zinc-200">
                                                        {{ $change['old'] ?: '(empty)' }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-green-500 mb-2">After:</div>
                                                    <div class="text-sm bg-white p-2 rounded border border-zinc-200">
                                                        {{ $change['new'] ?: '(empty)' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(in_array($log['event'], ['created', 'deleted']) && isset($log['formatted_data']))
                                <div class="mt-2 bg-zinc-50 rounded-lg p-3">
                                    <div class="grid grid-cols-1 gap-4">
                                        @foreach ($log['formatted_data'] as $key => $value)
                                            <div class="flex">
                                                <span
                                                    class="text-xs font-medium text-zinc-500 w-24">{{ ucfirst($key) }}:</span>
                                                <span class="text-sm">{{ $value }}</span>
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

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Activity Detail Modal --}}
    <flux:modal name="activity-detail" class="md:w-[40rem]">
        @if ($selectedActivity)
            <div class="space-y-4">
                <flux:heading size="lg">Activity Details</flux:heading>

                <div class="space-y-3">
                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <span class="font-medium text-zinc-500">Event:</span>
                        <span class="col-span-2">
                            <flux:badge
                                color="{{ $selectedActivity['event'] === 'created'
                                    ? 'green'
                                    : ($selectedActivity['event'] === 'updated'
                                        ? 'blue'
                                        : 'red') }}">
                                {{ ucfirst($selectedActivity['event']) }}
                            </flux:badge>
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <span class="font-medium text-zinc-500">Description:</span>
                        <span class="col-span-2">{{ $selectedActivity['description'] }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <span class="font-medium text-zinc-500">Performed by:</span>
                        <span class="col-span-2">{{ $selectedActivity['causer'] }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <span class="font-medium text-zinc-500">Date & Time:</span>
                        <span class="col-span-2">{{ $selectedActivity['created_at'] }}</span>
                    </div>

                    @if (!empty($selectedActivity['properties']))
                        <div class="mt-4">
                            <div class="font-medium text-zinc-700 mb-2">Full Properties:</div>
                            <pre class="">{{ json_encode($selectedActivity['properties'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">Close</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Category Form Modal --}}
    <flux:modal name="category-form" class="md:w-120">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $categoryId ? 'Edit Category' : 'Create New Category' }}
                </flux:heading>
                <flux:subheading>
                    {{ $categoryId ? 'Update the category details below.' : 'Enter the details for the new category.' }}
                </flux:subheading>
            </div>

            {{-- Name --}}
            <flux:input wire:model="name" label="Category Name"
                placeholder="e.g., Government Official, Diplomat, Expert" required />

            {{-- Form Actions --}}
            <div class="flex justify-end gap-4">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">
                    {{ $categoryId ? 'Update Category' : 'Create Category' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
