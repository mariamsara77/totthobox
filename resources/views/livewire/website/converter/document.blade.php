{{-- resources/views/livewire/converter/document.blade.php --}}
<?php

use App\Enums\ConversionStatus;
use App\Enums\ConversionType;
use App\Jobs\ProcessDocumentJob;
use App\Models\ConversionTask;
use App\Services\DocumentConverterService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    #[Validate('required|file|mimes:doc,docx,odt,rtf,txt,xls,xlsx,ods,csv,ppt,pptx,odp,pdf|max:20480')]
    public $file = null;

    public string $sourceFormat = '';
    public array $targetOptions = [];
    public string $targetFormat = '';
    public ?int $lastTaskId = null;

    public function updatedFile(): void
    {
        $this->sourceFormat = strtolower($this->file->getClientOriginalExtension());
        $this->targetOptions = DocumentConverterService::availableTargets($this->sourceFormat);
        $this->targetFormat = $this->targetOptions[0] ?? '';
    }

    public function convert(): void
    {
        $this->validate();
        $this->validate(['targetFormat' => 'required|in:' . implode(',', $this->targetOptions)]);

        $task = ConversionTask::create([
            'user_id' => auth()->id(),
            'type' => ConversionType::Document,
            'source_format' => $this->sourceFormat,
            'target_format' => $this->targetFormat,
            'status' => ConversionStatus::Pending,
            'original_filename' => $this->file->getClientOriginalName(),
        ]);

        $task
            ->addMedia($this->file->getRealPath())
            ->usingFileName($this->file->getClientOriginalName())
            ->toMediaCollection('original_files');

        activity()->performedOn($task)->log('Document conversion task created');

        ProcessDocumentJob::dispatch($task);

        $this->lastTaskId = $task->id;
        $this->reset(['file', 'sourceFormat', 'targetOptions', 'targetFormat']);
        $this->dispatch('document-queued', taskId: $task->id);
    }

    public function with(): array
    {
        return ['task' => $this->lastTaskId ? ConversionTask::find($this->lastTaskId) : null];
    }

    public function downloadFile()
    {
        $task = ConversionTask::find($this->lastTaskId);
        $media = $task?->getFirstMedia('converted_files');

        if ($media && file_exists($media->getPath())) {
            $timestamp = now()->format('Ymd-His');

            return response()->download($media->getPath(), "Totthobox_document_converter_{$timestamp}.{$task->target_format}");
        }
    }

    public function removeImage(): void
    {
        $this->reset(['file', 'sourceFormat', 'targetOptions', 'targetFormat', 'lastTaskId']);
    }
}; ?>

