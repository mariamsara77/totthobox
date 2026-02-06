@props(['colspan' => 1, 'title' => 'No data found', 'description' => null, 'icon' => 'table'])

<tr>
    <td colspan="{{ $colspan }}" class="px-6 py-16 text-center">
        <div class="flex flex-col items-center justify-center gap-4">
            @if ($icon === 'table')
                <div
                    class="mx-auto h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
            @elseif($icon === 'search')
                <div
                    class="mx-auto h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            @endif

            <div class="max-w-sm">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">{{ $title }}</h3>
                @if ($description)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                @endif
            </div>

            @if (isset($action))
                <div class="mt-2">
                    {{ $action }}
                </div>
            @endif
        </div>
    </td>
</tr>
