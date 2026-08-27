<?php

use App\Enums\ConversionStatus;
use App\Enums\ConversionType;
use App\Models\ConversionTask;
use App\Services\DataConverterService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    #[Validate('nullable|file|mimes:json,xml,yaml,yml,csv,txt|max:5120')]
    public $file = null;

    #[Validate('nullable|string|max:200000')]
    public string $rawInput = '';

    public string $sourceFormat = '';
    public array $targetOptions = [];
    public string $targetFormat = '';

    public ?string $resultContent = null;
    public ?string $resultFilename = null;
    public ?string $errorMessage = null;

    public function updatedFile(): void
    {
        $this->sourceFormat = DataConverterService::normalize($this->file->getClientOriginalExtension());
        $this->targetOptions = DataConverterService::availableTargets($this->sourceFormat);
        $this->targetFormat = $this->targetOptions[0] ?? '';
        $this->rawInput = file_get_contents($this->file->getRealPath());
        $this->resultContent = null;
    }

    public function setSourceFormatManually(string $format): void
    {
        $this->sourceFormat = $format;
        $this->targetOptions = DataConverterService::availableTargets($format);
        $this->targetFormat = $this->targetOptions[0] ?? '';
        $this->resultContent = null;
    }

    public function convert(DataConverterService $service): void
    {
        $this->errorMessage = null;
        $this->validate([
            'rawInput' => 'required|string',
            'sourceFormat' => 'required|in:json,xml,yaml,csv',
            'targetFormat' => 'required|in:json,xml,yaml,csv',
        ]);

        $task = ConversionTask::create([
            'user_id' => auth()->id(),
            'type' => ConversionType::Data,
            'source_format' => $this->sourceFormat,
            'target_format' => $this->targetFormat,
            'status' => ConversionStatus::Processing,
            'original_filename' => $this->file?->getClientOriginalName() ?? "input.{$this->sourceFormat}",
        ]);

        try {
            $converted = $service->convert($this->rawInput, $this->sourceFormat, $this->targetFormat);

            $this->resultContent = $converted;
            $timestamp = now()->format('Ymd-His');
            $this->resultFilename = "Totthobox_data_converter_{$timestamp}.{$this->targetFormat}";

            $task->update(['status' => ConversionStatus::Completed]);
            activity()->performedOn($task)->log('Data conversion completed');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $task->update(['status' => ConversionStatus::Failed, 'error_message' => $e->getMessage()]);
            activity()
                ->performedOn($task)
                ->withProperties(['error' => $e->getMessage()])
                ->log('Data conversion failed');
        }
    }

    public function removeImage($propertyName, $index = null)
    {
        if (!property_exists($this, $propertyName)) {
            return;
        }

        $propertyValue = $this->{$propertyName};

        if (is_array($propertyValue)) {
            if (!isset($propertyValue[$index])) {
                return;
            }

            $file = $propertyValue[$index];

            if (is_array($file) && isset($file['is_existing']) && isset($this->introBdId)) {
                $task = ConversionTask::withTrashed()->find($this->introBdId);
                if ($task && method_exists($task, 'deleteMedia')) {
                    $task->deleteMedia($file['id']);
                }
            }

            unset($this->{$propertyName}[$index]);
            $this->{$propertyName} = array_values($this->{$propertyName});
        } else {
            $this->{$propertyName} = null;
        }

        if ($propertyName === 'file') {
            $this->rawInput = '';
            $this->sourceFormat = '';
            $this->targetOptions = [];
            $this->targetFormat = '';
            $this->resultContent = null;
        }
    }
}; ?>

