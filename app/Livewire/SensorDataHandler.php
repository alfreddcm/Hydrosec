<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SensorDataHandler extends Component
{
    public $sensorData = [];
    public $towerId;

    protected $listeners = ['sensorDataBeforeSave' => 'updateSensorData'];

    public function updateSensorData($sensorData, $towerId)
    {
        // Update the component's sensor data
        $this->sensorData = $sensorData;

        // Emit a Livewire event that your frontend can listen to
        $this->emit('sensorDataUpdated', $this->sensorData);
    }
}
