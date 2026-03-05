@props(['icon', 'color', 'message', 'type'])

<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)" {{-- আমি সময় কিছুটা কমিয়ে ৮
    সেকেন্ড করেছি --}} x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
    class="bg-zinc-200 dark:bg-zinc-800 p-3 rounded-xl shadow-lg mb-3 border border-zinc-300 dark:border-zinc-700">

    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <flux:icon :icon="$icon" class="{{ $color }} h-5 w-5" />
            <p
                class="text-sm font-medium {{ $type === 'error' ? 'text-red-500' : 'text-zinc-800 dark:text-zinc-200' }}">
                {{ $message }}
            </p>
        </div>

        <button @click="show = false"
            class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-100 transition-colors">
            <flux:icon icon="x-mark" variant="micro" />
        </button>
    </div>
</div>