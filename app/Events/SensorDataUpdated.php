<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SensorDataUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    public $sensorData;
    public $towerId;

    public function __construct($sensorData, $towerId)
    {
        $this->sensorData = $sensorData;
        $this->towerId = $towerId;

        $directoryPath = 'tower_data';
        $filePath = "{$directoryPath}/tower_{$towerId}.json";

        if (!Storage::exists($directoryPath)) {
            Storage::makeDirectory($directoryPath);
        }

        // Retrieve existing data from the JSON file
        $existingData = [];
        if (Storage::exists($filePath)) {
            $existingData = json_decode(Storage::get($filePath), true) ?: [];
        }

        // Append the new data entry
        $existingData[] = [
            'timestamp' => now(),
            'data' => $sensorData,
        ];

        // Save the updated data back to the file in JSON format
        Storage::put($filePath, json_encode($existingData));

        Log::channel('custom')->info('Data saved to JSON file', ['tower_id' => $towerId]);
    }

    public function broadcastOn()
    {
        return new Channel('tower.' . $this->towerId);
    }

    public function broadcastAs()
    {
        return 'SensorDataUpdated';
    }
}
