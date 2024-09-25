<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SensorDataUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $sd;
    public $towerId;

    public function __construct(array $sd, $towerId)
    {
        $this->sd = $sd;
        $this->towerId = $towerId;

        // Log event creation with sensor data and tower ID
        Log::info('SensorDataUpdated event created', [
            'sensorData' => $sd,
            'towerId' => $towerId,
        ]);
    }

    public function broadcastOn()
    {
        // Log the channel that the event is being broadcasted on
        Log::info('Broadcasting on channel:', [
            'channel' => 'sensor-data-channel.' . $this->towerId,
        ]);

        return new Channel('sensor-data-channel.' . $this->towerId);
    }

    public function broadcastWith()
    {
        // Log the data being broadcasted
        Log::info('Broadcasting data:', [
            'sensorData' => $this->sd,
            'towerId' => $this->towerId,
        ]);

        return [
            'sensorData' => $this->sd,
            'towerId' => $this->towerId,
        ];
    }
}
