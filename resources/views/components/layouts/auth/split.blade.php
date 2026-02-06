<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    <style>
        /* প্রফেশনাল গ্রেডিয়েন্ট অ্যানিমেশন */
        @keyframes mesh-gradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .animate-mesh {
            background: linear-gradient(-45deg, #09090b, #171717, #1e1b4b, #020617);
            background-size: 400% 400%;
            animation: mesh-gradient 15s ease infinite;
        }
    </style>
</head>

<body class="min-h-screen bg-neutral-50 antialiased dark:bg-[#020617] selection:bg-indigo-500/30">

    <div class="relative flex min-h-screen flex-col lg:grid lg:grid-cols-12 overflow-hidden">

        <div class="relative hidden lg:flex lg:col-span-7 flex-col justify-between p-12 overflow-hidden animate-mesh">
            <div class="absolute -top-[10%] -left-[10%] h-[40%] w-[40%] rounded-full bg-indigo-500/10 blur-[120px]">
            </div>
            <div class="absolute -bottom-[10%] -right-[10%] h-[40%] w-[40%] rounded-full bg-blue-500/10 blur-[120px]">
            </div>

            <a href="{{ route('home') }}"
                class="relative z-20 flex items-center gap-3 transition-transform hover:scale-105" wire:navigate.hover>
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-xl shadow-indigo-500/20">
                    <x-app-logo-icon class="h-7 w-7 fill-current text-white" />
                </div>
                <span class="text-2xl font-bold tracking-tight text-white/90">
                    {{ config('app.name', 'Laravel') }}
                </span>
            </a>

            @php
                [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
            @endphp

            <div class="relative z-20 max-w-xl">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-medium text-indigo-300 mb-6 backdrop-blur-sm">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    System Updates Live
                </div>

                <blockquote class="space-y-6">
                    <h2 class="text-4xl font-semibold leading-tight text-white">
                        &ldquo;{{ trim($message) }}&rdquo;
                    </h2>
                    <footer class="flex items-center gap-4">
                        <div class="flex -space-x-2">
                            <img class="inline-block h-10 w-10 rounded-full ring-2 ring-neutral-900"
                                src="https://ui-avatars.com/api/?name=User&background=6366f1&color=fff" alt="User">
                            <img class="inline-block h-10 w-10 rounded-full ring-2 ring-neutral-900"
                                src="https://ui-avatars.com/api/?name=Admin&background=4f46e5&color=fff" alt="Admin">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-white">{{ trim($author) }}</span>
                            <span class="text-xs text-neutral-400">Thought Leader</span>
                        </div>
                    </footer>
                </blockquote>
            </div>

            <div class="relative z-20 flex gap-6 text-sm text-neutral-500">
                <span class="flex items-center gap-1"><flux:icon.check-circle class="size-4" /> Secure Data</span>
                <span class="flex items-center gap-1"><flux:icon.shield-check class="size-4" /> SSL Encrypted</span>
            </div>
        </div>

        <div class="relative flex lg:col-span-5 items-center justify-center p-8 bg-white dark:bg-[#09090b]">
            <div class="w-full max-w-md space-y-8">
                <div class="flex flex-col items-center justify-center lg:hidden mb-10">
                    <div
                        class="h-14 w-14 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-2xl shadow-indigo-500/40">
                        <x-app-logo-icon class="h-9 w-9 fill-current text-white" />
                    </div>
                </div>

                <div class="relative group">
                    <div
                        class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur opacity-10 group-hover:opacity-20 transition duration-1000 group-hover:duration-200">
                    </div>

                    <div
                        class="relative bg-white dark:bg-neutral-900/50 p-8 sm:p-10 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-2xl">
                        <div class="mb-8">
                            {{ $slot }}
                        </div>

                        <div class="mt-8 pt-6 border-t border-neutral-100 dark:border-neutral-800 text-center">
                            <p class="text-xs text-neutral-500">
                                Trusted by 10,000+ teams worldwide.
                            </p>
                        </div>
                    </div>
                </div>

                <nav class="flex justify-center gap-4 text-xs text-neutral-400">
                    <a href="#" class="hover:text-indigo-500 transition-colors">Support</a>
                    <span>&bull;</span>
                    <a href="#" class="hover:text-indigo-500 transition-colors">Privacy Policy</a>
                </nav>
            </div>
        </div>
    </div>

    @fluxScripts
</body>

</html>
