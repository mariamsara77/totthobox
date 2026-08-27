<?php

use App\Models\{Thana, Division, District};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    public ?int $editId = null;
    public string $viewType = 'active';
    public string $search = '';

    public array $activities = [];
    public ?array $selectedActivity = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|exists:districts,id')]
    public ?int $district_id = null;

    public ?int $division_id = null;

    public function save(): void
    {
        $this->validate();
        Thana::updateOrCreate(
            ['id' => $this->editId],
            [
                'name' => $this->name,
                'district_id' => $this->district_id,
            ],
        );
        $this->dispatch('modal-close', name: 'thana-form');
        $this->dispatch('toast', variant: 'success', heading: 'Success', text: 'Thana saved successfully.');
        $this->reset(['name', 'district_id', 'division_id', 'editId']);
    }

    public function edit(Thana $thana): void
    {
        $this->editId = $thana->id;
        $this->name = $thana->name;
        $this->division_id = $thana->district->division_id;
        $this->district_id = $thana->district_id;
        $this->dispatch('modal-show', name: 'thana-form');
    }

    public function delete($id)
    {
        Thana::find($id)->delete();
    }
    public function restore($id)
    {
        Thana::onlyTrashed()->findOrFail($id)->restore();
    }
    public function forceDelete($id)
    {
        Thana::onlyTrashed()->findOrFail($id)->forceDelete();
    }

    // View activity logs with detailed changes
    public function viewLogs(int $id): void
    {
        $this->activities = Activity::query()
            ->with('causer')
            ->where('subject_id', $id)
            ->where('subject_type', Thana::class)
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

    public function with(): array
    {
        $query = ($this->viewType === 'trashed' ? Thana::onlyTrashed() : Thana::query())->with('district.division')->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"));

        return [
            'thanas' => $query->latest()->paginate(10),
            'divisions' => Division::all(),
            'districts' => $this->division_id ? District::where('division_id', $this->division_id)->get() : [],
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <flux:heading size="xl">Thana Management</flux:heading>
            <flux:subheading>Manage your Thana locations.</flux:subheading>
        </div>
        <div class="flex gap-4">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="$set('editId', null); $dispatch('modal-show', {name: 'thana-form'})"
                variant="primary" icon="plus">Add Thana</flux:button>
        </div>
    </div>

    <flux:input wire:model.live.debounce.400ms="search" placeholder="Search Thana..." />

    <flux:table :paginate="$thanas">
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>District</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>
        @foreach ($thanas as $thana)
            <flux:table.row>
                <flux:table.cell>{{ $thana->name }}</flux:table.cell>
                <flux:table.cell>{{ $thana->district->name ?? 'N/A' }}</flux:table.cell>
                <flux:table.cell align="end">
                    @if ($viewType === 'active')
                        <flux:button variant="ghost" icon="clock" wire:click="viewLogs({{ $thana->id }})" />
                        <flux:button variant="ghost" icon="pencil-square" wire:click="edit({{ $thana->id }})" />
                        <flux:button variant="ghost" icon="trash" color="red"
                            wire:click="delete({{ $thana->id }})" />
                    @else
                        <flux:button icon="arrow-path" wire:click="restore({{ $thana->id }})" />
                        <flux:button icon="x-mark" color="red" wire:click="forceDelete({{ $thana->id }})" />
                    @endif
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>

    {{-- Form Modal --}}
    <flux:modal name="thana-form" class="md:w-120">
        <form wire:submit="save" class="space-y-4">
            <flux:heading size="lg">{{ $editId ? 'Edit' : 'Add' }} Thana</flux:heading>
            <flux:input wire:model="name" label="Name" />
            <flux:select wire:model.live="division_id" label="Division">
                <option value="">Select Division</option>
                @foreach ($divisions as $div)
                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="district_id" label="District" :disabled="!$division_id">
                @foreach ($districts as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="primary" class="w-full">Save</flux:button>
        </form>
    </flux:modal>

    {{-- Activity Logs Modal (Exact match with IntroBd) --}}
    <flux:modal name="activity-logs" class="w-full h-full">
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
                                    <span class="text-sm font-medium text-zinc-700">{{ $log['description'] }}</span>
                                </div>
                                <span
                                    class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($log['created_at'])->format('d M Y, h:i A') }}</span>
                            </div>

                            {{-- User --}}
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-zinc-600">By:</span>
                                <flux:profile :chevron="false" avatar="{{ $log['causer']['avatar_url'] }}"
                                    name="{{ $log['causer']['name'] ?? 'System' }}" />
                            </div>

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
