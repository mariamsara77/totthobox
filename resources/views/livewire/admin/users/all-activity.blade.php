<?php

use Livewire\Volt\Component;
use Spatie\Activitylog\Models\Activity;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Flux\Flux;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    // Filters (URL-synced for shareable links)
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $eventType = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $userId = '';

    #[Url]
    public string $subjectType = '';

    #[Url]
    public string $httpMethod = '';

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public string $perPage = '25';
    public bool $showAdvancedFilters = false;

    // Options
    public array $eventTypes = [];
    public array $users = [];
    public array $subjectModels = [];
    public array $httpMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    // Selection & modal state
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
            // Efficient distinct queries
            $this->eventTypes = Activity::query()
                ->select('description')
                ->distinct()
                ->orderBy('description')
                ->pluck('description')
                ->filter()
                ->values()
                ->toArray();

            // Only load users who actually caused activities
            $this->users = Activity::query()
                ->whereNotNull('causer_id')
                ->select('causer_id')
                ->distinct()
                ->with('causer:id,name')
                ->get()
                ->pluck('causer.name', 'causer.id')
                ->filter()
                ->sort()
                ->toArray();

            $this->subjectModels = Activity::query()
                ->whereNotNull('subject_type')
                ->select('subject_type')
                ->distinct()
                ->get()
                ->mapWithKeys(fn($item) => [
                    $item->subject_type => class_basename($item->subject_type),
                ])
                ->sort()
                ->toArray();
        } catch (\Throwable $e) {
            $this->eventTypes = [];
            $this->users = [];
            $this->subjectModels = [];
            report($e);
        }
    }

    public function with(): array
    {
        return [
            'activities' => $this->getFilteredActivities(),
            'stats'      => $this->getStats(),
        ];
    }

    protected function baseQuery()
    {
        return Activity::query()->with([
            'causer' => fn($q) => $q->with('roles:id,name'),
        ]);
    }

    protected function applyFilters($query)
    {
        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('causer', fn($cq) => $cq->where('name', 'like', $term))
                    ->orWhere('description', 'like', $term)
                    ->orWhere('subject_type', 'like', $term)
                    ->orWhere('properties', 'like', $term)
                    ->orWhere('properties->ip', 'like', $term)
                    ->orWhere('properties->user_agent', 'like', $term);
            });
        }

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

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query;
    }

    protected function getFilteredActivities()
    {
        $query = $this->applyFilters($this->baseQuery());

        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate((int) $this->perPage);
    }

    protected function getStats(): array
    {
        $base = $this->applyFilters(Activity::query());

        return [
            'total'        => (clone $base)->count(),
            'unique_users' => (clone $base)->whereNotNull('causer_id')->distinct('causer_id')->count('causer_id'),
            'today'        => Activity::whereDate('created_at', today())->count(),
            'week'         => Activity::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
        ];
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            // Only current page (safe & expected behaviour)
            $this->selectedActivities = $this->getFilteredActivities()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedActivities = [];
        }
    }

    public function updatedSelectedActivities(): void
    {
        $currentPageIds = $this->getFilteredActivities()
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        $this->selectAll = count($currentPageIds) > 0
            && count(array_intersect($this->selectedActivities, $currentPageIds)) === count($currentPageIds);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEventType(): void
    {
        $this->resetPage();
    }

    public function updatedUserId(): void
    {
        $this->resetPage();
    }

    public function updatedSubjectType(): void
    {
        $this->resetPage();
    }

    public function updatedHttpMethod(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'eventType',
            'dateFrom',
            'dateTo',
            'userId',
            'subjectType',
            'httpMethod',
        ]);
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function viewDetails(int $activityId): void
    {
        $this->selectedActivity = Activity::with('causer.roles')->find($activityId);
        Flux::modal('details-modal')->show();
    }

    public function confirmDelete(int $activityId): void
    {
        $this->selectedActivity = Activity::find($activityId);
        Flux::modal('delete-modal')->show();
    }

    public function deleteActivity(): void
    {
        if ($this->selectedActivity) {
            $this->selectedActivity->delete();
            $this->selectedActivity = null;
            $this->selectedActivities = array_filter(
                $this->selectedActivities,
                fn($id) => $id != $this->selectedActivity?->id
            );
            Flux::modal('delete-modal')->close();
            $this->dispatch('notify', type: 'success', message: 'Activity deleted');
        }
    }

    public function confirmBulkDelete(): void
    {
        if (count($this->selectedActivities) > 0) {
            Flux::modal('bulk-delete-modal')->show();
        }
    }

    public function bulkDelete(): void
    {
        $count = count($this->selectedActivities);
        Activity::whereIn('id', $this->selectedActivities)->delete();

        $this->selectedActivities = [];
        $this->selectAll = false;

        Flux::modal('bulk-delete-modal')->close();
        $this->dispatch('notify', type: 'success', message: "{$count} activities deleted");
    }

    public function getBadgeColor(string $event): string
    {
        return match ($event) {
            'created'      => 'green',
            'updated'      => 'amber',
            'deleted'      => 'red',
            'restored'     => 'blue',
            'logged_in'    => 'indigo',
            'logged_out'   => 'purple',
            'login_failed' => 'rose',
            'exported'     => 'emerald',
            'imported'     => 'cyan',
            default        => 'zinc',
        };
    }

    public function getEventIcon(string $event): string
    {
        return match ($event) {
            'created'      => 'plus-circle',
            'updated'      => 'pencil-square',
            'deleted'      => 'trash',
            'restored'     => 'arrow-path',
            'logged_in'    => 'arrow-right-on-rectangle',
            'logged_out'   => 'arrow-left-on-rectangle',
            'login_failed' => 'exclamation-triangle',
            'exported'     => 'arrow-down-tray',
            'imported'     => 'arrow-up-tray',
            default        => 'document-text',
        };
    }

    public function getChangesSummary($activity): array
    {
        $properties = $activity->properties ?? collect();
        $changes = [];

        if ($activity->description === 'updated' && $properties->has('old') && $properties->has('attributes')) {
            $old = $properties->get('old', []);
            $new = $properties->get('attributes', []);

            foreach ($new as $key => $value) {
                if (array_key_exists($key, $old) && $old[$key] != $value) {
                    $changes[$key] = ['old' => $old[$key], 'new' => $value];
                }
            }
        } elseif ($activity->description === 'created' && $properties->has('attributes')) {
            foreach ($properties->get('attributes', []) as $key => $value) {
                $changes[$key] = ['old' => null, 'new' => $value];
            }
        } elseif ($activity->description === 'deleted' && $properties->has('old')) {
            foreach ($properties->get('old', []) as $key => $value) {
                $changes[$key] = ['old' => $value, 'new' => null];
            }
        }

        return $changes;
    }
};
?>

