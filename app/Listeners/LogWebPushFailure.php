<?php

namespace App\Listeners;

use NotificationChannels\WebPush\Events\NotificationSendingFailed;

class LogWebPushFailure
{
    public function handle(NotificationSendingFailed $event): void
    {
        \Log::warning('WebPush delivery failed', [
            'notifiable_id' => $event->notifiable?->id,
            'endpoint' => $event->report->getEndpoint() ?? null,
            'reason' => $event->report->getReason() ?? null,
            'status_code' => method_exists($event->report, 'getResponse')
                ? optional($event->report->getResponse())->getStatusCode()
                : null,
        ]);
    }
}
