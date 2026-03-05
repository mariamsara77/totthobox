<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    
    // রিয়েল-টাইম ভ্যালিডেশন প্রোপার্টিজ
    public string $email = '';
    public string $password = '';
    public bool $emailLogin = false;
    public bool $remember = true;
    public ?string $status = null;

    /**
     * রিয়েল-টাইম ভ্যালিডেশন রুলস
     */
    protected function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8',
        ];
    }

    /**
     * কাস্টম বাংলা মেসেজ
     */
    protected function messages()
    {
        return [
            'email.required' => 'ইমেইল অ্যাড্রেসটি প্রয়োজন।',
            'email.email' => 'সঠিক ইমেইল ফরম্যাট ব্যবহার করুন।',
            'email.exists' => 'এই ইমেইলটি আমাদের রেকর্ডে নেই।',
            'password.required' => 'পাসওয়ার্ডটি অবশ্যই দিতে হবে।',
            'password.min' => 'পাসওয়ার্ডটি কমপক্ষে ৮ অক্ষরের হতে হবে।',
        ];
    }

    /**
     * লাইভ টাইপিং ভ্যালিডেশন
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    #[On('fill-login-email')]
    public function fillEmail(string $email): void
    {
        $this->email = $email;
        $this->validateOnly('email');
    }

    /**
     * প্রফেশনাল লগইন মেথড
     */
    public function login(): void
{
    // ১. ইনপুট স্যানিটাইজ করা (Email lower case kora)
    $this->email = Str::lower(trim($this->email));
    
    // ২. ডিফল্ট ভ্যালিডেশন (Email, Password, Remember me)
    $this->validate();

    // ৩. রেট লিমিট চেক (অতিরিক্ত ভুল লগইন আটকানো)
    $this->ensureIsNotRateLimited();

    // ৪. ইউজার খুঁজে বের করা
    $user = \App\Models\User::where('email', $this->email)->first();

    // ৫. পাসওয়ার্ড সিকিউরিটি চেক
    if ($user && !Hash::check($this->password, $user->password)) {
        RateLimiter::hit($this->throttleKey());
        $this->addError('password', 'আপনার দেওয়া পাসওয়ার্ডটি সঠিক নয়।');
        return;
    }

    // ৬. লগইন ট্রাই (Remember-me সহ)
    if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        // --- ৭. মাল্টিপল ইউজার কুকি ম্যানেজমেন্ট (NEW LOGIC) ---
        $cookieName = 'saved_accounts';
        $userIds = [];

        // বিদ্যমান কুকি রিড করা (যাতে আগের ইউজারদের ডেটা না হারায়)
        if ($existingCookie = request()->cookie($cookieName)) {
            try {
                // Decrypt kore array-te niye asa
                $userIds = json_decode(decrypt($existingCookie), true) ?: [];
            } catch (\Exception $e) { 
                $userIds = []; 
            }
        }

        // বর্তমান ইউজার যদি তালিকায় না থাকে, তবে নতুন করে যুক্ত করা
        if (!in_array($user->id, $userIds)) {
            $userIds[] = $user->id;
        }

        // কুকিটি 'Forever' (৫ বছর) হিসেবে সেভ করা যাতে লগআউট করলেও থেকে যায়
        Cookie::queue(
            cookie()->forever(
                $cookieName, 
                encrypt(json_encode($userIds))
            )
        );

        // পুরনো সিঙ্গেল কুকি (যদি থাকে) ক্লিনআপ করা
        Cookie::queue(Cookie::forget('last_logged_user'));
        // ----------------------------------------------------

        $this->status = 'লগইন সফল হয়েছে! রিডাইরেক্ট করা হচ্ছে...';
        
        $this->redirectIntended(
            default: route('home', absolute: false), 
            navigate: true
        );
    } else {
        RateLimiter::hit($this->throttleKey());
        $this->addError('email', 'লগইন করতে সমস্যা হচ্ছে। আবার চেষ্টা করুন।');
    }
}

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        $this->addError('email', "অতিরিক্ত বার চেষ্টার ফলে আপনার অ্যাকাউন্টটি সাময়িকভাবে লক করা হয়েছে। দয়া করে $seconds সেকেন্ড পর আবার চেষ্টা করুন।");
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->email).'|'.request()->ip();
    }

    public function toggleEmailLogin(): void { $this->emailLogin = true; }
    public function toggleEmailLoginClose(): void { $this->emailLogin = false; }
};

