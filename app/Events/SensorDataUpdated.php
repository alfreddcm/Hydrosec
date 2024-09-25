<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Import this interface
use Illuminate\Queue\SerializesModels;

class SensorDataUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    public $sensorData;
    public $towerId;

    public function __construct($sensorData, $towerId)
    {
        $this->sensorData = $sensorData;
        $this->towerId = $towerId;
    }

    // Define the broadcast channel
    public function broadcastOn()
    {
        return new Channel('tower.' . $this->towerId);
    }

    // Define the event name
    public function broadcastAs()
    {
        return 'SensorDataUpdated';
    }
}
