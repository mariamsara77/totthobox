{{--
    ════════════════════════════════════════════════════════════════════════════
    ADMIN — Live Channel Manager  (v5.1)
    App\Models\LiveChannel — categories, health score, featured, m3u tracking

    CHANGES in this version:
    ✅ Edit form moved to Flux modal
    ✅ All custom colors removed — relying on Flux defaults
    ✅ No extra padding/margins
    ✅ Clean, standardized Flux component usage
    ════════════════════════════════════════════════════════════════════════════
--}}
<?php

use App\Jobs\CheckAndSyncStreamJob;
use App\Models\LiveChannel;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

new #[Layout('components.layouts.admin')] #[Title('Live Channel Manager — Admin Panel')] class extends Component {
    use WithPagination;

    // ── Form Inputs ────────────────────────────────────────────────────
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|url|max:2048')]
    public string $stream_url = '';

    #[Validate('nullable|url|max:2048')]
    public string $embed_url = '';

    #[Validate('required|in:worldcup,bangladesh,football')]
    public string $category = 'football';

    #[Validate('nullable|string|max:5')]
    public string $country_code = '';

    #[Validate('nullable|string|max:50')]
    public string $language = '';

    #[Validate('nullable|string|max:255')]
    public string $broadcaster = '';

    #[Validate('nullable|url|max:2048')]
    public string $logo_url = '';

    public bool $is_live = false;

    public bool $is_featured = false;

    #[Validate('integer|min:0')]
    public int $sort_order = 0;

    // ── State ──────────────────────────────────────────────────────────
    public ?int $editingChannelId = null;

    public ?int $selectedChannelIdForLogs = null;

    // ── Sync Status ────────────────────────────────────────────────────
    public string $syncStatus = '';

    public string $syncMessage = '';

    public string $lastSyncType = '';

    // ── Filters ────────────────────────────────────────────────────────
    public string $search = '';

    public string $categoryFilter = 'all';

    public string $statusFilter = 'all';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $sortBy = 'newest';

    public int $perPage = 15;

    // ── Bulk Selection ─────────────────────────────────────────────────
    public array $selected = [];

    public bool $selectAll = false;

    public string $bulkCategoryTarget = 'football';

    // ─── updaters ─────────────────────────────────────────────────────
    public function updatedSelectAll(bool $value): void
    {
        $this->selected = $value ? $this->channels->pluck('id')->map(fn($id) => (string) $id)->all() : [];
    }

    public function updatedSelected(): void
    {
        $this->selectAll = count($this->selected) > 0 && count($this->selected) === $this->channels->count();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryFilter', 'statusFilter', 'dateFrom', 'dateTo', 'sortBy']);
        $this->perPage = 15;
        $this->resetPage();
        $this->clearSelection();
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return filled($this->search) || $this->categoryFilter !== 'all' || $this->statusFilter !== 'all' || filled($this->dateFrom) || filled($this->dateTo) || $this->sortBy !== 'newest';
    }

    // ═══════════════════════════════════════════════════════════════════
    //  COMPUTED PROPERTIES
    // ═══════════════════════════════════════════════════════════════════

    #[Computed]
    public function channels()
    {
        $q = LiveChannel::withTrashed();

        if (filled($this->search)) {
            $s = '%' . $this->search . '%';
            $q->where(fn($qq) => $qq->where('title', 'like', $s)->orWhere('stream_url', 'like', $s)->orWhere('broadcaster', 'like', $s));
        }

        if ($this->categoryFilter !== 'all') {
            $q->where('category', $this->categoryFilter);
        }

        match ($this->statusFilter) {
            'live' => $q->whereNull('deleted_at')->where('is_live', true),
            'offline' => $q->whereNull('deleted_at')->where('is_live', false),
            'featured' => $q->whereNull('deleted_at')->where('is_featured', true),
            'unfeatured' => $q->whereNull('deleted_at')->where('is_featured', false),
            'trashed' => $q->onlyTrashed(),
            'healthy' => $q->whereNull('deleted_at')->where('consecutive_failures', '<', 3),
            default => null,
        };

        if (filled($this->dateFrom)) {
            $q->whereDate('created_at', '>=', $this->dateFrom);
        }
        if (filled($this->dateTo)) {
            $q->whereDate('created_at', '<=', $this->dateTo);
        }

        match ($this->sortBy) {
            'oldest' => $q->oldest(),
            'name_asc' => $q->orderBy('title', 'asc'),
            'name_desc' => $q->orderBy('title', 'desc'),
            'updated' => $q->orderBy('updated_at', 'desc'),
            'health_desc' => $q->orderBy('health_score', 'desc'),
            'health_asc' => $q->orderBy('health_score', 'asc'),
            'sort_order' => $q->orderBy('sort_order', 'asc'),
            default => $q->latest(),
        };

        return $q->paginate($this->perPage);
    }

    #[Computed]
    public function totalCount(): int
    {
        return LiveChannel::query()->count();
    }

    #[Computed]
    public function liveCount(): int
    {
        return LiveChannel::query()->where('is_live', true)->count();
    }

    #[Computed]
    public function offlineCount(): int
    {
        return LiveChannel::query()->where('is_live', false)->count();
    }

    #[Computed]
    public function featuredCount(): int
    {
        return LiveChannel::query()->where('is_featured', true)->count();
    }

    #[Computed]
    public function trashedCount(): int
    {
        return LiveChannel::onlyTrashed()->count();
    }

    #[Computed]
    public function worldCupCount(): int
    {
        return LiveChannel::query()->worldCup()->count();
    }

    #[Computed]
    public function bangladeshCount(): int
    {
        return LiveChannel::query()->bangladesh()->count();
    }

    #[Computed]
    public function footballCount(): int
    {
        return LiveChannel::query()->football()->count();
    }

    #[Computed]
    public function activities()
    {
        if (!$this->selectedChannelIdForLogs) {
            return collect();
        }

        return Activity::query()->where('subject_type', LiveChannel::class)->where('subject_id', $this->selectedChannelIdForLogs)->latest()->limit(50)->get();
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SYNC ACTIONS
    // ═══════════════════════════════════════════════════════════════════

    public function syncAllChannels(): void
    {
        $this->runSyncCommand('all', false, 'Full Sync (All Categories)');
    }

    public function checkChannelHealth(): void
    {
        try {
            $channels = LiveChannel::query()
                ->select(['id', 'title', 'stream_url', 'category'])
                ->whereNotNull('stream_url')
                ->where('stream_url', '!=', '')
                ->get();

            if ($channels->isEmpty()) {
                Flux::toast(variant: 'warning', heading: 'No Channels', text: 'DB is empty. Run a sync first.');

                return;
            }

            $dispatched = 0;
            foreach ($channels as $channel) {
                CheckAndSyncStreamJob::dispatch(title: $channel->title, url: $channel->stream_url, isExisting: true, category: $channel->category)->onQueue('channels');
                $dispatched++;
            }

            $this->setSyncStatus('running', 'health', "{$dispatched} channel(s) queued for health check.");
            Flux::toast(variant: 'success', heading: 'Health Check Queued', text: "{$dispatched} channels being checked. Worker: php artisan queue:work --queue=channels,default");
        } catch (Exception $e) {
            $this->setSyncStatus('error', 'health', $e->getMessage());
            Flux::toast(variant: 'danger', heading: 'Failed', text: $e->getMessage());
        }
    }

    public function syncBangladesh(): void
    {
        $this->runSyncCommand('bangladesh', true, 'Bangladesh TV Sync');
    }

    public function syncFootball(): void
    {
        $this->runSyncCommand('football', true, 'Football Sync');
    }

    public function syncWorldCup(): void
    {
        $this->runSyncCommand('worldcup', true, 'FIFA World Cup 2026 Sync');
    }

    private function runSyncCommand(string $type, bool $forceFlag, string $label): void
    {
        try {
            $options = [
                '--type' => $type,
                '--chunk' => '20',
            ];

            if ($forceFlag) {
                $options['--force'] = true;
            }

            Artisan::call('channels:sync-all', $options);
            $output = trim(Artisan::output());

            $this->setSyncStatus('running', $type, "{$label} complete. Jobs dispatched to 'channels' queue.");

            Flux::toast(variant: 'success', heading: $label, text: 'Jobs queued. Worker: php artisan queue:work --queue=channels,default');
        } catch (Exception $e) {
            $this->setSyncStatus('error', $type, $e->getMessage());
            Flux::toast(variant: 'danger', heading: 'Sync Failed', text: $e->getMessage());
        }
    }

    private function setSyncStatus(string $status, string $type, string $message): void
    {
        $this->syncStatus = $status;
        $this->lastSyncType = $type;
        $this->syncMessage = $message;
    }

    public function clearSyncStatus(): void
    {
        $this->syncStatus = '';
        $this->syncMessage = '';
        $this->lastSyncType = '';
    }

    // ═══════════════════════════════════════════════════════════════════
    //  CHANNEL CRUD
    // ═══════════════════════════════════════════════════════════════════

    public function save(): void
    {
        $this->validate();

        $channel = $this->editingChannelId ? LiveChannel::findOrFail($this->editingChannelId) : new LiveChannel();

        $channel
            ->fill([
                'title' => $this->title,
                'stream_url' => $this->stream_url,
                'embed_url' => $this->embed_url ?: null,
                'category' => $this->category,
                'country_code' => $this->country_code ? strtoupper($this->country_code) : null,
                'language' => $this->language ?: null,
                'broadcaster' => $this->broadcaster ?: null,
                'logo_url' => $this->logo_url ?: null,
                'is_live' => $this->is_live,
                'is_featured' => $this->is_featured,
                'sort_order' => $this->sort_order,
            ])
            ->save();

        $this->resetForm();
        Flux::modal('channel-form-modal')->close();
        Flux::toast(variant: 'success', heading: 'Saved', text: 'Channel saved.');
    }

    public function edit(int $id): void
    {
        $ch = LiveChannel::findOrFail($id);

        $this->editingChannelId = $ch->id;
        $this->title = $ch->title;
        $this->stream_url = $ch->stream_url;
        $this->embed_url = (string) ($ch->embed_url ?? '');
        $this->category = $ch->category;
        $this->country_code = (string) ($ch->country_code ?? '');
        $this->language = (string) ($ch->language ?? '');
        $this->broadcaster = (string) ($ch->broadcaster ?? '');
        $this->logo_url = (string) ($ch->logo_url ?? '');
        $this->is_live = $ch->is_live;
        $this->is_featured = $ch->is_featured;
        $this->sort_order = $ch->sort_order;

        Flux::modal('channel-form-modal')->show();
    }

    public function toggleLive(int $id): void
    {
        $ch = LiveChannel::findOrFail($id);
        $ch->update(['is_live' => !$ch->is_live]);
        Flux::toast(variant: 'success', heading: 'Updated', text: 'Live status changed.');
    }

    public function toggleFeatured(int $id): void
    {
        $ch = LiveChannel::findOrFail($id);
        $ch->update(['is_featured' => !$ch->is_featured]);
        Flux::toast(variant: 'success', heading: 'Updated', text: 'Featured status changed.');
    }

    public function recheckSingle(int $id): void
    {
        $ch = LiveChannel::findOrFail($id);

        CheckAndSyncStreamJob::dispatch(title: $ch->title, url: $ch->stream_url, isExisting: true, category: $ch->category)->onQueue('channels');

        Flux::toast(variant: 'success', heading: 'Queued', text: "Health check queued for: {$ch->title}");
    }

    public function delete(int $id): void
    {
        LiveChannel::findOrFail($id)->delete();
        $this->forgetSelected($id);
        Flux::toast(variant: 'warning', heading: 'Deleted', text: 'Channel moved to trash.');
    }

    public function softDeleteAll(): void
    {
        LiveChannel::query()->delete();
        $this->clearSelection();
        Flux::toast(variant: 'warning', heading: 'All Deleted', text: 'All channels moved to trash.');
    }

    public function restore(int $id): void
    {
        LiveChannel::onlyTrashed()->findOrFail($id)->restore();
        $this->forgetSelected($id);
        Flux::toast(variant: 'success', heading: 'Restored', text: 'Channel restored.');
    }

    public function forceDeleteChannel(int $id): void
    {
        LiveChannel::onlyTrashed()->findOrFail($id)->forceDelete();
        $this->forgetSelected($id);
        Flux::toast(variant: 'danger', heading: 'Deleted', text: 'Channel permanently deleted.');
    }

    public function clearAllTrash(): void
    {
        LiveChannel::onlyTrashed()->get()->each(fn($ch) => $ch->forceDelete());
        $this->clearSelection();
        $this->dispatch('close-modal', name: 'clear-trash-modal');
        Flux::toast(variant: 'danger', heading: 'Trash Cleared', text: 'All trashed channels permanently deleted.');
    }

    public function viewLogs(int $id): void
    {
        $this->selectedChannelIdForLogs = $id;
        $this->dispatch('open-modal', name: 'activity-logs-modal');
    }

    public function resetForm(): void
    {
        $this->reset(['title', 'stream_url', 'embed_url', 'country_code', 'language', 'broadcaster', 'logo_url', 'is_live', 'is_featured', 'sort_order', 'editingChannelId']);
        $this->category = 'football';
        $this->resetValidation();
    }

    // ═══════════════════════════════════════════════════════════════════
    //  BULK ACTIONS
    // ═══════════════════════════════════════════════════════════════════

    private function forgetSelected(int $id): void
    {
        $this->selected = array_values(array_diff($this->selected, [(string) $id]));
        $this->selectAll = false;
    }

    private function selectedIds(): array
    {
        return array_map('intval', $this->selected);
    }

    public function bulkGoLive(): void
    {
        $ids = $this->selectedIds();
        if (empty($ids)) {
            return;
        }
        LiveChannel::query()
            ->whereIn('id', $ids)
            ->update(['is_live' => true]);
        Flux::toast(variant: 'success', heading: 'Updated', text: count($ids) . ' channel(s) marked live.');
        $this->clearSelection();
    }

    public function bulkSetOffline(): void
    {
        $ids = $this->selectedIds();
        if (empty($ids)) {
            return;
        }
        LiveChannel::query()
            ->whereIn('id', $ids)
            ->update(['is_live' => false]);
        Flux::toast(variant: 'success', heading: 'Updated', text: count($ids) . ' channel(s) set offline.');
        $this->clearSelection();
    }

    public function bulkFeature(): void
    {
        $ids = $this->selectedIds();
        if (empty($ids)) {
            return;
        }
        LiveChannel::query()
            ->whereIn('id', $ids)
            ->update(['is_featured' => true]);
        Flux::toast(variant: 'success', heading: 'Updated', text: count($ids) . ' channel(s) featured.');
        $this->clearSelection();
    }

    public function bulkUnfeature(): void
    {
        $ids = $this->selectedIds();
        if (empty($ids)) {
            return;
        }
        LiveChannel::query()
            ->whereIn('id', $ids)
            ->update(['is_featured' => false]);
        Flux::toast(variant: 'success', heading: 'Updated', text: count($ids) . ' channel(s) unfeatured.');
        $this->clearSelection();
    }

    public function bulkChangeCategory(): void
    {
        $ids = $this->selectedIds();
        if (empty($ids)) {
            return;
        }
        if (!array_key_exists($this->bulkCategoryTarget, LiveChannel::CATEGORIES)) {
            return;
        }

        LiveChannel::query()
            ->whereIn('id', $ids)
            ->update(['category' => $this->bulkCategoryTarget]);
        Flux::toast(variant: 'success', heading: 'Updated', text: count($ids) . ' channel(s) category changed.');
        $this->clearSelection();
    }

    public function bulkDelete(): void
    {
        $ids = $this->selectedIds();
        if (empty($ids)) {
            return;
        }
        LiveChannel::query()->whereIn('id', $ids)->delete();
        Flux::toast(variant: 'warning', heading: 'Moved to Trash', text: count($ids) . ' channel(s) trashed.');
        $this->clearSelection();
    }

    public function bulkRestore(): void
    {
        $ids = $this->selectedIds();
        if (empty($ids)) {
            return;
        }
        LiveChannel::onlyTrashed()->whereIn('id', $ids)->restore();
        Flux::toast(variant: 'success', heading: 'Restored', text: count($ids) . ' channel(s) restored.');
        $this->clearSelection();
    }

    public function bulkForceDelete(): void
    {
        $ids = $this->selectedIds();
        if (empty($ids)) {
            return;
        }
        LiveChannel::onlyTrashed()->whereIn('id', $ids)->get()->each(fn($ch) => $ch->forceDelete());
        $this->dispatch('close-modal', name: 'bulk-force-delete-modal');
        Flux::toast(variant: 'danger', heading: 'Deleted', text: count($ids) . ' channel(s) permanently deleted.');
        $this->clearSelection();
    }

    public function bulkRecheckHealth(): void
    {
        $ids = $this->selectedIds();
        if (empty($ids)) {
            return;
        }

        $channels = LiveChannel::query()
            ->whereIn('id', $ids)
            ->select(['id', 'title', 'stream_url', 'category'])
            ->get();
        foreach ($channels as $ch) {
            CheckAndSyncStreamJob::dispatch($ch->title, $ch->stream_url, true, $ch->category)->onQueue('channels');
        }

        Flux::toast(variant: 'success', heading: 'Queued', text: count($ids) . ' channel(s) queued for health check.');
        $this->clearSelection();
    }

    public function bulkExport(): void
    {
        $ids = $this->selectedIds();
        if (empty($ids)) {
            return;
        }

        $rows = LiveChannel::withTrashed()->whereIn('id', $ids)->get();

        $csv = "Title,Stream URL,Category,Broadcaster,Country,Health Score,Failures,Status,Featured,Source,Created At,Updated At\n";
        foreach ($rows as $row) {
            $status = $row->trashed() ? 'Trashed' : ($row->is_live ? 'Live' : 'Offline');
            $csv .= sprintf("\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d,%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n", str_replace('"', '""', $row->title), str_replace('"', '""', $row->stream_url), str_replace('"', '""', $row->category_label), str_replace('"', '""', (string) $row->broadcaster), str_replace('"', '""', (string) $row->country_code), $row->health_score, $row->consecutive_failures, $status, $row->is_featured ? 'Yes' : 'No', str_replace('"', '""', (string) $row->source_label), $row->created_at?->toDateTimeString(), $row->updated_at?->toDateTimeString());
        }

        $filename = 'channels-export-' . now()->format('Ymd-His') . '.csv';
        $this->dispatch('download-csv', filename: $filename, content: base64_encode($csv));
        Flux::toast(variant: 'success', heading: 'Export Ready', text: 'CSV download starting...');
    }

    public function selectAllMatching(): void
    {
        $q = LiveChannel::withTrashed();

        if (filled($this->search)) {
            $s = '%' . $this->search . '%';
            $q->where(fn($qq) => $qq->where('title', 'like', $s)->orWhere('stream_url', 'like', $s)->orWhere('broadcaster', 'like', $s));
        }

        if ($this->categoryFilter !== 'all') {
            $q->where('category', $this->categoryFilter);
        }

        match ($this->statusFilter) {
            'live' => $q->whereNull('deleted_at')->where('is_live', true),
            'offline' => $q->whereNull('deleted_at')->where('is_live', false),
            'featured' => $q->whereNull('deleted_at')->where('is_featured', true),
            'unfeatured' => $q->whereNull('deleted_at')->where('is_featured', false),
            'trashed' => $q->onlyTrashed(),
            default => null,
        };

        if (filled($this->dateFrom)) {
            $q->whereDate('created_at', '>=', $this->dateFrom);
        }
        if (filled($this->dateTo)) {
            $q->whereDate('created_at', '<=', $this->dateTo);
        }

        $this->selected = $q->pluck('id')->map(fn($id) => (string) $id)->all();
        $this->selectAll = true;

        Flux::toast(variant: 'success', heading: 'Selected', text: count($this->selected) . ' channel(s) selected.');
    }

    public function openCreateForm(): void
    {
        $this->resetForm();
        Flux::modal('channel-form-modal')->show();
    }
}; ?>

