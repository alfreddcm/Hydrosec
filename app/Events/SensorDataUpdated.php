<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SensorDataUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sensorData;
    public $towerId;

    public function __construct($sensorData, $towerId)
    {
        $this->sensorData = $sensorData;
        $this->towerId = $towerId;
    }

    // Define the channel where the event will be broadcast
    public function broadcastOn()
    {
        return new Channel('tower.' . $this->towerId);
    }
}