?>

<div class="flex flex-col gap-6">
    

    <x-auth-header 
        title="আপনার অ্যাকাউন্টে লগ ইন করুন" 
        description="লগ ইন করতে নিচের ধাপগুলো অনুসরণ করুন" 
    />

    @if ($status)
        <div class="p-3 text-center text-sm font-medium text-green-600 bg-green-50 rounded-xl border border-green-200">
            {{ $status }}
        </div>
    @endif

    @if ($emailLogin)
        <form wire:submit.prevent="login" class="flex flex-col gap-5">
            {{-- Email Input --}}
            <div class="space-y-1">
                <input 
                    wire:model.live.debounce.400ms="email" 
                    type="email" 
                    autocomplete="email" 
                    placeholder="ইমেইল অ্যাড্রেস (email@example.com)"
                    class="rounded-full py-3 text-black dark:text-white px-6 w-full transition-all duration-300 @error('email') bg-red-400/10 @else bg-zinc-400/10 @enderror" 
                />
                @error('email')
                   <flux:text class="text-red-600 text-xs pl-4">{{ $message }}</flux:text>
                @enderror
            </div>

            {{-- Password Input --}}
            <div class="space-y-1" x-data="{ show: false }">
                <div class="relative">
                    <input 
                        wire:model.live.debounce.400ms="password" 
                        :type="show ? 'text' : 'password'"
                        placeholder="পাসওয়ার্ড দিন"
                        class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-300 @error('password') bg-red-400/10 @else bg-zinc-400/10 @enderror" 
                    />
                    <div class="absolute inset-y-0 right-3 flex items-center">
                        <button type="button" @click="show = !show" class="p-2 text-zinc-500 hover:text-zinc-700">
                            <flux:icon x-show="!show" icon="eye" variant="micro" />
                            <flux:icon x-show="show" x-cloak icon="eye-slash" variant="micro" />
                        </button>
                    </div>
                </div>
                @error('password')
                    <flux:text class="text-red-600 text-xs pl-4">{{ $message }}</flux:text>
                @enderror
            </div>

            <div class="space-y-4">
                <flux:button type="submit" class="w-full !rounded-full py-6 font-bold" variant="primary">
                    লগ ইন করুন
                </flux:button>

                <div class="flex justify-between items-center px-2">
                    @if (Route::has('password.request'))
                        <flux:link class="text-xs" :href="route('password.request')" wire:navigate.hover>
                            পাসওয়ার্ড ভুলে গেছেন?
                        </flux:link>
                    @endif
                    <flux:button wire:click="toggleEmailLoginClose" icon="arrow-left" size="xs" variant="ghost" class="!rounded-full">
                       পিছনে যান
                    </flux:button>
                </div>
            </div>
        </form>
    @else
        <div class="space-y-4">
            <livewire:auth.last-user-display />

            <flux:button wire:click="toggleEmailLogin" class="w-full !rounded-full py-6" icon="envelope" variant="primary">
                ইমেইল দিয়ে লগ ইন
            </flux:button>

         
                @livewire('auth.google-login')
                @livewire('auth.facebook-auth')
           
        </div>
    @endif
    <div>

    <div class="relative flex items-center">
            <div class="flex-grow border-t border-zinc-400/25"></div>
            <span class="flex-shrink mx-4 text-zinc-400 text-xs uppercase">অথবা</span>
            <div class="flex-grow border-t border-zinc-400/25"></div>
        </div>
        @if (Route::has('register'))
            <div class="text-center text-sm mt-4">
                <span class="text-zinc-500">অ্যাকাউন্ট নেই?</span>
                <flux:link :href="route('register')" class="font-bold" wire:navigate.hover>সাইন আপ করুন</flux:link>
            </div>
        @endif
    </div>
</div>