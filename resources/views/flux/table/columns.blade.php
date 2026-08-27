@props(['sticky' => false])

<thead {{ $attributes->class(['border-b border-zinc-400/25', 'sticky top-0 z-20' => $sticky]) }}>
    <tr>
        {{ $slot }}
    </tr>
</thead>
