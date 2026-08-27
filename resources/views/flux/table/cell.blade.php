@props([
    'variant' => 'default',
    'sticky' => false,
])

<td
    {{ $attributes->class([
        'px-4 py-3.5 whitespace-nowrap text-zinc-600 dark:text-zinc-300',
        'font-medium text-zinc-900 dark:text-white' => $variant === 'strong',
        'sticky left-0 z-10' => $sticky,
    ]) }}>
    {{ $slot }}
</td>
