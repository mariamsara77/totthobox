<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink($this->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('status', __($status)); // "We have emailed your password reset link!"
        } else {
            // উদাহরণ: "passwords.user" হলে "We can't find a user with that email address."
            $this->addError('email', __($status));
        }
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <flux:input wire:model="email" :label="__('Email Address')" type="email" required autofocus
            placeholder="email@example.com" />
        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Email password reset link') }}
        </flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        <span>{{ __('Or, return to') }}</span>
        <flux:link :href="route('login')" wire:navigate.hover>{{ __('log in') }}</flux:link>
    </div>
</div>
