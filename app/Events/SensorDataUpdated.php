<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SensorDataUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $sensorData;
    public $towerId;

    public function __construct(array $sensorData, $towerId)
    {
        $this->sensorData = $sensorData;
        $this->towerId = $towerId;
    }

    public function broadcastOn()
    {
        return new Channel('sensor-data-channel.' . $this->towerId);
    }

    public function broadcastWith()
    {
        return [
            'sensorData' => $this->sensorData,
            'towerId' => $this->towerId,
        ];
    }
}
