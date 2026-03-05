@props([
    'multiple' => false,
    'model' => null,
    'label' => 'Upload Files',
])

@php
    $propertyName = $attributes->wire('model')->value() ?? $model;
    $files = data_get($this, $propertyName);
    
    // ফাইলগুলোকে অ্যারেতে রূপান্তর (এডিট মোডে ডাটাবেজের ইমেজগুলোও এখানে থাকবে)
    $fileArray = is_array($files) ? $files : ($files ? [$files] : []);
@endphp

<div x-data="{ 
    progress: 0,
    isUploading: false,
    fileProgresses: {},

    uploadFile(event) {
        const files = Array.from(event.target.files);
        if (files.length === 0) return;
        
        this.isUploading = true;
        this.progress = 0;
        this.fileProgresses = {};

        files.forEach((file, index) => {
            let fileKey = 'file_' + index;
            this.fileProgresses[fileKey] = 0;

            @this.upload('{{ $propertyName }}', file, 
                (uploadedFilename) => {
                    this.fileProgresses[fileKey] = 100;
                    this.updateCombinedProgress(files.length);

                    if (Object.values(this.fileProgresses).every(p => p === 100)) {
                        this.progress = 100; 
                        setTimeout(() => { 
                            this.isUploading = false;
                            setTimeout(() => { this.progress = 0; }, 500);
                        }, 1000);
                    }
                    event.target.value = '';
                }, 
                () => { 
                    this.isUploading = false;
                    this.progress = 0;
                },
                (event) => { 
                    this.fileProgresses[fileKey] = event.detail.progress;
                    this.updateCombinedProgress(files.length);
                }
            );
        });
    },

    updateCombinedProgress(totalCount) {
        let sum = Object.values(this.fileProgresses).reduce((a, b) => a + b, 0);
        let combined = Math.round(sum / totalCount);
        if (combined > this.progress) {
            this.progress = combined;
        }
    }
}" class="w-full space-y-3">
    
    @if($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div class="relative group min-h-[110px] flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl transition-all cursor-pointer bg-zinc-400/5 hover:border-accent hover:bg-zinc-400/10">
        
        <input type="file" 
            x-on:change="uploadFile($event)"
            {{ $multiple ? 'multiple' : '' }}
            accept="image/*"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
        >

        <div class="flex flex-col items-center justify-center py-2 pointer-events-none" x-show="!isUploading">
            <flux:icon.cloud-arrow-up variant="solid" class="w-6 h-6 text-zinc-400 group-hover:text-accent transition-colors" />
            <flux:heading size="sm" class="mt-1">Click to add files</flux:heading>
            <flux:subheading size="sm">JPG, PNG, WEBP up to 10MB</flux:subheading>
        </div>

        <div x-show="isUploading" 
             class="absolute inset-0 z-30 flex items-center justify-center bg-zinc-50/10 backdrop-blur-sm px-10"
        >
            <div class="w-full max-w-sm space-y-3">
                <div class="flex justify-between items-end">
                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Uploading...</span>
                    <span class="text-xs font-black text-accent tabular-nums"><span x-text="progress"></span>%</span>
                </div>
                <div class="w-full bg-zinc-400/10 h-1 rounded-full overflow-hidden">
                    <div class="bg-zinc-400/50 h-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Previews Section (নতুন এবং পুরাতন ইমেজ এখানেই দেখাবে) --}}
    @if(!empty($fileArray))
        <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-3">
            @foreach($fileArray as $index => $file)
                <div class="relative aspect-square rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 group shadow-sm bg-white dark:bg-zinc-800">
                    
                    {{-- লজিক: যদি লাইভওয়্যার অবজেক্ট হয় তবে টেম্পোরারি ইউআরএল, না হলে ডাটাবেজ ইউআরএল --}}
                    @if($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                        <img src="{{ $file->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif(isset($file['url']))
                        {{-- Spatie Media-র জন্য --}}
                        <img src="{{ $file['url'] }}" class="w-full h-full object-cover">
                    @endif

                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                       <flux:button variant="danger" size="xs" icon="trash" 
                            wire:click="removeImage('{{ $propertyName }}', {{ $index }})" 
                            wire:confirm="ইমেজটি কি মুছে ফেলতে চান?" />
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <flux:error :name="$propertyName" />
</div>