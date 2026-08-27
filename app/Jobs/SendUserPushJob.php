<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\SendWebPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUserPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public int $timeout = 120;

    public function __construct(
        public User $recipient,
        public array $payload,
    ) {
        $this->onQueue('push-realtime');
    }

    public function handle(): void
    {
        $this->recipient->notify(new SendWebPushNotification($this->payload));
    }

    public function failed(\Throwable $exception): void
    {
        \Log::warning('Push notification permanently failed', [
            'user_id' => $this->recipient->id,
            'title' => $this->payload['title'] ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
