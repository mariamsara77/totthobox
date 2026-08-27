<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

new class extends Component {
    use WithFileUploads;

    #[Validate('required|image|max:30720')]
    public $image;

    public int $quality = 85;
    public string $format = 'webp';
    public ?int $outputWidth = null;
    public ?int $outputHeight = null;
    public bool $lockAspectRatio = true;

    public ?int $cropX = null;
    public ?int $cropY = null;
    public ?int $cropWidth = null;
    public ?int $cropHeight = null;

    public int $rotation = 0;
    public bool $flipH = false;
    public bool $flipV = false;

    public ?int $originalSizeBytes = null;
    public ?int $compressedSizeBytes = null;
    public ?string $previewUrl = null;
    public ?string $filePath = null;
    public ?int $originalWidth = null;
    public ?int $originalHeight = null;
    public ?int $outputFinalWidth = null;
    public ?int $outputFinalHeight = null;

    public ?string $errorMessage = null;
    public bool $isProcessing = false;

    public const FORMATS = [
        'webp' => 'WebP',
        'jpg' => 'JPG',
        'png' => 'PNG',
    ];

    public function updatedImage(): void
    {
        $this->errorMessage = null;

        try {
            $this->validateOnly('image');
        } catch (Throwable $e) {
            $this->errorMessage = 'ফাইলটি বৈধ ইমেজ নয় অথবা সাইজ ৩০MB এর বেশি।';
            return;
        }

        try {
            $this->originalSizeBytes = $this->image->getSize();

            $this->reset(['cropX', 'cropY', 'cropWidth', 'cropHeight', 'rotation', 'flipH', 'flipV', 'outputWidth', 'outputHeight']);
            $this->lockAspectRatio = true;
            $this->quality = 85;
            $this->format = 'webp';

            $img = Image::read($this->image->getRealPath());
            $this->originalWidth = $img->width();
            $this->originalHeight = $img->height();

            $this->processImage();
        } catch (Throwable $e) {
            Log::error('ImageStudio upload failed: ' . $e->getMessage());
            $this->errorMessage = 'ইমেজ প্রসেস করতে সমস্যা হয়েছে।';
            $this->image = null;
        }
    }

    public function setCropData(int $x, int $y, int $w, int $h): void
    {
        $this->cropX = max(0, $x);
        $this->cropY = max(0, $y);
        $this->cropWidth = max(1, $w);
        $this->cropHeight = max(1, $h);
        $this->processImage();
    }

    public function resetCrop(): void
    {
        $this->cropX = $this->cropY = $this->cropWidth = $this->cropHeight = null;
        $this->processImage();
    }

    public function rotate(int $degree): void
    {
        $this->rotation = ($this->rotation + $degree + 360) % 360;
        $this->processImage();
    }

    public function toggleFlipH(): void
    {
        $this->flipH = !$this->flipH;
        $this->processImage();
    }

    public function toggleFlipV(): void
    {
        $this->flipV = !$this->flipV;
        $this->processImage();
    }

    public function resizeByPercent(int $percent): void
    {
        if (!$this->originalWidth || !$this->originalHeight) {
            return;
        }

        $this->lockAspectRatio = true;
        $this->outputWidth = max(1, (int) round(($this->originalWidth * $percent) / 100));
        $this->outputHeight = max(1, (int) round(($this->originalHeight * $percent) / 100));
        $this->processImage();
    }

    public function resetSize(): void
    {
        $this->outputWidth = $this->outputHeight = null;
        $this->lockAspectRatio = true;
        $this->processImage();
    }

    public function updatedQuality(): void
    {
        $this->processImage();
    }
    public function updatedFormat(): void
    {
        $this->processImage();
    }
    public function updatedOutputWidth(): void
    {
        $this->processImage();
    }
    public function updatedOutputHeight(): void
    {
        $this->processImage();
    }

    private function buildImage(): \Intervention\Image\Interfaces\ImageInterface
    {
        $img = Image::read($this->image->getRealPath());

        if ($this->rotation !== 0) {
            $img->rotate(-$this->rotation);
        }

        if ($this->flipH) {
            $img->flop();
        }
        if ($this->flipV) {
            $img->flip();
        }

        if ($this->cropWidth && $this->cropHeight) {
            $img->crop($this->cropWidth, $this->cropHeight, $this->cropX ?? 0, $this->cropY ?? 0);
        }

        if ($this->outputWidth || $this->outputHeight) {
            if ($this->lockAspectRatio) {
                $img->scale(width: $this->outputWidth ?: null, height: $this->outputHeight ?: null);
            } else {
                $img->resize($this->outputWidth ?: $img->width(), $this->outputHeight ?: $img->height());
            }
        }

        return $img;
    }

    private function encodeImage(\Intervention\Image\Interfaces\ImageInterface $img, string $format, int $quality)
    {
        return match ($format) {
            'jpg', 'jpeg' => $img->toJpeg($quality),
            'png' => $img->toPng(),
            default => $img->toWebp($quality),
        };
    }

    public function processImage(): void
    {
        if (!$this->image) {
            return;
        }

        $this->isProcessing = true;
        $this->errorMessage = null;

        try {
            $img = $this->buildImage();

            $this->outputFinalWidth = $img->width();
            $this->outputFinalHeight = $img->height();

            $encoded = $this->encodeImage($img, $this->format, $this->quality);

            if ($this->filePath && Storage::disk('public')->exists($this->filePath)) {
                Storage::disk('public')->delete($this->filePath);
            }

            $fileName = 'studio_' . time() . '_' . uniqid() . '.' . $this->format;
            $this->filePath = 'compressor/' . $fileName;

            Storage::disk('public')->put($this->filePath, (string) $encoded);

            $this->compressedSizeBytes = strlen((string) $encoded);
            $this->previewUrl = Storage::url($this->filePath) . '?t=' . time();
        } catch (Throwable $e) {
            Log::error('ImageStudio process failed: ' . $e->getMessage());
            $this->errorMessage = 'প্রসেস করতে সমস্যা হয়েছে।';
        } finally {
            $this->isProcessing = false;
        }
    }

    public function download()
    {
        if (!$this->image) {
            return;
        }

        try {
            $img = $this->buildImage();
            $encoded = $this->encodeImage($img, $this->format, $this->quality);
            $fileName = 'totthobox_' . time() . '.' . $this->format;

            return response()->streamDownload(fn() => print (string) $encoded, $fileName, ['Content-Type' => $encoded->mimetype() ?: 'application/octet-stream']);
        } catch (Throwable $e) {
            $this->errorMessage = 'ডাউনলোড করতে সমস্যা হয়েছে।';
        }
    }

    public function resetAll(): void
    {
        $this->reset(['cropX', 'cropY', 'cropWidth', 'cropHeight', 'rotation', 'flipH', 'flipV', 'outputWidth', 'outputHeight', 'quality', 'format']);
        $this->quality = 85;
        $this->format = 'webp';
        $this->lockAspectRatio = true;

        if ($this->image) {
            $this->processImage();
        }
    }

    public function removeImage(): void
    {
        if ($this->filePath && Storage::disk('public')->exists($this->filePath)) {
            Storage::disk('public')->delete($this->filePath);
        }

        $this->reset();
        $this->quality = 85;
        $this->format = 'webp';
        $this->lockAspectRatio = true;
    }

    public function formatBytes(?int $bytes): string
    {
        if (!$bytes) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = min((int) floor(log(max($bytes, 1)) / log(1024)), count($units) - 1);
        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }

    #[Computed]
    public function savingsPercentage(): int
    {
        if (!$this->originalSizeBytes || !$this->compressedSizeBytes || $this->originalSizeBytes == 0) {
            return 0;
        }
        $saving = (($this->originalSizeBytes - $this->compressedSizeBytes) / $this->originalSizeBytes) * 100;
        return $saving > 0 ? (int) $saving : 0;
    }
}; ?>

