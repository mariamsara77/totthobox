<?php
use App\Jobs\GenerateContentJob;
use App\Jobs\RemakeContentJob;
use App\Jobs\AutoImproveModelJob;
use App\Services\AutonomousContentAgent;
use App\Services\ContentGenerationService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Bus;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Flux\Flux;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    // ── Form State ───────────────────────────────────────────────────────────
    public string $instruction = '';
    public string $activeModel = 'TourismBd';
    public int $count = 10;
    public bool $optRealistic = true;
    public bool $optLocalized = true;
    public bool $optImages = true;
    public bool $useQueue = true;

    // ── Job Progress State (Generate/Remake) ────────────────────────────────
    public ?string $currentJobId = null;
    public string $jobStatus = '';
    public string $jobStage = '';
    public int $jobInserted = 0;
    public int $jobSkipped = 0;
    public int $jobImages = 0;
    public string $jobProvider = '';
    public ?string $jobError = null;
    public int $jobRelations = 0;
    public array $jobErrors = [];

    // ── UI State ─────────────────────────────────────────────────────────────
    public string $activeTab = 'generate';
    public array $healthResults = [];
    public array $logs = [];
    public array $stats = [];
    public bool $healthRunning = false;

    // ── Relation State ───────────────────────────────────────────────────────
    public array $relationContext = [];
    public bool $relationLoaded = false;

    // ── Remake State ─────────────────────────────────────────────────────────
    public string $remakeIds = '';
    public int $remakeBatch = 5;

    // ── Agent State ──────────────────────────────────────────────────────────
    public array $agentStats = [];
    public array $agentProgress = [];
    public ?string $agentBatchId = null;
    public array $agentBatchInfo = [];
    public bool $agentRunning = false;

    private function modelRegistry(): array
    {
        return collect(config('content_agent.models', []))->mapWithKeys(fn($cfg, $name) => [$name => $cfg['class']])->toArray();
    }

    private function modelConfig(string $name): ?array
    {
        return config("content_agent.models.{$name}");
    }

    public function mount(): void
    {
        $this->refreshStats();
        $this->loadRelationContext();
    }

    public function updatedActiveModel(): void
    {
        $this->relationLoaded = false;
        $this->relationContext = [];
        $this->loadRelationContext();
    }

    public function updatedActiveTab(string $value): void
    {
        if ($value === 'agent') {
            $this->refreshAgentStats();
            $this->refreshAgentProgress();
        }
    }

    #[Computed]
    public function activeSchema(): array
    {
        $registry = $this->modelRegistry();
        if (!isset($registry[$this->activeModel])) {
            return [];
        }

        $model = new ($registry[$this->activeModel])();
        $table = $model->getTable();

        return collect(Schema::getColumnListing($table))
            ->map(
                fn($col) => [
                    'name' => $col,
                    'type' => Schema::getColumnType($table, $col),
                    'fillable' => in_array($col, $model->getFillable()),
                    'is_fk' => in_array($col, $this->relationContext['fk_columns'] ?? []),
                ],
            )
            ->toArray();
    }

    #[Computed]
    public function isRunning(): bool
    {
        return in_array($this->jobStatus, ['pending', 'running'], true);
    }

    #[Computed]
    public function isAgentRunning(): bool
    {
        if ($this->agentRunning) {
            return true;
        }
        foreach ($this->agentProgress as $p) {
            if (in_array($p['status'] ?? '', ['pending', 'running'], true)) {
                return true;
            }
        }
        return false;
    }

    #[Computed]
    public function progressPercent(): int
    {
        if ($this->count <= 0) {
            return 0;
        }
        return (int) min(100, round(($this->jobInserted / $this->count) * 100));
    }

    #[Computed]
    public function hasBelongsTo(): bool
    {
        return !empty($this->relationContext['belongs_to']);
    }

    #[Computed]
    public function hasBelongsToMany(): bool
    {
        return !empty($this->relationContext['belongs_to_many']);
    }

    public function loadRelationContext(ContentGenerationService $service = null): void
    {
        $registry = $this->modelRegistry();
        if (!isset($registry[$this->activeModel])) {
            return;
        }

        try {
            $service ??= app(ContentGenerationService::class);
            $model = new ($registry[$this->activeModel])();
            $this->relationContext = $service->resolveRelations($model);
            $this->relationLoaded = true;

            $btCount = count($this->relationContext['belongs_to'] ?? []);
            $m2mCount = count($this->relationContext['belongs_to_many'] ?? []);

            if ($btCount > 0 || $m2mCount > 0) {
                $this->addLog("🔗 {$btCount} BelongsTo + {$m2mCount} BelongsToMany relations detected for {$this->activeModel}", 'info');
            }
        } catch (\Throwable $e) {
            $this->addLog("Relation scan failed: {$e->getMessage()}", 'error');
        }
    }

    public function generate(): void
    {
        $this->validate([
            'instruction' => 'required|min:10|max:1000',
            'count' => 'required|integer|min:1|max:100',
            'activeModel' => 'required|in:' . implode(',', array_keys($this->modelRegistry())),
        ]);

        $this->resetJobState();
        $this->logs = [];

        $modelClass = $this->modelRegistry()[$this->activeModel];
        $options = [
            'realistic' => $this->optRealistic,
            'localized' => $this->optLocalized,
            'with_images' => $this->optImages,
        ];

        if ($this->useQueue) {
            $jobId = GenerateContentJob::dispatchWithTracking(modelClass: $modelClass, instruction: $this->instruction, count: $this->count, options: $options, causerType: \App\Models\User::class, causerId: auth()->id());

            $this->currentJobId = $jobId;
            $this->jobStatus = 'pending';
            $this->jobStage = 'Queue-এ পাঠানো হয়েছে...';

            $this->addLog("Job dispatched: {$jobId}", 'info');
            $this->addLog('Queue: php artisan queue:work --queue=ai-generation', 'info');

            if ($this->relationContext) {
                $btCount = count($this->relationContext['belongs_to'] ?? []);
                $m2mCount = count($this->relationContext['belongs_to_many'] ?? []);
                $this->addLog("FK relations: {$btCount} BelongsTo, {$m2mCount} BelongsToMany", 'success');
            }
        } else {
            $this->jobStatus = 'running';
            $this->jobStage = 'Synchronous mode...';
            $this->addLog('Sync mode (useQueue=false) — blocks UI', 'warn');

            try {
                $service = app(ContentGenerationService::class);
                $model = new $modelClass();

                $result = $service->generate(model: $model, instruction: $this->instruction, count: $this->count, options: $options, causerType: \App\Models\User::class, causerId: auth()->id());

                $inserted = 0;
                foreach ($result['records'] as $record) {
                    $pivotData = $record['__pivot__'] ?? [];
                    unset($record['__pivot__']);

                    try {
                        $saved = $modelClass::create($record);
                        $inserted++;

                        foreach ($pivotData as $relName => $relIds) {
                            if (method_exists($saved, $relName)) {
                                $saved->{$relName}()->sync($relIds);
                            }
                        }

                        if ($this->optImages && method_exists($saved, 'addMediaFromUrl')) {
                            $keyword = $record['name'] ?? ($record['title'] ?? $this->instruction);
                            $service->attachImagesToSavedModels($saved, $keyword);
                            $this->jobImages++;
                        }
                    } catch (\Throwable $e) {
                        $this->addLog("Row failed: {$e->getMessage()}", 'error');
                    }
                }

                $this->jobInserted = $inserted;
                $this->jobSkipped = $result['skipped_dupes'];
                $this->jobProvider = $result['provider_used'];
                $this->jobRelations = count($result['relation_context']['belongs_to'] ?? []);
                $this->jobStatus = 'done';
                $this->jobStage = "সম্পন্ন — {$inserted} records যোগ হয়েছে।";

                $this->refreshStats();
                Flux::toast("✅ {$inserted} records generated successfully.", variant: 'success');
                $this->addLog("Provider: {$result['provider_used']}", 'success');
            } catch (\Throwable $e) {
                $this->jobStatus = 'failed';
                $this->jobError = $e->getMessage();
                $this->addLog("Fatal: {$e->getMessage()}", 'error');
                Flux::toast('Generation failed. Check logs.', variant: 'danger');
            }
        }
    }

    public function pollJobProgress(): void
    {
        if ($this->currentJobId && $this->isRunning) {
            $progress = GenerateContentJob::getProgress($this->currentJobId);
            if ($progress) {
                $this->jobStatus = $progress['status'];
                $this->jobStage = $progress['stage'] ?? '';
                $this->jobInserted = $progress['inserted'] ?? 0;
                $this->jobSkipped = $progress['skipped'] ?? 0;
                $this->jobImages = $progress['images'] ?? 0;
                $this->jobProvider = $progress['provider'] ?? '';
                $this->jobError = $progress['error'] ?? null;
                $this->jobRelations = $progress['relations_detected'] ?? 0;
                $this->jobErrors = $progress['errors'] ?? [];

                if ($this->jobStatus === 'done') {
                    $this->refreshStats();
                    Flux::toast("✅ {$this->jobInserted} records generated!", variant: 'success');
                    $this->addLog("Job complete via {$this->jobProvider}", 'success');
                    if (!empty($this->jobErrors)) {
                        $this->addLog(count($this->jobErrors) . ' row errors — check logs', 'warn');
                    }
                }

                if ($this->jobStatus === 'failed') {
                    Flux::toast('Job failed: ' . ($this->jobError ?? 'Unknown'), variant: 'danger');
                    $this->addLog("Failed: {$this->jobError}", 'error');
                }
            }
        }

        if ($this->activeTab === 'agent') {
            $this->refreshAgentProgress();
        }
    }

    public function runHealthCheck(ContentGenerationService $service): void
    {
        $this->healthRunning = true;
        $this->healthResults = [];
        $this->activeTab = 'health';
        $this->addLog('Running provider health check...', 'info');

        $results = $service->healthCheck();

        foreach ($results as $key => $result) {
            $this->healthResults[$key] = $result;
            $icon = $result['status'] === 'ok' ? '✅' : '❌';
            $this->addLog("{$icon} {$key}: {$result['status']} ({$result['latency_ms']}ms)", $result['status'] === 'ok' ? 'success' : 'error');
        }

        $this->healthRunning = false;
    }

    public function resetProviders(ContentGenerationService $service): void
    {
        $service->resetBlacklist();
        $this->addLog('Provider blacklist cleared.', 'success');
        Flux::toast('Provider stats reset.', variant: 'success');
    }

    public function refreshStats(): void
    {
        $this->stats = collect($this->modelRegistry())
            ->map(
                fn($class, $name) => [
                    'name' => $name,
                    'table' => new $class()->getTable(),
                    'count' => $class::count(),
                    'columns' => count(Schema::getColumnListing(new $class()->getTable())),
                ],
            )
            ->values()
            ->toArray();
    }

    public function cancelJob(): void
    {
        $this->resetJobState();
        $this->addLog('Job monitoring stopped.', 'warn');
    }

    public function remake(): void
    {
        $this->validate(['activeModel' => 'required|in:' . implode(',', array_keys($this->modelRegistry()))]);

        $modelClass = $this->modelRegistry()[$this->activeModel];
        $ids = [];

        if (!empty(trim($this->remakeIds))) {
            $ids = array_map('intval', array_filter(array_map('trim', explode(',', $this->remakeIds))));
        }

        $jobId = RemakeContentJob::dispatchWithTracking(modelClass: $modelClass, ids: $ids, batchSize: $this->remakeBatch);

        $this->currentJobId = $jobId;
        $this->jobStatus = 'pending';
        $this->jobStage = 'Remake queue-এ পাঠানো হয়েছে...';
        $this->addLog('Remake job dispatched', 'info');
        $this->addLog('IDs: ' . (empty($ids) ? 'সব records' : implode(', ', $ids)), 'info');
    }

    public function setActiveModel(string $model): void
    {
        $this->activeModel = $model;
        $this->loadRelationContext();
    }

    public function refreshAgentStats(AutonomousContentAgent $agent = null): void
    {
        $agent ??= app(AutonomousContentAgent::class);
        $this->agentStats = $agent->getModelStats();
    }

    public function refreshAgentProgress(): void
    {
        $progress = [];
        foreach (array_keys($this->modelRegistry()) as $name) {
            $p = AutoImproveModelJob::getProgress($name);
            if ($p) {
                $progress[$name] = $p;
            }
        }
        $this->agentProgress = $progress;

        if ($this->agentBatchId) {
            $batch = Bus::findBatch($this->agentBatchId);
            if ($batch) {
                $this->agentBatchInfo = [
                    'total' => $batch->totalJobs,
                    'pending' => $batch->pendingJobs,
                    'failed' => $batch->failedJobs,
                    'processed' => $batch->processedJobs(),
                    'progress' => $batch->progress(),
                    'finished' => $batch->finished(),
                ];

                if ($batch->finished() && $this->agentRunning) {
                    $this->agentRunning = false;
                    $this->refreshAgentStats();
                    $this->refreshStats();
                    Flux::toast('🎉 Autonomous agent batch complete!', variant: 'success');
                }
            }
        }
    }

    public function runAgentForModel(string $modelName): void
    {
        $config = $this->modelConfig($modelName);

        if (!$config) {
            Flux::toast("Model '{$modelName}' registry তে পাওয়া যায়নি।", variant: 'danger');
            return;
        }

        if (!empty($config['auto_generate_disabled'])) {
            Flux::toast("{$config['display_name']} — auto-generation disabled (sensitive/manual data)।", variant: 'warning');
            return;
        }

        AutoImproveModelJob::dispatch($modelName);
        $this->addLog("Agent job dispatched for {$modelName}", 'info');
        Flux::toast("🤖 {$config['display_name']} এর জন্য agent job শুরু হয়েছে।", variant: 'success');
        $this->refreshAgentProgress();
    }

    public function runAgentFullCycle(AutonomousContentAgent $agent): void
    {
        $batch = $agent->dispatchFullCycleAsJobs();

        $this->agentBatchId = $batch->id;
        $this->agentRunning = true;
        $this->addLog("Full agent cycle dispatched — batch {$batch->id} ({$batch->totalJobs} models)", 'success');
        Flux::toast("🤖 Autonomous agent শুরু হয়েছে — {$batch->totalJobs}টি model প্রসেস হচ্ছে সমান্তরালে।", variant: 'success');
        $this->refreshAgentProgress();
    }

    private function resetJobState(): void
    {
        $this->currentJobId = null;
        $this->jobStatus = '';
        $this->jobStage = '';
        $this->jobInserted = 0;
        $this->jobSkipped = 0;
        $this->jobImages = 0;
        $this->jobProvider = '';
        $this->jobError = null;
        $this->jobRelations = 0;
        $this->jobErrors = [];
    }

    private function addLog(string $message, string $type = 'info'): void
    {
        $this->logs[] = ['message' => $message, 'type' => $type, 'time' => now()->format('H:i:s')];
        if (count($this->logs) > 60) {
            $this->logs = array_slice($this->logs, -60);
        }
    }
};
?>

