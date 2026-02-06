<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

// class NewMessageNotification extends Notification implements ShouldQueue
class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public $message,
        public $sender
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'message',
            'sender_id' => $this->sender->id,
            'title' => 'একটি মেসেজ পাঠিয়েছে', // নামের পর সরাসরি এই অংশ বসবে
            'message' => $this->message->message, // মূল মেসেজটি শুধু এখানে থাকবে
            'action_url' => route('messages', ['slug' => $this->sender->slug]),
            'action_text' => 'উত্তর দিন',
        ];
    }
}