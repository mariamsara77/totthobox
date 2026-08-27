<?php

// app/Jobs/ProcessMediaJob.php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ConversionStatus;
use App\Models\ConversionTask;
use App\Services\MediaConverterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Throwable;

final class ProcessMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(public readonly ConversionTask $task) {}

    public function handle(MediaConverterService $converter): void
    {
        $this->task->update(['status' => ConversionStatus::Processing]);
        activity()->performedOn($this->task)->log('Media conversion started');

        $original = $this->task->getFirstMedia('original_files');

        if (! $original || ! $this->task->target_format) {
            $this->fail_('Missing source file or target format.');

            return;
        }

        $tempDir = storage_path("app/tmp/conversions/{$this->task->id}");

        try {
            $outputPath = $converter->convert($original->getPath(), $tempDir, $this->task->target_format);

            $this->task->addMedia($outputPath)
                ->usingFileName(pathinfo($outputPath, PATHINFO_BASENAME))
                ->toMediaCollection('converted_files');

            $this->task->update(['status' => ConversionStatus::Completed]);
            activity()->performedOn($this->task)->log('Media conversion completed');
        } catch (Throwable $e) {
            $this->fail_($e->getMessage());
            throw $e;
        } finally {
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->fail_($exception?->getMessage() ?? 'Unknown media conversion failure.');
    }

    private function fail_(string $message): void
    {
        $this->task->update(['status' => ConversionStatus::Failed, 'error_message' => $message]);
        activity()->performedOn($this->task)->withProperties(['error' => $message])->log('Media conversion failed');
    }
}