{{-- ═══════════════════════════════════════════════════════════════
DATA FORMAT CONVERTER
SEO Optimized · AdSense Policy Compliant · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="mx-auto max-w-2xl space-y-6">

    <x-seo title="Free Online Data Format Converter | JSON, XML, YAML, CSV"
        description="Convert your JSON, XML, YAML, and CSV files or raw text data instantly and securely online. Free developer tool for data format transformation."
        keywords="data converter, json to xml, xml to json, yaml to json, csv converter, developer tools, json to csv, yaml to xml"
        image="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRi-efrFPkveDhj0Mt--ltX8hST5urlcmVD2aDuuynt4A&s" />

    {{-- ══════════════════════════════════════
    Page Header (Only one H1)
    ══════════════════════════════════════ --}}
    <header class="text-center space-y-2">
        <flux:badge color="zinc" variant="pill" icon="arrows-right-left" size="sm">
            Instant Transformer
        </flux:badge>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
            Data Format Converter
        </h1>
        <p class="text-base text-zinc-500 dark:text-zinc-400">
            JSON <span class="text-zinc-400">⇄</span> XML <span class="text-zinc-400">⇄</span> YAML <span
                class="text-zinc-400">⇄</span> CSV — ফাইল আপলোড করুন অথবা কোড পেস্ট করুন
        </p>
    </header>

    {{-- ══════════════════════════════════════
    Converter Tool
    ══════════════════════════════════════ --}}
    <section class="space-y-5" aria-labelledby="converter-heading">
        <h2 id="converter-heading" class="sr-only">Data Format Converter Tool</h2>

        {{-- Input Controls --}}
        <div class="space-y-4">
            <flux:file-upload wire:model="file" accept=".json,.xml,.yaml,.yml,.csv,.txt" id="file-input"
                label="১. ফাইল আপলোড (ঐচ্ছিক)" description="JSON, XML, YAML, CSV (সর্বোচ্চ 5MB)"
                aria-label="ডাটা ফাইল আপলোড করুন" />

            <flux:field>
                <flux:label class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">
                    ২. উৎস (Source) ফরম্যাট নির্বাচন
                </flux:label>
                <div class="grid grid-cols-4 gap-2 mt-1.5" role="group" aria-label="সোর্স ফরম্যাট নির্বাচন">
                    @foreach (['json', 'xml', 'yaml', 'csv'] as $fmt)
                        <flux:button size="sm" type="button"
                            :variant="$sourceFormat === $fmt ? 'primary' : 'subtle'"
                            class="w-full font-mono text-xs uppercase"
                            wire:click="setSourceFormatManually('{{ $fmt }}')"
                            :aria-pressed="$sourceFormat === $fmt"
                            aria-label="{{ strtoupper($fmt) }} সোর্স ফরম্যাট নির্বাচন করুন">
                            {{ strtoupper($fmt) }}
                        </flux:button>
                    @endforeach
                </div>
            </flux:field>
        </div>

        {{-- Source Content Textarea --}}
        <flux:field>
            <div class="flex items-center justify-between mb-2.5">
                <flux:label class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">
                    ৩. Source Data
                </flux:label>
                @if ($sourceFormat)
                    <flux:badge color="indigo" size="sm" class="font-mono uppercase">
                        {{ $sourceFormat }}
                    </flux:badge>
                @endif
            </div>
            <flux:textarea wire:model="rawInput" rows="8" placeholder='{"example": "paste your raw data here..."}'
                class="font-mono text-xs sm:text-sm leading-relaxed resize-y rounded-xl"
                aria-label="সোর্স ডাটা পেস্ট করুন" />
            <flux:error name="rawInput" />
        </flux:field>

        {{-- Target Format Selection --}}
        @if ($sourceFormat)
            <div class="rounded-xl border border-zinc-100 bg-zinc-50/80 p-4 dark:border-zinc-800/60 dark:bg-zinc-800/40 space-y-2"
                role="group" aria-label="টার্গেট ফরম্যাট নির্বাচন">
                <div class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                    রূপান্তর করুন (Target Format):
                </div>
                <div class="flex flex-wrap gap-4">
                    @foreach ($targetOptions as $option)
                        <flux:button size="sm" type="button"
                            :variant="$targetFormat === $option ? 'primary' : 'ghost'"
                            class="font-mono text-xs uppercase" wire:click="$set('targetFormat', '{{ $option }}')"
                            :aria-pressed="$targetFormat === $option"
                            aria-label="{{ strtoupper($option) }} টার্গেট ফরম্যাট নির্বাচন করুন">
                            {{ strtoupper($option) }}
                        </flux:button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Convert Button --}}
        <flux:button variant="primary" wire:click="convert" :disabled="!$rawInput || !$sourceFormat"
            wire:loading.attr="disabled" wire:target="convert" class="w-full py-2.5 shadow-sm text-sm font-medium"
            aria-label="ডাটা কনভার্ট করুন">
            <span wire:loading.remove wire:target="convert" class="inline-flex items-center gap-4">
                <flux:icon name="sparkles" class="w-4  h-4" aria-hidden="true" />
                Convert to {{ strtoupper($targetFormat ?: 'Target') }}
            </span>
            <span wire:loading wire:target="convert" class="inline-flex items-center gap-4">
                <flux:icon name="arrow-path" class="w-4  h-4 animate-spin" aria-hidden="true" />
                Converting Data...
            </span>
        </flux:button>

        {{-- Error Callout --}}
        @if ($errorMessage)
            <flux:callout variant="danger" icon="exclamation-circle" class="text-xs" role="alert">
                {{ $errorMessage }}
            </flux:callout>
        @endif

        {{-- Result Output Section --}}
        @if ($resultContent)
            <div x-data="{
                content: @js($resultContent),
                filename: @js($resultFilename),
                copied: false,
                copy() {
                    navigator.clipboard.writeText(this.content);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
            }" class="pt-4 border-t border-zinc-200 dark:border-zinc-800 space-y-3"
                role="region" aria-label="কনভার্সন রেজাল্ট">
                <div class="flex items-center justify-between">
                    <h3
                        class="text-xs font-semibold uppercase text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                        <flux:icon name="check-circle" class="w-4  h-4" aria-hidden="true" />
                        Result ({{ strtoupper($targetFormat) }})
                    </h3>

                    <flux:button size="xs" variant="ghost" icon="clipboard" @click="copy()"
                        aria-label="রেজাল্ট কপি করুন">
                        <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                    </flux:button>
                </div>

                <flux:textarea readonly rows="8"
                    class="font-mono text-xs sm:text-sm leading-relaxed bg-zinc-50 dark:bg-zinc-950/60 rounded-xl"
                    aria-label="কনভার্টেড ডাটা">{{ $resultContent }}</flux:textarea>

                <div class="pt-1">
                    <flux:button variant="primary" color="emerald" class="w-full text-xs font-medium"
                        icon="arrow-down-tray" aria-label="কনভার্টেড ফাইল ডাউনলোড করুন"
                        @click="
                                    const blob = new Blob([content], {type: 'text/plain'});
                                    const url = URL.createObjectURL(blob);
                                    const a = document.createElement('a');
                                    a.href = url; a.download = filename; a.click();
                                    URL.revokeObjectURL(url);
                                ">
                        Download {{ strtoupper($targetFormat) }} File
                    </flux:button>
                </div>
            </div>
        @endif
    </section>

    {{-- ══════════════════════════════════════
    Informative Content (AdSense + SEO)
    ══════════════════════════════════════ --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-converter">
        <h2 id="about-converter" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            ফ্রি অনলাইন ডাটা ফরম্যাট কনভার্টার
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>JSON, XML, YAML এবং CSV</strong> ফরম্যাটগুলোর মধ্যে সহজেই ডাটা রূপান্তর করুন।
                ফাইল আপলোড করতে পারেন অথবা সরাসরি রো টেক্সট পেস্ট করে কনভার্ট করতে পারেন।
            </p>
            <p>
                ডেভেলপারদের জন্য দ্রুত ও সহজ টুল। সর্বোচ্চ ৫ MB ফাইল বা ২ লাখ ক্যারেক্টার পর্যন্ত টেক্সট সাপোর্ট করে।
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
            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>এই ডাটা কনভার্টার কি ফ্রি?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    হ্যাঁ। টুলটি সম্পূর্ণ ফ্রি। কোনো রেজিস্ট্রেশন বা পেমেন্ট লাগে না।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>কোন কোন ফরম্যাট সাপোর্টেড?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    JSON, XML, YAML এবং CSV — এই চারটি ফরম্যাটের মধ্যে যেকোনো দিকে কনভার্ট করা যায়।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>ফাইল না দিয়ে শুধু টেক্সট পেস্ট করে কি কনভার্ট করা যায়?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    হ্যাঁ। ফাইল আপলোড ঐচ্ছিক। সোর্স ফরম্যাট সিলেক্ট করে টেক্সটএরিয়াতে ডাটা পেস্ট করেই কনভার্ট করতে
                    পারবেন।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>সর্বোচ্চ কত বড় ডাটা সাপোর্ট করে?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    ফাইল আপলোডে সর্বোচ্চ ৫ MB এবং টেক্সট পেস্টে সর্বোচ্চ প্রায় ২ লাখ ক্যারেক্টার পর্যন্ত সাপোর্ট করে।
                </div>
            </details>
        </div>
    </section>

</div>
