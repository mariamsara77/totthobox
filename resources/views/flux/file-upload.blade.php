@props([
'multiple' => false,
'label' => 'Upload Files',
'accept' => '*', // যেমন: '.xlsx, .pdf, image/*',
'description' => 'JPG, PNG, PDF, Excel up to 10MB',
])

@php
$propertyName = $attributes->wire('model')->value();
$files = data_get($this, $propertyName);

// ফাইলগুলোকে অ্যারেতে রূপান্তর
$fileArray = is_array($files) ? $files : ($files ? [$files] : []);
@endphp

<div class="w-full space-y-3">

    @if ($label)
    <flux:label>{{ $label }}</flux:label>
    @endif

    {{-- Livewire Upload Events ব্যবহার করে Alpine Simplified --}}
    <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
        x-on:livewire-upload-finish="isUploading = false" x-on:livewire-upload-error="isUploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
        class="relative group min-h-[110px] flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl transition-all cursor-pointer bg-zinc-400/5 hover:border-accent hover:bg-zinc-400/10">

        <input type="file" wire:model="{{ $propertyName }}" {{ $multiple ? 'multiple' : '' }} accept="{{ $accept }}"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

        {{-- আপলোড করার আগের ভিউ --}}
        <div class="flex flex-col items-center justify-center py-2 pointer-events-none" x-show="!isUploading">
            <flux:icon.cloud-arrow-up variant="solid" class="w-6 h-6 text-zinc-400 group-hover: transition-colors" />
            <flux:heading size="sm" class="mt-1">Click to add files</flux:heading>
            <flux:subheading size="sm">{{ $description }}</flux:subheading>
        </div>

        {{-- অটোমেটিক প্রোগ্রেস বার --}}
        <div x-show="isUploading" x-cloak
            class="absolute inset-0 flex items-center justify-center bg-zinc-50/10 backdrop-blur px-10 rounded-xl">
            <div class="w-full  space-y-3">
                <div class="flex justify-between items-end">
                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Uploading...</span>
                    <span class="text-xs font-black  tabular-nums"><span x-text="progress"></span>%</span>
                </div>
                <div class="w-full bg-zinc-400/10 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-zinc-800 dark:bg-zinc-200 h-full transition-all duration-200"
                        :style="'width: ' + progress + '%'"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Previews Section (ইমেজ, এক্সেল, পিডিএফ সব ফাইলের জন্য ডায়নামিক) --}}
    @if (!empty($fileArray))
    <div class="grid grid-cols-2 grid-cols-4 gap-3">
        @foreach ($fileArray as $index => $file)
        @php
        $isTemp = $file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

        if ($isTemp) {
        $isImage = $file->isPreviewable();
        $filename = $file->getClientOriginalName();
        $imageUrl = $isImage ? $file->temporaryUrl() : null;
        } else {
        $imageUrl = $file['url'] ?? '';
        // URL থেকে ফাইলের এক্সটেনশন চেক করে ইমেজ কি না তা নির্ধারণ
        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i', $imageUrl);
        $filename = $file['name'] ?? basename($imageUrl);
        }
        @endphp

        <div
            class="relative p-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex flex-col items-center justify-center group shadow-sm min-h-[90px]">

            {{-- যদি ফাইলটি ইমেজ হয় --}}
            @if ($isImage && $imageUrl)
            <img src="{{ $imageUrl }}" class="w-full h-20 object-cover rounded">
            @else
            {{-- Excel, PDF বা Text ফাইলের ক্ষেত্রে আইকন ও ফাইলের নাম দেখাবে --}}
            <div class="flex flex-col items-center p-2 text-center w-full overflow-hidden">
                <flux:icon.document-text class="w-8 h-8 text-zinc-400 mb-2 shrink-0" />
                <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300 truncate w-full"
                    title="{{ $filename }}">{{ $filename }}</span>
            </div>
            @endif

            {{-- রিমুভ বাটন --}}
            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 ">
                <flux:button variant="danger" size="xs" icon="x-mark"
                    wire:click="removeImage('{{ $propertyName }}', {{ $index }})"
                    wire:confirm="ফাইলটি কি মুছে ফেলতে চান?" />
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <flux:error :name="$propertyName" />
</div>