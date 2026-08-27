<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main class="p-0!">
        <div class="p-4">
            {{ $slot }}
        </div>
        <div class="my-8">
            <flux:separator />
            <livewire:layout.footer-section />
        </div>
    </flux:main>
</x-layouts.app.sidebar>