<?php

use Livewire\Volt\Component;
use App\Models\User;
use Spatie\Activitylog\Models\Activity; // এই লাইনটি মিসিং ছিল
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $user;

    public function mount($slug)
    {
        $this->user = User::where('slug', $slug)->firstOrFail();
    }

    public function with(): array
    {
        return [
            // সরাসরি Activity মডেল ব্যবহার করে কোয়েরি করছি
            'activities' => Activity::where('causer_id', $this->user->id)
                ->where('causer_type', User::class)
                ->latest()
                ->paginate(10),
        ];
    }

    public function getActivityConfig($description): array
    {
        return match ($description) {
            'created' => ['icon' => 'plus-circle', 'color' => 'text-teal-600'],
            'updated' => ['icon' => 'pencil', 'color' => 'text-amber-600'],
            'deleted' => ['icon' => 'trash', 'color' => 'text-red-600'],
            default => ['icon' => 'information-circle', 'color' => 'text-zinc-500'],
        };
    }
};
?>

<div class="mt-8">
    <div class="mb-6">
        <flux:heading size="lg">Recent Activity</flux:heading>
        <flux:subheading>Timeline of actions performed by {{ $user->name }}</flux:subheading>
    </div>


    <flux:table>
        <flux:table.columns>
            <flux:table.column>Action</flux:table.column>
            <flux:table.column>Subject</flux:table.column>
            <flux:table.column>Date</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($activities as $activity)
            @php($config = $this->getActivityConfig($activity->description))
            <flux:table.row>
                <flux:table.cell>
                    <div class="flex items-center gap-3">
                        <flux:icon :name="$config['icon']" variant="outline" class="size-5 {{ $config['color'] }}" />
                        <span class="font-medium text-sm">{{ ucfirst($activity->description) }}</span>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold">{{ class_basename($activity->subject_type) }}</span>
                        <span class="text-xs text-zinc-500">ID: {{ $activity->subject_id }}</span>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="text-xs text-zinc-400 font-mono">{{ $activity->created_at->diffForHumans() }}</span>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="3" class="text-center py-8 text-zinc-500">
                    No recent activity found.
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-6">
        {{ $activities->links() }}
    </div>
</div>