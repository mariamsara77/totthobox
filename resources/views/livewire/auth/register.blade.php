<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Cookie;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $status = 'active';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // পাসওয়ার্ড হ্যাশ করা
        $validated['password'] = Hash::make($validated['password']);

        // স্ট্যাটাস সেট করা (যদি $this->status এর কোনো ভ্যালু থাকে)
        $validated['status'] = $this->status ?? 'active';

        // ১. ইউজার তৈরি
        $user = User::create($validated);

        // ২. রোল অ্যাসাইন করা (Spatie)
        // নিশ্চিত করুন ডাটাবেসে 'user' নামের রোলটি আগেই তৈরি করা আছে
        $user->assignRole('user');

        event(new Registered($user));

        Auth::login($user);

        session()->save();

        Cookie::queue('last_logged_user', encrypt($user->id), 60 * 24 * 365, '/', null, true, true);

        $this->redirectIntended(route('home', absolute: false));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <div>
            <!-- Name -->
            <input wire:model="name"
                class="border  rounded-full py-3 px-6 w-full @error('name') border-red-600 @else border-zinc-400/25 @enderror"
                type="text" autofocus autocomplete="name" placeholder="Full name" />
            @error('name')
                <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <!-- Email Address -->
            <input wire:model="email"
                class="border  rounded-full py-3 px-6 w-full @error('email') border-red-600 @else border-zinc-400/25 @enderror"
                type="email" autocomplete="email" placeholder="email@example.com" />
            @error('email')
                <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <div class="relative items-center" x-data="{ show: false }">
                <!-- Password -->
                <input wire:model="password" :type="show ? 'text' : 'password'"
                    class="border  rounded-full py-3 px-6 w-full @error('password') border-red-600 @else border-zinc-400/25 @enderror"
                    type="password" autocomplete="new-password" placeholder="Password" viewable />
                <div class="absolute inset-y-0 right-2 flex items-center">
                    <flux:button @click="show = !show" size="xs" variant="subtle" class="!rounded-full">
                        <flux:icon x-show="!show" icon="eye" class="size-" variant="micro" />

                        <flux:icon x-show="show" x-cloak icon="eye-slash" class="size-" variant="micro" />
                    </flux:button>
                </div>
            </div>
            @error('password')
                <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <div class="relative items-center" x-data="{ show: false }">
                <!-- Confirm Password -->
                <input wire:model="password_confirmation" :type="show ? 'text' : 'password'"
                    class="border  rounded-full py-3 px-6 w-full @error('password') border-red-600 @else border-zinc-400/25 @enderror"
                    type="password" autocomplete="new-password" placeholder="Confirm password" />
                <div class="absolute inset-y-0 right-2 flex items-center">
                    <flux:button @click="show = !show" size="xs" variant="subtle" class="!rounded-full">
                        <flux:icon x-show="!show" icon="eye" class="size-" variant="micro" />

                        <flux:icon x-show="show" x-cloak icon="eye-slash" class="size-" variant="micro" />
                    </flux:button>
                </div>
            </div>
            @error('password_confirmation')
                <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full !rounded-full py-6">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    @livewire('auth.google-login')
    @livewire('auth.facebook-auth')

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate.hover>{{ __('Log in') }}</flux:link>
    </div>
</div>
