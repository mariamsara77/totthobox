{{-- resources/views/livewire/website/file-converter/image.blade.php --}}
<?php

use Livewire\Component;

new class extends Component {}; ?>

{{-- ═══════════════════════════════════════════════════════════════
IMAGE CONVERTER – Client-side
SEO Optimized · AdSense Policy Compliant · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<main class="mx-auto max-w-2xl space-y-6">

    <x-seo title="Free Online Image Converter | JPG, PNG, WEBP, SVG, GIF"
        description="Convert image files online fast, free, and securely. Easily convert JPG, PNG, WebP, GIF, SVG, BMP, and more to any format. No upload required — everything runs in your browser."
        keywords="image converter, jpg to png, webp converter, png to jpg, convert image online, free image converter, webp to png, avif converter, bmp to png"
        image="https://play-lh.googleusercontent.com/_tyzzuKkf5yTkzyFzsBXMaPShBNgJFSueaCX9U6lS-pvAcAbIHVX5ZKUmQ5lN5SZCfIOVRPQRjgwd2rN1qhndLI=w240-h480-rw" />

    <div x-data="imageConverter()" class="space-y-6">

        {{-- ══════════════════════════════════════
        Page Header (Only one H1)
        ══════════════════════════════════════ --}}
        <header class="text-center space-y-2">
            <flux:badge color="zinc" variant="pill" icon="photo" size="sm">
                Universal Image Converter
            </flux:badge>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                Image Converter
            </h1>
            <p class="text-base text-zinc-500 dark:text-zinc-400">
                PNG, JPG, WEBP, AVIF, ICO, BMP, SVG — সব ব্রাউজারেই কনভার্ট হবে, কোনো সার্ভার লোড নেই।
            </p>
        </header>

        {{-- ══════════════════════════════════════
        Converter Tool
        ══════════════════════════════════════ --}}
        <section aria-labelledby="converter-heading">
            <h2 id="converter-heading" class="sr-only">Image Converter Tool</h2>

            {{-- STEP 1: Drop zone --}}
            <div @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                @drop.prevent="dragging = false; handleFiles($event.dataTransfer.files)"
                :class="dragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/30 scale-[1.01]' :
                    'border-zinc-300 dark:border-zinc-700'"
                class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-8 text-center transition-all duration-200 sm:p-12"
                x-show="step === 'idle'" role="button" tabindex="0"
                aria-label="Drag and drop an image here or click to select a file"
                @keydown.enter="$refs.fileInput.click()">
                <div class="mb-4 flex size-14 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-950/40 sm:size-16"
                    aria-hidden="true">
                    <flux:icon name="photo" class="size-7 text-blue-500 sm:size-8" />
                </div>
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">ইমেজ এখানে ড্র্যাগ &amp; ড্রপ করো</p>
                <p class="mt-1 text-xs text-zinc-400">অথবা নিচে ক্লিক করে বেছে নাও</p>
                <input type="file" accept="image/png,image/jpeg,image/webp,image/bmp,image/avif,image/svg+xml,.ico"
                    x-ref="fileInput" class="hidden" @change="handleFiles($event.target.files)"
                    aria-label="Select an image file">
                <flux:button variant="primary" class="mt-4" x-on:click="$refs.fileInput.click()">
                    ফাইল বেছে নাও
                </flux:button>
                <p class="mt-3 text-xs text-zinc-400">সর্বোচ্চ ২৫ MB</p>
            </div>

            {{-- STEP 2: Configure + preview original --}}
            <div x-show="step === 'ready'" x-cloak x-transition
                class="grid gap-6 rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700 sm:p-6 md:grid-cols-2">
                <div class="space-y-3">
                    <div
                        class="overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                        <img :src="previewUrl" :alt="'Original image: ' + fileName"
                            class="mx-auto w-full object-contain" style="max-height:260px">
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                        <div class="flex items-center gap-4">
                            <flux:badge color="blue" x-text="sourceFormat.toUpperCase()"></flux:badge>
                            <span class="max-w-[160px] truncate text-zinc-500 sm:max-w-[220px]"
                                x-text="fileName"></span>
                        </div>
                        <span class="text-xs text-zinc-400" x-text="fileSizeLabel"></span>
                    </div>
                    <flux:button variant="ghost" size="sm" x-on:click="reset()" aria-label="Choose another image">
                        অন্য ইমেজ বেছে নাও
                    </flux:button>
                </div>

                <div class="space-y-4">
                    <div>
                        <flux:label>কোন ফরম্যাটে কনভার্ট করবে?</flux:label>
                        <div class="mt-2 grid grid-cols-3 gap-4" role="group" aria-label="Select target format">
                            <template x-for="fmt in availableFormats" :key="fmt">
                                <button type="button" x-on:click="targetFormat = fmt"
                                    :class="targetFormat === fmt ?
                                        'bg-blue-600 text-white border-blue-600 shadow-sm' :
                                        'border-zinc-300 text-zinc-600 hover:border-blue-400 hover:text-blue-600 dark:border-zinc-700 dark:text-zinc-300'"
                                    class="rounded-xl border px-2 py-2 text-xs font-semibold uppercase transition-all duration-200 sm:text-sm"
                                    :aria-pressed="targetFormat === fmt"
                                    :aria-label="'Convert to ' + fmt.toUpperCase()">
                                    <span x-text="fmt"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <template x-if="['jpg', 'webp', 'avif'].includes(targetFormat)">
                        <div>
                            <div class="flex items-center justify-between">
                                <flux:label>Quality</flux:label>
                                <span class="text-xs font-medium text-zinc-500"
                                    x-text="Math.round(quality * 100) + '%'"></span>
                            </div>
                            <input type="range" min="0.4" max="1" step="0.05" x-model.number="quality"
                                class="mt-1 w-full accent-blue-600" aria-label="Image quality">
                        </div>
                    </template>

                    <flux:button variant="primary" class="w-full" x-on:click="convert()" aria-label="Convert image now">
                        <span class="flex items-center justify-center gap-4">
                            <flux:icon name="arrow-path-rounded-square" variant="micro" aria-hidden="true" />
                            Convert Now
                        </span>
                    </flux:button>

                    <p class="text-xs leading-relaxed text-zinc-400">
                        GIF শুধু input হিসেবে সাপোর্টেড, output হিসেবে না — LZW এনকোডিং ব্রাউজারে unreliable।
                    </p>
                </div>
            </div>

            {{-- STEP 3: Converting --}}
            <div x-show="step === 'converting'" x-cloak x-transition
                class="flex flex-col items-center justify-center rounded-2xl border border-zinc-200 p-10 text-center dark:border-zinc-700 sm:p-16"
                role="status" aria-live="polite">
                <div class="relative mb-5 flex size-16 items-center justify-center" aria-hidden="true">
                    <span
                        class="absolute inset-0 animate-spin rounded-full border-4 border-zinc-200 border-t-blue-600 dark:border-zinc-700"></span>
                    <flux:icon name="photo" class="size-6 text-blue-500" />
                </div>
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Converting your image...</p>
                <p class="mt-1 text-xs text-zinc-400"
                    x-text="`${sourceFormat.toUpperCase()} → ${targetFormat.toUpperCase()}`"></p>
            </div>

            {{-- STEP 4: Done --}}
            <div x-show="step === 'done'" x-cloak x-transition
                class="space-y-5 rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700 sm:p-6">
                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                    <flux:icon name="check-circle" variant="micro" aria-hidden="true" />
                    <span class="text-sm font-medium">Conversion Complete</span>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <p class="text-xs font-medium text-zinc-400">Before</p>
                        <div
                            class="overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                            <img :src="previewUrl" :alt="'Original image before conversion: ' + fileName"
                                class="mx-auto w-full object-contain" style="max-height:220px">
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <flux:badge color="zinc" x-text="sourceFormat.toUpperCase()"></flux:badge>
                            <span class="text-zinc-400" x-text="fileSizeLabel"></span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-xs font-medium text-zinc-400">After</p>
                        <div
                            class="overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/40 dark:border-emerald-900 dark:bg-emerald-950/20">
                            <img :src="resultPreviewUrl" :alt="'Converted image: ' + resultFilename"
                                class="mx-auto w-full object-contain" style="max-height:220px">
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <flux:badge color="lime" x-text="targetFormat.toUpperCase()"></flux:badge>
                            <span class="text-zinc-400" x-text="resultSizeLabel"></span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4 sm:flex-row">
                    <flux:button variant="primary" class="flex-1" x-on:click="downloadResult()"
                        aria-label="Download converted image">
                        <span class="flex items-center justify-center gap-4">
                            <flux:icon name="arrow-down-tray" variant="micro" aria-hidden="true" />
                            <span x-text="`Download .${targetFormat}`"></span>
                        </span>
                    </flux:button>
                    <flux:button variant="ghost" class="flex-1" x-on:click="reset()"
                        aria-label="Convert a new image">
                        নতুন ইমেজ কনভার্ট করো
                    </flux:button>
                </div>

                <p class="text-center text-xs text-zinc-400" x-text="resultFilename"></p>
            </div>

            <canvas x-ref="canvas" class="hidden" aria-hidden="true"></canvas>
        </section>

        {{-- ══════════════════════════════════════
        Informative Content (AdSense + SEO)
        ══════════════════════════════════════ --}}
        <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-converter">
            <h2 id="about-converter" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
                ফ্রি অনলাইন ইমেজ কনভার্টার
            </h2>
            <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
                <p>
                    <strong>JPG, PNG, WebP, AVIF, BMP, ICO এবং SVG</strong> সহ জনপ্রিয় ফরম্যাটগুলোর মধ্যে
                    ইমেজ কনভার্ট করুন সম্পূর্ণ ব্রাউজারেই। কোনো ফাইল সার্ভারে আপলোড হয় না — আপনার ইমেজ
                    আপনার ডিভাইসেই নিরাপদ থাকে।
                </p>
                <p>
                    শুধু ড্র্যাগ অ্যান্ড ড্রপ করুন বা ফাইল সিলেক্ট করুন, টার্গেট ফরম্যাট বেছে নিন,
                    প্রয়োজন হলে কোয়ালিটি অ্যাডজাস্ট করুন, আর সাথে সাথে কনভার্টেড ইমেজ ডাউনলোড করুন।
                    সর্বোচ্চ ২৫ MB পর্যন্ত ফাইল সাপোর্ট করে।
                </p>
            </div>
        </section>

        {{-- ══════════════════════════════════════
        FAQ Section
        ══════════════════════════════════════ --}}
        <section class="space-y-3" aria-labelledby="faq-heading">
            <h2 id="faq-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
                প্রায়শই জিজ্ঞাসিত প্রশ্ন
            </h2>

            <div class="space-y-2">
                <details class="group rounded-xl border border-zinc-400/25 overflow-hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <span>এই ইমেজ কনভার্টার কি ফ্রি?</span>
                        <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                            aria-hidden="true" />
                    </summary>
                    <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        হ্যাঁ। টুলটি সম্পূর্ণ ফ্রি। কোনো রেজিস্ট্রেশন, কোনো ওয়াটারমার্ক বা কনভার্সনের কোনো সীমা নেই।
                    </div>
                </details>

                <details class="group rounded-xl border border-zinc-400/25 overflow-hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <span>আমার ইমেজ কি সার্ভারে আপলোড হয়?</span>
                        <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                            aria-hidden="true" />
                    </summary>
                    <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        না। সব কনভার্সন আপনার ব্রাউজারেই HTML5 Canvas API ব্যবহার করে হয়।
                        আপনার ফাইল কখনোই ডিভাইস থেকে বের হয় না, ফলে সম্পূর্ণ প্রাইভেসি নিশ্চিত থাকে।
                    </div>
                </details>

                <details class="group rounded-xl border border-zinc-400/25 overflow-hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <span>কোন কোন ফরম্যাট সাপোর্টেড?</span>
                        <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                            aria-hidden="true" />
                    </summary>
                    <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        আপনি PNG, JPG, WebP, AVIF, ICO, BMP এবং SVG ফরম্যাটের মধ্যে কনভার্ট করতে পারবেন।
                        GIF শুধু ইনপুট হিসেবে সাপোর্টেড (আউটপুট GIF ব্রাউজারের সীমাবদ্ধতার কারণে সাপোর্টেড নয়)।
                    </div>
                </details>

                <details class="group rounded-xl border border-zinc-400/25 overflow-hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <span>সর্বোচ্চ ফাইল সাইজ কত?</span>
                        <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                            aria-hidden="true" />
                    </summary>
                    <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        সুপারিশকৃত সর্বোচ্চ সাইজ ২৫ MB। খুব বড় ইমেজ হলে আপনার ডিভাইসের পারফরম্যান্স অনুযায়ী
                        প্রসেসিংয়ে একটু বেশি সময় লাগতে পারে।
                    </div>
                </details>
            </div>
        </section>

    </div>
</main>

@script
    <script>
        Alpine.data('imageConverter', () => ({
            step: 'idle', // idle | ready | converting | done
            dragging: false,
            previewUrl: null,
            resultPreviewUrl: null,
            resultBlob: null,
            resultFilename: '',
            resultSizeLabel: '',
            fileName: '',
            fileSizeLabel: '',
            sourceFormat: '',
            targetFormat: '',
            quality: 0.92,
            ALL_FORMATS: ['png', 'jpg', 'webp', 'avif', 'ico', 'bmp', 'svg'],
            availableFormats: [],

            init() {
                // Feature-detect AVIF encode support; skip it in the list if the browser can't produce it.
                const testCanvas = document.createElement('canvas');
                testCanvas.width = 1;
                testCanvas.height = 1;
                testCanvas.toBlob((blob) => {
                    if (!blob || blob.type !== 'image/avif') {
                        this.ALL_FORMATS = this.ALL_FORMATS.filter(f => f !== 'avif');
                    }
                }, 'image/avif');
            },

            detectFormat(file) {
                const ext = file.name.split('.').pop().toLowerCase();
                return ext === 'jpeg' ? 'jpg' : ext;
            },

            formatBytes(bytes) {
                if (bytes < 1024) return `${bytes} B`;
                if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
                return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
            },

            handleFiles(fileList) {
                const file = fileList[0];
                if (!file) return;

                this.sourceFormat = this.detectFormat(file);
                this.fileName = file.name;
                this.fileSizeLabel = this.formatBytes(file.size);
                this.availableFormats = this.ALL_FORMATS.filter(f => f !== this.sourceFormat && f !== 'gif');
                this.targetFormat = this.availableFormats[0];

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewUrl = e.target.result;
                    this.step = 'ready';
                };
                reader.readAsDataURL(file);
            },

            reset() {
                if (this.resultPreviewUrl) URL.revokeObjectURL(this.resultPreviewUrl);
                this.step = 'idle';
                this.previewUrl = null;
                this.resultPreviewUrl = null;
                this.resultBlob = null;
                this.resultFilename = '';
                this.resultSizeLabel = '';
                this.$refs.fileInput.value = '';
            },

            // Filenames are always branded: totthobox-{original-name}.{ext}
            buildFilename(baseName, ext) {
                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                const timestamp =
                    `${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;

                return `Totthobox_image_converter_${timestamp}.${ext}`;
            },

            async convert() {
                this.step = 'converting';

                // Let the UI paint the spinner before the (synchronous-ish) canvas work runs.
                await new Promise((r) => setTimeout(r, 50));

                try {
                    const canvas = this.$refs.canvas;
                    const img = new Image();

                    await new Promise((resolve, reject) => {
                        img.onload = resolve;
                        img.onerror = reject;
                        img.src = this.previewUrl;
                    });

                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    // Flatten transparency with white when targeting formats with no alpha channel.
                    if (['jpg', 'bmp'].includes(this.targetFormat)) {
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                    }
                    ctx.drawImage(img, 0, 0);

                    const baseName = this.fileName.replace(/\.[^/.]+$/, '');
                    let blob;

                    switch (this.targetFormat) {
                        case 'png':
                            blob = await this.canvasToBlob(canvas, 'image/png');
                            break;
                        case 'jpg':
                            blob = await this.canvasToBlob(canvas, 'image/jpeg', this.quality);
                            break;
                        case 'webp':
                            blob = await this.canvasToBlob(canvas, 'image/webp', this.quality);
                            break;
                        case 'avif':
                            blob = await this.canvasToBlob(canvas, 'image/avif', this.quality);
                            break;
                        case 'ico':
                            blob = await this.canvasToIco(canvas);
                            break;
                        case 'bmp':
                            blob = this.canvasToBmp(canvas);
                            break;
                        case 'svg':
                            blob = this.canvasToSvg(canvas);
                            break;
                    }

                    this.resultBlob = blob;
                    this.resultFilename = this.buildFilename(baseName, this.targetFormat);
                    this.resultSizeLabel = this.formatBytes(blob.size);
                    this.resultPreviewUrl = URL.createObjectURL(blob);
                    this.step = 'done';
                } catch (e) {
                    this.step = 'ready';
                    alert('Conversion failed. এই ফরম্যাটটি হয়তো আপনার ব্রাউজারে সাপোর্টেড না।');
                }
            },

            downloadResult() {
                if (!this.resultBlob) return;
                const url = URL.createObjectURL(this.resultBlob);
                const a = document.createElement('a');
                a.href = url;
                a.download = this.resultFilename;
                a.click();
                URL.revokeObjectURL(url);
            },

            canvasToBlob(canvas, mime, quality) {
                return new Promise((resolve, reject) => {
                    canvas.toBlob((blob) => blob ? resolve(blob) : reject(new Error('encode failed')),
                        mime, quality);
                });
            },

            async canvasToIco(canvas) {
                const pngBlob = await this.canvasToBlob(canvas, 'image/png');
                const pngBytes = new Uint8Array(await pngBlob.arrayBuffer());
                const w = canvas.width >= 256 ? 0 : canvas.width;
                const h = canvas.height >= 256 ? 0 : canvas.height;

                const buffer = new ArrayBuffer(22 + pngBytes.length);
                const view = new DataView(buffer);
                view.setUint16(0, 0, true);
                view.setUint16(2, 1, true);
                view.setUint16(4, 1, true);
                view.setUint8(6, w);
                view.setUint8(7, h);
                view.setUint8(8, 0);
                view.setUint8(9, 0);
                view.setUint16(10, 1, true);
                view.setUint16(12, 32, true);
                view.setUint32(14, pngBytes.length, true);
                view.setUint32(18, 22, true);
                new Uint8Array(buffer, 22).set(pngBytes);

                return new Blob([buffer], {
                    type: 'image/x-icon'
                });
            },

            canvasToBmp(canvas) {
                const ctx = canvas.getContext('2d');
                const w = canvas.width,
                    h = canvas.height;
                const data = ctx.getImageData(0, 0, w, h).data;
                const rowSize = Math.floor((24 * w + 31) / 32) * 4;
                const pixelArraySize = rowSize * h;
                const fileSize = 54 + pixelArraySize;

                const buffer = new ArrayBuffer(fileSize);
                const view = new DataView(buffer);
                view.setUint8(0, 0x42);
                view.setUint8(1, 0x4D);
                view.setUint32(2, fileSize, true);
                view.setUint32(10, 54, true);
                view.setUint32(14, 40, true);
                view.setInt32(18, w, true);
                view.setInt32(22, h, true);
                view.setUint16(26, 1, true);
                view.setUint16(28, 24, true);
                view.setUint32(34, pixelArraySize, true);

                let offset = 54;
                for (let y = h - 1; y >= 0; y--) {
                    for (let x = 0; x < w; x++) {
                        const idx = (y * w + x) * 4;
                        view.setUint8(offset++, data[idx + 2]);
                        view.setUint8(offset++, data[idx + 1]);
                        view.setUint8(offset++, data[idx]);
                    }
                    offset += rowSize - w * 3;
                }

                return new Blob([buffer], {
                    type: 'image/bmp'
                });
            },

            canvasToSvg(canvas) {
                const dataUrl = canvas.toDataURL('image/png');
                const svg =
                    `<svg xmlns="http://www.w3.org/2000/svg" width="${canvas.width}" height="${canvas.height}" viewBox="0 0 ${canvas.width} ${canvas.height}"><image width="${canvas.width}" height="${canvas.height}" href="${dataUrl}"/></svg>`;
                return new Blob([svg], {
                    type: 'image/svg+xml'
                });
            },
        }));
    </script>
@endscript