<div class="space-y-6">
    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <flux:card
            class="bg-linear-to-br from-blue-50 to-blue-100/80 dark:from-blue-950/30 dark:to-blue-900/20 border-blue-200/50 dark:border-blue-800/30">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading>Total Activities</flux:subheading>
                    <flux:heading size="2xl" class="mt-1">{{ number_format($stats['total']) }}</flux:heading>
                </div>
                <div class="p-3 rounded-xl bg-blue-500/10">
                    <flux:icon.document-text class="size-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </flux:card>

        <flux:card
            class="bg-linear-to-br from-purple-50 to-purple-100/80 dark:from-purple-950/30 dark:to-purple-900/20 border-purple-200/50 dark:border-purple-800/30">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading>Unique Users</flux:subheading>
                    <flux:heading size="2xl" class="mt-1">{{ number_format($stats['unique_users']) }}</flux:heading>
                </div>
                <div class="p-3 rounded-xl bg-purple-500/10">
                    <flux:icon.users class="size-6 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
        </flux:card>

        <flux:card
            class="bg-linear-to-br from-emerald-50 to-emerald-100/80 dark:from-emerald-950/30 dark:to-emerald-900/20 border-emerald-200/50 dark:border-emerald-800/30">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading>Today</flux:subheading>
                    <flux:heading size="2xl" class="mt-1">{{ number_format($stats['today']) }}</flux:heading>
                </div>
                <div class="p-3 rounded-xl bg-emerald-500/10">
                    <flux:icon.calendar class="size-6 text-emerald-600 dark:text-emerald-400" />
                </div>
            </div>
        </flux:card>

        <flux:card
            class="bg-linear-to-br from-amber-50 to-amber-100/80 dark:from-amber-950/30 dark:to-amber-900/20 border-amber-200/50 dark:border-amber-800/30">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading>This Week</flux:subheading>
                    <flux:heading size="2xl" class="mt-1">{{ number_format($stats['week']) }}</flux:heading>
                </div>
                <div class="p-3 rounded-xl bg-amber-500/10">
                    <flux:icon.chart-bar class="size-6 text-amber-600 dark:text-amber-400" />
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Filters --}}
    <flux:card>
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                <div class="flex-1 w-full sm:max-w-md">
                    <flux:input wire:model.live.debounce.400ms="search"
                        placeholder="Search user, event, model, IP, user agent..." icon="magnifying-glass" clearable />
                </div>

                <div class="flex flex-wrap gap-2">
                    <flux:button wire:click="$toggle('showAdvancedFilters')"
                        variant="{{ $showAdvancedFilters ? 'primary' : 'subtle' }}" icon="funnel" size="sm">
                        Filters
                    </flux:button>

                    <flux:button wire:click="clearFilters" variant="ghost" icon="x-mark" size="sm">
                        Clear
                    </flux:button>

                    @if (count($selectedActivities) > 0)
                    <flux:button wire:click="confirmBulkDelete" variant="danger" icon="trash" size="sm">
                        Delete ({{ count($selectedActivities) }})
                    </flux:button>
                    @endif
                </div>
            </div>

            @if ($showAdvancedFilters)
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:select wire:model.live="eventType" placeholder="All Events">
                    <option value="">All Events</option>
                    @foreach ($eventTypes as $event)
                    <option value="{{ $event }}">{{ ucfirst($event) }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="userId" placeholder="All Users">
                    <option value="">All Users</option>
                    @foreach ($users as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="subjectType" placeholder="All Models">
                    <option value="">All Models</option>
                    @foreach ($subjectModels as $type => $name)
                    <option value="{{ $type }}">{{ $name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="httpMethod" placeholder="All Methods">
                    <option value="">All Methods</option>
                    @foreach ($httpMethods as $method)
                    <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </flux:select>

                <flux:input type="date" wire:model.live="dateFrom" label="From" />
                <flux:input type="date" wire:model.live="dateTo" label="To" />

                <flux:select wire:model.live="perPage">
                    <option value="15">15 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </flux:select>
            </div>
            @endif

            {{-- Active filter chips --}}
            @php
            $active = collect([
            'Event' => $eventType ? ucfirst($eventType) : null,
            'User' => $userId ? ($users[$userId] ?? $userId) : null,
            'Model' => $subjectType ? class_basename($subjectType) : null,
            'Method' => $httpMethod,
            'From' => $dateFrom,
            'To' => $dateTo,
            ])->filter();
            @endphp

            @if ($active->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <span class="text-xs font-medium text-zinc-500">Active:</span>
                @foreach ($active as $label => $value)
                <flux:badge size="sm" color="blue" variant="solid">
                    {{ $label }}: {{ $value }}
                </flux:badge>
                @endforeach
            </div>
            @endif
        </div>
    </flux:card>

    {{-- Table --}}
    <flux:card class="overflow-hidden p-0">
        <div class="overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-10">
                        <flux:checkbox wire:model.live="selectAll" />
                    </flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('causer_id')" class="cursor-pointer select-none">
                        User
                        @if ($sortField === 'causer_id')
                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                            class="size-3 inline" />
                        @endif
                    </flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('description')" class="cursor-pointer select-none">
                        Event
                        @if ($sortField === 'description')
                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                            class="size-3 inline" />
                        @endif
                    </flux:table.column>
                    <flux:table.column>Subject</flux:table.column>
                    <flux:table.column>Changes</flux:table.column>
                    <flux:table.column>Context</flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('created_at')" class="cursor-pointer select-none">
                        Timestamp
                        @if ($sortField === 'created_at')
                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                            class="size-3 inline" />
                        @endif
                    </flux:table.column>
                    <flux:table.column class="w-24">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($activities as $activity)
                    <flux:table.row wire:key="activity-{{ $activity->id }}"
                        class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors">
                        <flux:table.cell>
                            <flux:checkbox wire:model.live="selectedActivities" value="{{ $activity->id }}" />
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar src="{{ $activity->causer?->avatar_url }}"
                                    name="{{ $activity->causer?->name ?? 'System' }}" size="sm" />
                                <div class="min-w-0">
                                    <flux:heading size="sm" class="truncate">
                                        {{ $activity->causer?->name ?? 'System' }}
                                    </flux:heading>
                                    <flux:text size="xs" class="text-zinc-500 truncate">
                                        {{ $activity->causer?->roles?->first()?->name ?? 'System' }}
                                    </flux:text>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:icon name="{{ $this->getEventIcon($activity->description) }}"
                                    class="size-4 text-{{ $this->getBadgeColor($activity->description) }}-500" />
                                <flux:badge size="sm" color="{{ $this->getBadgeColor($activity->description) }}">
                                    {{ ucfirst($activity->description) }}
                                </flux:badge>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div>
                                <flux:heading size="sm">
                                    {{ $activity->subject_type ? class_basename($activity->subject_type) : '—' }}
                                </flux:heading>
                                <flux:text size="xs" class="font-mono text-zinc-500">
                                    ID: {{ $activity->subject_id ?? 'N/A' }}
                                </flux:text>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @php $changes = $this->getChangesSummary($activity); @endphp
                            @if (count($changes) > 0)
                            <flux:badge size="sm" color="sky" class="cursor-pointer"
                                wire:click="viewDetails({{ $activity->id }})">
                                {{ count($changes) }} field{{ count($changes) > 1 ? 's' : '' }}
                            </flux:badge>
                            @else
                            <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="space-y-1 text-xs">
                                @if ($ip = $activity->properties?->get('ip'))
                                <div class="flex items-center gap-1.5 text-zinc-600 dark:text-zinc-400">
                                    <flux:icon.computer-desktop class="size-3.5" />
                                    <span class="font-mono">{{ $ip }}</span>
                                </div>
                                @endif
                                @if ($method = $activity->properties?->get('method'))
                                <flux:badge size="xs" variant="outline">
                                    {{ $method }}
                                </flux:badge>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:tooltip content="{{ $activity->created_at->format('F d, Y H:i:s') }}">
                                <div class="text-sm font-medium">
                                    {{ $activity->created_at->format('M d, H:i') }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ $activity->created_at->diffForHumans() }}
                                </div>
                            </flux:tooltip>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                <flux:button wire:click="viewDetails({{ $activity->id }})" size="xs" variant="ghost"
                                    icon="eye" tooltip="View details" />
                                <flux:button wire:click="confirmDelete({{ $activity->id }})" size="xs" variant="ghost"
                                    icon="trash" tooltip="Delete"
                                    class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-16">
                            <flux:icon.document-text class="size-12 mx-auto text-zinc-300 dark:text-zinc-600" />
                            <flux:heading level="3" class="mt-4">No activities found</flux:heading>
                            <flux:text class="mt-1 text-zinc-500">
                                Try adjusting your search or filters
                            </flux:text>
                            @if ($search || $eventType || $userId || $subjectType || $httpMethod || $dateFrom ||
                            $dateTo)
                            <flux:button wire:click="clearFilters" variant="subtle" size="sm" class="mt-5">
                                Clear all filters
                            </flux:button>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700">
            {{ $activities->links() }}
        </div>
    </flux:card>

    {{-- Details Modal --}}
    <flux:modal name="details-modal" class="max-w-2xl">
        <div class="space-y-1">
            <flux:heading size="lg">Activity Details</flux:heading>
            <flux:text class="text-zinc-500">Full information about this activity log entry</flux:text>
        </div>

        @if ($selectedActivity)
        <div class="mt-6 space-y-6">
            <div class="grid grid-cols-2 gap-4 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50">
                <div>
                    <flux:text class="text-xs text-zinc-500 uppercase tracking-wide">Event</flux:text>
                    <div class="flex items-center gap-2 mt-1.5">
                        <flux:icon name="{{ $this->getEventIcon($selectedActivity->description) }}" class="size-4" />
                        <flux:badge color="{{ $this->getBadgeColor($selectedActivity->description) }}">
                            {{ ucfirst($selectedActivity->description) }}
                        </flux:badge>
                    </div>
                </div>
                <div>
                    <flux:text class="text-xs text-zinc-500 uppercase tracking-wide">Timestamp</flux:text>
                    <flux:text class="font-mono text-sm mt-1.5">
                        {{ $selectedActivity->created_at->format('M d, Y H:i:s') }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="text-xs text-zinc-500 uppercase tracking-wide">User</flux:text>
                    <flux:text class="font-medium mt-1.5">
                        {{ $selectedActivity->causer?->name ?? 'System' }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="text-xs text-zinc-500 uppercase tracking-wide">IP Address</flux:text>
                    <flux:text class="font-mono text-sm mt-1.5">
                        {{ $selectedActivity->properties?->get('ip', 'N/A') }}
                    </flux:text>
                </div>
            </div>

            @php $changes = $this->getChangesSummary($selectedActivity); @endphp
            @if (count($changes) > 0)
            <div>
                <flux:heading level="3" class="mb-3">Changes</flux:heading>
                <div class="space-y-4">
                    @foreach ($changes as $field => $change)
                    <div class="border-l-4 border-amber-500 pl-4 py-1">
                        <flux:heading level="4" class="text-sm font-semibold mb-2">
                            {{ ucwords(str_replace('_', ' ', $field)) }}
                        </flux:heading>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-xs font-medium text-red-600 dark:text-red-400">Old</span>
                                <pre
                                    class="mt-1 p-2.5 bg-red-50 dark:bg-red-950/30 rounded-lg text-xs overflow-x-auto">{{ is_array($change['old']) || is_object($change['old']) ? json_encode($change['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($change['old'] ?? 'null') }}</pre>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">New</span>
                                <pre
                                    class="mt-1 p-2.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg text-xs overflow-x-auto">{{ is_array($change['new']) || is_object($change['new']) ? json_encode($change['new'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($change['new'] ?? 'null') }}</pre>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if ($selectedActivity->properties && $selectedActivity->properties->count() > 0)
            <div>
                <flux:heading level="3" class="mb-3">Full Properties</flux:heading>
                <pre
                    class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl text-xs overflow-x-auto max-h-80">{{ json_encode($selectedActivity->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif

            @if ($ua = $selectedActivity->properties?->get('user_agent'))
            <div>
                <flux:heading level="3" class="mb-2">User Agent</flux:heading>
                <flux:text class="text-xs font-mono break-all text-zinc-600 dark:text-zinc-400">
                    {{ $ua }}
                </flux:text>
            </div>
            @endif
        </div>
        @endif
    </flux:modal>

    {{-- Single Delete --}}
    <flux:modal name="delete-modal" class="max-w-md">
        <div class="space-y-4">
            <div class="flex items-center gap-3 text-red-600 dark:text-red-400">
                <flux:icon.exclamation-triangle class="size-6" />
                <flux:heading size="lg">Delete Activity</flux:heading>
            </div>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                Are you sure you want to permanently delete this activity log entry? This cannot be undone.
            </flux:text>
            <div class="flex justify-end gap-3 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteActivity" variant="danger" icon="trash">
                    Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Bulk Delete --}}
    <flux:modal name="bulk-delete-modal" class="max-w-md">
        <div class="space-y-4">
            <div class="flex items-center gap-3 text-red-600 dark:text-red-400">
                <flux:icon.exclamation-triangle class="size-6" />
                <flux:heading size="lg">Bulk Delete</flux:heading>
            </div>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                You are about to permanently delete
                <strong class="text-zinc-900 dark:text-white">{{ count($selectedActivities) }}</strong>
                activity log entries. This action cannot be undone.
            </flux:text>
            <div class="flex justify-end gap-3 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="bulkDelete" variant="danger" icon="trash">
                    Delete All
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>