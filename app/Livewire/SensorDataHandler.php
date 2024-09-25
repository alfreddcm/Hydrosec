<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SensorDataHandler extends Component
{
    public $sensorData = [];
    public $towerId;

    protected $listeners = ['sensorDataBeforeSave' => 'updateSensorData'];

public function updateSensorData($sensorData, $towerId)
{
    // Here you can do additional processing if needed
    $this->sensorData = $sensorData;

    // Emit a new event for further handling in JavaScript
    $this->emit('sensorDataUpdated', ['sensorData' => $this->sensorData]);
}

}
