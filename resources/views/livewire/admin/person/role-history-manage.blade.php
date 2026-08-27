<?php

use Livewire\Volt\Component;
use App\Models\{RoleHistory, Person, Position};
use Livewire\WithPagination;
use Livewire\Attributes\{Computed, Validate};
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    public $historyId = null;

    // Single edit mode
    #[Validate('required|exists:people,id')]
    public $person_id = null;

    #[Validate('nullable|exists:positions,id')]
    public $position_id = null;

    #[Validate('nullable|string|max:255')]
    public $custom_role = null;

    #[Validate('required|date')]
    public $from_date = null;

    #[Validate('nullable|date|after_or_equal:from_date')]
    public $to_date = null;

    #[Validate('boolean')]
    public $is_current = false;

    // ========== Multiple create mode ==========
    public $isMultipleMode = false;
    public $multiPersonId = null;
    public $roleRows = [
        // ['position_id' => null, 'custom_role' => '', 'from_date' => '', 'to_date' => '', 'is_current' => false]
    ];

    // UI State
    public $viewType = 'active';
    public $search = '';

    // Activity Logs
    public $activities = [];
    public $selectedActivity = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    public function updatedIsCurrent($value)
    {
        if ($value) {
            $this->to_date = null;
        }
    }

    #[Computed]
    public function histories()
    {
        return ($this->viewType === 'trashed' ? RoleHistory::onlyTrashed() : RoleHistory::query())
            ->with(['person', 'position'])
            ->when($this->search, function ($q) {
                $q->whereHas('person', fn($p) => $p->where('name', 'like', "%{$this->search}%"))->orWhere('custom_role', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'people' => Person::orderBy('name')->get(['id', 'name']),
            'positions' => Position::orderBy('title')->get(['id', 'title']),
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['historyId', 'person_id', 'position_id', 'custom_role', 'from_date', 'to_date', 'is_current', 'isMultipleMode', 'multiPersonId', 'roleRows']);

        $this->is_current = false;
        $this->isMultipleMode = false;
        $this->roleRows = [['position_id' => null, 'custom_role' => '', 'from_date' => '', 'to_date' => '', 'is_current' => false]];

        $this->dispatch('modal-show', name: 'history-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $this->isMultipleMode = false;

        $history = RoleHistory::withTrashed()->findOrFail($id);

        $this->historyId = $history->id;
        $this->person_id = $history->person_id;
        $this->position_id = $history->position_id;
        $this->custom_role = $history->custom_role;
        $this->from_date = $history->from_date?->format('Y-m-d');
        $this->to_date = $history->to_date?->format('Y-m-d');
        $this->is_current = (bool) $history->is_current;

        $this->dispatch('modal-show', name: 'history-form');
    }

    // Multiple mode helpers
    public function enableMultipleMode()
    {
        $this->isMultipleMode = true;
        $this->roleRows = [['position_id' => null, 'custom_role' => '', 'from_date' => '', 'to_date' => '', 'is_current' => false]];
    }

    public function addRoleRow()
    {
        $this->roleRows[] = [
            'position_id' => null,
            'custom_role' => '',
            'from_date' => '',
            'to_date' => '',
            'is_current' => false,
        ];
    }

    public function removeRoleRow($index)
    {
        unset($this->roleRows[$index]);
        $this->roleRows = array_values($this->roleRows);
    }

    public function save()
    {
        if ($this->isMultipleMode) {
            $this->saveMultiple();
            return;
        }

        // Single save (edit or single create)
        $this->validate([
            'person_id' => 'required|exists:people,id',
            'position_id' => 'nullable|exists:positions,id',
            'custom_role' => 'nullable|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'is_current' => 'boolean',
        ]);

        if ($this->is_current) {
            $this->to_date = null;
        }

        RoleHistory::updateOrCreate(
            ['id' => $this->historyId],
            [
                'person_id' => $this->person_id,
                'position_id' => $this->position_id,
                'custom_role' => $this->custom_role,
                'from_date' => $this->from_date,
                'to_date' => $this->to_date,
                'is_current' => $this->is_current,
            ],
        );

        $this->dispatch('modal-close', name: 'history-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'তথ্যটি সংরক্ষিত হয়েছে।');
        $this->resetForm();
    }

    protected function saveMultiple()
    {
        $this->validate([
            'multiPersonId' => 'required|exists:people,id',
            'roleRows' => 'required|array|min:1',
            'roleRows.*.from_date' => 'required|date',
            'roleRows.*.to_date' => 'nullable|date',
            'roleRows.*.position_id' => 'nullable|exists:positions,id',
            'roleRows.*.custom_role' => 'nullable|string|max:255',
            'roleRows.*.is_current' => 'boolean',
        ]);

        foreach ($this->roleRows as $row) {
            $toDate = !empty($row['is_current']) ? null : $row['to_date'] ?? null;

            RoleHistory::create([
                'person_id' => $this->multiPersonId,
                'position_id' => $row['position_id'] ?: null,
                'custom_role' => $row['custom_role'] ?: null,
                'from_date' => $row['from_date'],
                'to_date' => $toDate,
                'is_current' => (bool) ($row['is_current'] ?? false),
            ]);
        }

        $this->dispatch('modal-close', name: 'history-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: count($this->roleRows) . 'টি রেকর্ড সংরক্ষিত হয়েছে।');
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->reset(['historyId', 'person_id', 'position_id', 'custom_role', 'from_date', 'to_date', 'is_current', 'isMultipleMode', 'multiPersonId', 'roleRows']);
    }

    public function delete($id)
    {
        RoleHistory::find($id)?->delete();
        $this->dispatch('toast', variant: 'warning', text: 'Item moved to trash.');
    }

    public function restore($id)
    {
        RoleHistory::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'Item restored successfully.');
    }

    public function forceDelete($id)
    {
        RoleHistory::onlyTrashed()->findOrFail($id)->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'Item deleted permanently.');
    }

    public function viewLogs($id)
    {
        $this->activities = Activity::query()
            ->with('causer')
            ->where('subject_id', $id)
            ->where('subject_type', RoleHistory::class)
            ->latest()
            ->get()
            ->map(
                fn($activity) => [
                    'id' => $activity->id,
                    'event' => $activity->event,
                    'description' => $activity->description,
                    'causer' => $activity->causer?->name ?? 'System',
                    'created_at' => $activity->created_at->format('d M Y, h:i A'),
                    'properties' => $activity->properties?->toArray() ?? [],
                ],
            )
            ->toArray();

        $this->dispatch('modal-show', name: 'activity-logs');
    }

    public function viewActivityDetails($id)
    {
        $activity = Activity::with('causer')->findOrFail($id);

        $this->selectedActivity = [
            'id' => $activity->id,
            'event' => $activity->event,
            'description' => $activity->description,
            'causer' => $activity->causer?->name ?? 'System',
            'created_at' => $activity->created_at->format('d M Y, h:i A'),
            'properties' => $activity->properties?->toArray() ?? [],
        ];

        $this->dispatch('modal-show', name: 'activity-details');
    }
}; ?>

<div>
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Role History Management</flux:heading>
            <flux:subheading>Track personnel roles and transitions.</flux:subheading>
        </div>
        <div class="flex items-center gap-4">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">
                Add Record
            </flux:button>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by person name or role..."
            icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->histories">
        <flux:table.columns>
            <flux:table.column>Person</flux:table.column>
            <flux:table.column>Role / Position</flux:table.column>
            <flux:table.column>Duration</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->histories as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell class="font-medium">
                        {{ $item->person?->name ?? '—' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        {{ $item->position?->title ?? ($item->custom_role ?? '—') }}
                    </flux:table.cell>

                    <flux:table.cell class="text-zinc-500">
                        {{ $item->from_date?->format('M Y') ?? '?' }}
                        —
                        {{ $item->is_current ? 'Present' : $item->to_date?->format('M Y') ?? '?' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" :color="$item->is_current ? 'green' : 'zinc'">
                            {{ $item->is_current ? 'Current' : 'Past' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        @if ($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="clock"
                                wire:click="viewLogs({{ $item->id }})" title="View Logs" />
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
                                    wire:confirm="Permanent delete?" wire:click="forceDelete({{ $item->id }})" />
                            @endcan
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-10 text-zinc-400">
                        No records found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Form Modal --}}
    <flux:modal name="history-form" class="md:w-[42rem]">
        <form wire:submit="save" class="space-y-6">
            <div class="flex justify-between items-start">
                <div>
                    <flux:heading size="lg">
                        @if ($historyId)
                            Edit Role History
                        @elseif ($isMultipleMode)
                            Add Multiple Roles
                        @else
                            Add New Role History
                        @endif
                    </flux:heading>
                    <flux:subheading>
                        @if ($isMultipleMode)
                            একজন Person-এর জন্য একাধিক Position/Role একসাথে যোগ করুন
                        @else
                            Record a person's role or position history.
                        @endif
                    </flux:subheading>
                </div>

                @if (!$historyId)
                    <flux:button type="button" size="sm" variant="ghost"
                        wire:click="{{ $isMultipleMode ? '$set(\'isMultipleMode\', false)' : 'enableMultipleMode' }}">
                        {{ $isMultipleMode ? 'Single Mode' : 'Multiple Mode' }}
                    </flux:button>
                @endif
            </div>

            {{-- ========== MULTIPLE MODE ========== --}}
            @if ($isMultipleMode)
                <flux:select wire:model="multiPersonId" label="Person" placeholder="Select a person...">
                    <option value="">Select Person</option>
                    @foreach ($people as $person)
                        <option value="{{ $person->id }}">{{ $person->name }}</option>
                    @endforeach
                </flux:select>

                <div class="space-y-4">
                    @foreach ($roleRows as $index => $row)
                        <div class="p-4 border border-zinc-400/25 rounded-lg space-y-3 relative">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-zinc-500">Role #{{ $index + 1 }}</span>
                                @if (count($roleRows) > 1)
                                    <button type="button" wire:click="removeRoleRow({{ $index }})"
                                        class="text-red-500 hover:text-zinc-400/25">
                                        <flux:icon name="trash" class="size-4" />
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:select wire:model="roleRows.{{ $index }}.position_id" label="Position">
                                    <option value="">Select Position</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->title }}</option>
                                    @endforeach
                                </flux:select>

                                <flux:input wire:model="roleRows.{{ $index }}.custom_role" label="Custom Role"
                                    placeholder="Optional..." />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:input type="date" wire:model="roleRows.{{ $index }}.from_date"
                                    label="From Date" />
                                <flux:input type="date" wire:model="roleRows.{{ $index }}.to_date"
                                    label="To Date" :disabled="$roleRows[$index]['is_current'] ?? false" />
                            </div>

                            <flux:checkbox wire:model.live="roleRows.{{ $index }}.is_current"
                                label="Currently in this role" />
                        </div>
                    @endforeach
                </div>

                <flux:button type="button" variant="ghost" icon="plus" wire:click="addRoleRow" size="sm">
                    Add Another Role
                </flux:button>

                {{-- ========== SINGLE MODE ========== --}}
            @else
                <flux:select wire:model="person_id" label="Person" placeholder="Select a person...">
                    <option value="">Select Person</option>
                    @foreach ($people as $person)
                        <option value="{{ $person->id }}">{{ $person->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="position_id" label="Defined Position (Optional)">
                    <option value="">Select Position</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}">{{ $position->title }}</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="custom_role" label="Custom Role (if no position)"
                    placeholder="e.g. Advisor, Consultant..." />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input type="date" wire:model="from_date" label="From Date" />
                    <flux:input type="date" wire:model="to_date" label="To Date" :disabled="$is_current" />
                </div>

                <flux:checkbox wire:model.live="is_current" label="Currently in this role" />
            @endif

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    Save Changes
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Activity Logs Modal --}}
    <flux:modal name="activity-logs" class="md:w-[45rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Activity History</flux:heading>
                <flux:subheading>Change logs for this record</flux:subheading>
            </div>

            <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                @forelse ($activities as $log)
                    <div
                        class="relative pl-4 border-l-2 {{ $log['event'] === 'created' ? 'border-green-500' : ($log['event'] === 'updated' ? 'border-blue-500' : 'border-red-500') }}">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <flux:badge size="sm"
                                        :color="$log['event'] === 'created' ? 'green' : ($log['event'] === 'updated' ?
                                            'blue' : 'red')">
                                        {{ ucfirst($log['event']) }}
                                    </flux:badge>
                                    <span class="text-sm font-medium">{{ $log['description'] }}</span>
                                </div>
                                <div class="text-xs text-zinc-500">
                                    By {{ $log['causer'] }} • {{ $log['created_at'] }}
                                </div>
                            </div>
                            <flux:button size="xs" variant="ghost" icon="eye"
                                wire:click="viewActivityDetails({{ $log['id'] }})">
                                Details
                            </flux:button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-zinc-400">
                        <flux:icon name="clock" class="w-10 h-10 mx-auto mb-2" />
                        <p>No activity logs found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </flux:modal>

    {{-- Activity Detail Modal --}}
    <flux:modal name="activity-details" class="md:w-[40rem]">
        @if ($selectedActivity)
            <div class="space-y-4">
                <flux:heading size="lg">Activity Details</flux:heading>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-zinc-500 mb-2">Event</div>
                        <flux:badge>{{ ucfirst($selectedActivity['event']) }}</flux:badge>
                    </div>
                    <div>
                        <div class="text-zinc-500 mb-2">Performed by</div>
                        <div>{{ $selectedActivity['causer'] }}</div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-zinc-500 mb-2">Date & Time</div>
                        <div>{{ $selectedActivity['created_at'] }}</div>
                    </div>
                </div>

                @if (!empty($selectedActivity['properties']))
                    <div>
                        <div class="text-zinc-500 mb-2">Properties</div>
                        <pre class="bg-zinc-950 text-emerald-400 p-4 rounded-lg text-xs overflow-auto max-h-64">{{ json_encode($selectedActivity['properties'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
        @endif
    </flux:modal>
</div>
