<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public $users = [];
    public $search = '';

    public function mount()
    {
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $this->users = User::where('id', '!=', auth()->id())
            ->where('role', '!=', 'Admin') // exclude admins from list
            ->when($this->search, function ($query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('slug', 'like', $term);
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function updatedSearch()
    {
        $this->loadUsers();
    }
}; ?>

<flux:modal name="open-conversations-modal" class="w-full">
    <flux:input icon="search" placeholder="Search users..." wire:model.live.debounce.300ms="search" class="my-6"
        variant="filled" autofocus />

    @foreach ($users as $user)
        <a href="{{ route('messages', $user->slug) }}">
            <div
                class="flex items-center gap-4 px-4 py-3 hover:bg-gray-400/10 transition-transform duration-300  hover:scale-105 rounded-xl">

                <!-- Avatar -->
                <div>

                    <flux:avatar src="{{ $user->avatar }}" name="{{ $user->name }}" badge
                        badge:color="{{ $user->isOnline() ? 'green' : 'zinc' }}" color="auto"
                        color:seed="{{ $user->id }}" />


                    {{-- @if ($user->isOnline())
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full
                                                                   border-2 border-white dark:border-zinc-800"></span>
                    @else
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-gray-600 dark:bg-gray-300 rounded-full
                                                                   border-2 border-white dark:border-zinc-800"></span>
                    @endif --}}
                </div>

                <!-- User Details -->
                <div>
                    <flux:heading>
                        {{ $user->name }}
                    </flux:heading>

                    <flux:text size="sm" color="{{ $user->isOnline() ? 'green' : null }}">
                        @if ($user->isOnline())
                            Online
                        @else
                            <flux:text size="sm" class="text-zinc-500">
                                Last seen {{ $user->last_active_at?->diffForHumans() ?? 'Never' }}
                            </flux:text>
                        @endif
                    </flux:text>



                </div>


            </div>
        </a>
    @endforeach
</flux:modal>