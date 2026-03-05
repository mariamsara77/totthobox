<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\User;
use Illuminate\Support\Collection;

new class extends Component {
    public Collection $notifications;
    public int $unreadCount = 0;
    public int $totalCount = 0;
    public string $filter = 'all';

    public function mount(): void
    {
        $this->refreshData();
    }

    // Volt বা Component এর refreshData মেথডে এটি আপডেট করুন
    public function refreshData(): void
    {
        $user = Auth::user();
        if (!$user)
            return;

        // ১. মাত্র একটি কুয়েরিতে সব নোটিফিকেশন নিয়ে আসা (Collection এ রাখা)
        // এটি করলে নিচের কাউন্টগুলোর জন্য আর ডাটাবেজে যেতে হবে না
        $allUserNotifications = $user->notifications()->latest()->get();

        // ২. ডাটাবেজে না গিয়ে কালেকশন থেকে কাউন্ট বের করা (০ কুয়েরি)
        $this->totalCount = $allUserNotifications->count();
        $this->unreadCount = $allUserNotifications->whereNull('read_at')->count();

        // ৩. ফিল্টার অনুযায়ী ৩০টি নোটিফিকেশন আলাদা করা
        $limitedNotifications = ($this->filter === 'unread')
            ? $allUserNotifications->whereNull('read_at')->take(30)
            : $allUserNotifications->take(30);

        // ৪. সেন্ডার আইডি বের করা এবং ইউজার কুয়েরি (Eager Loading style)
        $senderIds = $limitedNotifications->pluck('data.sender_id')->filter()->unique();

        $senders = collect();
        if ($senderIds->isNotEmpty()) {
            $senders = User::with(['media'])->whereIn('id', $senderIds)->get()->keyBy('id');
        }

        $this->notifications = $limitedNotifications->map(function ($n) use ($senders) {
            $senderId = $n->data['sender_id'] ?? null;
            return $this->format($n, $senderId ? $senders->get($senderId) : null);
        });
    }

    public function updatedFilter(): void
    {
        $this->refreshData();
    }

    /**
     * format মেথড এখন $sender অবজেক্ট সরাসরি রিসিভ করবে
     */
    private function format(DatabaseNotification $n, ?User $sender): object
    {
        $data = $n->data;

        return (object) [
            'id' => $n->id,
            'sender_name' => $sender?->name ?? 'System',
            'sender_avatar' => $sender ? ($sender->avatar_url) : null,
            'is_online' => $sender ? $sender->isOnline() : false,
            'display_title' => $data['title'] ?? 'sent a notification',
            'message' => str($data['message'] ?? '')->limit(70),
            'action_url' => $data['action_url'] ?? '#',
            'action_text' => $data['action_text'] ?? 'View',
            'time' => $n->created_at->diffForHumans(short: true),
            'is_unread' => $n->unread(),
        ];
    }

    public function markAsRead(string $id, string $url): mixed
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if ($notification && $notification->unread()) {
            $notification->markAsRead();
        }

        if ($url !== '#' && !empty($url)) {
            return redirect($url);
        }

        $this->refreshData();

        // TypeError এড়ানোর জন্য explicit null রিটার্ন করা হলো
        return null;
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();
        if ($user) {
            // রিলেশন মেথড () কল করে সরাসরি আপডেট করুন
            $user->unreadNotifications()->update(['read_at' => now()]);
        }
        $this->refreshData();
    }
    public function clearAll(): void
    {
        Auth::user()->notifications()->delete();
        $this->refreshData();
    }

    public function getListeners(): array
    {
        $userId = Auth::id();
        return ["echo-private:user.{$userId},.notificationReceived" => 'refreshData'];
    }
}; ?>

<section>
    {{-- Trigger Button --}}
    <flux:modal.trigger name="notifications_modal">
        <flux:sidebar.item icon="bell" class="cursor-pointer relative"
            :badge="$unreadCount > 0 ? ($unreadCount > 99 ? '99+' : $unreadCount) : null" badge:color="red"
            badge:variant="solid">
            Notifications
        </flux:sidebar.item>
    </flux:modal.trigger>

    {{-- Notification Flyout Modal --}}
    <flux:modal name="notifications_modal" flyout class="!p-0 w-full max-w-[400px]">

        {{-- Header Section --}}
        <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-4">
                <flux:icon.bell-alert
                    class="dark:text-white rounded-lg size-8 dark:bg-white/25 text-black bg-zinc-400/25 p-1"
                    variant="solid" />
                <flux:heading size="lg">Notifications</flux:heading>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading size="sm">
                        You have {{ $unreadCount }} unread messages
                    </flux:subheading>
                </div>

                @if ($unreadCount > 0)
                    <flux:button wire:click="markAllAsRead" variant="subtle" size="xs" icon="check-circle"
                        tooltip="Mark all as read" />
                @endif
            </div>

            {{-- Filter Tabs --}}
            <div class="mt-5">
                <flux:radio.group wire:model.live="filter" variant="segmented" size="sm">
                    <flux:radio value="all" label="All ({{ $totalCount }})" />
                    <flux:radio value="unread" label="Unread ({{ $unreadCount }})" />
                </flux:radio.group>
            </div>
        </div>

        {{-- Notifications List --}}
        <div class="overflow-y-auto max-h-[70vh] min-h-[70vh] divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse ($notifications as $notification)
                <div wire:key="{{ $notification->id }}"
                    wire:click="markAsRead('{{ $notification->id }}', '{{ $notification->action_url }}')"
                    class="group relative p-4 flex gap-3 transition-all cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-900 {{ $notification->is_unread ? 'bg-indigo-50/20 dark:bg-indigo-500/5' : '' }}">

                    {{-- Left: Avatar --}}
                    <div class="relative flex-shrink-0">
                        <flux:avatar src="{{ $notification->sender_avatar }}" size="sm"
                            name="{{ $notification->sender_name }}" :badge="$notification->is_online ? true : false"
                            badge:color="green" />
                    </div>

                    {{-- Middle: Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between gap-1">
                            <p class="text-sm text-zinc-600 dark:text-zinc-300 leading-tight">
                                <strong
                                    class="font-semibold text-zinc-900 dark:text-white">{{ $notification->sender_name }}</strong>
                                {{ $notification->display_title }}
                            </p>
                            <span class="text-[10px] font-medium text-zinc-400 whitespace-nowrap lowercase">
                                {{ $notification->time }}
                            </span>
                        </div>

                        @if($notification->message)
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-1 italic">
                                "{{ $notification->message }}"
                            </p>
                        @endif
                    </div>

                    {{-- Right: Unread Indicator --}}
                    @if ($notification->is_unread)
                        <div class="flex items-center">
                            <div class="size-2 rounded-full bg-indigo-600 shadow-sm shadow-indigo-200"></div>
                        </div>
                    @endif
                </div>
            @empty
                {{-- Empty State code here --}}
            @endforelse
        </div>

        {{-- Footer Actions --}}
        @if ($totalCount > 0)
            <div class="p-2">
                <flux:button wire:click="clearAll"
                    wire:confirm="This will permanently delete all notifications. Are you sure?" variant="subtle" size="sm"
                    class="w-full">
                    Delete All Notifications
                </flux:button>
            </div>
        @endif
    </flux:modal>
</section>