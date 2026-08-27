<?php

use Livewire\Volt\Component;
use Spatie\Activitylog\Models\Activity;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    // Filter properties
    public string $search = '';
    public string $eventType = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $userId = '';
    public string $subjectType = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public string $perPage = '15';
    public string $httpMethod = '';
    public bool $showAdvancedFilters = false;

    // Available options for filters
    public array $eventTypes = [];
    public array $users = [];
    public array $subjectModels = [];
    public array $httpMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    // Modal controls
    public $selectedActivity = null;
    public array $selectedActivities = [];
    public bool $selectAll = false;

    public function mount(): void
    {
        $this->loadFilterOptions();
    }

    protected function loadFilterOptions(): void
    {
        try {
            $this->eventTypes = Activity::query()->distinct()->pluck('description')->toArray();

            $this->users = Activity::query()->with('causer')->get()->pluck('causer.name', 'causer.id')->filter()->unique()->toArray();

            $this->subjectModels = Activity::query()
                ->whereNotNull('subject_type')
                ->distinct()
                ->get()
                ->mapWithKeys(
                    fn($item) => [
                        $item->subject_type => class_basename($item->subject_type),
                    ],
                )
                ->toArray();
        } catch (\Exception $e) {
            $this->eventTypes = [];
            $this->users = [];
            $this->subjectModels = [];
        }
    }

    public function with(): array
    {
        return [
            'activities' => $this->getFilteredActivities(),
            'stats' => $this->getStats(),
        ];
    }

    protected function getStats(): array
    {
        $query = Activity::query();

        if ($this->dateFrom || $this->dateTo) {
            $this->applyDateFilter($query);
        }

        return [
            'total' => $query->count(),
            'unique_users' => $query->distinct('causer_id')->count('causer_id'),
            'today' => Activity::whereDate('created_at', today())->count(),
            'week' => Activity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }

    protected function getFilteredActivities()
    {
        $query = Activity::query()->with(['causer' => fn($q) => $q->with('roles')]);

        // Search filter
        if ($this->search) {
            $query->where(function ($subQuery) {
                $subQuery
                    ->whereHas('causer', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('subject_type', 'like', '%' . $this->search . '%')
                    ->orWhere('properties', 'like', '%' . $this->search . '%')
                    ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                    ->orWhere('user_agent', 'like', '%' . $this->search . '%');
            });
        }

        // Apply filters
        if ($this->eventType) {
            $query->where('description', $this->eventType);
        }

        if ($this->userId) {
            $query->where('causer_id', $this->userId);
        }

        if ($this->subjectType) {
            $query->where('subject_type', $this->subjectType);
        }

        if ($this->httpMethod) {
            $query->where('properties->method', $this->httpMethod);
        }

        $this->applyDateFilter($query);

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    protected function applyDateFilter($query): void
    {
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedActivities = $this->getFilteredActivities()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedActivities = [];
        }
    }

    public function updatedSelectedActivities(): void
    {
        $this->selectAll = count($this->selectedActivities) === $this->getFilteredActivities()->count();
    }

    public function getBadgeColor(string $event): string
    {
        return match ($event) {
            'created' => 'green',
            'updated' => 'amber',
            'deleted' => 'red',
            'restored' => 'blue',
            'logged_in' => 'indigo',
            'logged_out' => 'purple',
            'login_failed' => 'rose',
            'exported' => 'emerald',
            'imported' => 'cyan',
            default => 'zinc',
        };
    }

    public function getEventIcon(string $event): string
    {
        return match ($event) {
            'created' => 'plus-circle',
            'updated' => 'pencil-square',
            'deleted' => 'trash',
            'restored' => 'arrow-path',
            'logged_in' => 'arrow-right-on-rectangle',
            'logged_out' => 'arrow-left-on-rectangle',
            'login_failed' => 'exclamation-triangle',
            'exported' => 'arrow-down-tray',
            'imported' => 'arrow-up-tray',
            default => 'document-text',
        };
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'eventType', 'dateFrom', 'dateTo', 'userId', 'subjectType', 'httpMethod']);
        $this->resetPage();
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function viewDetails($activityId): void
    {
        $this->selectedActivity = Activity::with(['causer'])->find($activityId);
        // $this->dispatch('open-modal', 'details-modal');
        Flux::modal('details-modal')->show();
    }

    public function confirmDelete($activityId): void
    {
        $this->selectedActivity = Activity::find($activityId);
        // $this->dispatch('open-modal', 'delete-modal');
        Flux::modal('delete-modal')->show();
    }

    public function deleteActivity(): void
    {
        if ($this->selectedActivity) {
            $this->selectedActivity->delete();
            $this->selectedActivity = null;
            // $this->dispatch('close-modal', 'delete-modal');
            Flux::modal('delete-modal')->close();
        }
    }

    public function confirmBulkDelete(): void
    {
        if (count($this->selectedActivities) > 0) {
            // $this->dispatch('open-modal', 'bulk-delete-modal');
            Flux::modal('bulk-delete-modal')->show();
        }
    }

    public function bulkDelete(): void
    {
        Activity::whereIn('id', $this->selectedActivities)->delete();
        $this->selectedActivities = [];
        $this->selectAll = false;
        // $this->dispatch('close-modal', 'bulk-delete-modal');
        Flux::modal('bulk-delete-modal')->close();
    }

    public function getChangesSummary($activity): array
    {
        $properties = $activity->properties ?? collect();
        $changes = [];

        if ($activity->description === 'updated' && $properties->has('old') && $properties->has('attributes')) {
            $old = $properties->get('old', []);
            $new = $properties->get('attributes', []);

            foreach ($new as $key => $value) {
                if (isset($old[$key]) && $old[$key] != $value) {
                    $changes[$key] = [
                        'old' => $old[$key],
                        'new' => $value,
                    ];
                }
            }
        } elseif ($activity->description === 'created' && $properties->has('attributes')) {
            $attributes = $properties->get('attributes', []);
            foreach ($attributes as $key => $value) {
                $changes[$key] = [
                    'old' => null,
                    'new' => $value,
                ];
            }
        } elseif ($activity->description === 'deleted' && $properties->has('old')) {
            $old = $properties->get('old', []);
            foreach ($old as $key => $value) {
                $changes[$key] = [
                    'old' => $value,
                    'new' => null,
                ];
            }
        }

        return $changes;
    }
};
?>

