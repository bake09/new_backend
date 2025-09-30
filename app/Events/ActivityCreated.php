<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Spatie\Activitylog\Models\Activity;

class ActivityCreated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public Activity $activity;

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    public function broadcastOn()
    {
        // return new Channel('activities'); // public channel
        return new PrivateChannel('activities');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->activity->id,
            'description' => $this->activity->description,
            'subject_type' => $this->activity->subject_type,
            'subject_id' => $this->activity->subject_id,
            'causer_type' => $this->activity->causer_type,
            'causer_id' => $this->activity->causer_id,
            'properties' => $this->activity->properties,
            'time' => $this->activity->created_at->format('H:i:s') . ' Uhr',
            'created_at' => $this->activity->created_at->toDateTimeString(),
        ];
    }
}

