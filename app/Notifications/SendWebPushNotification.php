<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class SendWebPushNotification extends Notification
{
    use Queueable;

    /**
     * @param  array{title?:string,body?:string,url?:string,icon?:string,tag?:string}  $payload
     */
    public function __construct(protected array $payload) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->payload['title'] ?? 'Totthobox')
            ->icon($this->payload['icon'] ?? '/web-app-manifest-192x192.png')
            ->badge('/web-app-manifest-192x192.png')
            ->body($this->payload['body'] ?? 'আপনার জন্য একটি নতুন নোটিফিকেশন')
            ->tag($this->payload['tag'] ?? null)
            ->data(['url' => $this->payload['url'] ?? '/'])
            ->action('দেখুন', 'open_url');
    }
}
