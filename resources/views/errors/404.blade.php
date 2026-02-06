<x-layouts.app.header>

    <div class="max-w-2xl mx-auto mt-8">

        <section role="alert" aria-live="polite" class="rounded-4xl flex flex-col items-center text-center gap-6">

            <!-- Big numeric header -->
            <h1 class="font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-pink-500
           leading-none select-none text-[clamp(3.5rem,8vw,7rem)]">
                404
            </h1>

            <div class="max-w-prose">
                <h2 class="font-semibold text-zinc-900 dark:text-zinc-100 text-[clamp(1.25rem,2vw,1.875rem)]">
                    দুঃখিত! পৃষ্ঠা পাওয়া যায়নি
                </h2>

                <p class="mt-3 text-base md:text-lg text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    আপনি হয়তো ভুল URL এ চলে গেছেন বা পৃষ্ঠাটি মুছে ফেলা হয়েছে। হোমপেজে ফিরে যান বা নীচের বিকল্প ব্যবহার করুন।
                </p>
            </div>


            <!-- Action buttons -->
            <div class="mt-4 w-full flex flex-col sm:flex-row items-center justify-center gap-3">

                <flux:button href="{{ url('/') }}" class="!rounded-full !bg-black !text-white !border-0 flex items-center gap-2" aria-label="হোমপেজে ফিরে যান">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    হোমপেজে ফিরে যান
                </flux:button>

                <flux:button x-data @click="window.history.length > 1 ? window.history.back() : window.location.href='{{ url('/') }}'" class="!rounded-full flex items-center gap-2" aria-label="পেছনে ফিরে যান">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                    </svg>
                    পেছনে ফিরে যান
                </flux:button>

            </div>

            <!-- Optional contextual links -->
            <nav aria-label="alternative navigation" class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">
                <ul class="flex flex-wrap gap-3 justify-center">
                    <li><a href="{{ url('/help') }}" class="underline hover:text-zinc-800 dark:hover:text-zinc-100">হেল্প সেন্টার</a></li>
                    <li><a href="{{ url('/status') }}" class="underline hover:text-zinc-800 dark:hover:text-zinc-100">সিস্টেম স্ট্যাটাস</a></li>
                    <li><a href="{{ url('/contact') }}" class="underline hover:text-zinc-800 dark:hover:text-zinc-100">আমাদেরকে জানাবেন</a></li>
                </ul>
            </nav>

        </section>
    </div>

</x-layouts.app.header>
