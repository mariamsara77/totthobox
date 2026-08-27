<tr
    {{ $attributes->class([
        'group/row transition-colors duration-200',
        'hover:bg-zinc-50/80 dark:hover:bg-white/[0.03]',
    ]) }}>
    {{ $slot }}
</tr>
