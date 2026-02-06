@props([
    'sortable' => false,
    'direction' => 'asc',
    'sorted' => false,
    'sticky' => false,
])

<th
    {{ $attributes->class([
        'px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 whitespace-nowrap',
        'sticky left-0 z-30' => $sticky,
    ]) }}>
    @if ($sortable)
        <button type="button"
            class="group inline-flex items-center gap-2 hover:text-zinc-900 dark:hover:text-white transition-colors">
            {{ $slot }}
            <span class="text-zinc-400">
                @if ($sorted && $direction === 'asc')
                    <flux:icon.chevron-up variant="micro" class="size-3" />
                @elseif ($sorted && $direction === 'desc')
                    <flux:icon.chevron-down variant="micro" class="size-3" />
                @else
                    <flux:icon.chevron-up-down variant="micro" class="size-3 opacity-0 group-hover:opacity-100" />
                @endif
            </span>
        </button>
    @else
        {{ $slot }}
    @endif
</th>
