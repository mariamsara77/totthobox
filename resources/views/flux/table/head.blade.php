@props([
    'sortable' => false,
    'sorted' => false,
    'direction' => null,
    'align' => 'left',
])

@php
    $alignment = match ($align) {
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };

    $sortableClass = $sortable
        ? 'cursor-pointer select-none group hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors'
        : '';
@endphp

<th
    {{ $attributes->merge([
        'scope' => 'col',
        'class' => "px-4 py-3.5 text-sm font-semibold text-gray-900 dark:text-gray-100 {$alignment} {$sortableClass}",
    ]) }}>
    @if ($sortable)
        <button type="button"
            class="flex items-center gap-2 w-full focus:outline-none focus:ring-2 focus:ring-primary-500 rounded"
            {{ $attributes->whereStartsWith('wire:click') }}>
            <span class="truncate">{{ $slot }}</span>

            @if ($sorted)
                <span class="flex-shrink-0">
                    @if ($direction === 'asc')
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    @endif
                </span>
            @else
                <span class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                </span>
            @endif
        </button>
    @else
        <span class="truncate">{{ $slot }}</span>
    @endif
</th>
