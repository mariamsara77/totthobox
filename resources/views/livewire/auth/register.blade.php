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

    public string $otp_input = '';
    public bool $is_otp_sent = false;

    // রিয়েল-টাইম ভ্যালিডেশন
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

   public function getRules()
{
    return [
        'name' => ['required', 'string', 'min:3', 'max:50'],
        'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
        'password' => [
            'required', 
            'string', 
            'min:8', // শুধুমাত্র ন্যূনতম ৮ অক্ষর হলেই হবে
            'max:255', 
            Rules\Password::defaults() // এটি আপনার AppServiceProvider-এর ডিফল্ট সেটিংস ফলো করবে
        ],
        'password_confirmation' => ['required', 'same:password'],
    ];
}

public function messages()
{
    return [
        // নাম
        'name.required' => 'আপনার নাম দিতে হবে।',
        'name.min' => 'নাম অন্তত ৩ অক্ষরের হতে হবে।',
        'name.max' => 'নাম ৫০ অক্ষরের বেশি হতে পারবে না।',

        // ইমেইল
        'email.required' => 'আপনার ইমেইল ঠিকানা দিতে হবে।',
        'email.email' => 'সঠিক ইমেইল ফরম্যাট ব্যবহার করুন।',
        'email.unique' => 'এই ইমেইলটি দিয়ে ইতিমধ্যে অ্যাকাউন্ট খোলা হয়েছে।',

        // পাসওয়ার্ড
        'password.required' => 'একটি পাসওয়ার্ড দিন।',
        'password.min' => 'পাসওয়ার্ডটি অন্তত ৮ অক্ষরের হতে হবে।',

        // কনফার্ম পাসওয়ার্ড
        'password_confirmation.required' => 'পাসওয়ার্ডটি আবার লিখুন।',
        'password_confirmation.same' => 'পাসওয়ার্ড দুটি মিলছে না, আবার চেক করুন।',
    ];
}
    /**
     * ধাপ ১: OTP পাঠানো
     */
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
            session()->flash('success', 'আপনার ইমেইলে ৪ ডিজিটের একটি কোড পাঠানো হয়েছে।');

        } catch (\Exception $e) {
            $this->addError('email', 'ইমেইল পাঠাতে সমস্যা হচ্ছে। পরে আবার চেষ্টা করুন।');
        }
    }

    /**
     * ধাপ ২: রেজিস্ট্রেশন সম্পন্ন করা
     */
    public function verifyAndRegister(): void
    {
        $pendingUser = Session::get('pending_user');

        if (!$pendingUser || now()->isAfter($pendingUser['expires_at'])) {
            $this->addError('otp_input', 'ওটিপির মেয়াদ শেষ। পুনরায় চেষ্টা করুন।');
            $this->is_otp_sent = false;
            return;
        }

        if ($this->otp_input !== $pendingUser['otp']) {
            $this->addError('otp_input', 'ভেরিফিকেশন কোডটি সঠিক নয়।');
            return;
        }

        try {
        DB::transaction(function () use ($pendingUser) {
            $user = User::create([
                'name' => $pendingUser['name'],
                'email' => $pendingUser['email'],
                'password' => Hash::make($pendingUser['password']),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('user');
            Auth::login($user);
            
            // --- মাল্টিপল ইউজার কুকি ম্যানেজমেন্ট (Register Logic) ---
            $cookieName = 'saved_accounts';
            $userIds = [];

            // বিদ্যমান কুকি রিড করা
            if ($existingCookie = request()->cookie($cookieName)) {
                try {
                    $userIds = json_decode(decrypt($existingCookie), true) ?: [];
                } catch (\Exception $e) { 
                    $userIds = []; 
                }
            }

            // বর্তমান নতুন ইউজারকে তালিকায় যুক্ত করা
            if (!in_array($user->id, $userIds)) {
                $userIds[] = $user->id;
            }

            // ৫ বছরের জন্য কুকি সেভ করা
            Cookie::queue(
                cookie()->forever(
                    $cookieName, 
                    encrypt(json_encode($userIds))
                )
            );

            // পুরনো সিঙ্গেল কুকি থাকলে ডিলিট করা
            Cookie::queue(Cookie::forget('last_logged_user'));
            // ----------------------------------------------------
        });

        Session::forget('pending_user');
        $this->redirectIntended(route('home', absolute: false), navigate: true);

    } catch (\Exception $e) {
        $this->addError('otp_input', 'অ্যাকাউন্ট তৈরিতে কারিগরি সমস্যা হয়েছে।');
    }
    }

    public function resetForm()
    {
        $this->is_otp_sent = false;
        $this->otp_input = '';
    }


    // ইমেইল ডোমেইন অনুযায়ী ডায়নামিক ইউআরএল তৈরি
public function getEmailDashboardUrl(): string
{
    $domain = Str::after($this->email, '@');
    
    return match ($domain) {
        'gmail.com' => 'https://mail.google.com/',
        'yahoo.com' => 'https://mail.yahoo.com/',
        'outlook.com', 'hotmail.com', 'live.com' => 'https://outlook.live.com/',
        'icloud.com' => 'https://www.icloud.com/mail',
        default => 'mailto:' . $this->email, // অন্য কোনো ডোমেইন হলে সরাসরি মেইল অ্যাপ ওপেন হবে
    };
}

}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="$is_otp_sent ? __('ইমেইল ভেরিফাই করুন') : __('নতুন অ্যাকাউন্ট তৈরি করুন')"
        :description="$is_otp_sent ? __('আপনার ইমেইলে পাঠানো ৪ ডিজিটের কোডটি দিন') : __('আপনার তথ্য দিয়ে রেজিস্ট্রেশন সম্পন্ন করুন')" />

    @if (!$is_otp_sent)
    {{-- রেজিস্ট্রেশন ফর্ম --}}
    <form wire:submit="sendOtp" class="flex flex-col gap-5">
        <div class="relative">
            <input wire:model.live.debounce.500ms="name"
                class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-300 focus:ring-2 focus:ring-primary/50 outline-none @error('name') bg-red-400/10 border-red-500 @else bg-zinc-400/10 border-transparent @enderror border"
                type="text" autofocus placeholder="আপনার পূর্ণ নাম" />
            
            @if($name !== '' && !$errors->has('name'))
                <div class="absolute inset-y-0 right-5 flex items-center text-green-500">
                    <flux:icon icon="check-circle" variant="mini" />
                </div>
            @endif
            @error('name') <flux:text class="text-red-600 text-xs pl-4 mt-1">{{ $message }}</flux:text> @enderror
        </div>

        <div class="relative">
            <input wire:model.live.debounce.500ms="email"
                class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-300 focus:ring-2 focus:ring-primary/50 outline-none @error('email') bg-red-400/10 border-red-500 @else bg-zinc-400/10 border-transparent @enderror border"
                type="email" placeholder="ইমেইল (যেমন: name@example.com)" />
            
            @if($email !== '' && !$errors->has('email'))
                <div class="absolute inset-y-0 right-5 flex items-center text-green-500">
                    <flux:icon icon="check-circle" variant="mini" />
                </div>
            @endif
            @error('email') <flux:text class="text-red-600 text-xs pl-4 mt-1">{{ $message }}</flux:text> @enderror
        </div>

        <div x-data="{ show: false }">
            <div class="relative">
                <input wire:model.live.debounce.500ms="password" :type="show ? 'text' : 'password'"
                    class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-300 focus:ring-2 focus:ring-primary/50 outline-none @error('password') bg-red-400/10 border-red-500 @else bg-zinc-400/10 border-transparent @enderror border"
                    placeholder="পাসওয়ার্ড দিন" />
                
                <div class="absolute inset-y-0 right-3 flex items-center gap-2">
                    @if($password !== '' && !$errors->has('password'))
                        <flux:icon icon="check-circle" class="text-green-500" variant="mini" />
                    @endif
                    <button type="button" @click="show = !show" class="p-1 text-zinc-400 hover:text-zinc-600">
                        <flux:icon x-show="!show" icon="eye" variant="micro" />
                        <flux:icon x-show="show" x-cloak icon="eye-slash" variant="micro" />
                    </button>
                </div>
            </div>
            @error('password') <flux:text class="text-red-600 text-xs pl-4 mt-1">{{ $message }}</flux:text> @enderror
        </div>

        <div x-data="{ show: false }">
            <div class="relative">
                <input wire:model.live.debounce.500ms="password_confirmation" :type="show ? 'text' : 'password'"
                    class="rounded-full py-3 px-6 w-full text-black dark:text-white transition-all duration-300 focus:ring-2 focus:ring-primary/50 outline-none @error('password_confirmation') bg-red-400/10 border-red-500 @else bg-zinc-400/10 border-transparent @enderror border"
                    placeholder="পাসওয়ার্ডটি পুনরায় লিখুন" />
                
                <div class="absolute inset-y-0 right-3 flex items-center gap-2">
                    @if($password_confirmation !== '' && $password_confirmation === $password && !$errors->has('password_confirmation'))
                        <flux:icon icon="check-circle" class="text-green-500" variant="mini" />
                    @else
                        @if($password_confirmation !== '')
                             <flux:icon icon="x-circle" class="text-red-400" variant="mini" />
                        @endif
                    @endif
                    <button type="button" @click="show = !show" class="p-1 text-zinc-400 hover:text-zinc-600">
                        <flux:icon x-show="!show" icon="eye" variant="micro" />
                        <flux:icon x-show="show" x-cloak icon="eye-slash" variant="micro" />
                    </button>
                </div>
            </div>
            @error('password_confirmation') <flux:text class="text-red-600 text-xs pl-4 mt-1">{{ $message }}</flux:text> @enderror
        </div>

        <flux:button type="submit" variant="primary" class="w-full !rounded-full py-6 font-bold shadow-lg shadow-primary/20">
            {{ __('ভেরিফিকেশন কোড পাঠান') }}
        </flux:button>
    </form>
    @else
   {{-- ওটিপি ভেরিফিকেশন ফর্ম --}}
<form wire:submit="verifyAndRegister" class="space-y-6">
    <div class="flex flex-col items-center justify-center text-center space-y-3">
        
        <div class="space-y-1">
            <div class="flex items-center justify-center gap-2 text-zinc-500">
                <flux:icon icon="envelope-open" variant="micro" class="text-primary" />
                <flux:text size="sm">আমরা কোডটি পাঠিয়েছি:</flux:text>
            </div>
            
            <flux:tooltip content="সরাসরি ইনবক্স ওপেন করুন" position="top">
                <div class="flex gap-2 items-center">
                    <flux:link
                        href="{{ $this->getEmailDashboardUrl() }}"
                        target="_blank">
                        {{ $email }}
                    </flux:link>
                    <flux:icon icon="arrow-top-right-on-square" variant="micro" class="opacity-50 cursor-pointer hover:opacity-100 text-zinc-600" />
                </div>
            </flux:tooltip>
        </div>

        <flux:text size="xs" class="text-zinc-400 italic">
            ইনবক্স না পেলে স্প্যাম ফোল্ডার চেক করুন।
        </flux:text>
    </div>

    <div class="flex flex-col items-center">
        <flux:otp wire:model="otp_input" length="4" class="mx-auto" />
        @error('otp_input') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
    </div>

    <div class="space-y-3">
        <flux:button type="submit" variant="primary" class="w-full !rounded-full py-6 font-bold">
            {{ __('যাচাই ও অ্যাকাউন্ট তৈরি') }}
        </flux:button>

         <flux:link as="button" wire:click="resetForm" class="w-full">
                ভুল ইমেইল? তথ্য পরিবর্তন করুন
        </flux:link>
    </div>
</form>
    @endif

    @if (!$is_otp_sent)
        <div class="relative flex items-center py-2">
            <div class="flex-grow border-t border-zinc-400/20"></div>
            <span class="flex-shrink mx-4 text-zinc-400 text-xs uppercase tracking-widest">অথবা</span>
            <div class="flex-grow border-t border-zinc-400/20"></div>
        </div>

        <div class="flex flex-col gap-3">
            @livewire('auth.google-login')
            @livewire('auth.facebook-auth')
        </div>

        <div class="text-center text-sm text-zinc-600 pt-2">
            <span>{{ __('অ্যাকাউন্ট আছে?') }}</span>
            <flux:link :href="route('login')" class="font-bold text-primary" wire:navigate.hover>{{ __('লগ ইন করুন') }}</flux:link>
        </div>
    @endif
</div>