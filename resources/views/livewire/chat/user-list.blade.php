<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\Computed;

new class extends Component {
    public $search = '';

    /**
     * Computed property ব্যবহার করার সুবিধা হলো এটি মেমোরি বাঁচায় 
     * এবং শুধুমাত্র যখন প্রয়োজন তখনই ডাটা ফেচ করে।
     */
    #[Computed]
    public function users()
    {
        return User::query()
            ->where('id', '!=', auth()->id())
            // Eager loading roles and media to prevent N+1
            ->with(['roles', 'media'])
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Admin', 'Super Admin']);
            })
            ->when(trim($this->search), function ($query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('slug', 'like', $term);
                });
            })
            ->orderBy('name')
            ->limit(50) // Performance guard
            ->get();
    }

    // সার্চ আপডেট হলে অটো রেন্ডার হবে
    public function updatedSearch()
    {
        // No manual call needed when using Computed Properties
    }
}; ?>

<flux:modal name="open-conversations-modal" class="w-full">
    {{-- Search Input with Debounce --}}
    <flux:input icon="search" placeholder="Search users..." wire:model.live.debounce.400ms="search" class="my-6"
        variant="filled" autofocus />

    <div class="space-y-1">
        @forelse ($this->users as $user)
            <a href="{{ route('messages', $user->slug) }}" wire:key="user-{{ $user->id }}">
                <div
                    class="flex items-center gap-4 px-4 py-3 hover:bg-gray-400/10 transition-all duration-200 hover:scale-[1.02] rounded-xl">

                    <div class="relative">
                        <flux:avatar src="{{ $user->getFirstMediaUrl('avatars', 'thumb') }}" name="{{ $user->name }}" badge
                            badge:color="{{ $user->isOnline() ? 'green' : 'zinc' }}" color="auto"
                            color:seed="{{ $user->id }}" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <flux:heading class="truncate">
                            {{ $user->name }}
                        </flux:heading>

                        <div class="flex items-center gap-1">
                            @if ($user->isOnline())
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                                <flux:text size="sm" class="text-green-600 dark:text-green-400">Online</flux:text>
                            @else
                                <flux:text size="sm" class="text-zinc-500 truncate">
                                    Last seen {{ $user->last_active_at?->diffForHumans() ?? 'long ago' }}
                                </flux:text>
                            @endif
                        </div>
                    </div>

                    {{-- Arrow icon for better UX --}}
                    <flux:icon.chevron-right variant="micro" class="text-zinc-400" />
                </div>
            </a>
        @empty
            <div class="py-12 text-center">
                <flux:icon.users class="mx-auto h-12 w-12 text-zinc-300" />
                <flux:heading class="mt-2">No users found</flux:heading>
                <flux:text>Try searching for a different name or email.</flux:text>
            </div>
        @endforelse
    </div>
</flux:modal>