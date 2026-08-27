{{-- resources/views/partials/countries-skeleton.blade.php --}}

<div class="space-y-8 animate-pulse">

    {{-- স্ট্যাটস (৪টা callout) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @for ($i = 0; $i < 4; $i++)
            <flux:skeleton animate="shimmer" class="aspect-[4/1] h-30 size-full rounded-lg" />
        @endfor
    </div>

    {{-- ফিল্টার বার --}}
    <div class="bg-zinc-400/10 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
        <div class="flex gap-4">
            <div class="h-10 bg-zinc-400/10 rounded-lg flex-1 min-w-[200px]"></div>
            <div class="h-10 bg-zinc-400/10 rounded-lg w-full sm:w-36"></div>
            <div class="h-10 bg-zinc-400/10 rounded-lg w-full sm:w-40"></div>
        </div>
        <div class="mt-3 h-4 bg-zinc-400/10 rounded w-40"></div>
    </div>

    {{-- দেশের কার্ড গ্রিড --}}
    <div class="grid md:grid-cols-2 gap-6">
        @for ($i = 0; $i < 6; $i++)
            <div class="bg-zinc-400/10 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                {{-- হেডার: ফ্ল্যাগ + নাম --}}
                <div class="flex items-center gap-4 p-4 border-b border-zinc-400/25">
                    <div class="w-16 h-11 bg-zinc-400/10 rounded shrink-0"></div>
                    <div class="space-y-2 flex-1 min-w-0">
                        <div class="h-5 bg-zinc-400/10 rounded w-4/4"></div>
                        <div class="h-4 bg-zinc-400/10 rounded-full w-20"></div>
                    </div>
                </div>

                {{-- বডি --}}
                <div class="p-4 space-y-3">
                    <div class="space-y-2">
                        <div class="h-3.5 bg-zinc-400/10 rounded w-full"></div>
                        <div class="h-3.5 bg-zinc-400/10 rounded w-5/6"></div>
                        <div class="h-3.5 bg-zinc-400/10 rounded w-4/6"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <div class="space-y-1">
                            <div class="h-3 bg-zinc-400/10 rounded w-16"></div>
                            <div class="h-4 bg-zinc-400/10 rounded w-12"></div>
                        </div>
                        <div class="space-y-1">
                            <div class="h-3 bg-zinc-400/10 rounded w-14"></div>
                            <div class="h-4 bg-zinc-400/10 rounded w-10"></div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <div class="h-8 bg-zinc-400/10 rounded-lg w-24"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

</div>