<x-seo title="ছবি রিসাইজ, ক্রপ ও সাইজ কমানোর অনলাইন টুল | Image Resizer"
    description="যেকোনো ফর্ম, সিভি, ভিসা বা ভার্সিটি অ্যাডমিশনের জন্য ছবির রেজুলেশন (Pixel) ও সাইজ (KB) সহজেই ঠিক করুন। ছবি রিসাইজ, ক্রপ এবং কম্প্রেস করে JPG/PNG/WebP ফরম্যাটে ফ্রিতে ডাউনলোড করুন।"
    keywords="image resizer, photo crop online, compress image kb, 300x300 photo maker, signature resizer, visa photo resize, reduce picture size, ছবির সাইজ কমানো, ছবি রিসাইজ, পাসপোর্ট সাইজ ছবি, cv photo maker, image format converter" />

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
@endpush

<div class="max-w-2xl mx-auto">

    {{-- Error --}}
    @if ($errorMessage)
        <div
            class="mb-4 rounded-2xl border border-rose-200 dark:border-rose-900/40 bg-rose-50 dark:bg-rose-950/30 px-4 py-3 flex items-start gap-4">
            <flux:icon name="exclamation-circle" class="size-5 text-rose-500 shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-rose-700 dark:text-rose-300">সমস্যা হয়েছে</p>
                <p class="text-sm text-rose-600 dark:text-rose-400 mt-0.5">{{ $errorMessage }}</p>
            </div>
            <button wire:click="$set('errorMessage', null)" class="text-rose-400 hover:text-rose-600">
                <flux:icon name="x-mark" class="size-4" />
            </button>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 mb-5">
        <div>
            <h1 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">ইমেজ স্টুডিও</h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">ক্রপ · রিসাইজ · কম্প্রেস</p>
        </div>

        @if ($image)
            <div class="flex items-center gap-2">
                <flux:button wire:click="resetAll" variant="ghost" size="sm">রিসেট</flux:button>
                <label class="cursor-pointer">
                    <flux:button as="span" variant="ghost" size="sm" icon="arrow-path">বদলান</flux:button>
                    <input type="file" wire:model="image" accept="image/*" class="hidden" />
                </label>
                <flux:button wire:click="removeImage" variant="ghost" size="sm" icon="trash"
                    class="!text-rose-500" />
            </div>
        @endif
    </div>

    @if (!$image)
        {{-- Empty State --}}
        <div>
            <flux:file-upload wire:model="image" />
        </div>
        {{-- <div x-data="{ dragging: false }" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
            @drop.prevent="
                        dragging = false;
                        $refs.fileInput.files = $event.dataTransfer.files;
                        $refs.fileInput.dispatchEvent(new Event('change'));
                    ">
            <div @click="$refs.fileInput.click()" :class="dragging
                            ?
                            'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/20 scale-[1.01]' :
                            'border-zinc-200 dark:border-zinc-800 hover:border-indigo-400 dark:hover:border-indigo-600'"
                class="border-2 border-dashed rounded-3xl p-10 sm:p-14 text-center cursor-pointer transition-all duration-200">
                <input x-ref="fileInput" type="file" wire:model="image" accept="image/*" class="hidden" />

                <div wire:loading.remove wire:target="image" class="space-y-4">
                    <div
                        class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                        <flux:icon name="cloud-arrow-up" class="size-6 text-white" />
                    </div>
                    <div>
                        <p class="text-base font-semibold text-zinc-900 dark:text-white">ইমেজ আপলোড করুন</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">ড্র্যাগ করে ছাড়ুন অথবা ক্লিক করুন</p>
                    </div>
                    <p class="text-xs text-zinc-400">JPG · PNG · WebP · GIF · সর্বোচ্চ ৩০MB</p>
                </div>

                <div wire:loading wire:target="image" class="py-5">
                    <div class="flex flex-col items-center gap-2">
                        <flux:icon name="loading" class="" />
                        <span class="text-sm font-medium">আপলোড হচ্ছে...</span>
                    </div>
                </div>
            </div>
        </div> --}}

        @error('image')
            <p class="mt-3 text-sm text-rose-500 text-center">{{ $message }}</p>
        @enderror
    @else
        {{-- ═══════════════ EDITOR ═══════════════ --}}
        <div class="space-y-4" x-data="studioApp" wire:key="editor-{{ $image->getFilename() }}">

            {{-- Side-by-side Compare --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Original / Cropper --}}
                <div class="bg-zinc-400/10 rounded-2xl overflow-hidden">
                    <div class="px-3 py-2 border-b border-zinc-400/25 flex items-center justify-between">
                        <span class="text-xs font-semibold tracking-wider text-zinc-500">Original</span>
                        @if ($originalWidth)
                            <span class="text-xs text-zinc-400">{{ $originalWidth }}×{{ $originalHeight }}</span>
                        @endif
                        @if ($originalSizeBytes)
                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ $this->formatBytes($originalSizeBytes) }}
                            </span>
                        @endif
                    </div>
                    <div class="relative">
                        <img x-ref="cropTarget" src="{{ $image->temporaryUrl() }}" class="block w-full"
                            style="max-height: 280px; object-fit: contain;" @load="initCropper()" />
                    </div>
                    @if ($cropWidth)
                        <div class="px-3 py-1.5 flex items-center justify-between gap-2">
                            <flux:icon name="check-circle" class="size-3.5 text-indigo-500" />
                            <span class="text-xs text-indigo-700 dark:text-indigo-300 truncate">
                                {{ $cropWidth }}×{{ $cropHeight }}
                            </span>
                            <flux:button wire:click="resetCrop" size="xs" icon="x-mark" variant="subtle">
                            </flux:button>
                        </div>
                    @endif
                    {{-- Crop Toolbar --}}
                    {{-- Crop Toolbar --}}
                    <div class="p-2 space-y-1">
                        {{-- 1st Line (Mobile) / Left (Desktop) : Ratios --}}
                        <div class="flex items-center gap-1">
                            @foreach ([['NaN', 'Free'], ['1', '1:1'], ['1.7778', '16:9'], ['1.3333', '4:3'], ['0.5625', '9:16']] as [$ratio, $label])
                                <flux:button type="button" size="xs" variant="ghost"
                                    @click="setRatio({{ $ratio }})" class="!text-xs !px-2">
                                    {{ $label }}
                                </flux:button>
                            @endforeach

                            <flux:button size="xs" variant="ghost" wire:click="rotate(-90)" title="বামে"
                                icon="arrow-path">
                            </flux:button>

                            <flux:button type="button" size="xs" variant="ghost" wire:click="rotate(90)"
                                title="ডানে" icon="arrow-path" />

                            <flux:button type="button" size="xs" variant="{{ $flipH ? 'filled' : 'ghost' }}"
                                wire:click="toggleFlipH" icon="arrows-right-left" />

                            <flux:button type="button" size="xs" variant="{{ $flipV ? 'filled' : 'ghost' }}"
                                wire:click="toggleFlipV" icon="arrows-up-down" />
                        </div>

                        {{-- 2nd Line (Mobile) / Right (Desktop) : Actions --}}
                        <div class="flex items-center gap-2 justify-between">


                            <flux:button type="button" size="xs" variant="ghost" wire:click="resetCrop"
                                icon="x-mark" />

                            <flux:button type="button" size="xs" variant="primary" color="green"
                                @click="applyCrop()" icon="check">
                                Crop Image
                            </flux:button>
                        </div>
                    </div>
                </div>

                {{-- Live Preview --}}
                <div class="bg-zinc-400/10  rounded-2xl overflow-hidden">
                    <div class="px-3 py-2 border-b border-zinc-400/25 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-1.5 w-1.5">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-zinc-400/10 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-zinc-400/10"></span>
                            </span>
                            <span class="text-xs font-semibold tracking-wider text-zinc-500">Edited</span>
                        </div>
                        @if ($outputFinalWidth)
                            <div class="text-xs text-zinc-400">
                                {{ $outputFinalWidth }}×{{ $outputFinalHeight }}px
                                @if ($this->savingsPercentage > 0)
                                    · <span
                                        class="text-emerald-600 dark:text-emerald-400 font-medium">↓{{ $this->savingsPercentage }}%</span>
                                @endif
                            </div>
                        @endif
                        @if ($compressedSizeBytes)
                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ $this->formatBytes($compressedSizeBytes) }}
                            </span>
                        @endif
                    </div>

                    <div
                        class="relative min-h-[140px] flex items-center justify-center p-3 bg-zinc-400 dark:bg-zinc-900">
                        @if ($previewUrl)
                            <img src="{{ $previewUrl }}" class="max-w-full rounded-lg shadow-md"
                                style="max-height: 260px; object-fit: contain;" />
                        @else
                            <span class="text-xs text-zinc-400">প্রিভিউ লোড হচ্ছে...</span>
                        @endif

                        <div wire:loading.delay
                            wire:target="quality,format,outputWidth,outputHeight,setCropData,rotate,toggleFlipH,toggleFlipV,resizeByPercent"
                            class="absolute inset-0 bg-black/35 backdrop-blur">
                            <div class="flex items-center justify-center w-full h-full">
                                <flux:icon name="loading" />
                            </div>
                        </div>
                    </div>


                </div>
            </div>

            {{-- ═══════════════ BOTTOM TOOLS ═══════════════ --}}
            {{-- ═══════════════ BOTTOM TOOLS ═══════════════ --}}
            <flux:card class="overflow-hidden">

                {{-- Format + Quality + Resize --}}
                <div class="p-4 space-y-4">

                    {{-- Format (Top) --}}
                    <div>
                        <flux:radio.group wire:model.live="format" label="ফরম্যাট" variant="segmented"
                            class="w-full">
                            @foreach (self::FORMATS as $val => $label)
                                <flux:radio value="{{ $val }}" label="{{ $label }}" />
                            @endforeach
                        </flux:radio.group>
                    </div>

                    {{-- Quality --}}
                    @if ($format !== 'png')
                        <flux:field>
                            <div class="flex justify-between mb-2.5">
                                <flux:label>কোয়ালিটি</flux:label>
                                <flux:label class="!font-bold">
                                    {{ $quality }}%
                                </flux:label>
                            </div>
                            {{-- রেঞ্জ স্লাইডারের জন্য নেটিভ ইনপুট রাখা হয়েছে, কারণ Flux এ ডিফল্ট রেঞ্জ স্লাইডার নেই --}}
                            <input type="range" min="10" max="100" step="5"
                                wire:model.live="quality"
                                class="w-full h-1.5 bg-zinc-400/25 rounded-full appearance-none cursor-pointer accent-indigo-400" />
                            <div class="flex justify-between text-xs text-zinc-400 mt-1">
                                <span>Low</span>
                                <span>Heigh</span>
                            </div>
                        </flux:field>
                    @endif

                    {{-- Resize --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <flux:label>রিসাইজ</flux:label>
                            <flux:button variant="ghost" size="sm" wire:click="resetSize"
                                class="!text-xs !py-0.5 !h-auto">
                                রিসেট
                            </flux:button>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <flux:input type="number" wire:model.live.debounce.500ms="outputWidth"
                                placeholder="প্রস্থ {{ $originalWidth ?? '' }}" />
                            <flux:input type="number" wire:model.live.debounce.500ms="outputHeight"
                                placeholder="উচ্চতা {{ $originalHeight ?? '' }}" />
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach ([[100, '১০০%'], [75, '৭৫%'], [50, '৫০%'], [25, '২৫%'], [1920, 'FHD'], [800, 'Web'], [400, 'Thumb']] as [$val, $label])
                                <flux:button size="sm" variant="subtle" class="!px-2.5 !py-1 !text-xs !h-auto"
                                    wire:click="{{ is_int($val) && $val <= 100 ? 'resizeByPercent(' . $val . ')' : '$set(\'outputWidth\', ' . $val . ')' }}">
                                    {{ $label }}
                                </flux:button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Single Download Button Area --}}
                <div class="px-4 pb-4">
                    <div class="flex items-center gap-2 rounded-2xl">

                        {{-- Inline Format Selector --}}
                        <div class="flex-1 grid grid-cols-3 gap-0.5">
                            @foreach (self::FORMATS as $val => $label)
                                <flux:button type="button" wire:click="$set('format', '{{ $val }}')"
                                    variant="{{ $format === $val ? 'filled' : 'ghost' }}" size="sm"
                                    class="w-full !rounded-xl !text-xs !h-auto py-2">
                                    {{ $label }}
                                </flux:button>
                            @endforeach
                        </div>

                        {{-- Download Button --}}
                        <flux:button wire:click="download" variant="primary" class="!rounded-xl shrink-0 px-5"
                            icon="arrow-down-tray">
                            ডাউনলোড
                            @if ($compressedSizeBytes)
                                <span
                                    class="opacity-80 text-xs ml-1">{{ $this->formatBytes($compressedSizeBytes) }}</span>
                            @endif
                        </flux:button>
                    </div>

                    @if ($this->savingsPercentage > 0)
                        <p class="text-center text-xs text-emerald-600 dark:text-emerald-400 mt-2 font-medium">
                            মূল ফাইলের চেয়ে {{ $this->savingsPercentage }}% ছোট
                        </p>
                    @endif
                </div>
            </flux:card>
        </div>
    @endif
    <flux:separator class="mt-16 mb-6" />
    {{-- ═══════════════ SEO & INSTRUCTIONS SECTION ═══════════════ --}}
    <div class="">
        <div class="text-center mb-20">
            <h2 class="text-xl md:text-2xl font-bold text-zinc-900 dark:text-white tracking-tight">
                সিভি ও চাকরির আবেদনের জন্য পারফেক্ট ছবি তৈরি করুন
            </h2>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 max-w-2xl mx-auto">
                যেকোনো ওয়েবসাইটে ছবি আপলোডের নির্দিষ্ট শর্ত (যেমন: রেজুলেশন, ফাইল সাইজ ও ফরম্যাট) এখন পূরণ করুন কোনো
                ঝামেলা ছাড়াই।
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-20">
            <flux:callout icon="briefcase" heading="সরকারি চাকরির আবেদন">
                <flux:callout.text>
                    টেলিটক বা বিপিএসসি (BPSC) ফর্মে ছবির নির্দিষ্ট মাপ <strong>৩০০x৩০০ পিক্সেল</strong> (সর্বোচ্চ ১০০KB)
                    এবং স্বাক্ষরের মাপ <strong>৩০০x৮০ পিক্সেল</strong> (সর্বোচ্চ ৬০KB) সহজেই সেট করুন।
                </flux:callout.text>
            </flux:callout>

            <flux:callout icon="document-text" heading="সিভি ও পোর্টফোলিও">
                <flux:callout.text>
                    প্রফেশনাল সিভি বা লিংকডইন (LinkedIn) প্রোফাইলের জন্য আপনার ছবিকে প্রয়োজন অনুযায়ী ক্রপ করে
                    <strong>১:১ (স্কয়ার)</strong> রেশিওতে কনভার্ট করুন এক ক্লিকেই।
                </flux:callout.text>
            </flux:callout>

            <flux:callout icon="arrows-pointing-in" heading="ফাইল সাইজ কমানো (Compress)">
                <flux:callout.text>
                    ছবির কোয়ালিটি ঠিক রেখে ফাইলের সাইজ (KB/MB) কমান। ভিসা ফর্ম বা ভার্সিটি অ্যাডমিশনে আপলোডের সাইজ লিমিট
                    নিয়ে আর কোনো চিন্তা নেই।
                </flux:callout.text>
            </flux:callout>
        </div>

        {{-- How to use --}}
        <flux:card>
            <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">কীভাবে ছবির সাইজ ও রেজুলেশন ঠিক করবেন?
            </h3>
            <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                <li class="flex items-start gap-4">
                    <flux:icon name="check-circle" class="size-5 text-emerald-500 shrink-0" />
                    <span><strong>১. ছবি আপলোড:</strong> প্রথমে আপনার মোবাইল বা কম্পিউটার থেকে ছবিটি সিলেক্ট
                        করুন।</span>
                </li>
                <li class="flex items-start gap-4">
                    <flux:icon name="check-circle" class="size-5 text-emerald-500 shrink-0" />
                    <span><strong>২. ক্রপ করুন (ঐচ্ছিক):</strong> ছবির অপ্রয়োজনীয় অংশ বাদ দিতে ক্রপ টুলটি ব্যবহার
                        করুন।</span>
                </li>
                <li class="flex items-start gap-4">
                    <flux:icon name="check-circle" class="size-5 text-emerald-500 shrink-0" />
                    <span><strong>৩. পিক্সেল সেট করুন:</strong> 'রিসাইজ' অপশনে গিয়ে আপনার প্রয়োজনীয় প্রস্থ (Width) এবং
                        উচ্চতা (Height) দিন (যেমন: 300x300)।</span>
                </li>
                <li class="flex items-start gap-4">
                    <flux:icon name="check-circle" class="size-5 text-emerald-500 shrink-0" />
                    <span><strong>৪. কোয়ালিটি ও ফরম্যাট:</strong> ফাইলের সাইজ কমাতে কোয়ালিটি স্লাইডার অ্যাডজাস্ট করুন
                        এবং নির্দিষ্ট ফরম্যাট (JPG, PNG, WebP) নির্বাচন করে ডাউনলোড করুন।</span>
                </li>
            </ul>
        </flux:card>
    </div>
