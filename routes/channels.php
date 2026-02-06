<?php
use Illuminate\Support\Facades\Broadcast;

// routes/channels.php
// routes/channels.php
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