<div class="space-y-6">


    {{-- Header with Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <flux:card class="bg-linear-to-br from-blue-50 to-blue-100 dark:from-blue-950/20 dark:to-blue-900/20">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading>Total Activities</flux:subheading>
                    <flux:heading size="2xl">{{ number_format($stats['total']) }}</flux:heading>
                </div>
                <flux:icon.document-text class="size-8 text-blue-500" />
            </div>
        </flux:card>

        <flux:card class="bg-linear-to-br from-purple-50 to-purple-100 dark:from-purple-950/20 dark:to-purple-900/20">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading>Unique Users</flux:subheading>
                    <flux:heading size="2xl">{{ number_format($stats['unique_users']) }}</flux:heading>
                </div>
                <flux:icon.users class="size-8 text-purple-500" />
            </div>
        </flux:card>

        <flux:card class="bg-linear-to-br from-green-50 to-green-100 dark:from-green-950/20 dark:to-green-900/20">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading>Today's Activity</flux:subheading>
                    <flux:heading size="2xl">{{ number_format($stats['today']) }}</flux:heading>
                </div>
                <flux:icon.calendar class="size-8 text-green-500" />
            </div>
        </flux:card>

        <flux:card class="bg-linear-to-br from-amber-50 to-amber-100 dark:from-amber-950/20 dark:to-amber-900/20">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading>This Week</flux:subheading>
                    <flux:heading size="2xl">{{ number_format($stats['week']) }}</flux:heading>
                </div>
                <flux:icon.chart-bar class="size-8 text-amber-500" />
            </div>
        </flux:card>
    </div>

    {{-- Main Controls --}}
    <flux:card>
        <div class="space-y-4">
            {{-- Quick Search and Actions --}}
            <div class="flex flex-wrap gap-4 items-center justify-between">
                <div class="flex-1 min-w-62.5">
                    <flux:input wire:model.live.ebounce.300ms="search"
                        placeholder="Search by user, event, model, IP, or user agent..." icon="magnifying-glass" />
                </div>
                <div class="flex gap-4">
                    <flux:button wire:click="$toggle('showAdvancedFilters')" variant="subtle" icon="funnel">
                        Advanced Filters
                    </flux:button>
                    <flux:button wire:click="clearFilters" variant="ghost" icon="x-mark">
                        Clear
                    </flux:button>
                    @if (count($selectedActivities) > 0)
                        <flux:button wire:click="confirmBulkDelete" variant="danger" icon="trash">
                            Delete ({{ count($selectedActivities) }})
                        </flux:button>
                    @endif
                </div>
            </div>

            {{-- Advanced Filters Panel --}}
            @if ($showAdvancedFilters)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t">
                    <flux:select wire:model.live="eventType">
                        <option value="">All Events</option>
                        @foreach ($eventTypes as $event)
                            <option value="{{ $event }}">{{ ucfirst($event) }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="userId">
                        <option value="">All Users</option>
                        @foreach ($users as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="subjectType">
                        <option value="">All Models</option>
                        @foreach ($subjectModels as $type => $name)
                            <option value="{{ $type }}">{{ $name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="httpMethod">
                        <option value="">All Methods</option>
                        @foreach ($httpMethods as $method)
                            <option value="{{ $method }}">{{ $method }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input type="date" wire:model.live="dateFrom" placeholder="From Date" />
                    <flux:input type="date" wire:model.live="dateTo" placeholder="To Date" />
                    <flux:select wire:model.live="perPage">
                        <option value="15">15 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </flux:select>
                </div>
            @endif

            {{-- Active Filters Display --}}
            @php
                $activeFilters = collect([
                    'Event' => $eventType,
                    'User' => $userId ? $users[$userId] ?? $userId : null,
                    'Model' => $subjectType ? class_basename($subjectType) : null,
                    'Method' => $httpMethod,
                    'From' => $dateFrom,
                    'To' => $dateTo,
                ])
                    ->filter()
                    ->values();
            @endphp

            @if ($activeFilters->isNotEmpty())
                <div class="flex flex-wrap gap-2 pt-2">
                    <span class="text-sm font-medium text-zinc-500">Active Filters:</span>
                    @foreach ($activeFilters as $filter)
                        <flux:badge size="sm" color="blue" variant="solid">{{ $filter }}</flux:badge>
                    @endforeach
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Activities Table --}}
    <flux:card class="overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column style="width: 40px">
                        <flux:checkbox wire:model.live="selectAll" />
                    </flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('causer_id')" class="cursor-pointer">
                        User
                    </flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('description')" class="cursor-pointer">
                        Event
                    </flux:table.column>
                    <flux:table.column>Subject</flux:table.column>
                    <flux:table.column>Changes</flux:table.column>
                    <flux:table.column>Context</flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('created_at')" class="cursor-pointer">
                        Timestamp
                    </flux:table.column>
                    <flux:table.column style="width: 100px">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($activities as $activity)
                        <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <flux:table.cell>
                                <flux:checkbox wire:model.live="selectedActivities" value="{{ $activity->id }}" />
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-4">
                                    <flux:avatar src="{{ $activity->causer?->avatar_url }}"
                                        name="{{ $activity->causer?->name ?? 'System' }}" size="sm" />
                                    <div>
                                        <flux:heading size="sm">{{ $activity->causer?->name ?? 'System' }}
                                        </flux:heading>
                                        <flux:text size="xs" class="text-zinc-500">
                                            {{ $activity->causer?->roles?->first()?->name ?? 'System' }}
                                        </flux:text>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-4">
                                    <flux:icon name="{{ $this->getEventIcon($activity->description) }}"
                                        class="size-4 text-{{ $this->getBadgeColor($activity->description) }}-500" />
                                    <flux:badge size="sm"
                                        color="{{ $this->getBadgeColor($activity->description) }}">
                                        {{ ucfirst($activity->description) }}
                                    </flux:badge>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div>
                                    <flux:heading size="sm">{{ class_basename($activity->subject_type) }}
                                    </flux:heading>
                                    <flux:text size="xs" class="font-mono text-zinc-500">
                                        ID: {{ $activity->subject_id ?? 'N/A' }}
                                    </flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @php $changes = $this->getChangesSummary($activity); @endphp
                                @if (count($changes) > 0)
                                    <flux:badge size="sm" color="info" class="cursor-pointer"
                                        wire:click="viewDetails({{ $activity->id }})">
                                        {{ count($changes) }} field(s) changed
                                    </flux:badge>
                                @else
                                    <span class="text-xs text-zinc-400">No changes</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="space-y-1 text-xs">
                                    @if ($activity->properties->get('ip'))
                                        <div class="flex items-center gap-1">
                                            <flux:icon.computer-desktop class="size-3" />
                                            <span>{{ $activity->properties->get('ip') }}</span>
                                        </div>
                                    @endif
                                    @if ($activity->properties->get('method'))
                                        <flux:badge size="xs" variant="outline">
                                            {{ $activity->properties->get('method') }}
                                        </flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:tooltip content="{{ $activity->created_at->format('F d, Y H:i:s') }}">
                                    <div class="text-sm">
                                        {{ $activity->created_at->format('M d, H:i') }}
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </div>
                                </flux:tooltip>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex gap-1">
                                    <flux:button wire:click="viewDetails({{ $activity->id }})" size="xs"
                                        variant="ghost" icon="eye" tooltip="View details" />
                                    <flux:button wire:click="confirmDelete({{ $activity->id }})" size="xs"
                                        variant="ghost" icon="trash" tooltip="Delete"
                                        class="text-red-500 hover:text-zinc-400/25" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="text-center py-12">
                                <flux:icon.document-text class="size-12 mx-auto text-zinc-300" />
                                <flux:heading level="3" class="mt-4">No activities found</flux:heading>
                                <flux:text class="mt-1">Try adjusting your search or filters</flux:text>
                                @if ($search || $eventType || $userId || $subjectType || $httpMethod || $dateFrom || $dateTo)
                                    <flux:button wire:click="clearFilters" variant="subtle" size="sm"
                                        class="mt-4">
                                        Clear all filters
                                    </flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="px-4 py-3 border-t">
            {{ $activities->links() }}
        </div>
    </flux:card>

    {{-- Details Modal --}}
    <flux:modal name="details-modal">
        <div>
            <flux:heading size="lg">Activity Details</flux:heading>

        </div>

        @if ($selectedActivity)
            <div class="mt-4 space-y-4">
                {{-- Header Info --}}
                <div class="grid grid-cols-2 gap-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                    <div>
                        <flux:text class="text-xs text-zinc-500">Event Type</flux:text>
                        <div class="flex items-center gap-2 mt-1">
                            <flux:icon name="{{ $this->getEventIcon($selectedActivity->description) }}"
                                class="size-4" />
                            <flux:badge color="{{ $this->getBadgeColor($selectedActivity->description) }}">
                                {{ ucfirst($selectedActivity->description) }}
                            </flux:badge>
                        </div>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500">Timestamp</flux:text>
                        <flux:text class="font-mono text-sm mt-1">
                            {{ $selectedActivity->created_at->format('F d, Y H:i:s') }}
                        </flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500">User</flux:text>
                        <flux:text class="font-medium mt-1">{{ $selectedActivity->causer?->name ?? 'System' }}
                        </flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500">IP Address</flux:text>
                        <flux:text class="font-mono text-sm mt-1">
                            {{ $selectedActivity->properties->get('ip', 'N/A') }}</flux:text>
                    </div>
                </div>

                {{-- Changes --}}
                @php $changes = $this->getChangesSummary($selectedActivity); @endphp
                @if (count($changes) > 0)
                    <div>
                        <flux:heading level="3" class="mb-3">Changes</flux:heading>
                        <div class="space-y-3">
                            @foreach ($changes as $field => $change)
                                <div class="border-l-4 border-amber-500 pl-3">
                                    <flux:heading level="4" class="text-sm font-semibold mb-2">
                                        {{ ucwords(str_replace('_', ' ', $field)) }}</flux:heading>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-xs text-red-600">Old Value</span>
                                            <pre class="mt-1 p-2 bg-red-50 dark:bg-red-950/20 rounded text-xs overflow-x-auto">{{ is_array($change['old']) ? json_encode($change['old'], JSON_PRETTY_PRINT) : $change['old'] }}</pre>
                                        </div>
                                        <div>
                                            <span class="text-xs text-green-600">New Value</span>
                                            <pre class="mt-1 p-2 bg-green-50 dark:bg-green-950/20 rounded text-xs overflow-x-auto">{{ is_array($change['new']) ? json_encode($change['new'], JSON_PRETTY_PRINT) : $change['new'] }}</pre>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Full Properties --}}
                @if ($selectedActivity->properties && $selectedActivity->properties->count() > 0)
                    <div>
                        <flux:heading level="3" class="mb-3">Full Data</flux:heading>
                        <pre class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg text-xs overflow-x-auto">{{ json_encode($selectedActivity->properties->toArray(), JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif

                {{-- User Agent --}}
                @if ($selectedActivity->properties->get('user_agent'))
                    <div>
                        <flux:heading level="3" class="mb-2">User Agent</flux:heading>
                        <flux:text class="text-xs font-mono">
                            {{ $selectedActivity->properties->get('user_agent') }}</flux:text>
                    </div>
                @endif
            </div>
        @endif
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-modal" class="max-w-md">
        <div class="p-6">
            <div class="flex items-center gap-4 text-red-600">
                <flux:icon.exclamation-triangle class="size-6" />
                <flux:heading size="lg">Delete Activity</flux:heading>
            </div>
            <div class="mt-2 text-zinc-600 dark:text-zinc-400">
                Are you sure you want to delete this activity log? This action cannot be undone.
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteActivity" variant="danger">
                    Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Bulk Delete Modal --}}
    <flux:modal name="bulk-delete-modal" class="max-w-md">
        <div class="p-6">
            <div class="flex items-center gap-4 text-red-600">
                <flux:icon.exclamation-triangle class="size-6" />
                <flux:heading size="lg">Bulk Delete Activities</flux:heading>
            </div>
            <div class="mt-2 text-zinc-600 dark:text-zinc-400">
                Are you sure you want to delete <strong>{{ count($selectedActivities) }}</strong> selected activity
                logs? This action cannot be undone.
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="bulkDelete" variant="danger">
                    Delete All
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
