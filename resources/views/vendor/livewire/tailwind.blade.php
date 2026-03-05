@php
    if (!isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
                                                                                                                                                                                                                                            (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
                                                                                                                                                                                                                                        JS
        : '';
@endphp

<div class="mt-4">
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center lg:justify-between justify-end">

            <div class="hidden lg:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Showing
                        <span class="font-medium">{{ $paginator->firstItem() ?: 0 }}</span>
                        to
                        <span class="font-medium">{{ $paginator->lastItem() ?: 0 }}</span>
                        of
                        <span class="font-medium">{{ $paginator->total() }}</span>
                        results
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1 bg-zinc-400/10 rounded-lg p-0.5">
                {{-- Previous Page Button --}}
                @if ($paginator->onFirstPage())
                    <flux:button icon="chevron-left" size="xs" variant="subtle" disabled />
                @else
                    <flux:button wire:click="previousPage" x-on:click="{{ $scrollIntoViewJsSnippet }}" icon="chevron-left"
                        size="xs" variant="ghost" />
                @endif

                {{-- Page Numbers --}}
                <div class="lg:flex items-center gap-1 hidden">
                    @foreach ($elements as $element)
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                <span wire:key="paginator-page{{ $page }}">
                                    @if ($page == $paginator->currentPage())
                                        {{-- Active Page: Ghost Variant --}}
                                        <flux:button size="xs" variant="ghost" class="font-bold">
                                            {{ $page }}
                                        </flux:button>
                                    @else
                                        {{-- Inactive Page: Subtle Variant --}}
                                        <flux:button wire:click="gotoPage({{ $page }})" x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                            size="xs" variant="subtle">
                                            {{ $page }}
                                        </flux:button>
                                    @endif
                                </span>
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Next Page Button --}}
                @if ($paginator->hasMorePages())
                    <flux:button wire:click="nextPage" x-on:click="{{ $scrollIntoViewJsSnippet }}" icon="chevron-right"
                        size="xs" variant="ghost" />
                @else
                    <flux:button icon="chevron-right" size="xs" variant="subtle" disabled />
                @endif
            </div>
        </nav>
    @endif
</div>