<div class="space-y-6" wire:poll.3000ms="pollJobProgress">

    {{-- ══════════════ HEADER ══════════════ --}}

    <div class="flex justify-between gap-4">
        <div class="flex items-center gap-4">
            <div>
                <flux:icon.cpu-chip variant="solid" class="size-6 text-white" />
            </div>
            <div>
                <div class="flex items-center gap-4">
                    <flux:heading size="lg">Genesis Pro</flux:heading>
                    <flux:badge size="sm" color="indigo">v4 · Agent + Judge + Grounding</flux:badge>
                </div>
                <flux:text class="mt-0.5">Zero-Cost Autonomous Content Engine</flux:text>
            </div>
        </div>

        <div class="flex items-center gap-4 flex-wrap">
            <flux:button wire:click="runHealthCheck" icon="heart" size="sm" variant="ghost">
                Health
            </flux:button>
            <flux:button wire:click="resetProviders" icon="arrow-path" size="sm" variant="ghost">
                Reset Blacklist
            </flux:button>
            <flux:button wire:click="refreshStats" icon="chart-bar" size="sm" variant="ghost">
                Refresh
            </flux:button>
        </div>
    </div>

    {{-- ══════════════ TABS ══════════════ --}}
    <flux:tabs wire:model="activeTab" variant="pills">
        <flux:tab name="generate" icon="sparkles">Generate</flux:tab>
        <flux:tab name="remake" icon="pencil-square">Remake</flux:tab>
        <flux:tab name="agent" icon="cpu-chip">
            Agent
            @if ($this->isAgentRunning)
                <span class="ml-1 size-1.5 rounded-full bg-indigo-500 animate-pulse inline-block"></span>
            @endif
        </flux:tab>
        <flux:tab name="health" icon="heart">Health</flux:tab>
        <flux:tab name="stats" icon="chart-bar">Stats</flux:tab>
    </flux:tabs>

    {{-- ══════════════ MAIN GRID ══════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        {{-- LEFT: Model Selector (Generate / Remake) --}}
        @if (in_array($activeTab, ['generate', 'remake']))
            <aside class="lg:col-span-3 space-y-3">
                <flux:heading size="sm" class="uppercase">Target Model</flux:heading>

                <div class="space-y-2 overflow-y-auto">
                    @foreach ($stats as $stat)
                        @php $isActive = $activeModel === $stat['name']; @endphp
                        <button wire:click="setActiveModel('{{ $stat['name'] }}')" class="w-full text-left">
                            <flux:card size="sm" class="{{ $isActive ? '' : '' }} hover:bg-zinc-400/25">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <flux:heading size="sm" class="truncate">{{ $stat['name'] }}</flux:heading>
                                        <flux:text class="truncate">{{ $stat['table'] }} · {{ $stat['columns'] }}c
                                        </flux:text>
                                    </div>
                                    <div>
                                        <flux:heading size="sm">{{ number_format($stat['count']) }}</flux:heading>
                                        <flux:text>rows</flux:text>
                                    </div>
                                </div>
                            </flux:card>
                        </button>
                    @endforeach
                </div>

                {{-- Schema --}}
                @if ($this->activeSchema)
                    <flux:separator />
                    <flux:heading size="sm" class="uppercase ">Schema</flux:heading>
                    <div class="flex flex-wrap gap-41.5">
                        @foreach ($this->activeSchema as $col)
                            <flux:badge size="sm" color="{{ $col['is_fk'] ? 'amber' : ($col['fillable'] ? 'indigo' : 'zinc') }}">
                                {{ $col['name'] }}
                                @if ($col['is_fk'])
                                    🔗
                                @endif
                            </flux:badge>
                        @endforeach
                    </div>
                @endif

                {{-- Relations --}}
                @if ($relationLoaded && ($this->hasBelongsTo || $this->hasBelongsToMany))
                    <flux:callout icon="link" color="amber" class="mt-3">
                        <flux:callout.heading>Relations Detected</flux:callout.heading>
                        <div class="space-y-2 mt-2">
                            @foreach ($relationContext['belongs_to'] ?? [] as $fk => $rel)
                                <div>
                                    <flux:text>→ {{ $fk }}</flux:text>
                                    <flux:text>
                                        {{ $rel['table'] }} ({{ count($rel['valid_ids']) }} valid IDs)
                                        @if (empty($rel['valid_ids']))
                                            <flux:badge size="sm" color="red">Table empty</flux:badge>
                                        @endif
                                    </flux:text>
                                </div>
                            @endforeach
                            @foreach ($relationContext['belongs_to_many'] ?? [] as $name => $rel)
                                <div>
                                    <flux:text>⇌ {{ $name }}</flux:text>
                                    <flux:text>pivot: {{ $rel['pivot_table'] }}</flux:text>
                                </div>
                            @endforeach
                        </div>
                    </flux:callout>
                @endif
            </aside>
        @endif

        {{-- RIGHT / FULL CONTENT --}}
        <main class="{{ in_array($activeTab, ['generate', 'remake']) ? 'lg:col-span-9' : 'lg:col-span-12' }} space-y-5">

            {{-- ══════ GENERATE ══════ --}}
            @if ($activeTab === 'generate')
                <flux:card>
                    <div class="space-y-6">
                        {{-- Options row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Records</flux:label>
                                <flux:select wire:model="count" size="sm">
                                    @foreach ([5, 10, 15, 25, 50, 100] as $n)
                                        <flux:select.option value="{{ $n }}">{{ $n }} records
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            </flux:field>

                            <div class="flex flex-wrap items-end gap-4x-5 gap-4y-3">
                                <flux:checkbox wire:model="optRealistic" label="Realistic Data" />
                                <flux:checkbox wire:model="optLocalized" label="Bangladesh Context" />
                                <flux:checkbox wire:model="optImages" label="Auto Images" />
                                <flux:checkbox wire:model="useQueue" label="Use Queue" />
                            </div>
                        </div>

                        <flux:separator />

                        {{-- Instruction --}}
                        <flux:field>
                            <flux:label>Instruction</flux:label>
                            <flux:textarea wire:model="instruction" rows="4"
                                placeholder="e.g. Generate 10 famous tourist spots in Cox's Bazar with rich Bengali descriptions, historical context, GPS coordinates, and visitor tips..." />
                            <flux:description>{{ mb_strlen($instruction) }}/1000</flux:description>
                            <flux:error name="instruction" />
                        </flux:field>

                        {{-- Relation warning --}}
                        @if ($this->hasBelongsTo)
                            @php $emptyTables = collect($relationContext['belongs_to'])->filter(fn($r) => empty($r['valid_ids'])) @endphp
                            @if ($emptyTables->isNotEmpty())
                                <flux:callout icon="exclamation-triangle" color="red">
                                    <flux:callout.heading>Warning</flux:callout.heading>
                                    <flux:text>
                                        {{ $emptyTables->keys()->join(', ') }} related table(s) are empty.
                                        Generate parent records first, or FK fields will be set to null.
                                    </flux:text>
                                </flux:callout>
                            @endif
                        @endif

                        {{-- Actions --}}
                        <div class="flex items-center justify-between gap-4">
                            <flux:text>
                                @if ($useQueue)
                                    ⚡ Non-blocking queue mode
                                @else
                                    ⚠ Sync mode (blocks UI)
                                @endif
                            </flux:text>

                            <div class="flex items-center gap-4">
                                @if ($this->isRunning)
                                    <flux:button wire:click="cancelJob" variant="danger" icon="stop" size="sm">
                                        Stop Watch
                                    </flux:button>
                                @endif

                                <flux:button wire:click="generate" variant="primary" icon="sparkles"
                                    :disabled="$this->isRunning">
                                    Execute Generation
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </flux:card>

                {{-- Job Progress --}}
                @if ($jobStatus)
                    <flux:card>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div class="flex items-center gap-4">
                                    <flux:badge size="sm"
                                        color="{{ $jobStatus === 'done' ? 'emerald' : ($jobStatus === 'failed' ? 'red' : 'indigo') }}">
                                        {{ strtoupper($jobStatus) }}
                                    </flux:badge>
                                    @if ($jobProvider)
                                        <flux:text>via {{ $jobProvider }}</flux:text>
                                    @endif
                                    @if ($jobRelations > 0)
                                        <flux:badge size="sm" color="amber">🔗 {{ $jobRelations }} FK
                                        </flux:badge>
                                    @endif
                                </div>
                            </div>

                            <flux:text>{{ $jobStage }}</flux:text>

                            @if ($this->isRunning || $jobStatus === 'done')
                                <div>
                                    <div class="flex justify-between mb-1.5">
                                        <flux:text>Progress</flux:text>
                                        <flux:text>{{ $this->progressPercent }}%</flux:text>
                                    </div>
                                    <flux:progress :value="$this->progressPercent" />
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <flux:card size="sm" class="text-center">
                                    <flux:heading size="lg" class="text-emerald-500">{{ $jobInserted }}
                                    </flux:heading>
                                    <flux:text>Inserted</flux:text>
                                </flux:card>
                                <flux:card size="sm" class="text-center">
                                    <flux:heading size="lg" class="text-amber-500">{{ $jobSkipped }}
                                    </flux:heading>
                                    <flux:text>Skipped</flux:text>
                                </flux:card>
                                <flux:card size="sm" class="text-center">
                                    <flux:heading size="lg" class="text-sky-500">{{ $jobImages }}
                                    </flux:heading>
                                    <flux:text>Images</flux:text>
                                </flux:card>
                                <flux:card size="sm" class="text-center">
                                    <flux:heading size="lg" class="text-violet-500">{{ $jobRelations }}
                                    </flux:heading>
                                    <flux:text>FK Fixed</flux:text>
                                </flux:card>
                            </div>

                            @if (!empty($jobErrors))
                                <flux:callout icon="exclamation-triangle" color="amber">
                                    <flux:callout.heading>{{ count($jobErrors) }} row error(s)</flux:callout.heading>
                                    @foreach (array_slice($jobErrors, 0, 3) as $err)
                                        <flux:text>Row {{ $err['index'] }}: {{ Str::limit($err['error'], 80) }}
                                        </flux:text>
                                    @endforeach
                                </flux:callout>
                            @endif

                            @if ($jobError)
                                <flux:callout icon="x-circle" color="red">
                                    <flux:text>{{ $jobError }}</flux:text>
                                </flux:callout>
                            @endif

                            @if ($currentJobId)
                                <flux:text class="font-mono text-xs">JOB: {{ $currentJobId }}</flux:text>
                            @endif
                        </div>
                    </flux:card>
                @endif
            @endif

            {{-- ══════ REMAKE ══════ --}}
            @if ($activeTab === 'remake')
                <flux:card>
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div>
                                <flux:icon.pencil-square class="size-5 text-amber-500" />
                            </div>
                            <div>
                                <flux:heading size="lg">Remake Existing Records</flux:heading>
                                <flux:text>দুর্বল/ইংরেজি content → সমৃদ্ধ বাংলায় rewrite</flux:text>
                            </div>
                        </div>

                        <flux:separator />

                        <flux:field>
                            <flux:label>Record IDs (comma separated — empty = all)</flux:label>
                            <flux:input wire:model="remakeIds"
                                placeholder="e.g. 2, 3, 4, 5 — অথবা খালি রাখুন সব records এর জন্য" />
                            <flux:description>⚠ Empty = first 50 records will be processed</flux:description>
                        </flux:field>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" items-end">
                            <flux:field>
                                <flux:label>Batch Size</flux:label>
                                <flux:select wire:model="remakeBatch" size="sm">
                                    @foreach ([3, 5, 10] as $n)
                                        <flux:select.option value="{{ $n }}">{{ $n }}
                                            records/batch</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </flux:field>

                            <flux:button wire:click="remake" variant="primary" icon="arrow-path"
                                :disabled="$this->isRunning" class="">
                                Start Remake
                            </flux:button>
                        </div>

                        @if ($jobStatus && $currentJobId)
                            <flux:separator />
                            <div class="flex items-center gap-4">
                                <flux:badge size="sm"
                                    color="{{ $jobStatus === 'done' ? 'emerald' : ($jobStatus === 'failed' ? 'red' : 'amber') }}">
                                    {{ $jobStatus }}
                                </flux:badge>
                                <flux:text>{{ $jobStage }}</flux:text>
                            </div>
                            @if ($jobInserted > 0)
                                <flux:badge color="emerald">✅ {{ $jobInserted }} records updated</flux:badge>
                            @endif
                        @endif
                    </div>
                </flux:card>
            @endif

            {{-- ══════ AGENT ══════ --}}
            @if ($activeTab === 'agent')
                <flux:card>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="size-10 rounded-lg flex items-center justify-center">
                                    <flux:icon.cpu-chip class="size-5 text-violet-500" />
                                </div>
                                <div>
                                    <flux:heading size="lg">Autonomous Content Agent</flux:heading>
                                    <flux:text>Topic-gap detection · Wikipedia grounding · LLM-judge · Queue-parallel
                                    </flux:text>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <flux:button wire:click="refreshAgentStats" icon="arrow-path" size="sm" variant="ghost">
                                    Refresh
                                </flux:button>
                                <flux:button wire:click="runAgentFullCycle" variant="primary" icon="play"
                                    :disabled="$this->isAgentRunning">
                                    Run Full Cycle
                                </flux:button>
                            </div>
                        </div>

                        {{-- Batch progress --}}
                        @if ($agentBatchInfo)
                            <flux:card size="sm" class="">
                                <div class="flex justify-between mb-2">
                                    <flux:text>Batch: {{ $agentBatchId }}</flux:text>
                                    <flux:text>
                                        {{ $agentBatchInfo['processed'] }} / {{ $agentBatchInfo['total'] }} models
                                        @if ($agentBatchInfo['failed'] > 0)
                                            · <span class="text-red-500">{{ $agentBatchInfo['failed'] }} failed</span>
                                        @endif
                                    </flux:text>
                                </div>
                                <flux:progress :value="$agentBatchInfo['progress']" />
                            </flux:card>
                        @endif

                        {{-- Agent table --}}
                        <div class="overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>Model</flux:table.column>
                                    <flux:table.column align="end">Rows</flux:table.column>
                                    <flux:table.column align="end">Min</flux:table.column>
                                    <flux:table.column align="center">Quality</flux:table.column>
                                    <flux:table.column align="center">Grounded</flux:table.column>
                                    <flux:table.column align="center">Status</flux:table.column>
                                    <flux:table.column align="end">Action</flux:table.column>
                                </flux:table.columns>

                                <flux:table.rows>
                                    @forelse ($agentStats as $s)
                                        @php
                                            $progress = $agentProgress[$s['model']] ?? null;
                                            $isRunningThisModel =
                                                $progress &&
                                                in_array($progress['status'] ?? '', ['pending', 'running'], true);
                                            $qColor =
                                                $s['avg_quality_score'] >= 65
                                                ? 'emerald'
                                                : ($s['avg_quality_score'] >= 40
                                                    ? 'amber'
                                                    : 'red');
                                        @endphp
                                        <flux:table.row>
                                            <flux:table.cell>
                                                <flux:heading size="sm">{{ $s['display_name'] }}</flux:heading>
                                                <flux:text>{{ $s['table'] }}</flux:text>
                                            </flux:table.cell>
                                            <flux:table.cell align="end">{{ number_format($s['count']) }}
                                            </flux:table.cell>
                                            <flux:table.cell align="end">{{ $s['min_rows'] }}</flux:table.cell>
                                            <flux:table.cell align="center">
                                                <flux:badge size="sm" color="{{ $qColor }}">
                                                    {{ $s['avg_quality_score'] }}%
                                                </flux:badge>
                                            </flux:table.cell>
                                            <flux:table.cell align="center">
                                                @if ($s['grounding_enabled'])
                                                    <flux:badge size="sm" color="sky">📚</flux:badge>
                                                @else
                                                    <flux:text>—</flux:text>
                                                @endif
                                            </flux:table.cell>
                                            <flux:table.cell align="center">
                                                @if ($s['auto_generate_disabled'])
                                                    <flux:badge size="sm">🚫 Manual</flux:badge>
                                                @elseif ($isRunningThisModel)
                                                    <flux:badge size="sm" color="indigo">⚙
                                                        {{ $progress['status'] }}
                                                    </flux:badge>
                                                @elseif ($progress && $progress['status'] === 'done')
                                                    <flux:badge size="sm" color="emerald">
                                                        ✓ +{{ $progress['generated'] ?? 0 }} /
                                                        ↻{{ $progress['remade'] ?? 0 }}
                                                    </flux:badge>
                                                @elseif ($progress && $progress['status'] === 'failed')
                                                    <flux:badge size="sm" color="red">✗ Failed</flux:badge>
                                                @elseif ($s['needs_generation'])
                                                    <flux:badge size="sm" color="amber">⚠ Needs Gen</flux:badge>
                                                @else
                                                    <flux:badge size="sm">✓ OK</flux:badge>
                                                @endif
                                            </flux:table.cell>
                                            <flux:table.cell align="end">
                                                @if (!$s['auto_generate_disabled'])
                                                    <flux:button wire:click="runAgentForModel('{{ $s['model'] }}')" size="xs"
                                                        variant="ghost" :disabled="$isRunningThisModel">
                                                        {{ $isRunningThisModel ? 'Running...' : 'Run' }}
                                                    </flux:button>
                                                @endif
                                            </flux:table.cell>
                                        </flux:table.row>

                                        @if ($isRunningThisModel && !empty($progress['stage']))
                                            <flux:table.row>
                                                <flux:table.cell colspan="7">
                                                    <flux:text class="font-mono text-indigo-500">
                                                        {{ $progress['stage'] }}
                                                    </flux:text>
                                                </flux:table.cell>
                                            </flux:table.row>
                                        @endif
                                    @empty
                                        <flux:table.row>
                                            <flux:table.cell colspan="7" class="text-center py-8">
                                                <flux:text>কোনো stats নেই — উপরে "Refresh" চাপুন</flux:text>
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforelse
                                </flux:table.rows>
                            </flux:table>
                        </div>

                        <flux:callout icon="information-circle">
                            <flux:text>
                                "🚫 Manual Only" মার্ক করা model গুলো (Quran, ContactNumber, Minister, BuySellPost,
                                NewsHeading, Holiday) ইচ্ছাকৃতভাবে auto-generation থেকে বাদ রাখা হয়েছে —
                                <code>config/content_agent.php</code> এ কারণ লেখা আছে।
                            </flux:text>
                        </flux:callout>
                    </div>
                </flux:card>
            @endif

            {{-- ══════ HEALTH ══════ --}}
            @if ($activeTab === 'health')
                <flux:card>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <flux:heading size="lg">Provider Health Monitor</flux:heading>
                            <flux:button wire:click="runHealthCheck" variant="primary" icon="play" size="sm">
                                Run Check
                            </flux:button>
                        </div>

                        @if (count($healthResults) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($healthResults as $key => $result)
                                    @php $ok = $result['status'] === 'ok'; @endphp
                                    <flux:callout icon="{{ $ok ? 'check-circle' : 'x-circle' }}"
                                        color="{{ $ok ? 'emerald' : 'red' }}">
                                        <flux:callout.heading>
                                            {{ $ok ? '✅' : '❌' }} {{ $key }}
                                        </flux:callout.heading>
                                        <flux:text>{{ $result['latency_ms'] }}ms · {{ $result['provider'] ?? '' }}
                                        </flux:text>
                                        @if (isset($result['error']))
                                            <flux:text class="text-red-500">{{ Str::limit($result['error'], 60) }}
                                            </flux:text>
                                        @endif
                                    </flux:callout>
                                @endforeach
                            </div>
                        @else
                            <div class="py-12 text-center">
                                <flux:icon.heart class="size-10 mx-auto mb-3 opacity-30" />
                                <flux:text>Run a health check to see provider status</flux:text>
                            </div>
                        @endif
                    </div>
                </flux:card>
            @endif

            {{-- ══════ STATS ══════ --}}
            @if ($activeTab === 'stats')
                <flux:card>
                    <div class="space-y-6">
                        <flux:heading size="lg">Database Overview</flux:heading>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($stats as $stat)
                                <flux:card size="sm">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <flux:heading size="sm">{{ $stat['name'] }}</flux:heading>
                                            <flux:text>{{ $stat['table'] }}</flux:text>
                                        </div>
                                        <div class="text-right">
                                            <flux:heading size="lg" class="text-indigo-500">
                                                {{ number_format($stat['count']) }}
                                            </flux:heading>
                                            <flux:text>rows</flux:text>
                                        </div>
                                    </div>
                                    <flux:text class="mt-2">{{ $stat['columns'] }} columns</flux:text>
                                </flux:card>
                            @endforeach
                        </div>
                    </div>
                </flux:card>
            @endif

            {{-- ══════ LOG ══════ --}}
            @if (count($logs) > 0)
                <flux:card class="!p-0 overflow-hidden">
                    <div
                        class="flex items-center justify-between px-4 py-2.5 border-b border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-4">
                            <div class="flex gap-41">
                                <span class="size-2 rounded-full bg-red-400/70"></span>
                                <span class="size-2 rounded-full bg-amber-400/70"></span>
                                <span class="size-2 rounded-full bg-emerald-400/70"></span>
                            </div>
                            <flux:heading size="sm">Genesis Log</flux:heading>
                        </div>
                        <flux:button wire:click="$set('logs', [])" size="xs" variant="ghost">
                            Clear
                        </flux:button>
                    </div>
                    <div class="p-4 h-44 overflow-y-auto space-y-1 font-mono text-sm">
                        @foreach (array_reverse($logs) as $log)
                                        <div class="flex gap-4">
                                            <span class="text-zinc-500 shrink-0">[{{ $log['time'] }}]</span>
                                            <span class="
                                                        {{ match ($log['type']) {
                                'error' => 'text-red-500',
                                'success' => 'text-emerald-500',
                                'warn' => 'text-amber-500',
                                default => 'text-indigo-500',
                            } }}
                                                    ">{{ $log['message'] }}</span>
                                        </div>
                        @endforeach
                    </div>
                </flux:card>
            @endif

        </main>
    </div>
</div>