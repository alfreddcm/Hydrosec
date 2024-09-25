<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SensorDataHandler extends Component
{
    public $sensorData = [];
    public $towerId;

    protected $listeners = ['sensorDataBeforeSave'];

    // Method to handle the event when it's emitted
    public function sensorDataBeforeSave($sensorData, $towerId)
    {
        $this->sensorData = $sensorData;
        $this->towerId = $towerId;

        // You can log or perform additional logic here
        Log::info('Received sensor data before save:', [
            'sensorData' => $this->sensorData,
            'towerId' => $this->towerId,
        ]);

        // Optionally, you could broadcast this data or trigger more logic.
    }

    public function render()
    {
        return view('livewire.sensor-data-handler');
    }
}
