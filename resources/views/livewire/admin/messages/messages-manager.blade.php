<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use App\Models\Message;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    // Form States
    #[Validate('required|exists:users,id')]
    public $sender_id = '';

    #[Validate('required|exists:users,id|different:sender_id')]
    public $receiver_id = '';

    #[Validate('required|string|max:5000')]
    public $body = '';

    public $messageId = null;
    public $search = '';

    // Audit Logs States
    public $activeLogs = [];
    public $logDetails = null;

    public function with(): array
    {
        $messages = Message::with(['sender', 'receiver'])
            ->when($this->search, function ($query) {
                // Encrypted data-র কারণে বডি সার্চের চেয়ে ইউজার নেমে সার্চ বেশি কার্যকর
                $query->whereHas('sender', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))->orWhereHas('receiver', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->withTrashed()
            ->latest()
            ->paginate(10);

        return [
            'messages' => $messages,
            'users' => User::select(['id', 'name', 'email'])->get(),
        ];
    }

    // Create or Update
    public function save()
    {
        $this->validate();

        $data = [
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'body' => encrypt($this->body), // মেসেজ প্রাইভেসি সুরক্ষিত রাখতে ইনক্রিপশন
        ];

        if ($this->messageId) {
            $message = Message::withTrashed()->findOrFail($this->messageId);
            $message->update($data);
            Flux::toast('Message updated successfully.');
        } else {
            Message::create($data);
            Flux::toast('Message log created successfully.');
        }

        $this->resetForm();
    }

    // Edit
    public function edit($id)
    {
        $message = Message::withTrashed()->findOrFail($id);
        $this->messageId = $message->id;
        $this->sender_id = $message->sender_id;
        $this->receiver_id = $message->receiver_id;

        try {
            $this->body = decrypt($message->body);
        } catch (\Exception $e) {
            $this->body = $message->body; // Fallback
        }

        Flux::modal('message-modal')->show();
    }

    // Soft Delete
    public function delete($id)
    {
        $message = Message::findOrFail($id);
        $message->delete();
        Flux::toast('Message soft deleted.');
    }

    // Restore
    public function restore($id)
    {
        $message = Message::onlyTrashed()->findOrFail($id);
        $message->restore();
        Flux::toast('Message restored successfully.');
    }

    // Force Delete
    public function forceDelete($id)
    {
        $message = Message::onlyTrashed()->findOrFail($id);
        $message->clearMediaCollection(); // Spatie media cleanup
        $message->forceDelete();
        Flux::toast('Message permanently removed.');
    }

    // Spatie Activity Logs
    public function viewLogs($id)
    {
        $this->activeLogs = Activity::query()
            ->where('subject_type', Message::class)
            ->where('subject_id', $id)
            ->latest()
            ->get()
            ->map(
                fn($activity) => [
                    'id' => $activity->id,
                    'event' => strtoupper($activity->event),
                    'causer' => $activity->causer?->name ?? 'System',
                    'time' => $activity->created_at->format('Y-m-d H:i:s'),
                ],
            )
            ->toArray();

        Flux::modal('logs-modal')->show();
    }

    public function viewActivityDetails($activityId)
    {
        $activity = Activity::findOrFail($activityId);
        $this->logDetails = json_encode($activity->properties, JSON_PRETTY_PRINT);
        Flux::modal('log-details-modal')->show();
    }

    public function resetForm()
    {
        $this->reset(['sender_id', 'receiver_id', 'body', 'messageId']);
        Flux::modal('message-modal')->close();
    }
}; ?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by user name..." class="max-w-xs" />
        <flux:button wire:click="resetForm" onclick="Flux.modal('message-modal').show()" variant="primary">Create
            Message Log</flux:button>
    </div>

    <flux:table :paginate="$messages">
        <flux:table.columns>
            <flux:table.column>Sender (কে পাঠিয়েছে)</flux:table.column>
            <flux:table.column class="text-center">Direction</flux:table.column>
            <flux:table.column>Receiver (কাকে পাঠিয়েছে)</flux:table.column>
            <flux:table.column>Secure Payload</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($messages as $message)
                <flux:table.row :key="$message->id">
                    <flux:table.cell class="flex items-center space-x-3">
                        <flux:avatar src="{{ $message->sender->getFirstMediaUrl('avatars', 'thumb') }}"
                            name="{{ $message->sender->neme }}" alt="Sender" />
                        <div>
                            <span class="font-semibold text-sm block">{{ $message->sender->name }}</span>
                            <span class="text-xs text-gray-400 block">ID: {{ $message->sender_id }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-500 font-bold">
                            ➔
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="flex items-center space-x-3">
                        <flux:avatar src="{{ $message->sender->getFirstMediaUrl('avatars', 'thumb') }}"
                            name="{{ $message->sender->neme }}" alt="Sender" />
                        <div>
                            <span class="font-semibold text-sm block">{{ $message->sender->name }}</span>
                            <span class="text-xs text-gray-400 block">ID: {{ $message->sender_id }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="w-4 0 truncate">
                            {{ $message->message }}
                        </span>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($message->trashed())
                            <flux:badge color="red" size="sm">Trashed</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">Active</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="flex space-x-1.5">
                        @if (!$message->trashed())
                            <flux:button wire:click="edit({{ $message->id }})" size="sm" variant="ghost">Edit
                            </flux:button>
                            <flux:button wire:click="delete({{ $message->id }})" size="sm" variant="ghost"
                                color="red">Delete
                            </flux:button>
                        @else
                            <flux:button wire:click="restore({{ $message->id }})" size="sm" variant="ghost"
                                color="green">
                                Restore</flux:button>
                            <flux:button wire:click="forceDelete({{ $message->id }})" size="sm" variant="ghost"
                                color="red">
                                Force</flux:button>
                        @endif
                        <flux:button wire:click="viewLogs({{ $message->id }})" size="sm" variant="ghost"
                            color="amber">Logs
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="message-modal" class="md:max-w-lg">
        <form wire:submit="save" class="space-y-4">
            <flux:heading>{{ $messageId ? 'Modify Message Node' : 'Inject Message Transaction' }}</flux:heading>

            <flux:select wire:model="sender_id" label="From (Sender)">
                <option value="">Select Sender</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="receiver_id" label="To (Receiver)">
                <option value="">Select Receiver</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </flux:select>

            <flux:textarea wire:model="body" label="Raw Text Payload (Will be encrypted dynamically)" rows="3" />

            <div class="flex space-x-2 justify-end">
                <flux:button onclick="Flux.modal('message-modal').close()" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Execute</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="logs-modal" class="md:max-w-xl">
        <flux:heading class="mb-4">Spatie Node Audit Trail</flux:heading>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Action</flux:table.column>
                <flux:table.column>Operator</flux:table.column>
                <flux:table.column>Timestamp</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($activeLogs as $log)
                    <flux:table.row :key="$log['id']">
                        <flux:table.cell>**{{ $log['event'] }}**</flux:table.cell>
                        <flux:table.cell>{{ $log['causer'] }}</flux:table.cell>
                        <flux:table.cell>{{ $log['time'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button wire:click="viewActivityDetails({{ $log['id'] }})" size="sm"
                                variant="ghost">
                                Properties</flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:modal>

    <flux:modal name="log-details-modal">
        <flux:heading class="mb-2">Activity Data Matrix</flux:heading>
        <pre class="bg-gray-900 text-emerald-400 p-4 rounded text-xs overflow-x-auto font-mono">{{ $logDetails }}</pre>
    </flux:modal>
</div>
