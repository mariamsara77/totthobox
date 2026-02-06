<x-layouts.app.header :title="__('অফলাইন')" :description="__('আপনি বর্তমানে অফলাইনে আছেন')" :image="asset('images/logo.gif')">
    <div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-6">
        <div class="relative flex items-center justify-center py-10">
            <div
                class="absolute w-44 h-44 bg-zinc-400/10 dark:bg-zinc-500/5 rounded-full animate-[ping_3s_linear_infinite]">
            </div>
            <div
                class="absolute w-32 h-32 bg-zinc-300/20 dark:bg-zinc-700/10 rounded-full animate-[pulse_4s_ease-in-out_infinite]">
            </div>

            <div
                class="relative z-10 p-7 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-white dark:border-zinc-800 animate-[float_4s_ease-in-out_infinite] group">

                <div class="relative flex items-center justify-center">
                    <flux:icon.wifi variant="outline"
                        class="w-16 h-16 text-zinc-300 dark:text-zinc-600 transition-colors duration-500 group-hover:text-zinc-400" />

                    <div
                        class="absolute w-[120%] h-1 bg-gradient-to-r from-transparent via-red-500 to-transparent rounded-full -rotate-45 shadow-[0_0_15px_rgba(239,68,68,0.4)] opacity-80">
                    </div>

                    <div class="absolute -top-1 -right-1 flex h-6 w-6">
                        <span
                            class="animate-[ping_1.5s_cubic-bezier(0,0,0.2,1)_infinite] absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-40"></span>
                        <div
                            class="relative flex items-center justify-center rounded-full h-6 w-6 bg-amber-500 border-2 border-white dark:border-zinc-900 shadow-sm">
                            <span class="text-[10px] font-black text-white">!</span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="absolute -bottom-2 w-16 h-3 bg-black/10 dark:bg-black/40 blur-xl rounded-[100%] animate-[shadow_4s_ease-in-out_infinite]">
            </div>
        </div>

        <style>
            /* স্মুথ অ্যানিমেশনের জন্য কাস্টম কীফ্রেম */
            @keyframes float {

                0%,
                100% {
                    transform: translateY(0px) scale(1);
                }

                50% {
                    transform: translateY(-15px) scale(1.02);
                }
            }

            @keyframes shadow {

                0%,
                100% {
                    transform: scale(1);
                    opacity: 0.3;
                }

                50% {
                    transform: scale(1.5);
                    opacity: 0.1;
                }
            }
        </style>

        <flux:heading size="xl" level="1" class="font-black tracking-tight mb-3">
            আপনি অফলাইনে আছেন!
        </flux:heading>

        <flux:subheading size="lg" class="max-w-md mx-auto mb-10 leading-relaxed text-gray-500">
            আপনার ইন্টারনেট সংযোগ বিচ্ছিন্ন হয়ে গেছে। তবে আপনি আপনার আগে থেকে লোড হওয়া তথ্যগুলো এখান থেকে দেখতে
            পারবেন।
        </flux:subheading>

        <div class="flex flex-col sm:flex-row gap-4 w-full justify-center items-center">
            <flux:button href="/" variant="primary" icon="home"
                class="w-full sm:w-auto px-10 rounded-2xl shadow-lg">
                হোম পেজে যান
            </flux:button>
        </div>
    </div>

</x-layouts.app.header>
