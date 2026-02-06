<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email|exists:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    public bool $emailLogin = false;
    public bool $remember = true;
    public ?string $status = null;

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    #[On('fill-login-email')]
    public function fillEmail(string $email): void
    {
        $this->email = $email;
    }

    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());
            $this->addError('email', __('auth.failed'));
            return;
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        // ★ Secure Last User Cookie (Encrypted + HttpOnly)
        Cookie::queue(
            Cookie::make(
                'last_logged_user',
                encrypt(Auth::id()), // encrypted for security
                60 * 24 * 365, // 365 days
                null,
                null,
                true, // secure (HTTPS)
                true, // HttpOnly
            ),
        );

        $this->status = __('Login successful!');
        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        $this->addError(
            'email',
            __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        );
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }

    public function toggleEmailLogin(): void
    {
        $this->emailLogin = true;
    }

    public function toggleEmailLoginClose(): void
    {
        $this->emailLogin = false;
    }
};

?>

<div class="flex flex-col gap-6 space-y-4">

    @include('partials.toast')

    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    @if ($status)
        <div class="text-center text-green-600">{{ $status }}</div>
    @endif

    @if ($emailLogin == true)
        <div class="">
            <form wire:submit.prevent="login" class="flex flex-col gap-6">
                <div>

                    <input wire:model.live="email" type="email" autocomplete="email" placeholder="email@example.com"
                        class="border  rounded-full py-3 px-6 w-full @error('email') border-red-600 @else border-zinc-400/25 @enderror" />
                    @error('email')
                        <flux:text class="text-red-600 text-xs mt-1">{{ $message }}</flux:text>
                    @enderror
                </div>

                <div>
                    <div class="relative items-center" x-data="{ show: false }">
                        <input wire:model.live.debounce.300ms="password" :type="show ? 'text' : 'password'"
                            label="Password" type="password" autocomplete="current-password" placeholder="Password"
                            viewable
                            class="border  rounded-full py-3 px-6 w-full @error('password') border-red-600 @else border-zinc-400/25 @enderror" />

                        <div class="absolute inset-y-0 right-2 flex items-center">
                            <flux:button @click="show = !show" size="xs" variant="subtle" class="!rounded-full">
                                <flux:icon x-show="!show" icon="eye" class="size-" variant="micro" />

                                <flux:icon x-show="show" x-cloak icon="eye-slash" class="size-" variant="micro" />
                            </flux:button>
                        </div>
                    </div>
                    @error('password')
                        <flux:text class="text-red-600 text-xs mt-1">{{ $message }}</flux:text>
                    @enderror
                </div>

                <div class="space-y-4">
                    <flux:button type="submit" class="w-full !rounded-full py-6" variant="primary">Log in</flux:button>

                    <div class="flex gap-3 justify-between">

                        {{-- <flux:checkbox wire:model="remember" label="Remember me" /> --}}

                        @if (Route::has('password.request'))
                            <flux:link class="text-sm" :href="route('password.request')" wire:navigate.hover>
                                {{ __('Forgot your password?') }}
                            </flux:link>
                        @endif
                        <!-- CLOSE USING ALPINE -->
                        <flux:button variant="subtle" size="xs" wire:click="toggleEmailLoginClose"
                            icon="arrow-left">
                            Back
                        </flux:button>
                    </div>

                </div>
            </form>
        </div>
    @else
        <div class=" space-y-4">
            <livewire:auth.last-user-display />

            <flux:button wire:click="toggleEmailLogin" class="w-full !rounded-full py-6" icon="envelope"
                variant="primary">
                Log in with Email
            </flux:button>

            @livewire('auth.google-login')
            @livewire('auth.facebook-auth')
        </div>
    @endif


    @if (Route::has('register'))
        <div class="text-center text-sm">
            <span>Don’t have an account?</span>
            <flux:link :href="route('register')" wire:navigate.hover>Sign up</flux:link>
        </div>
    @endif

</div>