</div>

@script
    <script>
        Alpine.data('studioApp', () => ({
            cropper: null,

            init() {
                // Image already loaded from cache?
                this.$nextTick(() => {
                    const image = this.$refs.cropTarget;
                    if (image && image.complete && image.naturalWidth > 0) {
                        this.initCropper();
                    }
                });

                // Re-init when Livewire morphs the img
                Livewire.hook('morph.updated', ({
                    el
                }) => {
                    if (el === this.$refs.cropTarget) {
                        if (el.complete && el.naturalWidth > 0) {
                            this.initCropper();
                        }
                    }
                });
            },

            initCropper() {
                const image = this.$refs.cropTarget;
                if (!image || !image.naturalWidth) return;

                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }

                this.cropper = new Cropper(image, {
                    viewMode: 1,
                    dragMode: 'crop',
                    autoCropArea: 0.9,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: true,
                    background: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: true,
                    responsive: true,
                    checkOrientation: true,
                    minContainerHeight: 200,
                });
            },

            setRatio(ratio) {
                if (this.cropper) {
                    this.cropper.setAspectRatio(ratio === 'NaN' ? NaN : ratio);
                }
            },

            applyCrop() {
                if (!this.cropper) return;

                const d = this.cropper.getData(true);
                if (d.width < 1 || d.height < 1) return;

                // Livewire 4: $wire দিয়ে call করো
                $wire.setCropData(
                    Math.round(d.x),
                    Math.round(d.y),
                    Math.round(d.width),
                    Math.round(d.height)
                );
            }
        }));
    </script>
@endscript
