@props(['clickable' => false])

<tr
    {{ $attributes->merge([
        'class' => $clickable ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors' : '',
    ]) }}>
    {{ $slot }}
</tr>
