<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Laravel\Reverb\Protocols\Pusher\Channels\PrivateChannel as ChannelsPrivateChannel;
use Spatie\Permission\Models\Role;

class RolePermissionsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $role;

    public function __construct(Role $role)
    {
        $this->role = $role->load('permissions'); // Lade die Berechtigungen der Rolle
    }

    public function broadcastOn(): Channel
    {
        return new Channel('roles');
        // return new PrivateChannel('roles.' . $this->role->id);
    }

    public function broadcastWith(): array
    {
        return [
            'role' => $this->role,
            'message' => 'Role permissions have been updated.',
        ];
    }
}