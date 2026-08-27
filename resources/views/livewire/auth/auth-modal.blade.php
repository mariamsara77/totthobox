<?php

use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\{Auth, Hash, RateLimiter, Session, Cookie, Mail, DB};
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\{Layout, On};
use Livewire\Volt\Component;
use Flux\Flux;

new #[Layout('components.layouts.auth')] class extends Component {
    // ========== Common ==========
    public string $activeTab = 'login'; // login | register

    // ========== Login ==========
    public string $login_email = '';
    public string $login_password = '';
    public bool $remember = true;
    public bool $showEmailLogin = false;
    public ?string $loginStatus = null;

    // ========== Register ==========
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $otp_input = '';
    public bool $is_otp_sent = false;

    // public function mount(): void
    // {
    //     if (auth()->guest()) {
    //         $this->js("setTimeout(() => Flux.modal('auth-modal').show(), 80)");
    //     }
    // }

    // ==================== LOGIN ====================

    public function updated($property)
    {
        // Real-time validation only for the active form
        if (str_starts_with($property, 'login_') || in_array($property, ['name', 'email', 'password', 'password_confirmation', 'otp_input'])) {
            $this->validateOnly($property);
        }
    }

    protected function rules()
    {
        if ($this->activeTab === 'login') {
            return [
                'login_email' => 'required|email|exists:users,email',
                'login_password' => 'required|min:8',
            ];
        }

        if ($this->is_otp_sent) {
            return [
                'otp_input' => 'required|digits:4',
            ];
        }

        return [
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255', Rules\Password::defaults()],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }

    protected function messages()
    {
        return [
            // Login
            'login_email.required' => 'ইমেইল অ্যাড্রেসটি প্রয়োজন।',
            'login_email.email' => 'সঠিক ইমেইল ফরম্যাট ব্যবহার করুন।',
            'login_email.exists' => 'এই ইমেইলটি আমাদের রেকর্ডে নেই।',
            'login_password.required' => 'পাসওয়ার্ডটি অবশ্যই দিতে হবে।',
            'login_password.min' => 'পাসওয়ার্ডটি কমপক্ষে ৮ অক্ষরের হতে হবে।',

            // Register
            'name.required' => 'আপনার নাম দিতে হবে।',
            'name.min' => 'নাম অন্তত ৩ অক্ষরের হতে হবে।',
            'email.required' => 'আপনার ইমেইল ঠিকানা দিতে হবে।',
            'email.email' => 'সঠিক ইমেইল ফরম্যাট ব্যবহার করুন।',
            'email.unique' => 'এই ইমেইলটি দিয়ে ইতিমধ্যে অ্যাকাউন্ট খোলা হয়েছে।',
            'password.required' => 'একটি পাসওয়ার্ড দিন।',
            'password.min' => 'পাসওয়ার্ডটি অন্তত ৮ অক্ষরের হতে হবে।',
            'password_confirmation.required' => 'পাসওয়ার্ডটি আবার লিখুন।',
            'password_confirmation.same' => 'পাসওয়ার্ড দুটি মিলছে না।',
            'otp_input.required' => 'ভেরিফিকেশন কোড দিন।',
            'otp_input.digits' => '৪ ডিজিটের কোড দিন।',
        ];
    }

    #[On('fill-login-email')]
    public function fillEmail(string $email): void
    {
        $this->login_email = $email;
        $this->showEmailLogin = true;
        $this->validateOnly('login_email');
    }

    public function login(): void
    {
        $this->login_email = Str::lower(trim($this->login_email));
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->login_email, 'password' => $this->login_password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());
            $this->addError('login_email', 'ইমেইল অথবা পাসওয়ার্ড সঠিক নয়।');
            return;
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->saveAccountCookie(Auth::id());

        $this->loginStatus = 'লগইন সফল হয়েছে! রিডাইরেক্ট করা হচ্ছে...';
        $this->modal('auth-modal')->close();

        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        $this->addError('login_email', "অতিরিক্ত চেষ্টার কারণে অ্যাকাউন্ট সাময়িকভাবে লক। {$seconds} সেকেন্ড পর চেষ্টা করুন।");
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->login_email) . '|' . request()->ip();
    }

    // ==================== REGISTER ====================

    public function sendOtp(): void
    {
        $this->validate();

        try {
            $otp = (string) rand(1000, 9999);

            Session::put('pending_user', [
                'name' => trim(ucwords($this->name)),
                'email' => Str::lower(trim($this->email)),
                'password' => $this->password,
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10),
            ]);

            Mail::to($this->email)->send(new OtpMail($otp));

            $this->is_otp_sent = true;
            $this->dispatch('otp-sent'); // optional toast
        } catch (\Exception $e) {
            $this->addError('email', 'ইমেইল পাঠাতে সমস্যা হচ্ছে। পরে আবার চেষ্টা করুন।');
        }
    }

    public function verifyAndRegister(): void
    {
        $this->validate();

        $pending = Session::get('pending_user');

        if (!$pending || now()->isAfter($pending['expires_at'])) {
            $this->addError('otp_input', 'ওটিপির মেয়াদ শেষ হয়েছে। পুনরায় চেষ্টা করুন।');
            $this->is_otp_sent = false;
            return;
        }

        if ($this->otp_input !== $pending['otp']) {
            $this->addError('otp_input', 'ভেরিফিকেশন কোডটি সঠিক নয়।');
            return;
        }

        try {
            DB::transaction(function () use ($pending) {
                $user = User::create([
                    'name' => $pending['name'],
                    'email' => $pending['email'],
                    'password' => Hash::make($pending['password']),
                    'email_verified_at' => now(),
                ]);

                $user->assignRole('user');
                Auth::login($user);

                $this->saveAccountCookie($user->id);
            });

            Session::forget('pending_user');
            $this->modal('auth-modal')->close();
            $this->redirectIntended(route('home', absolute: false), navigate: true);
        } catch (\Exception $e) {
            $this->addError('otp_input', 'অ্যাকাউন্ট তৈরিতে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        }
    }

    public function resetRegisterForm(): void
    {
        $this->is_otp_sent = false;
        $this->otp_input = '';
        $this->resetValidation();
    }

    public function getEmailDashboardUrl(): string
    {
        $domain = Str::after($this->email, '@');

        return match ($domain) {
            'gmail.com' => 'https://mail.google.com/',
            'yahoo.com' => 'https://mail.yahoo.com/',
            'outlook.com', 'hotmail.com', 'live.com' => 'https://outlook.live.com/',
            'icloud.com' => 'https://www.icloud.com/mail',
            default => 'mailto:' . $this->email,
        };
    }

    // ==================== Shared Helpers ====================

    protected function saveAccountCookie(int $userId): void
    {
        $cookieName = 'saved_accounts';
        $userIds = [];

        if ($existing = request()->cookie($cookieName)) {
            try {
                $userIds = json_decode(decrypt($existing), true) ?: [];
            } catch (\Exception $e) {
                $userIds = [];
            }
        }

        if (!in_array($userId, $userIds)) {
            $userIds[] = $userId;
        }

        Cookie::queue(cookie()->forever($cookieName, encrypt(json_encode($userIds))));
        Cookie::queue(Cookie::forget('last_logged_user'));
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetValidation();
        $this->is_otp_sent = false;
        $this->showEmailLogin = false;
    }
};
?>

<div>
    <flux:modal name="auth-modal" class="sm:w-full" :dismissible="true">
        <div class="flex flex-col gap-6">

            {{-- Logo + Heading --}}
            <div class="text-center space-y-2">
                <div class="flex justify-center my-2">
                    <x-app-logo />
                </div>
                <flux:heading size="xl">
                    {{ $activeTab === 'login' ? 'আপনার অ্যাকাউন্টে লগ ইন করুন' : ($is_otp_sent ? 'ইমেইল ভেরিফাই করুন' : 'নতুন অ্যাকাউন্ট তৈরি করুন') }}
                </flux:heading>
                <flux:text class="text-base">
                    {{ $activeTab === 'login'
                        ? 'লগ ইন করতে নিচের ধাপগুলো অনুসরণ করুন'
                        : ($is_otp_sent
                            ? 'আপনার ইমেইলে পাঠানো ৪ ডিজিটের কোডটি দিন'
                            : 'আপনার তথ্য দিয়ে রেজিস্ট্রেশন সম্পন্ন করুন') }}
                </flux:text>
            </div>
            <flux:tab.group>
                {{-- Tabs (Login / Register) --}}
                @if (!$is_otp_sent)
                    <flux:tabs wire:model="activeTab" class="w-full">
                        <flux:tab name="login" wire:click="switchTab('login')">লগ ইন</flux:tab>
                        <flux:tab name="register" wire:click="switchTab('register')">সাইন আপ</flux:tab>
                    </flux:tabs>
                @endif
                <flux:tab.panel name="login">
                    {{-- ========== LOGIN TAB ========== --}}

                    @if ($loginStatus)
                        <div
                            class="p-3 text-center text-sm font-medium text-green-600 bg-green-50 rounded-xl border border-green-200">
                            {{ $loginStatus }}
                        </div>
                    @endif

                    @if ($showEmailLogin)
                        <form wire:submit="login" class="flex flex-col gap-4">
                            <div class="space-y-1">
                                <input wire:model="login_email" type="email" autocomplete="email"
                                    placeholder="ইমেইল অ্যাড্রেস"
                                    class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-200 @error('login_email') bg-red-400/10 @else bg-zinc-400/10 @enderror" />
                                @error('login_email')
                                    <flux:text class="text-red-600 text-xs pl-4">{{ $message }}</flux:text>
                                @enderror
                            </div>

                            <div class="space-y-1" x-data="{ show: false }">
                                <div class="relative">
                                    <input wire:model="login_password" :type="show ? 'text' : 'password'"
                                        placeholder="পাসওয়ার্ড দিন"
                                        class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-200 @error('login_password') bg-red-400/10 @else bg-zinc-400/10 @enderror" />
                                    <div class="absolute inset-y-0 right-3 flex items-center">
                                        <button type="button" @click="show = !show"
                                            class="p-2 text-zinc-500 hover:text-zinc-700">
                                            <flux:icon x-show="!show" icon="eye" variant="micro" />
                                            <flux:icon x-show="show" x-cloak icon="eye-slash" variant="micro" />
                                        </button>
                                    </div>
                                </div>
                                @error('login_password')
                                    <flux:text class="text-red-600 text-xs pl-4">{{ $message }}</flux:text>
                                @enderror
                            </div>

                            <div class="space-y-4">
                                <flux:button type="submit" class="w-full !rounded-full py-6 font-bold"
                                    variant="primary">
                                    লগ ইন করুন
                                </flux:button>

                                <div class="flex justify-between items-center px-2">
                                    @if (Route::has('password.request'))
                                        <flux:link class="text-xs" :href="route('password.request')"
                                            wire:navigate.hover>
                                            পাসওয়ার্ড ভুলে গেছেন?
                                        </flux:link>
                                    @endif
                                    <flux:button wire:click="$set('showEmailLogin', false)" icon="arrow-left"
                                        size="xs" variant="ghost" class="!rounded-full">
                                        পিছনে যান
                                    </flux:button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="space-y-4">
                            <livewire:auth.last-user-display lazy />

                            <flux:button wire:click="$set('showEmailLogin', true)" class="w-full !rounded-full py-6"
                                icon="envelope" variant="primary">
                                ইমেইল দিয়ে লগ ইন
                            </flux:button>

                            {{-- @livewire('auth.google-login')
                        @livewire('auth.facebook-auth') --}}
                        </div>
                    @endif

                </flux:tab.panel>

                {{-- ========== REGISTER TAB ========== --}}
                <flux:tab.panel name="register">

                    @if (!$is_otp_sent)
                        <form wire:submit="sendOtp" class="flex flex-col gap-4">
                            {{-- Name --}}
                            <div class="relative">
                                <input wire:model="name"
                                    class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-200 @error('name') bg-red-400/10 border-red-500 @else bg-zinc-400/10 border-transparent @enderror border"
                                    type="text" autofocus placeholder="আপনার পূর্ণ নাম" />
                                @if ($name !== '' && !$errors->has('name'))
                                    <div class="absolute inset-y-0 right-5 flex items-center text-green-500">
                                        <flux:icon icon="check-circle" variant="mini" />
                                    </div>
                                @endif
                                @error('name')
                                    <flux:text class="text-red-600 text-xs pl-4 mt-1">{{ $message }}</flux:text>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="relative">
                                <input wire:model="email"
                                    class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-200 @error('email') bg-red-400/10 border-red-500 @else bg-zinc-400/10 border-transparent @enderror border"
                                    type="email" placeholder="ইমেইল (যেমন: name@example.com)" />
                                @if ($email !== '' && !$errors->has('email'))
                                    <div class="absolute inset-y-0 right-5 flex items-center text-green-500">
                                        <flux:icon icon="check-circle" variant="mini" />
                                    </div>
                                @endif
                                @error('email')
                                    <flux:text class="text-red-600 text-xs pl-4 mt-1">{{ $message }}</flux:text>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div x-data="{ show: false }">
                                <div class="relative">
                                    <input wire:model="password" :type="show ? 'text' : 'password'"
                                        class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-200 @error('password') bg-red-400/10 border-red-500 @else bg-zinc-400/10 border-transparent @enderror border"
                                        placeholder="পাসওয়ার্ড দিন" />
                                    <div class="absolute inset-y-0 right-3 flex items-center gap-4">
                                        @if ($password !== '' && !$errors->has('password'))
                                            <flux:icon icon="check-circle" class="text-green-500" variant="mini" />
                                        @endif
                                        <button type="button" @click="show = !show" class="p-1 text-zinc-400 ">
                                            <flux:icon x-show="!show" icon="eye" variant="micro" />
                                            <flux:icon x-show="show" x-cloak icon="eye-slash" variant="micro" />
                                        </button>
                                    </div>
                                </div>
                                @error('password')
                                    <flux:text class="text-red-600 text-xs pl-4 mt-1">{{ $message }}</flux:text>
                                @enderror
                            </div>

                            {{-- Confirm Password --}}
                            <div x-data="{ show: false }">
                                <div class="relative">
                                    <input wire:model="password_confirmation" :type="show ? 'text' : 'password'"
                                        class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-200 @error('password_confirmation') bg-red-400/10 border-red-500 @else bg-zinc-400/10 border-transparent @enderror border"
                                        placeholder="পাসওয়ার্ডটি পুনরায় লিখুন" />
                                    <div class="absolute inset-y-0 right-3 flex items-center gap-4">
                                        @if ($password_confirmation !== '' && $password_confirmation === $password && !$errors->has('password_confirmation'))
                                            <flux:icon icon="check-circle" class="text-green-500" variant="mini" />
                                        @elseif ($password_confirmation !== '')
                                            <flux:icon icon="x-circle" class="text-red-400" variant="mini" />
                                        @endif
                                        <button type="button" @click="show = !show" class="p-1 text-zinc-400 ">
                                            <flux:icon x-show="!show" icon="eye" variant="micro" />
                                            <flux:icon x-show="show" x-cloak icon="eye-slash" variant="micro" />
                                        </button>
                                    </div>
                                </div>
                                @error('password_confirmation')
                                    <flux:text class="text-red-600 text-xs pl-4 mt-1">{{ $message }}</flux:text>
                                @enderror
                            </div>

                            <flux:button type="submit" variant="primary"
                                class="w-full !rounded-full py-6 font-bold shadow-lg">
                                ভেরিফিকেশন কোড পাঠান
                            </flux:button>
                        </form>
                    @else
                        {{-- OTP Step (same modal) --}}
                        <form wire:submit="verifyAndRegister" class="space-y-6">
                            <div class="flex flex-col items-center justify-center text-center space-y-3">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-center gap-2 text-zinc-500">
                                        <flux:icon icon="envelope-open" variant="micro" class="" />
                                        <flux:text size="sm">আমরা কোডটি পাঠিয়েছি:</flux:text>
                                    </div>

                                    <flux:tooltip content="সরাসরি ইনবক্স ওপেন করুন" position="top">
                                        <div class="flex gap-2 items-center">
                                            <flux:link href="{{ $this->getEmailDashboardUrl() }}" target="_blank">
                                                {{ $email }}
                                            </flux:link>
                                            <flux:icon icon="arrow-top-right-on-square" variant="micro"
                                                class="opacity-50" />
                                        </div>
                                    </flux:tooltip>
                                </div>

                                <flux:text size="xs" class="text-zinc-400 italic">
                                    ইনবক্স না পেলে স্প্যাম ফোল্ডার চেক করুন।
                                </flux:text>
                            </div>

                            <div class="flex flex-col items-center">
                                <flux:otp wire:model="otp_input" length="4" class="mx-auto" />
                                @error('otp_input')
                                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-3">
                                <flux:button type="submit" variant="primary"
                                    class="w-full !rounded-full py-6 font-bold">
                                    যাচাই ও অ্যাকাউন্ট তৈরি
                                </flux:button>

                                <flux:link as="button" wire:click="resetRegisterForm" class="w-full text-center">
                                    ভুল ইমেইল? তথ্য পরিবর্তন করুন
                                </flux:link>
                            </div>
                        </form>
                    @endif

                </flux:tab.panel>
            </flux:tab.group>
            {{-- Social + Footer (only when not in OTP step) --}}
            @if (!$is_otp_sent)
                <div class="relative flex items-center">
                    <div class=" border-t border-zinc-400/25"></div>
                    <span class="flex-shrink mx-4 text-zinc-400 text-xs uppercase">অথবা</span>
                    <div class=" border-t border-zinc-400/25"></div>
                </div>

                <div class="flex flex-col gap-4">
                    <livewire:auth.google-login />
                    <livewire:auth.facebook-auth />
                </div>
            @endif

        </div>
    </flux:modal>
</div>