{{-- ─── HLS.js CDN ──────────────────────────────────────────────────────────── --}}
@assets
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.15/dist/hls.min.js"></script>
@endassets

{{-- ─── Main Container ──────────────────────────────────────────────────────── --}}
<div class="space-y-6" wire:poll.60000ms x-data
    x-on:download-csv.window="
        const blob = new Blob([atob($event.detail.content)], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = $event.detail.filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    ">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-4">
                Live Channel Control Center
            </h1>
            <p class="text-sm text-zinc-500 mt-1">Totthobox — Live Streaming Channel Automation</p>
        </div>

        {{-- ── Sync Button Group ──────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center gap-4">
            <flux:button wire:click="checkChannelHealth" wire:loading.attr="disabled" wire:target="checkChannelHealth"
                variant="subtle" size="sm">
                <span wire:loading wire:target="checkChannelHealth" class="flex items-center gap-2">
                    <flux:icon.loading class="animate-spin h-3.5 w-4.5" />
                    Queuing...
                </span>
                <span wire:loading.remove wire:target="checkChannelHealth">Health Check</span>
            </flux:button>

            <flux:button wire:click="syncBangladesh" wire:loading.attr="disabled" wire:target="syncBangladesh"
                variant="subtle" size="sm">
                <span wire:loading wire:target="syncBangladesh" class="flex items-center gap-2">
                    <flux:icon.loading class="animate-spin h-3.5 w-4.5" />
                    Fetching...
                </span>
                <span wire:loading.remove wire:target="syncBangladesh">BD Sync</span>
            </flux:button>

            <flux:button wire:click="syncFootball" wire:loading.attr="disabled" wire:target="syncFootball"
                variant="subtle" size="sm">
                <span wire:loading wire:target="syncFootball" class="flex items-center gap-2">
                    <flux:icon.loading class="animate-spin h-3.5 w-4.5" />
                    Fetching...
                </span>
                <span wire:loading.remove wire:target="syncFootball">Football Sync</span>
            </flux:button>

            <flux:button wire:click="syncWorldCup" wire:loading.attr="disabled" wire:target="syncWorldCup"
                variant="subtle" size="sm">
                <span wire:loading wire:target="syncWorldCup" class="flex items-center gap-2">
                    <flux:icon.loading class="animate-spin h-3.5 w-4.5" />
                    Fetching...
                </span>
                <span wire:loading.remove wire:target="syncWorldCup">WC2026 Sync</span>
            </flux:button>

            <flux:button wire:click="syncAllChannels" wire:loading.attr="disabled" wire:target="syncAllChannels"
                variant="subtle" size="sm">
                <span wire:loading wire:target="syncAllChannels" class="flex items-center gap-2">
                    <flux:icon.loading class="animate-spin h-3.5 w-4.5" />
                    Syncing...
                </span>
                <span wire:loading.remove wire:target="syncAllChannels">Full Sync</span>
            </flux:button>

            <flux:button wire:click="openCreateForm" variant="primary" size="sm" icon="plus">
                Add Channel
            </flux:button>
        </div>
    </div>

    {{-- ── Sync Status Banner ────────────────────────────────────────────── --}}
    @if ($syncStatus)
        <flux:callout variant="{{ $syncStatus === 'running' ? 'info' : 'danger' }}" icon="information-circle"
            dismissible wire:dismiss="clearSyncStatus">
            <flux:callout.heading>
                @switch($lastSyncType)
                    @case('health')
                        Health Check
                    @break

                    @case('bangladesh')
                        Bangladesh
                    @break

                    @case('football')
                        Football
                    @break

                    @case('worldcup')
                        WC2026
                    @break

                    @default
                        Full Sync
                @endswitch
            </flux:callout.heading>
            <flux:callout.text>
                {{ $syncMessage }}
                <br />
                <code>php artisan queue:work --queue=channels,default</code>
            </flux:callout.text>
        </flux:callout>
    @endif

    {{-- ── Stats Cards ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2  gap-4">
        @php
            $statCards = [
                ['filter' => 'all', 'count' => $this->totalCount, 'label' => 'Total Channels'],
                ['filter' => 'live', 'count' => $this->liveCount, 'label' => 'Live Now'],
                ['filter' => 'featured', 'count' => $this->featuredCount, 'label' => 'Featured'],
                ['filter' => 'trashed', 'count' => $this->trashedCount, 'label' => 'In Trash'],
            ];
        @endphp

        @foreach ($statCards as $card)
            <flux:card wire:click="$set('statusFilter', '{{ $card['filter'] }}')" class="cursor-pointer"
                :class="$this->statusFilter === $card['filter'] ? 'ring-2 ring-zinc-500' : ''">
                <div class="text-center">
                    <p class="text-2xl font-bold">{{ $card['count'] }}</p>
                    <p class="text-xs text-zinc-500 mt-1">{{ $card['label'] }}</p>
                </div>
            </flux:card>
        @endforeach
    </div>

    {{-- ── Category Quick Filters ───────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-4">
        @php
            $catBtns = [
                ['val' => 'all', 'label' => 'All Categories', 'count' => $this->totalCount],
                ['val' => 'worldcup', 'label' => 'FIFA World Cup 2026', 'count' => $this->worldCupCount],
                ['val' => 'bangladesh', 'label' => 'Bangladesh TV', 'count' => $this->bangladeshCount],
                ['val' => 'football', 'label' => 'Football / Sports', 'count' => $this->footballCount],
            ];
        @endphp

        @foreach ($catBtns as $btn)
            <flux:badge wire:click="$set('categoryFilter', '{{ $btn['val'] }}')"
                variant="{{ $categoryFilter === $btn['val'] ? 'solid' : 'subtle' }}" class="cursor-pointer">
                {{ $btn['label'] }} ({{ $btn['count'] }})
            </flux:badge>
        @endforeach
    </div>

    {{-- ── Table Panel ──────────────────────────────────────────────────── --}}
    <flux:card>
        <div class="flex items-center justify-between mb-4 gap-4 flex-wrap">
            <h2 class="text-lg font-semibold">Channel List</h2>

            @if ($this->trashedCount > 0 && $statusFilter !== 'trashed')
                <flux:modal.trigger name="clear-trash-modal">
                    <flux:button variant="danger" size="xs" icon="trash">
                        Clear Trash ({{ $this->trashedCount }})
                    </flux:button>
                </flux:modal.trigger>
            @endif
        </div>

        {{-- ── Filter Bar ─────────────────────────────────────────────── --}}
        <div class="mb-4 space-y-2.5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <flux:input wire:model.live.debounce.350ms="search" type="search"
                    placeholder="Search title, URL, broadcaster..." icon="magnifying-glass" size="sm" clearable />
                <flux:select wire:model.live="categoryFilter" size="sm">
                    <flux:select.option value="all">All Categories</flux:select.option>
                    <flux:select.option value="worldcup">FIFA World Cup 2026</flux:select.option>
                    <flux:select.option value="bangladesh">Bangladesh TV</flux:select.option>
                    <flux:select.option value="football">Football / Sports</flux:select.option>
                </flux:select>
                <flux:select wire:model.live="statusFilter" size="sm">
                    <flux:select.option value="all">All Statuses</flux:select.option>
                    <flux:select.option value="live">Live</flux:select.option>
                    <flux:select.option value="offline">Offline</flux:select.option>
                    <flux:select.option value="healthy">Healthy (<3 fails)</flux:select.option>
                            <flux:select.option value="featured">Featured</flux:select.option>
                            <flux:select.option value="unfeatured">Unfeatured</flux:select.option>
                            <flux:select.option value="trashed">Trashed</flux:select.option>
                </flux:select>
            </div>

            <div class="grid grid-cols-2  gap-2">
                <flux:select wire:model.live="sortBy" size="sm">
                    <flux:select.option value="newest">Newest First</flux:select.option>
                    <flux:select.option value="oldest">Oldest First</flux:select.option>
                    <flux:select.option value="updated">Recently Updated</flux:select.option>
                    <flux:select.option value="health_desc">Health: High Low</flux:select.option>
                    <flux:select.option value="health_asc">Health: Low High</flux:select.option>
                    <flux:select.option value="sort_order">Sort Order</flux:select.option>
                    <flux:select.option value="name_asc">Name A Z</flux:select.option>
                    <flux:select.option value="name_desc">Name Z A</flux:select.option>
                </flux:select>
                <flux:select wire:model.live="perPage" size="sm">
                    <flux:select.option value="15">15 / page</flux:select.option>
                    <flux:select.option value="25">25 / page</flux:select.option>
                    <flux:select.option value="50">50 / page</flux:select.option>
                    <flux:select.option value="100">100 / page</flux:select.option>
                </flux:select>
                <flux:input wire:model.live="dateFrom" type="date" size="sm" />
                <flux:input wire:model.live="dateTo" type="date" size="sm" />
            </div>

            <div class="flex items-center justify-end gap-4">
                @if ($this->hasActiveFilters)
                    <flux:button wire:click="resetFilters" variant="subtle" size="sm" icon="x-mark">
                        Reset Filters
                    </flux:button>
                @endif
                <span class="text-xs text-zinc-500">
                    <span class="font-bold">{{ $this->channels->total() }}</span> result(s)
                </span>
            </div>
        </div>

        {{-- ── Bulk Action Toolbar ─────────────────────────────────────── --}}
        @if (count($selected) > 0)
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="text-sm font-semibold">{{ count($selected) }} selected</span>

                @if (!$selectAll && count($selected) === $this->channels->count() && $this->channels->total() > $this->channels->count())
                    <flux:button wire:click="selectAllMatching" variant="subtle" size="xs">
                        Select all {{ $this->channels->total() }} matching
                    </flux:button>
                @endif

                @if ($statusFilter !== 'trashed')
                    <flux:button wire:click="bulkGoLive" variant="subtle" size="xs">Go Live</flux:button>
                    <flux:button wire:click="bulkSetOffline" variant="subtle" size="xs">Offline</flux:button>
                    <flux:button wire:click="bulkFeature" variant="subtle" size="xs">Feature</flux:button>
                    <flux:button wire:click="bulkUnfeature" variant="subtle" size="xs">Unfeature</flux:button>
                    <flux:button wire:click="bulkRecheckHealth" variant="subtle" size="xs">Recheck</flux:button>

                    <div class="flex items-center gap-1">
                        <flux:select wire:model="bulkCategoryTarget" size="xs" class="w-36">
                            <flux:select.option value="worldcup">WC 2026</flux:select.option>
                            <flux:select.option value="bangladesh">Bangladesh</flux:select.option>
                            <flux:select.option value="football">Football</flux:select.option>
                        </flux:select>
                        <flux:button wire:click="bulkChangeCategory" variant="subtle" size="xs">Move
                        </flux:button>
                    </div>

                    <flux:button wire:confirm="Move {{ count($selected) }} channel(s) to trash?"
                        wire:click="bulkDelete" variant="subtle" size="xs">
                        Trash
                    </flux:button>
                @else
                    <flux:button wire:click="bulkRestore" variant="subtle" size="xs">Restore</flux:button>
                    <flux:modal.trigger name="bulk-force-delete-modal">
                        <flux:button variant="subtle" size="xs">Delete Forever</flux:button>
                    </flux:modal.trigger>
                @endif

                <flux:button wire:click="bulkExport" variant="subtle" size="xs">CSV</flux:button>
                <flux:button wire:click="clearSelection" variant="ghost" size="xs" icon="x-mark"
                    class="ml-auto">Clear</flux:button>
            </div>
        @endif

        {{-- ── Table ──────────────────────────────────────────────────── --}}
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="w-10">
                    <input type="checkbox" wire:model.live="selectAll" class="rounded" />
                </flux:table.column>
                <flux:table.column class="w-32">Preview</flux:table.column>
                <flux:table.column>Logo</flux:table.column>
                <flux:table.column>Channel</flux:table.column>
                <flux:table.column class="text-center">Category</flux:table.column>
                <flux:table.column class="text-center">Health</flux:table.column>
                <flux:table.column class="text-center">Status</flux:table.column>
                <flux:table.column class="text-right">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->channels as $channel)
                    <flux:table.row wire:key="row-{{ $channel->id }}"
                        :class="$channel->trashed() ? 'opacity-40' : ''">
                        {{-- Checkbox --}}
                        <flux:table.cell>
                            <input type="checkbox" value="{{ $channel->id }}" wire:model.live="selected"
                                class="rounded" />
                        </flux:table.cell>

                        {{-- ── HLS Preview ──────────────────────────────────────── --}}
                        <flux:table.cell>
                            @if ($channel->trashed() || !$channel->stream_url)
                                <div
                                    class="w-28 aspect-video bg-zinc-200 rounded flex items-center justify-center text-xs">
                                    No Stream
                                </div>
                            @else
                                <div class="relative w-28 aspect-video rounded overflow-hidden bg-black"
                                    x-data="hlsPlayer('{{ $channel->stream_url }}', {{ $channel->id }})" x-init="init()"
                                    wire:key="player-{{ $channel->id }}">
                                    <video x-ref="video" class="w-full h-full object-cover" muted playsinline
                                        autoplay></video>

                                    <div x-ref="loading"
                                        class="absolute inset-0 flex items-center justify-center bg-zinc-400/10">
                                        <flux:icon.loading class="animate-spin h-5 w-5 text-white" />
                                    </div>

                                    <div x-ref="error"
                                        class="absolute inset-0 hidden flex-col items-center justify-center bg-black/70 text-xs text-white gap-1">
                                        <span>Offline</span>
                                    </div>

                                    @if ($channel->is_live)
                                        <span class="absolute top-1.5 left-1.5 flex h-2 w-2 z-10">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                        </span>
                                    @endif

                                    @if ($channel->is_featured)
                                        <flux:icon.star
                                            class="absolute top-1.5 right-1.5 h-3.5 w-4.5 text-amber-400 z-10"
                                            variant="solid" />
                                    @endif
                                </div>
                            @endif
                        </flux:table.cell>

                        {{-- Channel Info --}}
                        <flux:table.cell class="font-medium">
                            <flux:avatar :src="$channel->logo_url" :name="$channel->title" :alt="$channel->title"
                                color="auto" size="sm" class="rounded" />
                        </flux:table.cell>
                        {{-- Channel Info --}}
                        <flux:table.cell class="font-medium">
                            <span class="block text-sm font-semibold">{{ $channel->title }}</span>
                            <span class="text-xs text-zinc-500 block max-w-xs truncate mt-0.5"
                                title="{{ $channel->stream_url }}">
                                {{ $channel->stream_url }}
                            </span>
                            <span class="text-xs text-zinc-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                @if ($channel->broadcaster)
                                    <span>{{ $channel->broadcaster }}</span>
                                @endif
                                @if ($channel->country_code)
                                    <span class="uppercase font-mono">{{ $channel->country_code }}</span>
                                @endif
                                @if ($channel->source_label)
                                    <span>{{ $channel->source_label }}</span>
                                @endif
                                <span>{{ $channel->updated_at->diffForHumans() }}</span>
                            </span>
                        </flux:table.cell>

                        {{-- Category --}}
                        <flux:table.cell class="text-center">
                            <flux:badge size="sm"
                                :color="match($channel->category) {
                                                                                                                                                                                                'worldcup'   => 'yellow',
                                                                                                                                                                                                'bangladesh' => 'green',
                                                                                                                                                                                                'football'   => 'blue',
                                                                                                                                                                                                default      => 'zinc',
                                                                                                                                                                                            }">
                                {{ $channel->category_label }}
                            </flux:badge>
                        </flux:table.cell>

                        {{-- Health --}}
                        <flux:table.cell class="text-center">
                            <div class="flex flex-col items-center gap-1">
                                <flux:badge size="sm"
                                    :color="match($channel->health_badge) {
                                                                                                                                                                                                                        'excellent' => 'emerald',
                                                                                                                                                                                                                        'good'      => 'sky',
                                                                                                                                                                                                                        'poor'      => 'amber',
                                                                                                                                                                                                                        default     => 'rose',
                                                                                                                                                                                                                    }">
                                    {{ $channel->health_score }}
                                </flux:badge>
                                @if ($channel->consecutive_failures > 0)
                                    <span class="text-xs text-red-500">{{ $channel->consecutive_failures }}</span>
                                @endif
                                @if ($channel->last_checked_at)
                                    <span
                                        class="text-xs text-zinc-500">{{ $channel->last_checked_at->diffForHumans(short: true) }}</span>
                                @endif
                            </div>
                        </flux:table.cell>

                        {{-- Status --}}
                        <flux:table.cell class="text-center">
                            @if ($channel->trashed())
                                <flux:badge variant="danger" size="sm">Trashed</flux:badge>
                            @else
                                <div class="flex flex-col items-center gap-1">
                                    <flux:button wire:click="toggleLive({{ $channel->id }})"
                                        wire:loading.attr="disabled" wire:target="toggleLive({{ $channel->id }})"
                                        :variant="$channel->is_live ? 'primary' : 'subtle'" size="xs"
                                        class="font-bold min-w-[72px]">
                                        <span wire:loading wire:target="toggleLive({{ $channel->id }})">...</span>
                                        <span wire:loading.remove wire:target="toggleLive({{ $channel->id }})">
                                            {{ $channel->is_live ? 'LIVE' : 'OFF' }}
                                        </span>
                                    </flux:button>

                                    <button wire:click="toggleFeatured({{ $channel->id }})"
                                        class="text-xs flex items-center gap-1 cursor-pointer">
                                        <flux:icon.star class="h-3 w-4"
                                            :variant="$channel->is_featured ? 'solid' : 'outline'" />
                                        {{ $channel->is_featured ? 'Featured' : 'Feature' }}
                                    </button>
                                </div>
                            @endif
                        </flux:table.cell>

                        {{-- Actions --}}
                        <flux:table.cell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button wire:click="viewLogs({{ $channel->id }})" variant="subtle"
                                    size="xs" icon="document-text" square title="Activity Log" />

                                @if ($channel->trashed())
                                    <flux:button wire:click="restore({{ $channel->id }})" variant="subtle"
                                        size="xs" icon="arrow-path" square title="Restore" />
                                    <flux:button wire:confirm="Permanently delete this channel? This cannot be undone."
                                        wire:click="forceDeleteChannel({{ $channel->id }})" variant="subtle"
                                        size="xs" icon="x-mark" square title="Delete Forever" />
                                @else
                                    <flux:button wire:click="recheckSingle({{ $channel->id }})" variant="subtle"
                                        size="xs" icon="signal" square title="Recheck Stream Health" />
                                    <flux:button wire:click="edit({{ $channel->id }})" variant="subtle"
                                        size="xs" icon="pencil-square" square title="Edit" />
                                    <flux:button wire:confirm="Move this channel to trash?"
                                        wire:click="delete({{ $channel->id }})" variant="subtle" size="xs"
                                        icon="trash" square title="Trash" />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-12">
                            <div class="flex flex-col items-center gap-2 text-zinc-500">
                                <span class="text-sm">
                                    {{ $this->hasActiveFilters ? 'No channels match your filters.' : 'No channels found. Run a sync to fetch channels.' }}
                                </span>
                                @if ($this->hasActiveFilters)
                                    <flux:button wire:click="resetFilters" variant="subtle" size="xs">
                                        Clear Filters
                                    </flux:button>
                                @else
                                    <flux:button wire:click="syncAllChannels" variant="subtle" size="sm">
                                        Run Full Sync
                                    </flux:button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{-- Delete all button --}}
        @if ($this->totalCount > 0 && $statusFilter === 'all' && !$this->hasActiveFilters)
            <div class="mt-3 flex justify-end">
                <flux:button wire:confirm="Move all active channels to trash?" wire:click="softDeleteAll"
                    variant="subtle" size="xs" icon="trash">
                    Move All to Trash
                </flux:button>
            </div>
        @endif

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $this->channels->links() }}
        </div>
    </flux:card>

    {{-- ── Modals ────────────────────────────────────────────────────────── --}}

    {{-- Channel Form Modal --}}
    <flux:modal name="channel-form-modal" class="md:w-[600px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingChannelId ? 'Edit Channel' : 'Add New Channel' }}
                </flux:heading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="title" label="Channel Name" placeholder="e.g. T Sports HD" required />
                <flux:input wire:model="stream_url" label="Stream URL (HLS / M3U8)"
                    placeholder="https://example.com/live/stream.m3u8" required />
                <flux:input wire:model="embed_url" label="Embed URL (iframe fallback)"
                    placeholder="https://example.com/embed/stream" />

                <flux:select wire:model="category" label="Category" required>
                    <flux:select.option value="worldcup">FIFA World Cup 2026</flux:select.option>
                    <flux:select.option value="bangladesh">Bangladesh TV</flux:select.option>
                    <flux:select.option value="football">Football / Sports</flux:select.option>
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="country_code" label="Country Code" placeholder="BD" maxlength="5" />
                    <flux:input wire:model="language" label="Language" placeholder="bengali" />
                </div>

                <flux:input wire:model="broadcaster" label="Broadcaster" placeholder="T Sports" />
                <flux:input wire:model="logo_url" label="Logo URL" placeholder="https://..." />
                <flux:input wire:model="sort_order" type="number" label="Sort Order" placeholder="0" />

                <flux:checkbox wire:model="is_live" label="Currently Live"
                    description="Marks this channel as live on the website." />
                <flux:checkbox wire:model="is_featured" label="Featured"
                    description="Pins this channel to the top of its category." />

                <div class="flex items-center gap-2 pt-2">
                    <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled"
                        wire:target="save">
                        <span wire:loading wire:target="save">Saving...</span>
                        <span wire:loading.remove wire:target="save">
                            {{ $editingChannelId ? 'Update Channel' : 'Save Channel' }}
                        </span>
                    </flux:button>
                    <flux:modal.close>
                        <flux:button variant="subtle" class="w-full">Cancel</flux:button>
                    </flux:modal.close>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Clear Trash Modal --}}
    <flux:modal name="clear-trash-modal" class="md:w-[440px]">
        <div class="space-y-4 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full">
                <flux:icon.trash class="h-6 w-6 text-red-500" />
            </div>
            <div>
                <flux:heading size="lg">Confirm Permanent Delete</flux:heading>
                <p class="text-sm text-zinc-500 mt-1">
                    <strong class="text-red-500">{{ $this->trashedCount }}</strong> trashed channels will be
                    permanently deleted.
                </p>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <flux:button wire:click="clearAllTrash" variant="danger" class="w-full" wire:loading.attr="disabled"
                    wire:target="clearAllTrash">
                    <span wire:loading wire:target="clearAllTrash">Deleting...</span>
                    <span wire:loading.remove wire:target="clearAllTrash">Yes, Delete All</span>
                </flux:button>
                <flux:modal.close class="w-full">
                    <flux:button variant="subtle" class="w-full">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Bulk Force Delete Modal --}}
    <flux:modal name="bulk-force-delete-modal" class="md:w-[440px]">
        <div class="space-y-4 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full">
                <flux:icon.x-mark class="h-6 w-6 text-red-500" />
            </div>
            <div>
                <flux:heading size="lg">Confirm Permanent Delete</flux:heading>
                <p class="text-sm text-zinc-500 mt-1">
                    <strong class="text-red-500">{{ count($selected) }}</strong> selected channel(s) will be
                    permanently deleted.
                </p>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <flux:button wire:click="bulkForceDelete" variant="danger" class="w-full"
                    wire:loading.attr="disabled" wire:target="bulkForceDelete">
                    <span wire:loading wire:target="bulkForceDelete">Deleting...</span>
                    <span wire:loading.remove wire:target="bulkForceDelete">Yes, Delete Forever</span>
                </flux:button>
                <flux:modal.close class="w-full">
                    <flux:button variant="subtle" class="w-full">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Activity Log Modal --}}
    <flux:modal name="activity-logs-modal" class="md:w-[600px]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Audit Log</flux:heading>
                <p class="text-sm text-zinc-500">Who changed what and when for this channel.</p>
            </div>
            <div class="max-h-80 overflow-y-auto space-y-2 pr-1">
                @forelse($this->activities as $activity)
                    <flux:card>
                        <div class="flex justify-between items-center text-sm text-zinc-500 mb-2.5">
                            <span class="font-medium">{{ $activity->description }}</span>
                            <span class="shrink-0 ml-2 text-xs">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-xs text-zinc-500">
                            By: {{ $activity->causer_id ?? 'System / Queue Worker' }}
                        </div>
                        @if (isset($activity->properties['attributes']))
                            <pre class="mt-2 p-2 bg-zinc-100 rounded text-xs overflow-x-auto">{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        @endif
                    </flux:card>
                @empty
                    <div class="text-center py-6 text-zinc-500 text-sm">No activity logs found.</div>
                @endforelse
            </div>
            <div class="flex justify-end pt-2">
                <flux:modal.close>
                    <flux:button variant="subtle">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

</div>

@push('scripts')
    <script>
        function hlsPlayer(src, channelId) {
            return {
                src,
                channelId,
                hls: null,
                retryTimer: null,
                retries: 0,
                MAX_RETRIES: 4,
                RETRY_DELAY: 6000,

                init() {
                    this.$cleanup(() => {
                        this.destroy();
                    });

                    this.startPlayer();
                },

                startPlayer() {
                    if (!this.src) {
                        this.showError();
                        return;
                    }

                    this.showLoading();
                    this.clearRetryTimer();

                    if (this.hls) {
                        this.hls.destroy();
                        this.hls = null;
                    }

                    const video = this.$refs.video;
                    if (!video) return;

                    video.muted = true;
                    video.playsInline = true;

                    if (window.Hls && Hls.isSupported()) {
                        const hls = new Hls({
                            maxBufferLength: 8,
                            maxMaxBufferLength: 20,
                            liveSyncDurationCount: 2,
                            liveMaxLatencyDurationCount: 5,
                            enableWorker: true,
                            lowLatencyMode: true,
                            enableSoftwareAES: true,
                            xhrSetup: (xhr) => {
                                xhr.timeout = 10000;
                            },
                        });

                        this.hls = hls;

                        hls.on(Hls.Events.MANIFEST_PARSED, () => {
                            this.hideLoading();
                            video.play().catch(() => this.showError());
                        });

                        hls.on(Hls.Events.ERROR, (event, data) => {
                            if (!data.fatal) return;

                            console.warn('[HLS Player #' + this.channelId + '] Fatal error:', data.type, data
                                .details);

                            if (this.retries < this.MAX_RETRIES) {
                                this.retries++;
                                if (data.type === Hls.ErrorTypes.MEDIA_ERROR) {
                                    hls.recoverMediaError();
                                } else {
                                    setTimeout(() => {
                                        if (this.hls === hls) hls.startLoad();
                                    }, 2000);
                                }
                            } else {
                                this.scheduleRetry();
                            }
                        });

                        hls.loadSource(this.src);
                        hls.attachMedia(video);

                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        video.src = this.src;
                        video.addEventListener('loadedmetadata', () => {
                            this.hideLoading();
                            video.play().catch(() => this.showError());
                        }, {
                            once: true
                        });
                        video.addEventListener('error', () => {
                            this.scheduleRetry();
                        }, {
                            once: true
                        });

                    } else {
                        this.showError();
                    }
                },

                scheduleRetry() {
                    if (this.hls) {
                        this.hls.destroy();
                        this.hls = null;
                    }
                    this.showError();

                    this.retryTimer = setTimeout(() => {
                        this.retries = 0;
                        this.startPlayer();
                    }, this.RETRY_DELAY);
                },

                clearRetryTimer() {
                    if (this.retryTimer) {
                        clearTimeout(this.retryTimer);
                        this.retryTimer = null;
                    }
                },

                destroy() {
                    this.clearRetryTimer();
                    if (this.hls) {
                        this.hls.destroy();
                        this.hls = null;
                    }
                    const video = this.$refs.video;
                    if (video) {
                        video.pause();
                        video.src = '';
                        video.srcObject = null;
                    }
                },

                showLoading() {
                    if (this.$refs.loading) {
                        this.$refs.loading.style.display = 'flex';
                        this.$refs.loading.style.opacity = '1';
                    }
                    if (this.$refs.error) {
                        this.$refs.error.style.display = 'none';
                    }
                },

                hideLoading() {
                    if (this.$refs.loading) {
                        this.$refs.loading.style.opacity = '0';
                        setTimeout(() => {
                            if (this.$refs.loading) {
                                this.$refs.loading.style.display = 'none';
                            }
                        }, 300);
                    }
                },

                showError() {
                    if (this.$refs.loading) {
                        this.$refs.loading.style.display = 'none';
                    }
                    if (this.$refs.error) {
                        this.$refs.error.style.display = 'flex';
                    }
                },
            };
        }
    </script>
@endpush
