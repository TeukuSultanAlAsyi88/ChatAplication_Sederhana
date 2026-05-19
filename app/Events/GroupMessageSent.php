<?php

namespace App\Events;

use App\Models\GroupMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(GroupMessage $message)
    {
        $this->message = $message->load('user');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('group.' . $this->message->group_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'group.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'group_id' => $this->message->group_id,
                'user_id' => $this->message->user_id,
                'message' => $this->message->message,
                'created_at' => optional($this->message->created_at)->toISOString(),
                'user' => [
                    'id' => $this->message->user->id,
                    'name' => $this->message->user->name,
                ],
            ],
        ];
    }
}