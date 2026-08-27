<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Mail;

new #[Layout('components.layouts.app.header')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $message = '';

    public bool $sent = false;

    public function getAdminsProperty()
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->get();
    }

    public function submit()
    {
        $validated = $this->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|max:150',
            'subject' => 'required|min:3|max:150',
            'message' => 'required|min:10|max:2000',
        ]);

        // এখানে আপনি Mail পাঠাতে পারেন (Resend)
        // Mail::raw($validated['message'], function ($mail) use ($validated) {
        //     $mail->to('admin@totthobox.com')
        //          ->subject('Contact Form: ' . $validated['subject'])
        //          ->replyTo($validated['email'], $validated['name']);
        // });

        $this->reset(['name', 'email', 'subject', 'message']);
        $this->sent = true;
    }
}; ?>

<x-seo title="যোগাযোগ করুন (Contact Us)"
    description="Totthobox-এর সাথে যোগাযোগ করুন। আপনার যেকোনো জিজ্ঞাসা, মতামত, বিজ্ঞাপন বা সাপোর্টের জন্য আমাদের মেসেজ দিন।"
    keywords="যোগাযোগ, কন্টাক্ট পেজ, Totthobox contact, সাপোর্ট সেন্টার, মেসেজ দিন" />

<div class="max-w-2xl mx-auto space-y-10 py-6">

    {{-- Header --}}
    <div class="text-center space-y-3">
        <flux:heading size="xl" level="1">যোগাযোগ করুন</flux:heading>
        <flux:subheading class="max-w-xl mx-auto text-balance">
            আপনার যেকোনো প্রশ্ন, মতামত বা সাহায্যের জন্য আমরা সবসময় প্রস্তুত। নিচের যেকোনো মাধ্যমে আমাদের সাথে যোগাযোগ
            করুন।
        </flux:subheading>
    </div>

    {{-- Quick Contact --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Phone --}}
        <flux:card class="p-5 hover:shadow-lg transition-all duration-200">
            <div class="flex items-start gap-4">
                <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                    <flux:icon name="phone" class="size-6 " />
                </div>
                <div class="flex-1 space-y-3">
                    <div>
                        <flux:heading size="sm">ফোন সাপোর্ট</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">সরাসরি কথা বলুন</flux:text>
                    </div>
                    <flux:button href="tel:+8801340792677" variant="primary" icon="phone" class="w-full">
                        +880 1340-792677
                    </flux:button>
                </div>
            </div>
        </flux:card>

        {{-- WhatsApp --}}
        <flux:card class="p-5 hover:shadow-lg transition-all duration-200">
            <div class="flex items-start gap-4">
                <div class="p-4 rounded-2xl bg-zinc-400/10">
                    <flux:icon name="chat-bubble-left-right" class="size-6 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div class="flex-1 space-y-3">
                    <div>
                        <flux:heading size="sm">WhatsApp চ্যাট</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">দ্রুত উত্তর পান</flux:text>
                    </div>
                    <flux:button href="https://wa.me/8801340792677" target="_blank" variant="primary"
                        class="w-full bg-emerald-600 hover:bg-emerald-700">
                        চ্যাট শুরু করুন
                    </flux:button>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Contact Form --}}
    <flux:card class="p-6 sm:p-8">
        <div class="space-y-6">
            <div class="space-y-1">
                <flux:heading size="lg">মেসেজ পাঠান</flux:heading>
                <flux:text class="text-zinc-500">ফর্ম পূরণ করে সরাসরি আমাদের ইনবক্সে মেসেজ পাঠান</flux:text>
            </div>

            @if ($sent)
                <flux:callout icon="check-circle" color="green">
                    আপনার মেসেজ সফলভাবে পাঠানো হয়েছে। আমরা শীঘ্রই উত্তর দিব।
                </flux:callout>
            @else
                <form wire:submit="submit" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>আপনার নাম</flux:label>
                            <flux:input wire:model="name" placeholder="সম্পূর্ণ নাম লিখুন" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>ইমেইল</flux:label>
                            <flux:input wire:model="email" type="email" placeholder="example@email.com" />
                            <flux:error name="email" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>বিষয়</flux:label>
                        <flux:input wire:model="subject" placeholder="মেসেজের বিষয় লিখুন" />
                        <flux:error name="subject" />
                    </flux:field>

                    <flux:field>
                        <flux:label>আপনার মেসেজ</flux:label>
                        <flux:textarea wire:model="message" rows="5" placeholder="বিস্তারিত লিখুন..." />
                        <flux:error name="message" />
                    </flux:field>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" icon="paper-airplane" class="w-full sm:w-auto">
                            মেসেজ পাঠান
                        </flux:button>
                    </div>
                </form>
            @endif
        </div>
    </flux:card>

    {{-- Social Media --}}
    <section class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">আমাদের সাথে যুক্ত থাকুন</flux:heading>
            <flux:text class="text-zinc-500">সর্বশেষ আপডেট পেতে ফলো করুন</flux:text>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Facebook --}}
            <a href="https://facebook.com/totthobox" target="_blank" rel="noopener" class="group block">
                <flux:card class="p-5 transition-all duration-200 hover:shadow-lg hover:ring-2 hover:ring-blue-500/20">
                    <div class="flex items-center gap-4">
                        <div
                            class="p-4 rounded-2xl text-blue-600 bg-blue-50 dark:bg-blue-950/40 group-hover:scale-105 transition-transform">
                            <flux:icon name="facebook" class="size-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <flux:heading size="sm">Facebook</flux:heading>
                            <flux:text size="sm" class="text-zinc-500 truncate">Totthobox পেজ ফলো করুন</flux:text>
                        </div>
                        <flux:icon name="arrow-up-right" variant="micro"
                            class="text-zinc-400 group-hover:text-blue-500 transition-colors" />
                    </div>
                </flux:card>
            </a>

            {{-- X --}}
            <a href="https://x.com/totthobox" target="_blank" rel="noopener" class="group block">
                <flux:card class="p-5 transition-all duration-200 hover:shadow-lg hover:ring-2 hover:ring-zinc-500/20">
                    <div class="flex items-center gap-4">
                        <div
                            class="p-4 rounded-2xl bg-zinc-100 text-black dark:text-white dark:bg-zinc-800 group-hover:scale-105 transition-transform">
                            <flux:icon name="x" class="size-6" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <flux:heading size="sm">X (Twitter)</flux:heading>
                            <flux:text size="sm" class="text-zinc-500 truncate">আপডেট ও খবর পান</flux:text>
                        </div>
                        <flux:icon name="arrow-up-right" variant="micro"
                            class="text-zinc-400 group-hover:text-zinc-900 dark:group-hover:text-white transition-colors" />
                    </div>
                </flux:card>
            </a>

            {{-- Telegram --}}
            <a href="https://t.me/totthobox" target="_blank" rel="noopener" class="group block">
                <flux:card class="p-5 transition-all duration-200 hover:shadow-lg hover:ring-2 hover:ring-sky-500/20">
                    <div class="flex items-center gap-4">
                        <div
                            class="p-4 rounded-2xl bg-sky-50 dark:bg-sky-950/40 group-hover:scale-105 transition-transform">
                            <flux:icon name="paper-airplane" class="size-6 text-sky-600 dark:text-sky-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <flux:heading size="sm">Telegram</flux:heading>
                            <flux:text size="sm" class="text-zinc-500 truncate">চ্যানেল জয়েন করুন</flux:text>
                        </div>
                        <flux:icon name="arrow-up-right" variant="micro"
                            class="text-zinc-400 group-hover:text-sky-500 transition-colors" />
                    </div>
                </flux:card>
            </a>

            {{-- Email --}}
            <a href="mailto:admin@totthobox.com" class="group block">
                <flux:card class="p-5 transition-all duration-200 hover:shadow-lg hover:ring-2 hover:ring-rose-500/20">
                    <div class="flex items-center gap-4">
                        <div
                            class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 group-hover:scale-105 transition-transform">
                            <flux:icon name="envelope" class="size-6 text-rose-600 dark:text-rose-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <flux:heading size="sm">Email</flux:heading>
                            <flux:text size="sm" class="text-zinc-500 truncate">admin@totthobox.com</flux:text>
                        </div>
                        <flux:icon name="arrow-up-right" variant="micro"
                            class="text-zinc-400 group-hover:text-rose-500 transition-colors" />
                    </div>
                </flux:card>
            </a>
        </div>
    </section>

    {{-- Office Address --}}
    <flux:card class="p-5">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="p-4 rounded-2xl text-orange-600 bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="map-pin" class="size-6" />
                </div>
                <div>
                    <flux:heading size="sm">আমাদের ঠিকানা</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">মিরপুর ডিওএইচএস, এভিনিউ-৩, ঢাকা ১২১৬</flux:text>
                </div>
            </div>
            <flux:button href="https://maps.google.com/?q=মিরপুর+ডিওএইচএস+এভিনিউ-৩+ঢাকা" target="_blank"
                variant="subtle" icon-trailing="arrow-up-right">
                ম্যাপে দেখুন
            </flux:button>
        </div>
    </flux:card>

    <flux:separator />

    {{-- Direct Message to Admins --}}
    <section class="space-y-5">
        <div class="space-y-1">
            <flux:heading size="lg">সরাসরি মেসেজ পাঠান</flux:heading>
            <flux:text class="text-zinc-500">আমাদের সাপোর্ট টিমের সাথে সরাসরি কথা বলুন</flux:text>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($this->admins as $admin)
                <a href="/messages/{{ $admin->slug }}" wire:navigate class="block group">
                    <flux:card class="p-4 transition-all duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <div class="flex items-center gap-4">
                            <flux:avatar src="{{ $admin->getFirstMediaUrl('avatars', 'thumb') }}"
                                name="{{ $admin->name }}" size="sm" badge
                                badge:color="{{ $admin->isOnline() ? 'green' : 'zinc' }}" />
                            <div class="flex-1 min-w-0">
                                <flux:heading size="sm"
                                    class="truncate hover:text-zinc-400/25 transition-colors">
                                    {{ $admin->name }}
                                </flux:heading>
                                <flux:text size="xs" class="text-zinc-500">
                                    {{ $admin->role ?? 'Support Team' }}
                                </flux:text>
                            </div>
                            <flux:icon name="chevron-right" variant="micro"
                                class="text-zinc-400 group-hover:translate-x-0.5 transition-transform" />
                        </div>
                    </flux:card>
                </a>
            @empty
                <div class="col-span-full py-12 text-center">
                    <flux:text class="text-zinc-400">এই মুহূর্তে কোনো সাপোর্ট মেম্বার উপলব্ধ নেই।</flux:text>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Bottom Badge --}}
    <div class="flex justify-center pt-2">
        <div class="flex items-center gap-2 px-4 py-2 bg-zinc-400/10 rounded-full">
            <flux:icon name="clock" variant="micro" class="text-zinc-500" />
            <flux:text size="sm" class="font-medium text-zinc-600 dark:text-zinc-300">
                ২৪/৭ সাপোর্ট উপলব্ধ
            </flux:text>
        </div>
    </div>

</div>
