<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Route param থেকে token hydrate করা
    public function mount($token)
    {
        $this->token = $token;
        // Laravel ডিফল্ট ইমেইল লিঙ্কে email query আসে (?email=...)
        $this->email = request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'token' => ['required', 'string'],
        ]);

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function (User $user, $password) {
                $user
                    ->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])
                    ->save();

                event(new PasswordReset($user));
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', __($status)); // "Your password has been reset!"
            // চাইলে লগইন পেইজে রিডাইরেক্ট
            redirect()->route('login');
        } else {
            $this->addError('email', __($status)); // token invalid/expired ইত্যাদি
        }
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Reset password')" :description="__('Set a new password for your account')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <input type="hidden" wire:model="token">

        <flux:input wire:model="email" :label="__('Email Address')" type="email" required
            placeholder="email@example.com" />

        <flux:input wire:model="password" :label="__('New Password')" type="password" required />

        <flux:input wire:model="password_confirmation" :label="__('Confirm Password')" type="password" required />

        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Reset Password') }}
        </flux:button>
    </form>

    <div class="text-center text-sm text-zinc-400">
        <flux:link :href="route('login')" wire:navigate.hover>{{ __('Back to login') }}</flux:link>
    </div>
</div>
