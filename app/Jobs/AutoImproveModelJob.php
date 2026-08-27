<?php

namespace App\Jobs;

use App\Services\AutonomousContentAgent;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║        AutoImproveModelJob — একটা model এর জন্য queue-based worker       ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 *
 * AutonomousContentAgent::dispatchFullCycleAsJobs() এই job কে Bus::batch এ
 * প্রতিটা registered model এর জন্য একবার করে dispatch করে। এতে:
 *   - প্রতিটা model আলাদা queue worker এ parallel এ প্রসেস হয়
 *   - একটা model fail করলে বাকিগুলো থেমে যায় না (allowFailures)
 *   - প্রতিটা model এর progress আলাদাভাবে track করা যায়
 *
 * Progress cache key: "agent_model_progress_{modelName}"
 */
class AutoImproveModelJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // ১০ মিনিট — generation + judge scoring + remake সবকিছুর জন্য
    public int $tries = 1;

    public function __construct(
        public readonly string $modelName,
    ) {
        $this->onQueue('ai-generation');
    }

    public function handle(AutonomousContentAgent $agent): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $this->updateProgress('running', 'শুরু হচ্ছে...');

        $logLines = [];
        $logger = function (string $msg) use (&$logLines) {
            $logLines[] = $msg;
            if (count($logLines) > 100) {
                $logLines = array_slice($logLines, -100);
            }
            $this->updateProgress('running', trim($msg), ['logs' => $logLines]);
        };

        try {
            $result = $agent->runForModel($this->modelName, $logger);

            $this->updateProgress('done', "সম্পন্ন — +{$result['generated']} generated, {$result['remade']} remade", [
                'logs' => $logLines,
                'generated' => $result['generated'],
                'remade' => $result['remade'],
                'skipped' => $result['skipped'],
                'avg_quality_before' => $result['avg_quality_before'] ?? null,
                'avg_quality_after' => $result['avg_quality_after'] ?? null,
            ]);

            Log::info("AutoImproveModelJob [{$this->modelName}]: done", $result);

        } catch (\Throwable $e) {
            $this->updateProgress('failed', $e->getMessage(), ['logs' => $logLines]);
            Log::error("AutoImproveModelJob [{$this->modelName}]: failed", ['error' => $e->getMessage()]);
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->updateProgress('failed', $exception->getMessage());
    }

    private function updateProgress(string $status, string $stage, array $extras = []): void
    {
        $existing = Cache::get($this->progressKey(), []);

        Cache::put($this->progressKey(), array_merge([
            'status' => $status,
            'stage' => $stage,
            'model' => $this->modelName,
            'generated' => $existing['generated'] ?? 0,
            'remade' => $existing['remade'] ?? 0,
            'skipped' => $existing['skipped'] ?? 0,
            'avg_quality_before' => $existing['avg_quality_before'] ?? null,
            'avg_quality_after' => $existing['avg_quality_after'] ?? null,
            'logs' => $existing['logs'] ?? [],
            'started_at' => $existing['started_at'] ?? time(),
            'finished_at' => in_array($status, ['done', 'failed']) ? time() : null,
        ], $extras), 3600);
    }

    private function progressKey(): string
    {
        return "agent_model_progress_{$this->modelName}";
    }

    public static function getProgress(string $modelName): ?array
    {
        return Cache::get("agent_model_progress_{$modelName}");
    }
}