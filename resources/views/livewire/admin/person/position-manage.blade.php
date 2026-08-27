<?php

use Livewire\Volt\Component;
use App\Models\Position;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    // Properties with proper typing
    public ?int $positionId = null;
    public string $title = '';
    public string $search = '';
    public string $viewType = 'active'; // active or trashed
    public array $activities = [];
    public ?array $selectedActivity = null; // For detailed view

    // Rules for validation
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:255', 'unique:positions,title,' . $this->positionId],
        ];
    }

    // Custom validation messages
    protected function messages(): array
    {
        return [
            'title.required' => 'The position title is required.',
            'title.min' => 'The title must be at least 2 characters.',
            'title.unique' => 'This position title already exists.',
        ];
    }

    // Reset form
    public function resetForm(): void
    {
        $this->reset(['positionId', 'title']);
        $this->resetValidation();
    }

    // Show create form
    public function showCreateForm(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'position-form');
    }

    // Edit position
    public function edit(int $id): void
    {
        try {
            $position = Position::findOrFail($id);

            $this->positionId = $position->id;
            $this->title = $position->title;

            $this->dispatch('modal-show', name: 'position-form');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Position not found.', type: 'error');
        }
    }

    // Save position with activity logging
    public function save(): void
    {
        $this->validate();

        // Get old data before update for logging
        $oldData = null;
        if ($this->positionId) {
            $oldPosition = Position::find($this->positionId);
            if ($oldPosition) {
                $oldData = [
                    'title' => $oldPosition->title,
                ];
            }
        }

        // Create or update position
        $position = Position::updateOrCreate(['id' => $this->positionId], ['title' => $this->title]);

        // Log the changes with detailed information
        if ($this->positionId) {
            // This is an update - log what changed
            $changes = [];

            if ($oldData && $oldData['title'] !== $this->title) {
                $changes['title'] = [
                    'old' => $oldData['title'],
                    'new' => $this->title,
                ];
            }

            // Log the changes if any
            if (!empty($changes)) {
                activity()
                    ->performedOn($position)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'changes' => $changes,
                        'old_data' => $oldData,
                        'new_data' => ['title' => $this->title],
                    ])
                    ->event('updated')
                    ->log('updated position');
            }
        } else {
            // This is a creation
            activity()
                ->performedOn($position)
                ->causedBy(Auth::user())
                ->withProperties([
                    'created_data' => [
                        'title' => $this->title,
                    ],
                ])
                ->event('created')
                ->log('created position');
        }

        $this->dispatch('modal-close', name: 'position-form');
        $this->dispatch('toast', message: $this->positionId ? 'Position updated successfully.' : 'Position created successfully.', type: 'success');

        $this->resetForm();
    }

    // Soft delete with logging
    public function delete(int $id): void
    {
        $position = Position::findOrFail($id);

        activity()
            ->performedOn($position)
            ->causedBy(Auth::user())
            ->withProperties([
                'deleted_data' => [
                    'title' => $position->title,
                ],
            ])
            ->event('deleted')
            ->log('moved to trash');

        $position->delete();

        $this->dispatch('toast', message: 'Position moved to trash.', type: 'warning');
    }

    // Restore from trash
    public function restore(int $id): void
    {
        $position = Position::withTrashed()->findOrFail($id);

        activity()->performedOn($position)->causedBy(Auth::user())->event('restored')->log('restored from trash');

        $position->restore();

        $this->dispatch('toast', message: 'Position restored successfully.', type: 'success');
    }

    // Force delete with logging
    public function forceDelete(int $id): void
    {
        $position = Position::withTrashed()->findOrFail($id);

        // Log before permanent deletion
        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'permanently_deleted_data' => [
                    'title' => $position->title,
                ],
            ])
            ->event('force_deleted')
            ->log('permanently deleted');

        // Delete the position
        $position->forceDelete();

        $this->dispatch('toast', message: 'Position permanently deleted.', type: 'error');
    }

    // View activity logs
    public function viewLogs(int $id): void
    {
        $this->activities = Activity::query()
            ->with('causer')
            ->where('subject_id', $id)
            ->where('subject_type', Position::class)
            ->latest()
            ->get()
            ->map(function ($activity) {
                $properties = $activity->properties;

                // Format the changes for display
                if ($activity->event === 'updated' && isset($properties['changes'])) {
                    $activity->formatted_changes = $this->formatChanges($properties['changes']);
                } elseif (in_array($activity->event, ['created', 'deleted', 'force_deleted'])) {
                    // Check each possible data key separately - don't use ?? inside isset
                    $hasCreatedData = isset($properties['created_data']);
                    $hasDeletedData = isset($properties['deleted_data']);
                    $hasPermanentlyDeletedData = isset($properties['permanently_deleted_data']);

                    if ($hasCreatedData || $hasDeletedData || $hasPermanentlyDeletedData) {
                        $data = [];
                        if ($hasCreatedData) {
                            $data = $properties['created_data'];
                        } elseif ($hasDeletedData) {
                            $data = $properties['deleted_data'];
                        } elseif ($hasPermanentlyDeletedData) {
                            $data = $properties['permanently_deleted_data'];
                        }
                        $activity->formatted_data = $data;
                    }
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
            if ($field === 'title') {
                $formatted[] = [
                    'field' => 'Title',
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
        $tableExists = Schema::hasTable('positions');

        $query = Position::query()->when($this->search, function (Builder $query) {
            $query->where('title', 'like', '%' . $this->search . '%');
        });

        return [
            'positions' => $tableExists ? $query->latest()->paginate(10) : collect(),
            'tableExists' => $tableExists,
        ];
    }

    // Listeners
    protected function getListeners(): array
    {
        return [
            'refresh-positions' => '$refresh',
        ];
    }
}; ?>

<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl">Position Management</flux:heading>
            <flux:subheading>Manage official designations and roles for profiles.</flux:subheading>
        </div>
        <div class="flex items-center gap-4">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>

            <flux:button wire:click="showCreateForm" icon="plus" variant="primary">
                Add New Position
            </flux:button>
        </div>
    </div>

    {{-- Table Missing Warning --}}
    @if (!$tableExists)
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-center gap-2 text-amber-700">
                <flux:icon name="exclamation-triangle" class="w-5 h-5" />
                <span class="text-sm">Warning: The 'positions' table does not exist in the database. Please run
                    migrations.</span>
            </div>
        </div>
    @endif

    {{-- Search --}}
    <div>
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by title..." icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$positions">
        <flux:table.columns>
            <flux:table.column>Designation Title</flux:table.column>
            <flux:table.column>Created At</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($positions as $position)
                <flux:table.row :key="$position->id">
                    {{-- Title --}}
                    <flux:table.cell class="font-medium">
                        <div class="flex items-center gap-4">
                            <span>{{ $position->title }}</span>

                        </div>
                    </flux:table.cell>

                    {{-- Created At --}}
                    <flux:table.cell>
                        {{ $position->created_at?->format('d M, Y') }}
                    </flux:table.cell>

                    {{-- Actions --}}
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-1">

                            <flux:button variant="ghost" size="sm" icon="clock"
                                wire:click="viewLogs({{ $position->id }})" title="View Activity Logs" />

                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="edit({{ $position->id }})" title="Edit" />

                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                wire:click="delete({{ $position->id }})"
                                wire:confirm="Are you sure you want to move this to trash?" title="Delete" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="text-center py-12">
                        <div class="flex flex-col items-center gap-2 text-zinc-400">
                            <flux:icon name="briefcase" class="w-12 h-12" />
                            <p>No positions found.</p>
                            @if ($search)
                                <flux:button size="sm" variant="ghost" wire:click="$set('search', '')">
                                    Clear search
                                </flux:button>
                            @else
                                <flux:button size="sm" variant="primary" wire:click="showCreateForm">
                                    Add your first position
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
                                    avatar="{{ $log['causer']['avatar_url'] ?? 'System' }}" />
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
                            @elseif(in_array($log['event'], ['created', 'deleted', 'force_deleted']) && isset($log['formatted_data']))
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
                                        : ($selectedActivity['event'] === 'restored'
                                            ? 'yellow'
                                            : 'red')) }}">
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

    {{-- Position Form Modal --}}
    <flux:modal name="position-form" class="md:w-120">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $positionId ? 'Edit Position' : 'Create New Position' }}
                </flux:heading>
                <flux:subheading>
                    {{ $positionId ? 'Update the position details below.' : 'Enter the details for the new position.' }}
                </flux:subheading>
            </div>

            {{-- Title --}}
            <flux:input wire:model="title" label="Position Title"
                placeholder="e.g., Minister of Finance, Director, Manager" required />

            {{-- Form Actions --}}
            <div class="flex justify-end gap-4">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">
                    {{ $positionId ? 'Update Position' : 'Create Position' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
