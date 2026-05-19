<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    return true;
});