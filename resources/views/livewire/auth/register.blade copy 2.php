<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\{Auth, Hash, DB, Cookie, Mail, Session};
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Mail\OtpMail;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // OTP সংশ্লিষ্ট প্রপার্টিজ
    public string $otp_input = '';
    public bool $is_otp_sent = false;


    /**
     * ভ্যালিডেশন রুলস (অবশ্যই public হতে হবে)
     */
    public function getRules()
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * কাস্টম মেসেজ (এটিও public রাখা নিরাপদ)
     */
    public function messages()
    {
        return [
            'email.unique' => 'এই ইমেইলটি দিয়ে ইতিমধ্যে অ্যাকাউন্ট খোলা হয়েছে।',
            'email.email' => 'সঠিক ইমেইল ফরম্যাট ব্যবহার করুন (যেমন: name@example.com)।',
            'email.required' => 'আপনার ইমেইল দিতে হবে।',
            'name.required' => 'আপনার নাম দিতে হবে।',
            'password.required' => 'পাসওয়ার্ড দিতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড কনফার্মেশন মিলেনি।',
        ];
    }

    /**
     * ধাপ ১: ইমেইল যাচাই এবং OTP পাঠানো
     */
    public function sendOtp(): void
    {
        $this->validate();

        try {
            // ৪ ডিজিটের র‍্যান্ডম কোড
            $otp = (string) rand(1000, 9999);

            // সেশনে ইউজারের তথ্য এবং OTP সাময়িকভাবে রাখা (১০ মিনিটের জন্য)
            Session::put('pending_user', [
                'name' => trim(ucwords($this->name)),
                'email' => Str::lower(trim($this->email)),
                'password' => $this->password,
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10),
            ]);

            // ইমেইল পাঠানো
            Mail::to($this->email)->send(new OtpMail($otp));

            $this->is_otp_sent = true;
            session()->flash('success', 'আপনার ইমেইলে ৪ ডিজিটের একটি কোড পাঠানো হয়েছে।');

        } catch (\Exception $e) {
            $this->addError('email', 'ইমেইল পাঠাতে সমস্যা হচ্ছে। দয়া করে আপনার মেইল সেটিংস চেক করুন।');
        }
    }

    /**
     * ধাপ ২: OTP যাচাই এবং ডাটাবেসে সেভ করা
     */
    public function verifyAndRegister(): void
    {
        $pendingUser = Session::get('pending_user');

        if (!$pendingUser || now()->isAfter($pendingUser['expires_at'])) {
            $this->addError('otp_input', 'আপনার ওটিপির মেয়াদ শেষ হয়ে গেছে। আবার চেষ্টা করুন।');
            $this->is_otp_sent = false;
            return;
        }

        if ($this->otp_input !== $pendingUser['otp']) {
            $this->addError('otp_input', 'ভেরিফিকেশন কোডটি সঠিক নয়।');
            return;
        }

        try {
            DB::transaction(function () use ($pendingUser) {
                // ডাটাবেসে এখন ইউজার তৈরি হবে
                $user = User::create([
                    'name' => $pendingUser['name'],
                    'email' => $pendingUser['email'],
                    'password' => Hash::make($pendingUser['password']),
                    'email_verified_at' => now(), // সরাসরি ভেরিফাইড হিসেবে গণ্য হবে
                ]);

                $user->assignRole('user');

                Auth::login($user);

                Cookie::queue(Cookie::make('last_logged_user', encrypt($user->id), 60 * 24 * 365, '/', null, true, true));
            });

            Session::forget('pending_user');
            $this->redirectIntended(route('home', absolute: false), navigate: true);

        } catch (\Exception $e) {
            $this->addError('otp_input', 'অ্যাকাউন্ট তৈরিতে কারিগরি সমস্যা হয়েছে।');
        }
    }

    public function resetForm()
    {
        $this->is_otp_sent = false;
        $this->otp_input = '';
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="$is_otp_sent ? __('ইমেইল ভেরিফাই করুন') : __('নতুন অ্যাকাউন্ট তৈরি করুন')"
        :description="$is_otp_sent ? __('আপনার ইমেইলে পাঠানো ৪ ডিজিটের কোডটি দিন') : __('আপনার তথ্য দিয়ে রেজিস্ট্রেশন সম্পন্ন করুন')" />

    @if (!$is_otp_sent)
    {{-- রেজিস্ট্রেশন ফর্ম --}}
    <form wire:submit="sendOtp" class="flex flex-col gap-5">
        <div>
            <input wire:model="name"
                class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-300 @error('name') bg-red-400/10 @else bg-zinc-400/10 @enderror"
                type="text" autofocus autocomplete="name" placeholder="আপনার পূর্ণ নাম" />
            @error('name')
            <flux:text class="text-red-600 text-xs pl-4">{{ $message }}</flux:text>
            @enderror
        </div>
        <div>
            <input wire:model="email"
                class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-300 @error('email') bg-red-400/10 @else bg-zinc-400/10 @enderror"
                type="email" autocomplete="email" placeholder="ইমেইল (যেমন: email@example.com)" />
            @error('email')
            <flux:text class="text-red-600 text-xs pl-4">{{ $message }}</flux:text>
            @enderror
        </div>
        <div>
            <div class="relative items-center" x-data="{ show: false }">
                <input wire:model="password" :type="show ? 'text' : 'password'"
                    class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-300 @error('password') bg-red-400/10 @else bg-zinc-400/10 @enderror"
                    autocomplete="new-password" placeholder="পাসওয়ার্ড দিন" />
                <div class="absolute inset-y-0 right-2 flex items-center">
                    <flux:button @click="show = !show" size="xs" variant="subtle" class="!rounded-full">
                        <flux:icon x-show="!show" icon="eye" variant="micro" />
                        <flux:icon x-show="show" x-cloak icon="eye-slash" variant="micro" />
                    </flux:button>
                </div>
            </div>
            @error('password')
            <flux:text class="text-red-600 text-xs pl-4">{{ $message }}</flux:text>
            @enderror
        </div>
        <div>
            <div class="relative items-center" x-data="{ show: false }">
                <input wire:model="password_confirmation" :type="show ? 'text' : 'password'"
                    class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-300 @error('password_confirmation') bg-red-400/10 @else bg-zinc-400/10 @enderror"
                    autocomplete="new-password" placeholder="পাসওয়ার্ডটি পুনরায় লিখুন" />
                <div class="absolute inset-y-0 right-2 flex items-center">
                    <flux:button @click="show = !show" size="xs" variant="subtle" class="!rounded-full">
                        <flux:icon x-show="!show" icon="eye" variant="micro" />
                        <flux:icon x-show="show" x-cloak icon="eye-slash" variant="micro" />
                    </flux:button>
                </div>
            </div>
            @error('password_confirmation')
            <flux:text class="text-red-600 text-xs pl-4">{{ $message }}</flux:text>
            @enderror
        </div>

        <flux:button type="submit" variant="primary" class="w-full !rounded-full py-6 font-bold">
            {{ __('ভেরিফিকেশন কোড পাঠান') }}
        </flux:button>
    </form>
    @else
    {{-- ওটিপি ভেরিফিকেশন ফর্ম --}}
    <form wire:submit="verifyAndRegister" class="flex flex-col justify-center gap-5">
        <div class="mx-auto text-center">
            <flux:heading>কোডটি যাচাই করুন</flux:heading>
            <flux:text class="mb-4">আপনার ইমেইলে পাঠানো ৪ ডিজিটের কোডটি লিখুন</flux:text>

            <flux:otp wire:model="otp_input" length="4" size="xl" />
            @error('otp_input') <p class="text-red-600 text-xs text-center mt-2">{{ $message }}</p> @enderror
        </div>

        <flux:button type="submit" variant="primary" class="w-full !rounded-full py-6 font-bold">
            {{ __('যাচাই করুন এবং অ্যাকাউন্ট তৈরি করুন') }}
        </flux:button>

        <button type="button" wire:click="resetForm"
            class="text-xs text-zinc-500 hover:text-primary transition-colors underline text-center">
            ইমেইল বা তথ্য পরিবর্তন করুন
        </button>
    </form>
    @endif

    {{-- Divider and Social Login --}}
    @if (!$is_otp_sent)
    <div class="relative flex items-center">
        <div class="flex-grow border-t border-zinc-400/25"></div>
        <span class="flex-shrink mx-4 text-zinc-400 text-xs uppercase">অথবা</span>
        <div class="flex-grow border-t border-zinc-400/25"></div>
    </div>

    <div class="flex flex-col gap-3">
        @livewire('auth.google-login')
        @livewire('auth.facebook-auth')
    </div>

    <div class="text-center text-sm text-zinc-600">
        <span>{{ __('অ্যাকাউন্ট আছে?') }}</span>
        <flux:link :href="route('login')" class="font-bold" wire:navigate.hover>{{ __('লগ ইন করুন') }}</flux:link>
    </div>
    @endif
</div>