{{-- ═══════════════════════════════════════════════════════════════
     DOCUMENT CONVERTER
     SEO Optimized · AdSense Policy Compliant · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="mx-auto max-w-2xl space-y-6">

    <x-seo title="Free Online Document Converter | PDF, Word, Excel, PPT"
        description="Convert documents online securely. Easily transform PDF, Word (DOCX), Excel (XLSX), PowerPoint (PPTX), TXT, and more for free. Fast, private and easy to use."
        keywords="document converter, pdf to word, word to pdf, excel converter, ppt to pdf, online document converter, free pdf converter, docx to pdf, odt to pdf"
        image="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRi-efrFPkveDhj0Mt--ltX8hST5urlcmVD2aDuuynt4A&s" />

    {{-- ══════════════════════════════════════
         Page Header (Only one H1)
    ══════════════════════════════════════ --}}
    <header class="text-center space-y-2">
        <flux:badge color="zinc" variant="pill" icon="document-text" size="sm">
            Universal Document Converter
        </flux:badge>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
            Document Converter
        </h1>
        <p class="text-base text-zinc-500 dark:text-zinc-400">
            PDF ⇄ Word ⇄ Excel ⇄ PowerPoint — ফাইল আপলোড করলেই ফরম্যাট auto-detect হবে।
        </p>
    </header>

    {{-- ══════════════════════════════════════
         Converter Tool
    ══════════════════════════════════════ --}}
    <section class="space-y-5" aria-labelledby="converter-heading">
        <h2 id="converter-heading" class="sr-only">Document Converter Tool</h2>

        <form wire:submit="convert" class="space-y-5">
            <flux:field>
                <flux:file-upload type="file" wire:model="file" label="ফাইল আপলোড করো"
                    accept=".doc,.docx,.odt,.rtf,.txt,.xls,.xlsx,.ods,.csv,.ppt,.pptx,.odp,.pdf"
                    aria-label="ডকুমেন্ট ফাইল আপলোড করুন" />
            </flux:field>

            @if ($sourceFormat)
                <div class="flex items-center gap-2 rounded-xl bg-zinc-50 p-3 text-sm dark:bg-zinc-800/60"
                    role="group" aria-label="টার্গেট ফরম্যাট নির্বাচন">
                    <flux:badge color="blue">{{ strtoupper($sourceFormat) }}</flux:badge>
                    <span class="text-zinc-500">থেকে</span>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($targetOptions as $option)
                            <flux:button size="sm" type="button"
                                :variant="$targetFormat === $option ? 'primary' : 'ghost'"
                                wire:click="$set('targetFormat', '{{ $option }}')"
                                :aria-pressed="$targetFormat === $option"
                                aria-label="{{ strtoupper($option) }} ফরম্যাটে কনভার্ট করুন">
                                {{ strtoupper($option) }}
                            </flux:button>
                        @endforeach
                    </div>
                </div>
                <flux:error name="targetFormat" />
            @endif

            <flux:button type="submit" variant="primary" :disabled="!$sourceFormat" wire:loading.attr="disabled"
                wire:target="convert" class="w-full" aria-label="কনভার্ট শুরু করুন">
                <span wire:loading.remove wire:target="convert">Convert</span>
                <span wire:loading wire:target="convert">Queuing...</span>
            </flux:button>
        </form>

        @if ($task)
            <div wire:poll.3s="$refresh" class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
                role="status" aria-live="polite">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                        Task #{{ $task->id }} — {{ strtoupper($task->source_format) }} →
                        {{ strtoupper($task->target_format) }}
                    </h3>
                    <flux:badge :color="$task->status->color()">{{ ucfirst($task->status->value) }}</flux:badge>
                </div>

                @if ($task->status === \App\Enums\ConversionStatus::Completed && $task->getFirstMediaUrl('converted_files'))
                    <flux:button wire:click="downloadFile" variant="primary" color="green" class="mt-3 w-full"
                        aria-label="কনভার্টেড ফাইল ডাউনলোড করুন">
                        Download {{ strtoupper($task->target_format) }}
                    </flux:button>
                @endif

                @if ($task->status === \App\Enums\ConversionStatus::Failed)
                    <flux:callout variant="danger" class="mt-3">{{ $task->error_message }}</flux:callout>
                @endif
            </div>
        @endif
    </section>

    {{-- ══════════════════════════════════════
         Informative Content (AdSense + SEO)
    ══════════════════════════════════════ --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-converter">
        <h2 id="about-converter" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            ফ্রি অনলাইন ডকুমেন্ট কনভার্টার
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                <strong>PDF, Word (DOCX), Excel (XLSX), PowerPoint (PPTX), TXT, ODT, ODS, CSV</strong> সহ
                জনপ্রিয় ডকুমেন্ট ফরম্যাটগুলোর মধ্যে সহজেই কনভার্ট করুন। ফাইল আপলোড করলেই সোর্স ফরম্যাট
                অটো-ডিটেক্ট হবে এবং সম্ভাব্য টার্গেট ফরম্যাট দেখানো হবে।
            </p>
            <p>
                কনভার্সন শেষ হলে ডাউনলোড বাটনে ক্লিক করে ফাইল নিন। সর্বোচ্চ ২০ MB পর্যন্ত ফাইল সাপোর্ট করে।
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
                    <span>এই ডকুমেন্ট কনভার্টার কি ফ্রি?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    হ্যাঁ। টুলটি সম্পূর্ণ ফ্রি ব্যবহার করা যায়। কোনো রেজিস্ট্রেশন বা পেমেন্ট লাগে না।
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
                    PDF, DOC, DOCX, ODT, RTF, TXT, XLS, XLSX, ODS, CSV, PPT, PPTX এবং ODP ফরম্যাট সাপোর্টেড।
                    সোর্স ফাইল অনুযায়ী সম্ভাব্য টার্গেট ফরম্যাট অটো দেখানো হয়।
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
                    সর্বোচ্চ ২০ MB (20480 KB) পর্যন্ত ফাইল আপলোড করা যায়। এর বেশি সাইজের ফাইল আপলোড করলে এরর দেখাবে।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>কনভার্সন কতক্ষণ লাগে?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    সাধারণত কয়েক সেকেন্ড থেকে এক মিনিটের মধ্যে কনভার্সন শেষ হয়। ফাইল সাইজ ও সার্ভার লোডের উপর নির্ভর করে
                    সময় কম-বেশি হতে পারে।
                    স্ট্যাটাস অটো আপডেট হয়।
                </div>
            </details>
        </div>
    </section>

</div>
