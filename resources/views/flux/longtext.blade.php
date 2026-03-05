@props([
    'content' => '',
    'limit' => 200,
    'lineClamp' => 'line-clamp-3',
    'variant' => 'default'
])

@php
$plainText = strip_tags($content);
$shouldTruncate = mb_strlen($plainText) > $limit;

$variants = [
    'default' => '',
    'subtle' => 'text-zinc-500 dark:text-zinc-400',
    'accent' => 'text-zinc-900 dark:text-white font-medium',
];
@endphp

<div 
    x-data="{ 
        expanded: false, 
        isTruncated: {{ $shouldTruncate ? 'true' : 'false' }},
        init() {
            // নিশ্চিত করে যে কম্পোনেন্টটি লোড হওয়ার সময় সঠিক অবস্থায় আছে
            this.expanded = false;
        }
    }" 
    x-cloak
    {{ $attributes->merge(['class' => 'relative w-full group/readmore']) }}
>
    {{-- Content Area --}}
    <div 
        @click="if(isTruncated) expanded = !expanded" 
        :class="{ [expanded ? '' : '{{ $lineClamp }}']: isTruncated, 'cursor-pointer': isTruncated }"
        class="transition-all duration-700 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden relative"
    >
        <div class="{{ $variants[$variant] ?? $variants['default'] }} leading-relaxed">
            <flux:text>
                {!! $content !!}
            </flux:text>
        </div>

        {{-- Gradient Overlay for smooth fade (Optional, can be removed) --}}
        <template x-if="isTruncated && !expanded">
            <div class="absolute bottom-0 left-0 w-full h-8 bg-gradient-to-t from-white/10 dark:from-zinc-900/10 pointer-events-none"></div>
        </template>
    </div>

    {{-- Flux-style Action Button --}}
    <template x-if="isTruncated">
        <div class="flex items-center justify-start">
            <flux:link 
                @click="expanded = !expanded" class="text-xs text-zinc-400/50">
                <span x-text="expanded ? '' : 'বিস্তারিত পড়ুন'"></span>
            </flux:link>
        </div>
    </template>
</div>