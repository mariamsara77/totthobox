<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MissingDataNotification extends Notification
{
    use Queueable;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'missing_data',
            'sender_id' => $this->details['sender_id'] ?? null,
            'title' => 'তথ্যের ঘাটতি রিপোর্ট করেছেন',
            'message' => "পেজ: " . $this->details['search_query'] . " on " . $this->details['title'],
            'action_url' => $this->details['url'],
            'action_text' => 'আপডেট করুন',
        ];
    }
}