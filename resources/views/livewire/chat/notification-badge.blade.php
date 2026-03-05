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

    public function refreshData(): void
    {
        $user = Auth::user();
        if (!$user)
            return;

        // ১. একবারে নোটিফিকেশন ফেচ করা (৩০টি লিমিট)
        $query = ($this->filter === 'unread')
            ? $user->unreadNotifications()
            : $user->notifications();

        $fetchedNotifications = $query->latest()->limit(30)->get();

        // ২. কাউন্ট অপ্টিমাইজেশন (Memory-efficient approach)
        // যদি ৩০টির কম নোটিফিকেশন থাকে, তবে ডেটাবেজে নতুন করে কাউন্ট কুয়েরি করবে না
        if ($fetchedNotifications->count() < 30 && $this->filter === 'all') {
            $this->totalCount = $fetchedNotifications->count();
            $this->unreadCount = $fetchedNotifications->whereNull('read_at')->count();
        } else {
            // বড় ডেটাসেটের জন্য ইনডেক্সড কাউন্ট কুয়েরি
            $this->totalCount = $user->notifications()->count();
            $this->unreadCount = $user->unreadNotifications()->count();
        }

        // ৩. ইগার লোডিং (N+1 সমস্যার সমাধান)
        // যেহেতু User মডেলে $with=['roles', 'permissions', 'media'] আছে, 
        // তাই এখানে শুধু ইউজারদের আইডি দিয়ে গেট করলেই সব রিলেশন চলে আসবে।
        $senderIds = $fetchedNotifications->pluck('data.sender_id')->filter()->unique();

        $senders = collect();
        if ($senderIds->isNotEmpty()) {
            $senders = User::whereIn('id', $senderIds)->get()->keyBy('id');
        }

        // ৪. ইউআই অবজেক্টে ম্যাপিং
        $this->notifications = $fetchedNotifications->map(function ($n) use ($senders) {
            $senderId = $n->data['sender_id'] ?? null;
            $sender = $senderId ? $senders->get($senderId) : null;
            return $this->format($n, $sender);
        });
    }

    public function updatedFilter(): void
    {
        $this->refreshData();
    }

    private function format(DatabaseNotification $n, ?User $sender): object
    {
        return (object) [
            'id' => $n->id,
            'sender_name' => $sender?->name ?? 'System',
            'sender_avatar' => $sender?->avatar_url,
            'is_online' => $sender ? $sender->isOnline() : false,
            'display_title' => $n->data['title'] ?? 'Update',
            'message' => str($n->data['message'] ?? '')->limit(70),
            'action_url' => $n->data['action_url'] ?? '#',
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
            // রিয়েলটাইমে কাউন্ট কমানো (পুরো রিফ্রেশ না করে)
            $this->unreadCount = max(0, $this->unreadCount - 1);
        }

        if ($url !== '#' && !empty($url)) {
            return redirect($url);
        }

        $this->refreshData();
        return null;
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        $this->unreadCount = 0;
        $this->refreshData();
    }

    public function clearAll(): void
    {
        Auth::user()->notifications()->delete();
        $this->totalCount = 0;
        $this->unreadCount = 0;
        $this->notifications = collect();
    }

    public function getListeners(): array
    {
        $userId = Auth::id();
        return ["echo-private:user.{$userId},.notificationReceived" => 'refreshData'];
    }
}; ?>

<section>
    {{-- Trigger Button: Optimized with Livewire state --}}
    <flux:modal.trigger name="notifications_modal">
        <flux:sidebar.item icon="bell" class="cursor-pointer relative"
            :badge="$unreadCount > 0 ? ($unreadCount > 99 ? '99+' : $unreadCount) : null" badge:color="red"
            badge:variant="solid">
            Notifications
        </flux:sidebar.item>
    </flux:modal.trigger>

    <flux:modal name="notifications_modal" flyout class="!p-0 w-full max-w-[400px]">
        {{-- Header: Professional Design --}}
        <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-4 mb-3">
                <div class="p-2 dark:bg-white/25 text-black dark:text-white bg-zinc-400/25 rounded-xl">
                    <flux:icon.bell-alert class="size-6 " variant="solid" />
                </div>
                <div>
                    <flux:heading size="lg">Notifications</flux:heading>
                    <flux:subheading size="sm">
                        {{ $unreadCount > 0 ? "You have $unreadCount unread notifications" : "You're all caught up!" }}
                    </flux:subheading>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <flux:radio.group wire:model.live="filter" variant="segmented" size="sm" class="flex-1">
                    <flux:radio value="all" label="All ({{ $totalCount }})" />
                    <flux:radio value="unread" label="Unread" />
                </flux:radio.group>

                @if ($unreadCount > 0)
                    <flux:button wire:click="markAllAsRead" variant="subtle" size="xs" icon="check-circle"
                        tooltip="Mark all read" />
                @endif
            </div>
        </div>

        {{-- List Section with Smooth Transitions --}}
        <div
            class="overflow-y-auto max-h-[70vh] min-h-[70vh] divide-y divide-zinc-100 dark:divide-zinc-800 scrollbar-thin">
            @forelse ($notifications as $notification)
                <div wire:key="notif-{{ $notification->id }}"
                    wire:click="markAsRead('{{ $notification->id }}', '{{ $notification->action_url }}')"
                    class="group relative p-4 flex gap-3 transition-colors cursor-pointer dark:hover:bg-zinc-400/10 {{ $notification->is_unread ? 'bg-indigo-50/30 dark:bg-indigo-500/5' : '' }}">

                    {{-- Avatar with Online Indicator --}}
                    <div class="relative flex-shrink-0">
                        <flux:avatar src="{{ $notification->sender_avatar }}" size="sm" class="rounded-lg shadow-sm"
                            name="{{ $notification->sender_name }}" :badge="$notification->is_online" badge:color="green" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm text-zinc-600 dark:text-zinc-300 leading-snug">
                                <span
                                    class="font-bold text-zinc-900 dark:text-white">{{ $notification->sender_name }}</span>
                                {{ $notification->display_title }}
                            </p>
                            <span class="text-[10px] font-medium text-zinc-400 whitespace-nowrap pt-0.5">
                                {{ $notification->time }}
                            </span>
                        </div>

                        @if($notification->message)
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-2 leading-relaxed">
                                {{ $notification->message }}
                            </p>
                        @endif
                    </div>

                    @if ($notification->is_unread)
                        <div class="flex items-center">
                            <div class="size-2 rounded-full bg-indigo-600 animate-pulse"></div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 px-10 text-center">
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-full mb-4">
                        <flux:icon.bell class="size-10 text-zinc-300" />
                    </div>
                    <flux:heading>All clear!</flux:heading>
                    <flux:subheading>No notifications found in this category.</flux:subheading>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if ($totalCount > 0)
            <div class="p-2">
                <flux:button wire:click="clearAll" wire:confirm="Are you sure you want to delete all notifications?"
                    variant="subtle" size="sm" icon="trash"
                    class="w-full !text-red-500 hover:!bg-red-50 dark:hover:!bg-red-500/10">
                    Clear All Notifications
                </flux:button>
            </div>
        @endif
    </flux:modal>
</section>