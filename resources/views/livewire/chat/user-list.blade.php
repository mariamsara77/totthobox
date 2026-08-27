<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\Computed;

new class extends Component {
    public string $search = '';

    #[Computed]
    public function users()
    {
        return User::query()
            ->whereKeyNot(auth()->id()) // নিজেকে (auth user) বাদ দেওয়া হয়েছে
            ->with(['roles', 'media'])
            ->whereDoesntHave('roles', fn($query) => $query->whereIn('name', ['Admin', 'Super Admin']))
            ->when(filled($this->search), function ($query) {
                // লারাভেলের নতুন whereAny মেথড দিয়ে ক্লিন সার্চ লজিক
                $query->whereAny(['name', 'email', 'phone', 'slug'], 'like', '%' . trim($this->search) . '%');
            })
            // MariaDB-এর জন্য সঠিক পদ্ধতি
            ->orderByRaw('last_active_at IS NULL ASC')
            ->orderByDesc('last_active_at')
            ->limit(50)
            ->get();
    }
}; ?>

<flux:modal name="open-conversations-modal" class="w-full">
    <flux:input icon="search" placeholder="Search users..." wire:model.live.debounce.400ms="search" class="my-6"
        variant="filled" autofocus />

    <div class="space-y-2">
        @forelse ($this->users as $user)
        <flux:accordion>
            <flux:accordion.item>
                <flux:accordion.heading>
                    <div class="flex items-center gap-4">
                        <flux:avatar src="{{ $user->getFirstMediaUrl('avatars', 'thumb') }}" name="{{ $user->name }}"
                            badge badge:color="{{ $user->isOnline() ? 'green' : 'zinc' }}" color="auto"
                            color:seed="{{ $user->id }}" />
                        <div>
                            <flux:heading>{{ $user->name }}</flux:heading>

                            <div class="flex items-center gap-1">

                                @if ($user->isOnline())
                                <span class="relative flex h-2 w-2">

                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>

                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>

                                </span>

                                <flux:text size="sm" class="text-green-600 dark:text-green-400">Online
                                </flux:text>
                                @else
                                <flux:text size="sm" class="text-zinc-500 truncate">

                                    Last seen {{ $user->last_active_at?->diffForHumans() ?? 'long ago' }}

                                </flux:text>
                                @endif

                            </div>

                        </div>
                    </div>
                </flux:accordion.heading>
                <flux:accordion.content>
                    <div class="flex gap-4 p-2">
                        <flux:button href="{{ route('users.show', $user->slug) }}" icon="user" size="sm"
                            variant="filled" class="rounded-xl">Profile</flux:button>
                        <flux:button href="{{ route('messages', $user->slug) }}" icon="chat-bubble-left"
                            class="rounded-xl" variant="primary" size="sm">Message</flux:button>
                    </div>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
        @empty
        <div class="py-12 text-center">
            <flux:icon.users class="mx-auto h-12 w-12 text-zinc-300" />
            <flux:heading class="mt-2">No users found</flux:heading>
        </div>
        @endforelse
    </div>
</flux:modal>