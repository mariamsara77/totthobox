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
            {{-- <div
                class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-[2rem] blur opacity-20 group-hover:opacity-40 transition duration-500">
            </div> --}}


            <flux:callout class="!border-0">
                <div wire:click="quickContinue" class="flex items-center gap-4 cursor-pointer">

                    <div class="relative">
                        <flux:avatar badge badge:color="green" size="xl" src="{{ $lastAvatar }}"
                            name="{{ $lastName }}" class="ring-2 ring-white dark:ring-zinc-800 shadow-md" />
                    </div>

                    <div class="flex-1 min-w-0 text-left">
                        <flux:callout.heading class="">
                            {{ substr($lastName, 0, 20) }}
                        </flux:callout.heading>
                        <flux:text class="text-sm dark:text-zinc-400">সেশন চালিয়ে যান</flux:text>
                    </div>

                    <flux:icon name="chevron-right" variant="mini" />
                </div>
            </flux:callout>
            <div class=" px-4 py-1 flex justify-between items-center">
                <flux:text class="text-xs">

                    দ্রুত লগইন অ্যাক্টিভ
                </flux:text>

                <flux:link wire:click="toggleReset" variant="ghost" as="button" class="text-xs">
                    আপনি নন?
                </flux:link>
            </div>
            @if ($resetLastUserConfirm)
                <div
                    class="absolute inset-0 z-20 flex flex-col items-center justify-center backdrop-blur-sm rounded-[1.8rem] p-6 text-center animate-in fade-in slide-in-from-top-4">
                    <flux:text>আপনি কি নিশ্চিত যে এই অ্যাকাউন্টটি সরিয়ে ফেলতে চান?</flux:text>
                    <div class="flex gap-3">
                        <flux:button wire:click="removeAccount" variant="danger" size="sm">হ্যাঁ, রিমুভ করুন
                        </flux:button>
                        <flux:button wire:click="toggleReset" variant="ghost" size="sm">বাতিল</flux:button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
