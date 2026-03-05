<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * বর্তমানে লগইন করা ইউজারকে মুছে ফেলুন।
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ], [
            'password.required' => 'অ্যাকাউন্ট মুছতে পাসওয়ার্ড প্রয়োজন।',
            'password.current_password' => 'আপনার দেওয়া পাসওয়ার্ডটি সঠিক নয়।',
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="max-w-2xl mx-auto">
    @include('partials.settings-heading')
    <div class="mb-8 border-b border-red-100 dark:border-red-900/20 pb-4">
        <flux:heading size="xl" class="text-red-600 dark:text-red-500">
            অ্যাকাউন্ট মুছে ফেলুন
        </flux:heading>
        <flux:subheading class="mt-2">
            একবার আপনার অ্যাকাউন্ট মুছে ফেলা হলে, এর সমস্ত তথ্য এবং রিসোর্স স্থায়ীভাবে ডিলিট হয়ে যাবে।
        </flux:subheading>
    </div>

    <form wire:submit="deleteUser" class="space-y-6">
        <div class="bg-red-50 dark:bg-red-900/10 p-4 rounded-lg border-l-4 border-red-500">
            <flux:heading size="lg" class="text-red-700 dark:text-red-400">
                আপনি কি নিশ্চিতভাবে আপনার অ্যাকাউন্টটি মুছে ফেলতে চান?
            </flux:heading>
            <flux:subheading class="mt-1">
                নিরাপত্তার স্বার্থে এবং নিশ্চিত হতে আপনার পাসওয়ার্ডটি নিচে প্রদান করুন। এই কাজটি আর ফিরিয়ে আনা সম্ভব হবে
                না।
            </flux:subheading>
        </div>

        <div class="max-w-md">
            <flux:input wire:model="password" label="আপনার পাসওয়ার্ড দিন" type="password" viewable
                placeholder="••••••••" required />
        </div>

        <div class="flex items-center gap-4 pt-4">
            <flux:button variant="danger" type="submit" class="px-8">
                হ্যাঁ, অ্যাকাউন্ট মুছুন
            </flux:button>

            <flux:button variant="ghost" :href="route('settings.profile')" wire:navigate>
                বাতিল করুন
            </flux:button>
        </div>
    </form>
</section>