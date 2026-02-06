<?php

use Livewire\Volt\Component;
use App\Models\ExcelTutorial;
use Livewire\Attributes\Computed;

new class extends Component {
    public $currentSlug;

    public function mount($slug = null)
    {
        if (!$slug) {
            $first = ExcelTutorial::where('is_published', true)->orderBy('position', 'asc')->first();
            $this->currentSlug = $first ? $first->slug : null;
        } else {
            $this->currentSlug = $slug;
        }
    }

    #[Computed]
    public function tutorial()
    {
        return ExcelTutorial::where('slug', $this->currentSlug)->firstOrFail();
    }
}; ?>

<div class="max-w-2xl mx-auto">

    <div class="">

        {{-- Breadcrumbs using Flux --}}
        <flux:breadcrumbs class="mb-8">
            <flux:breadcrumbs.item href="/">Home</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="#">Excel Tutorial</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $this->tutorial->chapter_name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        {{-- Lesson Header --}}
        <header class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <flux:badge color="green" size="sm" variant="pill">Lesson #{{ $this->tutorial->position }}
                </flux:badge>
                <flux:text variant="prose" size="sm" class="italic">
                    Updated: {{ $this->tutorial->updated_at->format('M Y') }}
                </flux:text>
            </div>

            <flux:heading size="xl" class="!text-4xl md:!text-5xl tracking-tight text-zinc-900 dark:text-white">
                {{ $this->tutorial->title }}
            </flux:heading>
        </header>

        {{-- Image Section --}}
        @if($this->tutorial->hasMedia('lesson_image'))
            <div class="mb-10 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <img src="{{ $this->tutorial->getFirstMediaUrl('lesson_image') }}" alt="{{ $this->tutorial->title }}"
                    class="w-full h-auto">
            </div>
        @endif

        {{-- Article Content --}}
        <flux:text>
            <div class="ql-text-fromat">
                {!! $this->tutorial->description !!}
            </div>
        </flux:text>


        {{-- Interactive Formula Box (Alpine.js Clipboard) --}}
        @if($this->tutorial->excel_formula)
            <section
                x-data="{ 
                                                                                                                                                formula: '{{ $this->tutorial->excel_formula }}',
                                                                                                                                                copied: false,
                                                                                                                                                copyToClipboard() {
                                                                                                                                                    navigator.clipboard.writeText(this.formula);
                                                                                                                                                    this.copied = true;
                                                                                                                                                    setTimeout(() => this.copied = false, 2000);
                                                                                                                                                }
                                                                                                                                            }"
                class="my-10 p-6 md:p-8 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl relative">
                <div class="flex justify-between items-start mb-4">
                    <flux:heading size="lg" class="flex items-center gap-2">
                        <flux:icon.variable class="size-5 text-green-600" />
                        নিজে চেষ্টা করুন
                    </flux:heading>

                    <flux:badge color="green" variant="flat" size="sm" class="uppercase tracking-widest">Formula
                    </flux:badge>
                </div>

                <div class="relative group">
                    <div
                        class="w-full p-5 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl font-mono text-lg text-green-600 shadow-inner overflow-x-auto mb-4">
                        {{ $this->tutorial->excel_formula }}
                    </div>

                    {{-- Alpine Clipboard Button --}}
                    <flux:button x-on:click="copyToClipboard" size="sm" variant="filled" color="green"
                        class="w-full md:w-auto font-bold">
                        <span x-show="!copied" class="flex items-center gap-2">
                            <flux:icon.document-duplicate class="size-4" /> ফর্মুলা কপি করুন
                        </span>
                        <span x-show="copied" x-cloak class="flex items-center gap-2">
                            <flux:icon.check class="size-4" /> কপি হয়েছে!
                        </span>
                    </flux:button>
                </div>
            </section>
        @endif
    </div>

    <footer class="text-center mt-12">
        <flux:text size="sm" class="text-zinc-400">
            &copy; {{ date('Y') }} Excel Expert BD. সহজ শিক্ষায় সেরা সমাধান।
        </flux:text>
    </footer>
</div>