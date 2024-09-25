<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SensorDataUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $sd;
    public $towerId;

    public function __construct(array $sd, $towerId)
    {
        $this->sd = $sd;
        $this->towerId = $towerId;

        Log::info('SensorDataUpdated event created', [
            'sensorData' => $sd,
            'towerId' => $towerId,
        ]);
    }

    public function broadcastOn()
    {
        return new Channel('sensor-data-channel.' . $this->towerId);
    }

    public function broadcastWith()
    {
        return [
            'sensorData' => $this->sd,
            'towerId' => $this->towerId,
        ];
    }
}
