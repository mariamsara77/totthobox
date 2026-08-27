<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\SendWebPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class BroadcastAnnouncementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public ?string $tag = 'announcement',
    ) {
        $this->onQueue('push-broadcast');
    }

    public function handle(): void
    {
        $payload = [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url ?? '/',
            'tag' => $this->tag,
        ];

        User::query()
            ->whereHas('pushSubscriptions')
            ->select(['id'])
            ->chunkById(500, function ($users) use ($payload) {
                NotificationFacade::send($users, new SendWebPushNotification($payload));
            });
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Broadcast push job failed', [
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }
}
