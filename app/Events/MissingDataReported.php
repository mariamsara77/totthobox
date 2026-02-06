<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MissingDataReported implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $details;
    public $adminId;

    public function __construct($details, $adminId)
    {
        $this->details = $details;
        $this->adminId = $adminId;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->adminId)];
    }

    public function broadcastAs()
    {
        return 'notificationReceived';
    }

    public function broadcastWith()
    {
        return [
            'page_title' => $this->details['title'],
            'page_url' => $this->details['url'],
            'search_query' => $this->details['search_query'],
        ];
    }
}