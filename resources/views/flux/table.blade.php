@props([
    'paginate' => null,
    'simple' => false,
    'header' => null,
    'footer' => null,
])

@php
    $classes = Flux::classes()
        ->add('[:where(&)]:min-w-full border-separate border-spacing-0')
        ->add('*:border-b *:border-zinc-200 dark:*:border-white/10')
        ->add(
            '[:where(&_>_thead>_tr>_th)]:border-b [:where(&_>_thead>_tr>_th)]:border-zinc-200 dark:[:where(&_>_thead>_tr>_th)]:border-white/10',
        )
        ->add(
            '[:where(&_>_thead>_tr>_th)]:bg-zinc-50 [:where(&_>_thead>_tr>_th)]:py-3 [:where(&_>_thead>_tr>_th)]:text-left [:where(&_>_thead>_tr>_th)]:text-xs [:where(&_>_thead>_tr>_th)]:font-semibold [:where(&_>_thead>_tr>_th)]:text-zinc-800 dark:[:where(&_>_thead>_tr>_th)]:bg-white/10 dark:[:where(&_>_thead>_tr>_th)]:text-white',
        )
        ->add(
            '[:where(&_>_thead>_tr>_th:first-child)]:rounded-tl-lg [:where(&_>_thead>_tr>_th:last-child)]:rounded-tr-lg',
        )
        ->add('[:where(&_>_tbody>_tr:last-child>_td)]:border-b-0')
        ->add(
            '[:where(&_>_tbody>_tr>_td)]:py-3 [:where(&_>_tbody>_tr>_td)]:text-sm [:where(&_>_tbody>_tr>_td)]:text-zinc-500 dark:[:where(&_>_tbody>_tr>_td)]:text-zinc-400',
        )
        ->add(
            '[:where(&_>_thead>_tr>_th)]:px-4 first:[:where(&_>_thead>_tr>_th)]:pl-6 last:[:where(&_>_thead>_tr>_th)]:pr-6',
        )
        ->add(
            '[:where(&_>_tbody>_tr>_td)]:px-4 first:[:where(&_>_tbody>_tr>_td)]:pl-6 last:[:where(&_>_tbody>_tr>_td)]:pr-6',
        );
@endphp

<div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-white/10">
    {{ $header }}

    <div {{ $attributes->class('overflow-x-auto rounded-xl') }}>
        <table {{ $attributes->class($classes) }} data-flux-table>
            {{ $slot }}
        </table>
    </div>

    {{ $footer }}

    @if ($paginate)
        <div class="border-t border-zinc-200 dark:border-white/10 px-4 py-3">
            <flux:pagination :paginator="$paginate" :simple="$simple" />
        </div>
    @endif
</div>
