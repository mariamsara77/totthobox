@props(['media' => []])

@php
    // যদি সিঙ্গেল স্ট্রিং পাঠানো হয়, তবে তাকে অ্যারেতে রূপান্তর করবে
    $mediaArray = is_array($media) || $media instanceof \Illuminate\Support\Collection ? $media : [$media];

    if (empty($mediaArray) || count($mediaArray) === 0 || !$mediaArray[0])
        return;

    $items = collect($mediaArray)->map(function ($m) {
        // যদি এটি স্প্যাটি মিডিয়া অবজেক্ট হয়
        if (is_object($m) && method_exists($m, 'getUrl')) {
            return [
                'url' => $m->getUrl(),
                'thumb' => $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl(),
                'type' => str_contains($m->mime_type ?? '', 'video') ? 'video' : 'image',
                'caption' => $m->name ?? ''
            ];
        }

        // যদি এটি সরাসরি স্ট্রিং পাথ (URL) হয়
        return [
            'url' => (string) $m,
            'thumb' => (string) $m,
            'type' => 'image', // স্ট্রিং পাথের ক্ষেত্রে ডিফল্ট ইমেজ ধরা হয়েছে
            'caption' => ''
        ];
    })->values()->all();

    $count = count($items);
@endphp

<div x-data="{ galleryItems: @js($items) }" {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="grid gap-2 overflow-hidden rounded-2xl"
        style="grid-template-columns: repeat({{ $count > 1 ? 2 : 1 }}, 1fr);">

        @foreach(collect($items)->take(3) as $index => $item)
            <div class="{{ $count > 2 && $index === 0 ? 'row-span-2' : '' }} relative group cursor-pointer overflow-hidden bg-zinc-100 dark:bg-zinc-900 border border-black/5 dark:border-white/5"
                @click="$dispatch('open-lightbox', { index: {{ $index }}, items: galleryItems })">

                <img src="{{ $item['thumb'] }}"
                    class="w-full h-full object-cover {{ $count > 2 && $index === 0 ? 'aspect-auto' : 'aspect-[4/3]' }} group-hover:scale-105 transition-transform duration-500"
                    loading="lazy">

                {{-- ভিডিও প্লে আইকন --}}
                @if($item['type'] === 'video')
                    <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/40">
                        <div class="bg-white/20 backdrop-blur-md p-2 rounded-full text-white border border-white/30">
                            <flux:icon name="play" variant="solid" class="size-6" />
                        </div>
                    </div>
                @endif

                {{-- অতিরিক্ত ছবি সংখ্যা প্রদর্শন --}}
                @if($count > 3 && $index === 2)
                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center text-white font-bold">
                        <span class="text-lg">+{{ $count - 3 }} টি</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @once
        <x-global-lightbox />
    @endonce
</div>