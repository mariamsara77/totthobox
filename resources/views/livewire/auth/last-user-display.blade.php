<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;
use Illuminate\Support\Str;

new class extends Component {
    public array $savedUsers = [];
    public ?int $userToRemove = null;

    public function mount()
    {
        $this->loadAccounts();
    }

    public function loadAccounts()
    {
        $cookie = request()->cookie('saved_accounts');

        if (!$cookie) {
            $oldCookie = request()->cookie('last_logged_user');
            if ($oldCookie) {
                try {
                    $oldId = decrypt($oldCookie);
                    $this->updateCookieWithId($oldId);
                    $cookie = request()->cookie('saved_accounts');
                } catch (\Exception $e) {
                }
            }
        }

        if (!$cookie)
            return;

        try {
            $userIds = json_decode(decrypt($cookie), true);
            if (is_array($userIds)) {
                $this->savedUsers = User::whereIn('id', $userIds)
                    ->get()
                    ->map(fn($user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->getFirstMediaUrl('avatars', 'thumb') ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
                    ])->toArray();
            }
        } catch (\Exception $e) {
            Cookie::queue(Cookie::forget('saved_accounts'));
            Cookie::queue(Cookie::forget('last_logged_user'));
        }
    }

    protected function updateCookieWithId($id)
    {
        $ids = [$id];
        Cookie::queue(cookie()->forever('saved_accounts', encrypt(json_encode($ids))));
        Cookie::queue(Cookie::forget('last_logged_user'));
    }

    public function loginAs($id): void
    {
        $user = User::find($id);
        if ($user) {
            Auth::login($user);
            session()->regenerate();
            $this->redirect(route('home'), navigate: true);
        }
    }

    public function removeAccount(): void
    {
        if (!$this->userToRemove)
            return;

        $cookie = request()->cookie('saved_accounts');
        if ($cookie) {
            $userIds = json_decode(decrypt($cookie), true);
            $newIds = array_values(array_diff($userIds, [$this->userToRemove]));

            if (empty($newIds)) {
                Cookie::queue(Cookie::forget('saved_accounts'));
                $this->savedUsers = [];
            } else {
                Cookie::queue(cookie()->forever('saved_accounts', encrypt(json_encode($newIds))));
            }
        }

        $this->userToRemove = null;
        $this->redirect(request()->header('Referer'), navigate: false);
    }
}; ?>

<div class="space-y-4">
    @if (count($savedUsers) > 0 && !auth()->check())
        <div>
            <flux:heading size="lg">অ্যাকাউন্ট বাছাই করুন</flux:heading>
            <flux:text>আপনার সেভ করা অ্যাকাউন্টগুলো নিচে দেওয়া হলো</flux:text>
        </div>
        @foreach ($savedUsers as $user)
            <div
                class="group relative flex items-center gap-4 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-4 transition-all hover:border-blue-500 hover:shadow-md">

                <button wire:click="$set('userToRemove', {{ $user['id'] }})"
                    class="absolute top-2 right-2 p-1 text-zinc-400 hover:text-red-500 transition" title="অ্যাকাউন্ট সরান">
                    <flux:icon name="x-mark" variant="mini" />
                </button>

                <div wire:click="loginAs({{ $user['id'] }})" class="cursor-pointer shrink-0">
                    <img src="{{ $user['avatar'] }}"
                        class="w-12 h-12 rounded-full ring-2 ring-white dark:ring-zinc-800 object-cover" alt="প্রোফাইল ছবি">
                </div>

                <div wire:click="loginAs({{ $user['id'] }})" class="flex-1 min-w-0 cursor-pointer">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                        {{ $user['name'] }}
                    </h3>
                    <p class="text-[11px] text-zinc-500 truncate">{{ $user['email'] }}</p>
                </div>

                <div wire:click="loginAs({{ $user['id'] }})"
                    class="cursor-pointer text-zinc-300 group-hover:text-blue-500 transition">
                    <flux:icon name="arrow-right" variant="mini" />
                </div>

                @if($userToRemove == $user['id'])
                    <div
                        class="absolute inset-0 bg-white/95 dark:bg-zinc-900/95 flex items-center justify-between px-4 rounded-2xl z-10 animate-in fade-in zoom-in-95">
                        <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300">মুছে ফেলবেন?</span>
                        <div class="flex gap-2">
                            <button wire:click="removeAccount"
                                class="bg-red-500 text-white text-[10px] px-3 py-1 rounded-full font-bold">হ্যাঁ</button>
                            <button wire:click="$set('userToRemove', null)"
                                class="bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-[10px] px-3 py-1 rounded-full">না</button>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="relative flex items-center">
            <div class="flex-grow border-t border-zinc-400/25"></div>
            <span class="flex-shrink mx-4 text-zinc-400 text-xs uppercase">অথবা অন্যভাবে লগইন করুন</span>
            <div class="flex-grow border-t border-zinc-400/25"></div>
            {{-- <div class="absolute inset-0 flex items-center">
                <span class="w-full border-t border-zinc-200 dark:border-zinc-800"></span>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-white dark:bg-zinc-950 px-3 text-zinc-500 font-medium">অথবা অন্যভাবে লগইন করুন</span>
            </div> --}}
        </div>
    @endif
</div>