<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app.header')] class extends Component {
    public function getAdminsProperty()
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->get();
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-4 p-3">
    {{-- Hero Header --}}
    <header class="text-center space-y-4">
        <div>
            <flux:heading size="xl" level="1">আপনার প্রয়োজনে আমরা সব সময় পাশে আছি
            </flux:heading>
            <flux:subheading>
                কোনো ফর্ম পূরণের ঝামেলা ছাড়াই সরাসরি আমাদের সাপোর্ট টিমের সাথে কথা বলুন।
            </flux:subheading>
        </div>
    </header>

    {{-- Main Contact Options: Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Phone Card --}}
        <flux:card
            class="relative overflow-hidden group hover:ring-2 hover:ring-indigo-500 transition-all duration-300">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-indigo-50 dark:bg-indigo-950 rounded-xl">
                    <flux:icon.phone class="text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="flex-1">
                    <flux:heading weight="bold">Phone Support</flux:heading>
                    <flux:text size="sm">সরাসরি কথা বলতে কল করুন</flux:text>
                    <div class="mt-4">
                        <flux:button href="tel:+8801234567890" variant="filled" color="indigo" icon="phone"
                            class="w-full md:w-auto">
                            +880 1234 567 890
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:card>

        {{-- WhatsApp Card --}}
        <flux:card
            class="relative overflow-hidden group hover:ring-2 hover:ring-emerald-500 transition-all duration-300">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950 rounded-xl">
                    <flux:icon.whatsapp class="text-emerald-600 dark:text-emerald-400" />
                </div>
                <div class="flex-1">
                    <flux:heading weight="bold">WhatsApp Chat</flux:heading>
                    <flux:text size="sm">দ্রুত সমাধানের জন্য চ্যাট করুন</flux:text>
                    <div class="mt-4">
                        <flux:button href="https://wa.me/8801234567890" variant="filled" color="emerald"
                            class="w-full md:w-auto">
                            চ্যাট শুরু কুরন
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Office Address --}}
    <flux:card class="hover:shadow-md transition-shadow">
        <div class="flex flex-col md:flex-row md:items-center gap-6">
            <div class="flex items-center gap-4 flex-1">
                <div class="p-3 bg-zinc-400/25 rounded-xl">
                    <flux:icon.map-pin variant="solid" class="size-6" />
                </div>
                <div>
                    <flux:heading weight="bold">আমাদের অফিস</flux:heading>
                    <flux:text size="sm">মিরপুর ডিওএইচএস, এভিনিউ-৩, ঢাকা ১২১৬</flux:text>
                </div>
            </div>
            <flux:button variant="subtle" icon-trailing="arrow-up-right">ম্যাপে দেখুন</flux:button>
        </div>
    </flux:card>

    <flux:separator variant="faint" />

    {{-- Admins Section --}}
    <section class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="space-y-1">
                <flux:heading size="lg" weight="bold">সরাসরি মেসেজ</flux:heading>
                <flux:subheading>আমাদের অ্যাডমিনদের সাথে সরাসরি কথা বলুন</flux:subheading>
            </div>

            {{-- Flux UI standard avatar group --}}
            <flux:avatar.group class=" bg-zinc-400/10 p-4 rounded-xl">
                @foreach($this->admins->take(5) as $admin)
                    <flux:avatar src=" {{ $admin->avatar_url }}" name="{{ $admin->name }}" />
                @endforeach

                {{-- যদি ৫ জনের বেশি অ্যাডমিন থাকে তবে 'plus' কাউন্ট দেখানোর জন্য (Optional) --}}
                @if($this->admins->count() > 5)
                    <flux:avatar circle>{{ $this->admins->count() - 5 }}+</flux:avatar>
                @endif
            </flux:avatar.group>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-3">
            @forelse($this->admins as $admin)
                <a href="/messages/{{ $admin->slug }}" class="group block">
                    <flux:card
                        class="p-3 transition-all group-hover:bg-zinc-50 dark:group-hover:bg-zinc-400/10 border-zinc-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <flux:avatar src="{{ $admin->getFirstMediaUrl('avatars', 'thumb') }}"
                                    name="{{ $admin->name }}" size="sm" badge
                                    badge:color="{{ $admin->isOnline() ? 'green' : 'zinc' }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <flux:heading size="sm" class="truncate group-hover:text-indigo-600 transition-colors">
                                    {{ $admin->name }}
                                </flux:heading>
                                <flux:text size="xs" class="uppercase tracking-tighter font-semibold">
                                    {{ $admin->role ?? 'Support' }}
                                </flux:text>
                            </div>
                            <flux:icon.chevron-right size="sm" variant="mini"
                                class="text-zinc-400 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </flux:card>
                </a>
            @empty
                <flux:text class="col-span-full text-center py-8 italic opacity-50">সাপোর্ট মেম্বার এই মুহূর্তে উপলব্ধ নেই।
                </flux:text>
            @endforelse
        </div>
    </section>

    {{-- Footer Info --}}
    <footer
        class="pt-6 border-t border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-2">
            <flux:button variant="ghost" size="sm" icon="facebook">Facebook</flux:button>
            <flux:button variant="ghost" size="sm" icon="linkedin">LinkedIn</flux:button>
        </div>

        <div class="flex items-center gap-2 py-1 px-4 bg-zinc-100 dark:bg-zinc-800 rounded-full">
            <flux:icon.clock variant="mini" class="text-zinc-500" />
            <flux:text size="xs" weight="bold" class="uppercase tracking-wider">Sat - Thu (10 AM - 8 PM)</flux:text>
        </div>
    </footer>
</div>