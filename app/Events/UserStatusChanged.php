<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user)
    {
        $this->user->refresh();
    }

    public function broadcastOn(): array
    {
        return [new Channel('users.status')];
    }

    public function broadcastAs(): string
    {
        return 'user.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'is_online' => (bool) $this->user->is_online,
                'last_seen_at' => optional($this->user->last_seen_at)->toISOString(),
            ],
        ];
    }
}
