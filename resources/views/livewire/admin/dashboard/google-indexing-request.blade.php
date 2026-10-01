<?php

use App\Enums\IndexingStatus;
use App\Jobs\ImportUrlsFromCsvJob;
use App\Jobs\IndexUrlJob;
use App\Models\IndexedUrl;
use App\Services\GoogleIndexingService;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    #[Validate('required|url|max:2048')]
    public string $url = '';

    #[Validate('required|file|mimes:csv,txt|max:5120')]
    public $csvFile = null;

    #[Url(history: true)]
    public string $search = '';

    public ?int $editingUrlId = null;

    #[Validate('required|url|max:2048')]
    public string $editUrlValue = '';

    public bool $isModalOpen = false;

    #[Url(history: true)]
    public string $filterStatus = '';

    /** Per-page options: 10, 25, 50, 100 */
    #[Url(history: true)]
    public int $perPage = 10;

    /** @var array<int|string> */
    public array $selected = [];

    public bool $selectPage = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPerPage(): void
    {
        $allowed = [10, 25, 50, 100, 500000];
        if (! in_array($this->perPage, $allowed, true)) {
            $this->perPage = 10;
        }
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedSelectPage(bool $value): void
    {
        if ($value) {
            $this->selected = $this->urls->pluck('id')->map(fn($id) => (string) $id)->all();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected(): void
    {
        $pageIds = $this->urls->pluck('id')->map(fn($id) => (string) $id)->all();
        $this->selectPage = count($pageIds) > 0
            && count(array_intersect($this->selected, $pageIds)) === count($pageIds);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
    }

    #[Computed]
    public function urls()
    {
        return IndexedUrl::query()
            ->search($this->search)
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function remainingQuota(): int
    {
        return app(GoogleIndexingService::class)->remainingQuota();
    }

    #[Computed]
    public function selectedCount(): int
    {
        return count($this->selected);
    }

    #[Computed]
    public function statusCounts(): array
    {
        return IndexedUrl::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }

    // 1. এক-ক্লিকে ইনডেক্সিং
    public function submitForIndexing(): void
    {
        $this->validate(['url' => 'required|url|max:2048']);

        $throttleKey = 'submit-indexing:' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, maxAttempts: 20)) {
            Flux::toast('একটু ধীরে! কিছুক্ষণ পর আবার চেষ্টা করুন।', variant: 'warning');

            return;
        }
        RateLimiter::hit($throttleKey, decaySeconds: 60);

        if (! app(GoogleIndexingService::class)->isConfigured()) {
            Flux::toast('গুগল এপিআই ক্রেডেনশিয়াল সেটআপ করা নেই।', variant: 'danger');

            return;
        }

        try {
            $record = IndexedUrl::firstOrCreate(
                ['url' => $this->url],
                ['source' => 'manual', 'status' => IndexingStatus::Pending]
            );

            if ($record->wasRecentlyCreated === false && $record->status === IndexingStatus::Success) {
                Flux::toast('এই URL টি আগেই সফলভাবে ইনডেক্স করা হয়েছে। আবার পুশ করা হচ্ছে...', variant: 'info');
            }

            $record->update([
                'status' => IndexingStatus::Queued,
                'source' => $record->source ?: 'manual',
            ]);

            IndexUrlJob::dispatch($record->id);

            Flux::toast('ইনডেক্সিং কিউতে যোগ হয়েছে, শীঘ্রই প্রসেস হবে।', variant: 'success');
            $this->reset('url');
            unset($this->urls, $this->statusCounts);
        } catch (\Throwable $e) {
            Log::error('submitForIndexing: ব্যর্থ হয়েছে।', ['url' => $this->url, 'error' => $e->getMessage()]);
            Flux::toast('কিছু একটা সমস্যা হয়েছে, আবার চেষ্টা করুন।', variant: 'danger');
        }
    }

    // 2. CSV Import
    public function importCsv(): void
    {
        $this->validate(['csvFile' => 'required|file|mimes:csv,txt|max:5120']);

        try {
            $storedPath = $this->csvFile->store('csv-imports');

            ImportUrlsFromCsvJob::dispatch($storedPath);

            Flux::toast('CSV আপলোড হয়েছে, ব্যাকগ্রাউন্ডে ইমপোর্ট শুরু হয়েছে।', variant: 'success');
            $this->reset('csvFile');
        } catch (\Throwable $e) {
            Log::error('importCsv: ব্যর্থ হয়েছে।', ['error' => $e->getMessage()]);
            Flux::toast('CSV আপলোড করতে সমস্যা হয়েছে, আবার চেষ্টা করুন।', variant: 'danger');
        }
    }

    // 3. Edit
    public function edit(int $indexedUrlId): void
    {
        $record = IndexedUrl::findOrFail($indexedUrlId);

        $this->editingUrlId = $record->id;
        $this->editUrlValue = $record->url;
        $this->isModalOpen = true;
    }

    public function update(): void
    {
        $this->validate(['editUrlValue' => 'required|url|max:2048']);

        $record = IndexedUrl::findOrFail($this->editingUrlId);

        $duplicate = IndexedUrl::where('url', $this->editUrlValue)
            ->where('id', '!=', $record->id)
            ->exists();

        if ($duplicate) {
            $this->addError('editUrlValue', 'এই URL টি ইতিমধ্যে তালিকায় আছে।');

            return;
        }

        $record->update(['url' => $this->editUrlValue]);

        $this->isModalOpen = false;
        $this->editingUrlId = null;
        unset($this->urls);

        Flux::toast('URL আপডেট হয়েছে।', variant: 'success');
    }

    // 4. Single delete
    public function delete(int $indexedUrlId): void
    {
        IndexedUrl::findOrFail($indexedUrlId)->delete();

        $this->selected = array_values(array_filter(
            $this->selected,
            fn($id) => (int) $id !== $indexedUrlId
        ));
        $this->selectPage = false;

        unset($this->urls, $this->statusCounts);
        Flux::toast('URL মুছে ফেলা হয়েছে।', variant: 'success');
    }

    // 5. Single retry
    public function retry(int $indexedUrlId): void
    {
        $record = IndexedUrl::findOrFail($indexedUrlId);

        if (! app(GoogleIndexingService::class)->isConfigured()) {
            Flux::toast('গুগল এপিআই ক্রেডেনশিয়াল সেটআপ করা নেই।', variant: 'danger');

            return;
        }

        if ($record->status === IndexingStatus::Success) {
            Flux::toast('এই URL ইতিমধ্যে সফলভাবে ইনডেক্স করা আছে।', variant: 'info');

            return;
        }

        $record->update([
            'status' => IndexingStatus::Queued,
            'error_message' => null,
        ]);

        IndexUrlJob::dispatch($record->id);

        unset($this->urls, $this->statusCounts);
        Flux::toast('রিট্রাই কিউতে যোগ হয়েছে।', variant: 'success');
    }

    // ── Bulk actions ──────────────────────────────────────────────

    public function bulkDelete(): void
    {
        if ($this->selectedCount === 0) {
            Flux::toast('কোনো URL সিলেক্ট করা হয়নি।', variant: 'warning');

            return;
        }

        $ids = array_map('intval', $this->selected);

        IndexedUrl::whereIn('id', $ids)->delete();

        $count = count($ids);
        $this->clearSelection();
        unset($this->urls, $this->statusCounts);

        Flux::toast("{$count} টি URL মুছে ফেলা হয়েছে।", variant: 'success');
    }

    public function bulkRetry(): void
    {
        if ($this->selectedCount === 0) {
            Flux::toast('কোনো URL সিলেক্ট করা হয়নি।', variant: 'warning');

            return;
        }

        if (! app(GoogleIndexingService::class)->isConfigured()) {
            Flux::toast('গুগল এপিআই ক্রেডেনশিয়াল সেটআপ করা নেই।', variant: 'danger');

            return;
        }

        $ids = array_map('intval', $this->selected);

        $records = IndexedUrl::whereIn('id', $ids)
            ->whereIn('status', [
                IndexingStatus::Failed,
                IndexingStatus::QuotaExceeded,
                IndexingStatus::Pending,
            ])
            ->get();

        if ($records->isEmpty()) {
            Flux::toast('রিট্রাই করার মতো কোনো URL নেই (শুধু Failed / Quota Exceeded / Pending)।', variant: 'info');

            return;
        }

        foreach ($records as $record) {
            $record->update([
                'status' => IndexingStatus::Queued,
                'error_message' => null,
            ]);
            IndexUrlJob::dispatch($record->id);
        }

        $count = $records->count();
        $this->clearSelection();
        unset($this->urls, $this->statusCounts);

        Flux::toast("{$count} টি URL রিট্রাই কিউতে যোগ হয়েছে।", variant: 'success');
    }

    public function bulkReindex(): void
    {
        if ($this->selectedCount === 0) {
            Flux::toast('কোনো URL সিলেক্ট করা হয়নি।', variant: 'warning');

            return;
        }

        if (! app(GoogleIndexingService::class)->isConfigured()) {
            Flux::toast('গুগল এপিআই ক্রেডেনশিয়াল সেটআপ করা নেই।', variant: 'danger');

            return;
        }

        $ids = array_map('intval', $this->selected);

        $records = IndexedUrl::whereIn('id', $ids)->get();

        foreach ($records as $record) {
            $record->update([
                'status' => IndexingStatus::Queued,
                'error_message' => null,
            ]);
            IndexUrlJob::dispatch($record->id);
        }

        $count = $records->count();
        $this->clearSelection();
        unset($this->urls, $this->statusCounts);

        Flux::toast("{$count} টি URL আবার ইনডেক্সিং কিউতে যোগ হয়েছে।", variant: 'success');
    }
}; ?>

<div class="space-y-8">
    {{-- Header + Quota --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl font-bold">Google Indexing ম্যানেজার</h1>
        <flux:badge :color="$this->remainingQuota > 20 ? 'lime' : 'amber'">
            আজকের বাকি কোটা: {{ $this->remainingQuota }} / {{ config('services.google.indexing_daily_quota', 200) }}
        </flux:badge>
    </div>

    {{-- Quick stats --}}
    @php $counts = $this->statusCounts; @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
        <flux:card class="!p-3 text-center">
            <div class="text-xs text-zinc-500">Pending</div>
            <div class="text-lg font-semibold">{{ $counts['pending'] ?? 0 }}</div>
        </flux:card>
        <flux:card class="!p-3 text-center">
            <div class="text-xs text-zinc-500">Queued</div>
            <div class="text-lg font-semibold">{{ $counts['queued'] ?? 0 }}</div>
        </flux:card>
        <flux:card class="!p-3 text-center">
            <div class="text-xs text-zinc-500">Success</div>
            <div class="text-lg font-semibold text-green-600">{{ $counts['success'] ?? 0 }}</div>
        </flux:card>
        <flux:card class="!p-3 text-center">
            <div class="text-xs text-zinc-500">Failed</div>
            <div class="text-lg font-semibold text-red-600">{{ $counts['failed'] ?? 0 }}</div>
        </flux:card>
        <flux:card class="!p-3 text-center">
            <div class="text-xs text-zinc-500">Quota Exceeded</div>
            <div class="text-lg font-semibold text-amber-600">{{ $counts['quota_exceeded'] ?? 0 }}</div>
        </flux:card>
    </div>

    {{-- Quick Index + CSV --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:card>
            <h2 class="font-bold mb-4">Quick Indexing</h2>
            <form wire:submit="submitForIndexing" class="flex gap-4">
                <flux:input wire:model="url" placeholder="https://..." class="flex-1" />
                <flux:button type="submit" variant="primary" icon="bolt" wire:loading.attr="disabled"
                    wire:target="submitForIndexing" />
            </form>
            @error('url')
            <flux:text class="text-red-500 text-sm mt-1">{{ $message }}</flux:text>
            @enderror
        </flux:card>

        <flux:card>
            <h2 class="font-bold mb-4">CSV Import</h2>
            <form wire:submit="importCsv" class="flex gap-4">
                <flux:input type="file" wire:model="csvFile" accept=".csv,.txt" class="flex-1" />
                <flux:button type="submit" icon="document-arrow-up" wire:loading.attr="disabled"
                    wire:target="csvFile,importCsv" />
            </form>
            @error('csvFile')
            <flux:text class="text-red-500 text-sm mt-1">{{ $message }}</flux:text>
            @enderror
            <flux:text class="text-xs text-zinc-500 mt-2">CSV-এর প্রথম কলামে URL থাকতে হবে। সর্বোচ্চ 5MB।</flux:text>
        </flux:card>
    </div>

    {{-- Filters + Per page --}}
    <div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div class="flex flex-1 flex-wrap gap-3">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search URLs..." icon="magnifying-glass"
                    class="flex-1 min-w-[12rem] max-w-sm" />

                <flux:select wire:model.live="filterStatus" placeholder="সব স্ট্যাটাস" class="w-44">
                    <flux:select.option value="">সবগুলো</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="queued">Queued</flux:select.option>
                    <flux:select.option value="success">Success</flux:select.option>
                    <flux:select.option value="failed">Failed</flux:select.option>
                    <flux:select.option value="quota_exceeded">Quota Exceeded</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="perPage" class="w-28" aria-label="প্রতি পেজে">
                    <flux:select.option value="10">10</flux:select.option>
                    <flux:select.option value="25">25</flux:select.option>
                    <flux:select.option value="50">50</flux:select.option>
                    <flux:select.option value="100">100</flux:select.option>
                    <flux:select.option value="500000">All</flux:select.option>
                </flux:select>
            </div>
        </div>

        {{-- Bulk action bar --}}
        @if ($this->selectedCount > 0)
        <div
            class="mb-4 flex flex-wrap items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/50">
            <flux:text class="text-sm font-medium">
                {{ $this->selectedCount }} টি সিলেক্টেড
            </flux:text>

            <div class="flex flex-wrap gap-2">
                <flux:button size="sm" variant="primary" icon="arrow-path" wire:click="bulkRetry"
                    wire:loading.attr="disabled" wire:target="bulkRetry"
                    title="Failed / Pending / Quota Exceeded রিট্রাই">
                    Retry
                </flux:button>

                <flux:button size="sm" variant="filled" icon="bolt" wire:click="bulkReindex"
                    wire:loading.attr="disabled" wire:target="bulkReindex" title="সব সিলেক্টেড URL আবার ইনডেক্স">
                    Re-index All
                </flux:button>

                <flux:button size="sm" variant="danger" icon="trash" wire:click="bulkDelete"
                    wire:confirm="আপনি কি নিশ্চিত? সিলেক্ট করা {{ $this->selectedCount }} টি URL মুছে যাবে।"
                    wire:loading.attr="disabled" wire:target="bulkDelete">
                    Delete
                </flux:button>

                <flux:button size="sm" variant="ghost" wire:click="clearSelection">
                    Clear
                </flux:button>
            </div>
        </div>
        @endif

        <flux:table>
            <flux:table.columns>
                <flux:table.column class="w-10">
                    <flux:checkbox wire:model.live="selectPage" />
                </flux:table.column>
                <flux:table.column>URL</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Last Crawled</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->urls as $item)
                <flux:table.row wire:key="url-{{ $item->id }}">
                    <flux:table.cell>
                        <flux:checkbox wire:model.live="selected" value="{{ $item->id }}" />
                    </flux:table.cell>

                    <flux:table.cell class="max-w-xs truncate" title="{{ $item->url }}">
                        <a href="{{ $item->url }}" target="_blank" rel="noopener"
                            class="hover:underline text-zinc-800 dark:text-zinc-200">
                            {{ $item->url }}
                        </a>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" :color="$item->status->color()">
                            {{ $item->status->label() }}
                        </flux:badge>
                        @if ($item->status === \App\Enums\IndexingStatus::Failed && $item->error_message)
                        <flux:tooltip content="{{ $item->error_message }}">
                            <flux:icon.information-circle class="inline size-4 text-zinc-400" />
                        </flux:tooltip>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        {{ $item->last_crawled?->format('d M, Y') ?? '—' }}
                    </flux:table.cell>

                    <flux:table.cell align="end" class="space-x-1">
                        @if (in_array($item->status, [
                        \App\Enums\IndexingStatus::Failed,
                        \App\Enums\IndexingStatus::QuotaExceeded,
                        \App\Enums\IndexingStatus::Pending,
                        ]))
                        <flux:button wire:click="retry({{ $item->id }})" variant="subtle" icon="arrow-path" size="sm"
                            title="Retry indexing" wire:loading.attr="disabled" wire:target="retry" />
                        @endif

                        <flux:button wire:click="edit({{ $item->id }})" variant="subtle" icon="pencil" size="sm" />

                        <flux:button wire:click="delete({{ $item->id }})"
                            wire:confirm="আপনি কি নিশ্চিত এই URL টি মুছে ফেলতে চান?" variant="danger" icon="trash"
                            size="sm" />
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center text-zinc-500 py-8">
                        কোনো URL পাওয়া যায়নি।
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <flux:text class="text-sm text-zinc-500">
                প্রতি পেজে {{ $this->perPage }} টি · মোট {{ $this->urls->total() }} টি
            </flux:text>
            <div>{{ $this->urls->links() }}</div>
        </div>
    </div>

    {{-- Edit modal --}}
    <flux:modal wire:model="isModalOpen">
        <div class="p-6 space-y-4">
            <h3 class="font-bold">Edit URL</h3>
            <flux:input wire:model="editUrlValue" />
            @error('editUrlValue')
            <flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>
            @enderror
            <flux:button wire:click="update" variant="primary" class="w-full" wire:loading.attr="disabled"
                wire:target="update">
                Update
            </flux:button>
        </div>
    </flux:modal>
</div>