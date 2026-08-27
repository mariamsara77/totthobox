<x-layouts.app.header :title="__('অফলাইন')" :description="__('আপনি বর্তমানে অফলাইনে আছেন')" :image="asset('images/logo.gif')">
    <div class="flex flex-col items-center justify-center px-6 text-center">
        {{-- Icon Section --}}
        <div class="relative mb-8 flex items-center justify-center">
            <div class="rounded-3xl border border-zinc-200 bg-white p-7 shadow-lg border-zinc-400/25">
                <div class="relative flex items-center justify-center">
                    <flux:icon.wifi variant="outline" class="size-16 text-zinc-300 dark:text-zinc-600" />

                    {{-- Slash line --}}
                    <div
                        class="absolute h-1 w-[120%] -rotate-45 rounded-full bg-gradient-to-r from-transparent via-red-500 to-transparent opacity-80">
                    </div>

                    {{-- Alert badge --}}
                    <div
                        class="absolute -right-1 -top-1 flex size-6 items-center justify-center rounded-full border-2 border-zinc-400/25">
                        <span class="text-xs font-black text-white">!</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Heading --}}
        <flux:heading size="xl" level="1" class="mb-3 font-black tracking-tight">
            আপনি অফলাইনে আছেন!
        </flux:heading>

        {{-- Subheading --}}
        <flux:subheading size="lg" class="mx-auto mb-20 max-w-md leading-relaxed">
            আপনার ইন্টারনেট সংযোগ বিচ্ছিন্ন হয়ে গেছে। তবে আপনি আপনার আগে থেকে লোড হওয়া তথ্যগুলো এখান থেকে দেখতে
            পারবেন।
        </flux:subheading>

        {{-- Button --}}
        <flux:button href="/" variant="primary" icon="home" class="rounded-2xl px-10 shadow-lg">
            হোম পেজে যান
        </flux:button>
    </div>
</x-layouts.app.header>
