<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;

new class extends Component {
    public ?int $lastUserId = null;
    public ?string $lastName = null;
    public ?string $lastEmail = null;
    public ?string $lastAvatar = null;

    public bool $resetLastUserConfirm = false;

    public function mount()
    {
        $cookie = request()->cookie('last_logged_user');

        if (!$cookie) {
            return;
        }

        try {
            $id = decrypt($cookie);
            $user = User::find($id);

            if ($user) {
                $this->lastUserId = $user->id;
                $this->lastName = $user->name;
                $this->lastEmail = $user->email;
                $this->lastAvatar = $user->getFirstMediaUrl('avatars', 'thumb');
            }
        } catch (\Exception $e) {
            $this->resetLastUser();
        }
    }

    public function quickContinue(): void
    {
        if (!$this->lastUserId) {
            return;
        }

        $user = User::find($this->lastUserId);

        if ($user) {
            Auth::login($user);
            session()->regenerate();
            $this->redirect(route('home'), navigate: true);
        }
    }

    public function removeAccount(): void
    {
        $this->resetLastUser();
    }

    protected function resetLastUser(): void
    {
        $this->reset(['lastUserId', 'lastName', 'lastEmail', 'lastAvatar', 'resetLastUserConfirm']);
        Cookie::queue(Cookie::forget('last_logged_user'));
    }

    public function toggleReset()
    {
        $this->resetLastUserConfirm = !$this->resetLastUserConfirm;
    }
}; ?>

<div class="w-full">
    @if ($lastUserId && !auth()->check())
        <div class="relative ">
            <div class="bg-zinc-400/25 rounded-full p-2">
                <div class="flex items-center gap-4 cursor-pointer">
                    <div class="relative">
                        <flux:avatar badge circle badge:color="green" size="sm" src="{{ $lastAvatar }}"
                            name="{{ $lastName }}" />
                    </div>
                    <div class="flex-1 min-w-0 text-left">
                        <flux:callout.heading class="text-xs truncate">
                            {{ substr($lastName, 0, 15) }}
                        </flux:callout.heading>
                        {{-- <flux:callout.text class="text-xs dark:text-zinc-400">দ্রুত লগইন অ্যাক্টিভ</flux:callout.text>
                        --}}
                    </div>
                    {{--
                    <flux:icon name="chevron-right" variant="mini" /> --}}
                    <flux:button wire:click="quickContinue" variant="ghost" size="xs" class="!rounded-full"
                        icon="arrow-right">
                        সরাসরি লগইন
                    </flux:button>
                    <flux:button wire:click="toggleReset" variant="ghost" size="xs" class="!rounded-full !text-red-600"
                        icon="x-mark">

                    </flux:button>
                </div>
            </div>
            @if ($resetLastUserConfirm)
                <div
                    class="absolute inset-0 z-20 flex flex-col items-center justify-center backdrop-blur-sm rounded-full p-2 text-center animate-in fade-in slide-in-from-top-4">
                    <flux:text>আপনি কি নিশ্চিত যে এই অ্যাকাউন্টটি সরিয়ে ফেলতে চান?</flux:text>
                    <div class="flex gap-3">
                        <flux:button wire:click="removeAccount" variant="danger" size="xs">হ্যাঁ, রিমুভ করুন
                        </flux:button>
                        <flux:button wire:click="toggleReset" variant="ghost" size="xs">বাতিল</flux:button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>