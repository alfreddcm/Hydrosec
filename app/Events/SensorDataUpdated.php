<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SensorDataUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    public $sensorData;
    public $towerId;

    public function __construct($sensorData, $towerId)
    {
        $this->sensorData = $sensorData;
        $this->towerId = $towerId;

        $cachedData = Cache::get('cachetower.' . $towerId, []);

        $cachedData[] = [
            'timestamp' => now(),
            'data' => $sensorData,
        ];

        Log::channel('custom')->info('Data sent to cache', ['tower_id' => $towerId]);

        Cache::put('cachetower.' . $towerId, $cachedData, 1440);
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
