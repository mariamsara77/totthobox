<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $senderId;
    public $receiverId;

    public function __construct($senderId, $receiverId)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->senderId),
            new PrivateChannel('user.' . $this->receiverId),
        ];
    }

    public function broadcastAs()
    {
        return 'MessageRead';
    }

    public function broadcastWith()
    {
        return [
            'sender_id' => $this->senderId,
            'receiver_id' => $this->receiverId,
        ];
    }
}
