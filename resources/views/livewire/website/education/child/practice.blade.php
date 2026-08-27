<?php
use Livewire\Volt\Component;

new class extends Component {
    //
};
?>

<x-seo title="লেখা প্র্যাকটিস ও ড্রয়িং টুল - Totthobox"
    description="Totthobox-এর উন্নত ড্রয়িং এবং রাইটিং টুলের মাধ্যমে বাংলা ও ইংরেজি অক্ষর লেখা প্র্যাকটিস করুন। শিশুদের হাতের লেখা উন্নত করতে এবং ডিজিটাল ড্রয়িংয়ের জন্য সেরা প্ল্যাটফর্ম।"
    keywords="লেখা প্র্যাকটিস, হাতের লেখা শেখা, বাংলা অক্ষর ট্রেসিং, ডিজিটাল ড্রয়িং বোর্ড, Totthobox writing practice, online drawing tool bangla" />

<div class="max-w-7xl mx-auto space-y-4" x-data="modernDrawingApp()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">লেখা প্র্যাকটিস</flux:heading>
            <flux:text class="hidden sm:block">উন্নত ড্রয়িং এবং রাইটিং টুল</flux:text>
        </div>

        <div class="flex items-center gap-1">
            <flux:modal.trigger name="help">
                <flux:button variant="ghost" size="sm" icon="question-mark-circle" />
            </flux:modal.trigger>

            <flux:button variant="ghost" size="sm" icon="arrows-pointing-out" @click="toggleFullscreen()" />
        </div>
    </div>

    {{-- Canvas Area --}}
    <div id="myCanvas" class="relative border rounded-xl overflow-hidden bg-white dark:bg-zinc-900">

        <canvas id="drawing-canvas" x-ref="canvas" class="w-full h-[80vh] cursor-crosshair touch-none"
            @mousedown="startDrawing($event)" @mousemove="draw($event)" @mouseup="stopDrawing()"
            @mouseleave="stopDrawing()" @touchstart.prevent="startDrawing($event)" @touchmove.prevent="draw($event)"
            @touchend.prevent="stopDrawing()"></canvas>

        {{-- Guide Text Overlay --}}
        <div x-show="guideText" x-text="guideText"
            class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-10 text-zinc-400 z-10"
            x-bind:style="`font-size: ${Math.min(window.innerWidth * 0.35, 280)}px; font-family: 'Noto Serif Bengali', serif;`">
        </div>

        {{-- Floating Toolbar --}}
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20">
            <div class="p-2 rounded-2xl border border-zinc-400/25 bg-zinc-400">
                <div class="flex items-center gap-1">

                    {{-- Tools --}}
                    <template x-for="tool in tools" :key="tool.id">
                        <button type="button" @click="setActiveTool(tool.id)" x-bind:title="tool.name"
                            class="size-9 flex items-center justify-center rounded-lg transition-colors"
                            x-bind:class="activeTool === tool.id ? 'text-white bg-blue-600' :
                                'text-zinc-500 hover:bg-zinc-400/25'">
                            <template x-if="tool.id === 'pen'">
                                <svg class="size-4" fill="currentColor" viewBox="0 0 16 16">
                                    <path
                                        d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001" />
                                </svg>
                            </template>
                            <template x-if="tool.id === 'eraser'">
                                <svg class="size-4" fill="currentColor" viewBox="0 0 16 16">
                                    <path
                                        d="M8.086 2.207a2 2 0 0 1 2.828 0l3.879 3.879a2 2 0 0 1 0 2.828l-5.5 5.5A2 2 0 0 1 7.879 15H5.12a2 2 0 0 1-1.414-.586l-2.5-2.5a2 2 0 0 1 0-2.828zm.66 11.34L3.453 8.254 1.914 9.793a1 1 0 0 0 0 1.414l2.5 2.5a1 1 0 0 0 .707.293H7.88a1 1 0 0 0 .707-.293z" />
                                </svg>
                            </template>
                        </button>
                    </template>

                    <flux:separator vertical class="h-6 mx-0.5" />

                    {{-- History --}}
                    <flux:button size="sm" variant="ghost" icon="arrow-uturn-left" @click="undo()"
                        x-bind:disabled="historyIndex <= 0" title="Undo" />
                    <flux:button size="sm" variant="ghost" icon="arrow-uturn-right" @click="redo()"
                        x-bind:disabled="historyIndex >= drawingHistory.length - 1" title="Redo" />

                    <flux:separator vertical class="h-6 mx-0.5" />

                    {{-- Actions --}}
                    <flux:modal.trigger name="canvas-save-confirm">
                        <flux:button size="sm" variant="ghost" icon="arrow-down-tray" title="Save" />
                    </flux:modal.trigger>

                    <flux:modal.trigger name="canvas-clear-confirm">
                        <flux:button size="sm" variant="ghost" icon="trash" title="Clear" />
                    </flux:modal.trigger>

                    <flux:modal.trigger name="settings-writing-pad">
                        <flux:button size="sm" variant="ghost" icon="cog-6-tooth" title="Settings" />
                    </flux:modal.trigger>
                </div>
            </div>
        </div>
    </div>

    {{-- Settings Flyout --}}
    <flux:modal name="settings-writing-pad" variant="flyout" class="md:w-96">
        <div class="space-y-6">
            <flux:heading size="lg">সেটিংস</flux:heading>

            {{-- Color --}}
            <div class="space-y-3">
                <flux:heading size="sm">কলমের রঙ</flux:heading>
                <div class="flex items-center gap-2">
                    <input type="color" x-model="currentColor" class="size-10 rounded-lg cursor-pointer border" />
                    <div class="flex flex-wrap gap-2">
                        <template x-for="color in colorPresets" :key="color">
                            <button type="button" @click="currentColor = color" class="size-7 rounded-md border-2"
                                x-bind:style="`background-color: ${color}`"
                                x-bind:class="currentColor === color ? 'border-blue-500 scale-110' : 'border-zinc-300'"></button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Brush Size --}}
            <div class="space-y-2">
                <flux:heading size="sm">
                    ব্রাশের সাইজ: <span x-text="currentSize" class="text-blue-600"></span>px
                </flux:heading>
                <input type="range" x-model="currentSize" min="1" max="30"
                    class="w-full accent-blue-600" />
                <div class="flex justify-between">
                    <flux:text size="xs">চিকন</flux:text>
                    <flux:text size="xs">মাঝারি</flux:text>
                    <flux:text size="xs">মোটা</flux:text>
                </div>
            </div>

            {{-- Opacity --}}
            <div class="space-y-2">
                <flux:heading size="sm">
                    স্বচ্ছতা: <span x-text="Math.round(opacity * 100)" class="text-blue-600"></span>%
                </flux:heading>
                <input type="range" x-model="opacity" min="0.1" max="1" step="0.1"
                    class="w-full accent-blue-600" />
            </div>

            {{-- Practice Characters --}}
            <div class="space-y-3">
                <flux:heading size="sm">প্র্যাকটিস ক্যারেক্টার</flux:heading>
                <div class="space-y-2">
                    <template x-for="(category, index) in characterCategories" :key="index">
                        <div class="border rounded-lg overflow-hidden">
                            <button type="button" @click="toggleCategory(index)"
                                class="w-full flex justify-between items-center p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <span x-text="category.name"></span>
                                <div class="size-4 text-zinc-400">
                                    <svg x-show="!category.open" class="size-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <svg x-show="category.open" class="size-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                    </svg>
                                </div>
                            </button>
                            <div x-show="category.open" x-collapse class="p-3 grid grid-cols-6 gap-2 border-t">
                                <template x-for="char in category.characters" :key="char">
                                    <button type="button" @click="setGuideText(char)"
                                        class="h-9 flex items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 font-medium"
                                        x-text="char"></button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Paper Style --}}
            <div class="space-y-3">
                <flux:heading size="sm">পেপারের স্টাইল</flux:heading>
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="paper in paperStyles" :key="paper.id">
                        <button type="button" @click="setPaperStyle(paper.id)"
                            class="p-2 rounded-lg border-2 text-center"
                            x-bind:class="currentPaperStyle === paper.id ? 'border-blue-500 ring-1 ring-blue-500' :
                                'border-zinc-200 dark:border-zinc-700'">
                            <div class="h-10 rounded mb-1" x-bind:class="paper.class"></div>
                            <flux:text size="xs" x-text="paper.name"></flux:text>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Advanced --}}
            <div class="space-y-3">
                <flux:heading size="sm">অ্যাডভান্সড অপশন</flux:heading>
                <div class="space-y-2">
                    <flux:checkbox x-model="pressureSensitivity" label="প্রেসার সেনসিটিভিটি" />
                    <flux:checkbox x-model="smoothing" label="লাইন স্মুদিং" />
                    <flux:checkbox x-model="guideLines" label="গাইড লাইন দেখান" />
                </div>
            </div>
        </div>
    </flux:modal>

    {{-- Clear Confirm --}}
    <flux:modal name="canvas-clear-confirm" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ক্যানভাস মুছে ফেলবেন?</flux:heading>
                <flux:text class="mt-2">সব অঙ্কন মুছে যাবে। এই কাজটি পূর্বাবস্থায় ফেরানো যাবে না।</flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost" size="sm">বাতিল</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" size="sm" @click="clearCanvas()"
                    x-on:click="$flux.modal('canvas-clear-confirm').close()">
                    মুছে ফেলুন
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Save Options --}}
    <flux:modal name="canvas-save-confirm" class="min-w-[22rem]">
        <div class="space-y-6">
            <flux:heading size="lg">সংরক্ষণ অপশন</flux:heading>

            <flux:input type="text" x-model="fileName" label="ফাইলের নাম" />

            <flux:select x-model="saveFormat" label="ফরম্যাট">
                <option value="png">PNG (উচ্চ মান)</option>
                <option value="jpeg">JPEG (ছোট সাইজ)</option>
                <option value="webp">WebP (আধুনিক)</option>
            </flux:select>

            <div x-show="saveFormat === 'png'">
                <flux:checkbox x-model="transparentBackground" label="স্বচ্ছ ব্যাকগ্রাউন্ড" />
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost" size="sm">বাতিল</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" size="sm" @click="saveWithOptions()"
                    x-on:click="$flux.modal('canvas-save-confirm').close()">
                    সংরক্ষণ করুন
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Help Modal --}}
    <flux:modal name="help" class="min-w-[22rem] max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">সহায়তা ও শর্টকাট</flux:heading>

            <div>
                <flux:heading size="sm" class="mb-3">কীবোর্ড শর্টকাট</flux:heading>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:text size="sm">Undo</flux:text>
                        <flux:badge size="sm">Ctrl+Z</flux:badge>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:text size="sm">Redo</flux:text>
                        <flux:badge size="sm">Ctrl+Y</flux:badge>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:text size="sm">Save</flux:text>
                        <flux:badge size="sm">Ctrl+S</flux:badge>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:text size="sm">Clear</flux:text>
                        <flux:badge size="sm">Ctrl+Del</flux:badge>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:text size="sm">টুল পরিবর্তন</flux:text>
                        <flux:badge size="sm">E</flux:badge>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:text size="sm">সেটিংস</flux:text>
                        <flux:badge size="sm">S</flux:badge>
                    </div>
                </div>
            </div>

            <div>
                <flux:heading size="sm" class="mb-3">টিপস</flux:heading>
                <div class="space-y-2">
                    <flux:callout icon="information-circle">
                        লেখার গাইড হিসেবে সেটিংস থেকে প্র্যাকটিস ক্যারেক্টার বেছে নিন।
                    </flux:callout>
                    <flux:callout icon="information-circle">
                        বিভিন্ন পেপার স্টাইল দিয়ে আলাদা লেখার অভিজ্ঞতা নিন।
                    </flux:callout>
                    <flux:callout icon="information-circle">
                        গাইড অক্ষরের উপর ট্রেস করতে Opacity কমিয়ে নিন।
                    </flux:callout>
                    <flux:callout icon="information-circle">
                        স্টাইলাস ব্যবহার করলে Pressure Sensitivity চালু করুন।
                    </flux:callout>
                    <flux:callout icon="information-circle">
                        সঠিক এলাইনমেন্টের জন্য গাইড লাইন ব্যবহার করুন।
                    </flux:callout>
                </div>
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button size="sm">বুঝেছি!</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Minimal required styles (paper patterns only) --}}
    <style>
        [x-cloak] {
            display: none !important;
        }

        .bg-lined-paper {
            background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.12) 1px, transparent 1px);
            background-size: 100% 24px;
        }

        .bg-grid-paper {
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .bg-graph-paper {
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.06) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.06) 1px, transparent 1px),
                linear-gradient(to right, rgba(0, 0, 0, 0.18) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.18) 1px, transparent 1px);
            background-size: 24px 24px, 24px 24px, 120px 120px, 120px 120px;
        }
    </style>

    <script>
        function modernDrawingApp() {
            return {
                canvas: null,
                ctx: null,
                isDrawing: false,
                lastX: 0,
                lastY: 0,

                currentColor: '#000000',
                currentSize: 5,
                opacity: 1,
                activeTool: 'pen',

                guideText: '',
                currentPaperStyle: 'blank',
                fileName: `Totthobox-writing-practice-${new Date().toISOString().slice(0, 10)}`,
                saveFormat: 'png',
                transparentBackground: false,

                pressureSensitivity: false,
                smoothing: true,
                guideLines: true,

                drawingHistory: [],
                historyIndex: -1,

                colorPresets: ['#000000', '#dc2626', '#2563eb', '#16a34a', '#ea580c', '#9333ea', '#64748b', '#ffffff'],

                tools: [{
                        id: 'pen',
                        name: 'Pen'
                    },
                    {
                        id: 'eraser',
                        name: 'Eraser'
                    }
                ],

                paperStyles: [{
                        id: 'blank',
                        name: 'Blank',
                        class: 'bg-white'
                    },
                    {
                        id: 'lined',
                        name: 'Lined',
                        class: 'bg-white bg-lined-paper'
                    },
                    {
                        id: 'grid',
                        name: 'Grid',
                        class: 'bg-white bg-grid-paper'
                    },
                    {
                        id: 'graph',
                        name: 'Graph',
                        class: 'bg-white bg-graph-paper'
                    },
                    {
                        id: 'yellow',
                        name: 'Yellow',
                        class: 'bg-yellow-50'
                    },
                    {
                        id: 'parchment',
                        name: 'Parchment',
                        class: 'bg-amber-50'
                    }
                ],

                characterCategories: [{
                        name: 'English Uppercase',
                        open: false,
                        characters: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')
                    },
                    {
                        name: 'English Lowercase',
                        open: false,
                        characters: 'abcdefghijklmnopqrstuvwxyz'.split('')
                    },
                    {
                        name: 'Numbers',
                        open: false,
                        characters: '0123456789'.split('')
                    },
                    {
                        name: 'Bangla Vowels (স্বরবর্ণ)',
                        open: false,
                        characters: 'অআইঈউঊঋএঐওঔ'.split('')
                    },
                    {
                        name: 'Bangla Consonants (ব্যঞ্জনবর্ণ)',
                        open: false,
                        characters: 'কখগঘঙচছজঝঞটঠডঢণতথদধনপফবভমযরলশষসহড়ঢ়য়'.split('')
                    },
                    {
                        name: 'Bangla Numbers',
                        open: false,
                        characters: '০১২৩৪৫৬৭৮৯'.split('')
                    }
                ],

                init() {
                    this.$nextTick(() => {
                        this.setupCanvas();
                        this.setupEventListeners();
                        this.clearCanvas();
                    });
                },

                setupCanvas() {
                    this.canvas = this.$refs.canvas;
                    this.ctx = this.canvas.getContext('2d');
                    this.resizeCanvas();
                },

                setupEventListeners() {
                    window.addEventListener('resize', () => this.resizeCanvas());
                    document.addEventListener('keydown', (e) => this.handleKeydown(e));
                    this.canvas.addEventListener('contextmenu', e => e.preventDefault());
                },

                resizeCanvas() {
                    const container = this.canvas.parentElement;
                    const dpr = window.devicePixelRatio || 1;
                    const imageData = this.canvas.toDataURL();

                    this.canvas.width = container.clientWidth * dpr;
                    this.canvas.height = container.clientHeight * dpr;
                    this.ctx.scale(dpr, dpr);
                    this.canvas.style.width = `${container.clientWidth}px`;
                    this.canvas.style.height = `${container.clientHeight}px`;

                    if (imageData && imageData !== 'data:,') {
                        const img = new Image();
                        img.onload = () => {
                            this.drawPaperBackground();
                            this.ctx.drawImage(img, 0, 0);
                        };
                        img.src = imageData;
                    } else {
                        this.drawPaperBackground();
                    }
                },

                startDrawing(e) {
                    this.isDrawing = true;
                    const pos = this.getPosition(e);
                    this.lastX = pos.x;
                    this.lastY = pos.y;
                    this.ctx.beginPath();
                    this.ctx.moveTo(this.lastX, this.lastY);
                    this.setDrawingStyle();
                },

                draw(e) {
                    if (!this.isDrawing) return;
                    const pos = this.getPosition(e);

                    if (this.smoothing) {
                        this.drawSmoothLine(this.lastX, this.lastY, pos.x, pos.y);
                    } else {
                        this.ctx.lineTo(pos.x, pos.y);
                        this.ctx.stroke();
                    }
                    this.lastX = pos.x;
                    this.lastY = pos.y;
                },

                stopDrawing() {
                    if (this.isDrawing) {
                        this.isDrawing = false;
                        this.ctx.globalCompositeOperation = 'source-over';
                        this.saveState();
                    }
                },

                drawSmoothLine(x1, y1, x2, y2) {
                    const cp1x = x1 + (x2 - x1) / 3;
                    const cp1y = y1 + (y2 - y1) / 3;
                    const cp2x = x1 + 2 * (x2 - x1) / 3;
                    const cp2y = y1 + 2 * (y2 - y1) / 3;
                    this.ctx.bezierCurveTo(cp1x, cp1y, cp2x, cp2y, x2, y2);
                    this.ctx.stroke();
                },

                setDrawingStyle() {
                    if (this.activeTool === 'eraser') {
                        this.ctx.globalCompositeOperation = 'destination-out';
                        this.ctx.strokeStyle = 'rgba(255,255,255,1)';
                    } else {
                        this.ctx.globalCompositeOperation = 'source-over';
                        this.ctx.strokeStyle = this.hexToRgba(this.currentColor, this.opacity);
                    }
                    this.ctx.lineWidth = this.currentSize;
                    this.ctx.lineCap = 'round';
                    this.ctx.lineJoin = 'round';
                },

                getPosition(e) {
                    const rect = this.canvas.getBoundingClientRect();
                    const dpr = window.devicePixelRatio || 1;

                    if (e.type.includes('touch')) {
                        const touch = e.touches[0] || e.changedTouches[0];
                        return {
                            x: (touch.clientX - rect.left) * (this.canvas.width / rect.width) / dpr,
                            y: (touch.clientY - rect.top) * (this.canvas.height / rect.height) / dpr
                        };
                    }
                    return {
                        x: (e.clientX - rect.left) * (this.canvas.width / rect.width) / dpr,
                        y: (e.clientY - rect.top) * (this.canvas.height / rect.height) / dpr
                    };
                },

                hexToRgba(hex, alpha) {
                    const r = parseInt(hex.slice(1, 3), 16);
                    const g = parseInt(hex.slice(3, 5), 16);
                    const b = parseInt(hex.slice(5, 7), 16);
                    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                },

                setActiveTool(toolId) {
                    this.activeTool = toolId;
                },

                toggleCategory(index) {
                    this.characterCategories[index].open = !this.characterCategories[index].open;
                },

                setGuideText(char) {
                    this.guideText = char;
                },

                setPaperStyle(styleId) {
                    this.currentPaperStyle = styleId;
                    this.clearCanvas();
                },

                drawPaperBackground() {
                    const width = this.canvas.width / (window.devicePixelRatio || 1);
                    const height = this.canvas.height / (window.devicePixelRatio || 1);

                    this.ctx.fillStyle = this.getPaperColor();
                    this.ctx.fillRect(0, 0, width, height);

                    if (!this.guideLines) return;

                    switch (this.currentPaperStyle) {
                        case 'lined':
                            this.drawLinedPattern(width, height);
                            break;
                        case 'grid':
                            this.drawGridPattern(width, height);
                            break;
                        case 'graph':
                            this.drawGraphPattern(width, height);
                            break;
                    }
                },

                getPaperColor() {
                    return {
                        'blank': '#ffffff',
                        'lined': '#ffffff',
                        'grid': '#ffffff',
                        'graph': '#ffffff',
                        'yellow': '#fefce8',
                        'parchment': '#fffbeb'
                    } [this.currentPaperStyle] || '#ffffff';
                },

                drawLinedPattern(width, height) {
                    const spacing = 30;
                    this.ctx.strokeStyle = 'rgba(0,0,0,0.1)';
                    this.ctx.lineWidth = 1;
                    for (let y = spacing; y < height; y += spacing) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(0, y);
                        this.ctx.lineTo(width, y);
                        this.ctx.stroke();
                    }
                },

                drawGridPattern(width, height) {
                    const size = 25;
                    this.ctx.strokeStyle = 'rgba(0,0,0,0.1)';
                    this.ctx.lineWidth = 1;
                    for (let x = size; x < width; x += size) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(x, 0);
                        this.ctx.lineTo(x, height);
                        this.ctx.stroke();
                    }
                    for (let y = size; y < height; y += size) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(0, y);
                        this.ctx.lineTo(width, y);
                        this.ctx.stroke();
                    }
                },

                drawGraphPattern(width, height) {
                    const sub = 25,
                        main = 125;
                    this.ctx.strokeStyle = 'rgba(0,0,0,0.05)';
                    this.ctx.lineWidth = 1;
                    for (let x = sub; x < width; x += sub) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(x, 0);
                        this.ctx.lineTo(x, height);
                        this.ctx.stroke();
                    }
                    for (let y = sub; y < height; y += sub) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(0, y);
                        this.ctx.lineTo(width, y);
                        this.ctx.stroke();
                    }
                    this.ctx.strokeStyle = 'rgba(0,0,0,0.2)';
                    this.ctx.lineWidth = 1.5;
                    for (let x = main; x < width; x += main) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(x, 0);
                        this.ctx.lineTo(x, height);
                        this.ctx.stroke();
                    }
                    for (let y = main; y < height; y += main) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(0, y);
                        this.ctx.lineTo(width, y);
                        this.ctx.stroke();
                    }
                },

                saveState() {
                    if (this.historyIndex < this.drawingHistory.length - 1) {
                        this.drawingHistory = this.drawingHistory.slice(0, this.historyIndex + 1);
                    }
                    this.drawingHistory.push(this.canvas.toDataURL());
                    this.historyIndex = this.drawingHistory.length - 1;
                    if (this.drawingHistory.length > 50) {
                        this.drawingHistory.shift();
                        this.historyIndex--;
                    }
                },

                undo() {
                    if (this.historyIndex > 0) {
                        this.historyIndex--;
                        this.restoreCanvas();
                    }
                },

                redo() {
                    if (this.historyIndex < this.drawingHistory.length - 1) {
                        this.historyIndex++;
                        this.restoreCanvas();
                    }
                },

                restoreCanvas() {
                    if (this.drawingHistory.length === 0) return;
                    const img = new Image();
                    img.onload = () => {
                        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                        this.drawPaperBackground();
                        this.ctx.drawImage(img, 0, 0);
                    };
                    img.src = this.drawingHistory[this.historyIndex];
                },

                clearCanvas() {
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    this.drawPaperBackground();
                    this.saveState();
                },

                saveWithOptions() {
                    let mimeType = 'image/png',
                        quality = 1;
                    if (this.saveFormat === 'jpeg') {
                        mimeType = 'image/jpeg';
                        quality = 0.9;
                    }
                    if (this.saveFormat === 'webp') {
                        mimeType = 'image/webp';
                        quality = 0.9;
                    }

                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = this.canvas.width;
                    tempCanvas.height = this.canvas.height;
                    const tempCtx = tempCanvas.getContext('2d');

                    if (this.transparentBackground && this.saveFormat === 'png') {
                        tempCtx.clearRect(0, 0, tempCanvas.width, tempCanvas.height);
                    } else {
                        tempCtx.fillStyle = '#ffffff';
                        tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
                    }
                    tempCtx.drawImage(this.canvas, 0, 0);

                    const link = document.createElement('a');
                    link.download = `${this.fileName}.${this.saveFormat}`;
                    link.href = tempCanvas.toDataURL(mimeType, quality);
                    link.click();
                },

                toggleFullscreen() {
                    const el = document.getElementById('myCanvas');
                    if (!document.fullscreenElement) {
                        el.requestFullscreen?.().catch(() => {});
                    } else {
                        document.exitFullscreen();
                    }
                },

                handleKeydown(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
                        e.preventDefault();
                        this.undo();
                    }
                    if (((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'Z') ||
                        ((e.ctrlKey || e.metaKey) && e.key === 'y')) {
                        e.preventDefault();
                        this.redo();
                    }
                    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                        e.preventDefault();
                        $flux.modal('canvas-save-confirm').show();
                    }
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Delete') {
                        e.preventDefault();
                        $flux.modal('canvas-clear-confirm').show();
                    }
                    if (e.key.toLowerCase() === 'e' && !e.ctrlKey && !e.metaKey) {
                        e.preventDefault();
                        const idx = this.tools.findIndex(t => t.id === this.activeTool);
                        this.setActiveTool(this.tools[(idx + 1) % this.tools.length].id);
                    }
                    if (e.key.toLowerCase() === 's' && !e.ctrlKey && !e.metaKey) {
                        e.preventDefault();
                        $flux.modal('settings-writing-pad').show();
                    }
                }
            };
        }
    </script>
</div>
