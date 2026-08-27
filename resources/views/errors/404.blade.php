<x-layouts.app.header>

    <div class="max-w-2xl mx-auto mt-8">

        <section role="alert" aria-live="polite" class="rounded-4xl flex flex-col items-center text-center gap-6">

            <!-- Big numeric header -->
            <h1
                class="font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-pink-500
           leading-none select-none text-[clamp(3.5rem,8vw,7rem)]">
                404
            </h1>

            <div class="max-w-prose">
                <h2 class="font-semibold text-zinc-900 dark:text-zinc-100 text-[clamp(1.25rem,2vw,1.875rem)]">
                    দুঃখিত! পৃষ্ঠা পাওয়া যায়নি
                </h2>

                <p class="mt-3 text-base  text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    আপনি হয়তো ভুল URL এ চলে গেছেন বা পৃষ্ঠাটি মুছে ফেলা হয়েছে। হোমপেজে ফিরে যান বা নীচের বিকল্প ব্যবহার
                    করুন।
                </p>
            </div>


            <!-- Action buttons -->
            <div class="mt-4 w-full flex items-center justify-center gap-4">

                <flux:button variant="primary" href="{{ url('/') }}" class="!rounded-full"
                    aria-label="হোমপেজে ফিরে যান" icon="home">
                    হোমপেজে ফিরে যান
                </flux:button>

                <flux:button variant="filled"
                    onclick="window.location.href = document.referrer ? document.referrer : '{{ url('/') }}';"
                    class="!rounded-full" aria-label="পেছনে ফিরে যান" icon="arrow-left">
                    পেছনে ফিরে যান
                </flux:button>

            </div>

            <!-- Optional contextual links -->
            <nav aria-label="alternative navigation" class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">
                <ul class="flex flex-wrap gap-4 justify-center">
                    <li><a href="{{ url('/help') }}"
                            class="underline hover:text-zinc-800 dark:hover:text-zinc-100">হেল্প সেন্টার</a></li>
                    <li><a href="{{ url('/status') }}"
                            class="underline hover:text-zinc-800 dark:hover:text-zinc-100">সিস্টেম স্ট্যাটাস</a></li>
                    <li><a href="{{ url('/contact-us') }}"
                            class="underline hover:text-zinc-800 dark:hover:text-zinc-100">আমাদেরকে জানাবেন</a></li>
                </ul>
            </nav>

        </section>
    </div>

</x-layouts.app.header>
