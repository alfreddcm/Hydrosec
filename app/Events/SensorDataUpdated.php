<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SensorDataUpdated
{
    use Dispatchable, SerializesModels;

    public $sensorData;
    public $towerId;

    public function __construct(array $sensorData, $towerId)
    {
        $this->sensorData = $sensorData;
        $this->towerId = $towerId;
    }
}

