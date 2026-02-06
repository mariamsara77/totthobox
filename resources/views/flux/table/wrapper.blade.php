@props(['paginate' => null, 'responsive' => true, 'shadow' => true])

<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    <div class="@if ($responsive) -mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8 @endif">
        <div class="@if ($responsive) inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8 @endif">
            <div
                class="@if ($shadow) overflow-hidden shadow-sm ring-1 ring-gray-900/5 dark:ring-white/10 @else overflow-hidden @endif rounded-lg">
                {{ $slot }}

                @if ($paginate && $paginate->hasPages())
                    <div>
                        {{ $paginate->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>