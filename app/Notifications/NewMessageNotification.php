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
        'type'        => 'message',
        'sender_id'   => $this->sender->id,
        'title'       => 'একটি মেসেজ পাঠিয়েছে',
        'message'     => $this->message->message,
        'action_url'  => route('messages', ['slug' => $this->sender->slug], false),
        'action_text' => 'উত্তর দিন',
    ];
}
}