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

    /**
     * ডাটা লোড করার মেথড (Optimized)
     */
    public function refreshData(): void
    {
        $user = Auth::user();
        if (!$user)
            return;

        // দ্রুত কাউন্ট করার জন্য সরাসরি কুয়েরি
        $this->unreadCount = $user->unreadNotifications()->count();
        $this->totalCount = $user->notifications()->count();

        // নোটিফিকেশন কুয়েরি (Limit 30 for performance)
        $query = $user->notifications()->latest()->take(30);

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        // Map করার সময় রিলেশন লোড অপ্টিমাইজেশন
        $this->notifications = $query->get()->map(fn($n) => $this->format($n));
    }

    /**
     * ফিল্টার চেঞ্জ হলে ডাটা অটো রিফ্রেশ হবে
     */
    public function updatedFilter(): void
    {
        $this->refreshData();
    }

    /**
     * নোটিফিকেশন অবজেক্ট ফরম্যাটিং
     */
    // format মেথডটি এভাবে পরিবর্তন করুন
    private function format(DatabaseNotification $n): object
    {
        $data = $n->data;
        $senderId = $data['sender_id'] ?? null;
        $sender = $senderId ? User::find($senderId) : null;

        return (object) [
            'id' => $n->id,
            'sender_name' => $sender?->name ?? 'System',
            'sender_avatar' => $sender?->getFirstMediaUrl('avatars', 'thumb') ?? $sender?->avatar, // fallback photo
            'is_online' => $sender ? $sender->isOnline() : false,
            'display_title' => $data['title'] ?? 'sent a notification',
            'message' => str($data['message'] ?? '')->limit(70), // মেসেজটি ছোট রাখা হয়েছে
            'action_url' => $data['action_url'] ?? '#',
            'action_text' => $data['action_text'] ?? 'View',
            'time' => $n->created_at->diffForHumans(short: true), // '1 min ago' এর বদলে '1m'
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
        Auth::user()->unreadNotifications->markAsRead();
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
    <flux:modal name="notifications_modal" flyout variant="floating" class="!p-0 w-full max-w-[400px]">

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
                            {{-- size="xs" এবং ডট লুকের জন্য স্টাইল --}}
                            <flux:badge color="blue" variant="solid" class="!p-1">
                            </flux:badge>
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