<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new class extends Component {
    public User $user;

    public function mount(string $slug): void
    {
        $this->user = User::where('slug', $slug)
            ->with(['classLevel', 'district', 'division', 'thana'])
            ->withCount(['roles'])
            ->firstOrFail();
    }
}; ?>

<section class="max-w-2xl mx-auto">
    {{-- উপরের হেডিং সেকশন --}}
    <div class="mb-6">
        @include('partials.settings-heading')
    </div>

    <x-settings.layout :heading="__('পাবলিক প্রোফাইল')" :subheading="'@' . $user->username">
        <div class="mt-6 space-y-6">
            
            {{-- ১. হিরো কার্ড (Main Profile Card) --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-tr from-primary-500 via-indigo-500 to-purple-500 p-0.5 shadow-xl">
                <div class="relative bg-white dark:bg-zinc-950 rounded-[1.4rem] p-6 sm:p-8 overflow-hidden">
                    {{-- গ্রাফিকাল গ্লো --}}
                    <div class="absolute -top-20 -right-20 size-48 bg-primary-500/10 rounded-full blur-3xl"></div>
                    
                    <div class="flex flex-col items-center text-center md:flex-row md:items-start md:text-left gap-6 relative z-10">
                        {{-- অবতার সেকশন --}}
                        <div class="relative shrink-0">
                            <flux:avatar 
                                :src="$user->getFirstMediaUrl('avatars', 'thumb')" 
                                :initials="$user->initials()" 
                                class="size-28 sm:size-32 md:size-40 shadow-xl border-4 border-white dark:border-zinc-800 ring-1 ring-black/5" 
                            />
                        </div>

                        {{-- প্রোফাইল ডিটেইলস --}}
                        <div class="flex-1 space-y-4">
                            <div class="space-y-1">
                                <div class="flex flex-col items-center md:items-start gap-2">
                                    <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">
                                        {{ $user->name }}
                                    </h1>
                                    <flux:badge size="sm" color="zinc" variant="subtle" class="font-mono lowercase px-3">
                                        @ {{ $user->username }}
                                    </flux:badge>
                                </div>
                                <p class="text-base sm:text-lg text-zinc-500 dark:text-zinc-400 font-medium flex items-center justify-center md:justify-start gap-2 mt-2">
                                    <flux:icon.briefcase class="size-4 text-primary-500" />
                                    {{ $user->profession ?? 'সদস্য' }}
                                </p>
                            </div>

                            {{-- রোলস --}}
                            <div class="flex flex-wrap justify-center md:justify-start gap-2">
                                @foreach ($user->roles as $role)
                                    <flux:badge variant="solid" color="primary" class="rounded-full px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                                        {{ $role->name }}
                                    </flux:badge>
                                @endforeach
                            </div>

                            {{-- অ্যাকশন বাটন --}}
                            @if (auth()->id() === $user->id)
                                <div class="pt-2">
                                    <flux:button href="{{ route('settings.profile') }}" variant="filled" size="sm" icon="pencil-square" wire:navigate class="rounded-xl">
                                        প্রোফাইল এডিট করুন
                                    </flux:button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ২. ইনফরমেশন গ্রিড (মোবাইলে এক কলাম, বড় স্ক্রিনে দুই কলাম হতে পারে) --}}
            <div class="grid grid-cols-1 gap-6">

                {{-- বায়ো (Bio) --}}
                @if ($user->bio)
                    <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm">
                        <div class="prose prose-sm sm:prose-base dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
                            {!! $user->bio !!}
                        </div>
                    </div>
                @endif
                
                {{-- পরিচয় ও শিক্ষা কার্ড --}}
                <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm">
                    <flux:heading size="lg" class="mb-6 flex items-center gap-2">
                        <flux:icon.user-circle class="size-5 text-primary-500" />
                        পরিচয় ও শিক্ষা
                    </flux:heading>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- শিক্ষাগত যোগ্যতা --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-xl text-zinc-500">
                                <flux:icon.academic-cap class="size-5" />
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block mb-0.5">শিক্ষাগত যোগ্যতা</span>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $user->education ?? 'তথ্য নেই' }}</p>
                            </div>
                        </div>

                        {{-- শ্রেণী --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-xl text-zinc-500">
                                <flux:icon.list-bullet class="size-5" />
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block mb-0.5">শ্রেণী/স্তর</span>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $user->classLevel?->name ?? '—' }}</p>
                            </div>
                        </div>

                        {{-- লোকেশন --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-xl text-zinc-500">
                                <flux:icon.map-pin class="size-5" />
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block mb-0.5">বর্তমান এলাকা</span>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $user->location ?? 'গোপন' }}</p>
                            </div>
                        </div>

                        {{-- মেম্বারশিপ তারিখ --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-xl text-zinc-500">
                                <flux:icon.calendar-days class="size-5" />
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block mb-0.5">নিবন্ধন তারিখ</span>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $user->created_at?->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- আঞ্চলিক অবস্থান ও স্ট্যাটাস (মোবাইলে নিচে নিচে আসবে) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- আঞ্চলিক অবস্থান --}}
                    <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm">
                        <flux:heading size="sm" class="mb-4">আঞ্চলিক অবস্থান</flux:heading>
                        <div class="space-y-4">
                            @foreach([
                                ['label' => 'বিভাগ', 'value' => $user->division?->name, 'icon' => 'building-office'],
                                ['label' => 'জেলা', 'value' => $user->district?->name, 'icon' => 'map-pin'],
                                ['label' => 'থানা', 'value' => $user->thana?->name, 'icon' => 'home']
                            ] as $loc)
                                <div class="flex items-center gap-3">
                                    <div class="size-8 flex items-center justify-center bg-primary-50 dark:bg-primary-900/20 text-primary-600 rounded-lg">
                                        <flux:icon :name="$loc['icon']" class="size-4" />
                                    </div>
                                    <div class="flex-1 border-b border-zinc-100 dark:border-zinc-800 pb-1">
                                        <span class="text-[9px] font-bold uppercase text-zinc-400 block">{{ $loc['label'] }}</span>
                                        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $loc['value'] ?? '—' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ভেরিফিকেশন কার্ড --}}
                    <div class="p-6 bg-zinc-900 dark:bg-black rounded-3xl text-white shadow-lg relative overflow-hidden">
                        <flux:icon.shield-check class="absolute -top-2 -right-2 size-20 text-white/5 rotate-12" />
                        
                        <div class="relative z-10 space-y-5">
                            <div>
                                <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest">অ্যাকাউন্ট স্ট্যাটাস</span>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-sm">ভেরিফিকেশন</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm font-bold {{ $user->email_verified_at ? 'text-green-400' : 'text-orange-400' }}">
                                            {{ $user->email_verified_at ? 'প্রমাণিত' : 'অপ্রমাণিত' }}
                                        </span>
                                        <flux:icon.check-badge class="size-5 {{ $user->email_verified_at ? 'text-green-400' : 'text-zinc-600' }}" variant="solid" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t border-white/10">
                                <span class="text-[9px] text-zinc-500 uppercase font-bold">মেম্বার আইডি</span>
                                <p class="text-lg font-mono text-primary-400">#{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- বিস্তারিত বর্ণনা --}}
                @if($user->details || $user->description)
                    <div class="p-6 bg-zinc-50 dark:bg-zinc-900/50 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-3xl">
                        <flux:heading class="mb-4">বিস্তারিত তথ্য</flux:heading>
                        <div class="text-sm sm:text-base leading-relaxed text-zinc-600 dark:text-zinc-400 whitespace-pre-line">
                            {{ $user->details ?? $user->description }}
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </x-settings.layout>
</section>