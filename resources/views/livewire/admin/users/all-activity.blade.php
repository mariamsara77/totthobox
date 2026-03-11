<?php

use Livewire\Volt\Component;
use Spatie\Activitylog\Models\Activity;
use Livewire\WithPagination;
use Illuminate\View\View;

new class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [
            // Eager load roles to prevent N+1 queries during rendering
            'activities' => Activity::query()
                ->with(['causer' => fn($q) => $q->with('roles')])
                ->latest()
                ->paginate(15),
        ];
    }

    /**
     * Determine badge color based on activity event
     */
    public function getBadgeColor(string $event): string
    {
        return match ($event) {
            'created' => 'green',
            'updated' => 'amber',
            'deleted' => 'red',
            default => 'zinc',
        };
    }
};
?>


<div class="space-y-6">
    <div class="flex justify-between items-end">
        <div>
            <flux:heading size="xl">System Activity Logs</flux:heading>
            <flux:subheading>Comprehensive audit trail of administrative and user actions.</flux:subheading>
        </div>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>User</flux:table.column>
            <flux:table.column>Event</flux:table.column>
            <flux:table.column>Subject</flux:table.column>
            <flux:table.column>Timestamp</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($activities as $activity)
                <flux:table.row>
                    {{-- User Information --}}
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar src="{{ $activity->causer?->avatar_url }}"
                                name="{{ $activity->causer?->name ?? 'System' }}" />
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold">{{ $activity->causer?->name ?? 'System' }}</span>
                                <flux:text class="capitalize">
                                    {{ $activity->causer?->roles?->first()?->name ?? 'Guest' }}
                                </flux:text>
                            </div>
                        </div>
                    </flux:table.cell>

                    {{-- Action Badge --}}
                    <flux:table.cell>
                        <flux:badge size="sm" color="{{ $this->getBadgeColor($activity->description) }}">
                            {{ ucfirst($activity->description) }}
                        </flux:badge>
                    </flux:table.cell>

                    {{-- Subject Info --}}
                    <flux:table.cell>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium">
                                {{ class_basename($activity->subject_type) }}
                            </span>
                            <span class="text-xs ">ID: {{ $activity->subject_id ?? 'N/A' }}</span>
                        </div>
                    </flux:table.cell>

                    {{-- Time --}}
                    <flux:table.cell>
                        <flux:tooltip content="{{ $activity->created_at->format('M d, Y H:i:s') }}">
                            <span class="text-xs font-mono cursor-help">
                                {{ $activity->created_at->diffForHumans() }}
                            </span>
                        </flux:tooltip>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-10">
                        No activity logs found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="p-4 border-t border-zinc-100">
        {{ $activities->links() }}
    </div>
</div>