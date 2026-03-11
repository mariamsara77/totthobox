<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
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
        if (!$user) return;

        // ইউজার এবং ফিল্টার অনুযায়ী আলাদা ক্যাশ কি
        $cacheKey = "user_{$user->id}_notifications_{$this->filter}";

        // Cache::remember ব্যবহার করে ডেটাবেস কোয়েরি কমানো হয়েছে
        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            $query = ($this->filter === 'unread') 
                ? $user->unreadNotifications() 
                : $user->notifications();

            $fetched = $query->latest()->limit(30)->get();
            
            // ইগার লোডিং (N+1 সমস্যার সমাধান)
            $senderIds = $fetched->pluck('data.sender_id')->filter()->unique();
            $senders = User::whereIn('id', $senderIds)
                ->without(['roles', 'permissions']) 
                ->with(['media'])
                ->get()
                ->keyBy('id');

            return [
                'list' => $fetched,
                'senders' => $senders,
                'total' => $user->notifications()->count(),
                'unread' => $user->unreadNotifications()->count(),
            ];
        });

        // ডেটা এসাইন করা
        $this->notifications = $data['list']->map(function ($n) use ($data) {
            $sender = $data['senders']->get($n->data['sender_id'] ?? null);
            return $this->format($n, $sender);
        });

        $this->totalCount = $data['total'];
        $this->unreadCount = $data['unread'];
    }

    private function format(DatabaseNotification $n, ?User $sender): object
    {
        return (object) [
            'id' => $n->id,
            'sender_name' => $sender?->name ?? 'System',
            'sender_avatar' => $sender?->getAvatarUrlAttribute(),
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
            $this->invalidateCache(); // ক্যাশ ক্লিয়ার করা
        }

        return $url !== '#' && !empty($url) ? redirect($url) : null;
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        $this->invalidateCache();
    }

    public function clearAll(): void
    {
        Auth::user()->notifications()->delete();
        $this->invalidateCache();
    }

    public function invalidateCache(): void
    {
        $userId = Auth::id();
        Cache::forget("user_{$userId}_notifications_all");
        Cache::forget("user_{$userId}_notifications_unread");
        $this->refreshData();
    }

    public function getListeners(): array
    {
        $userId = Auth::id();
        return ["echo-private:user.{$userId},.notificationReceived" => 'invalidateCache'];
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