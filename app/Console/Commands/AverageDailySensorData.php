<?php

namespace App\Console\Commands;

use App\Models\Sensor;
use App\Models\Tower;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class AverageDailySensorData extends Command
{
    protected $signature = 'sensor:adda';
    protected $description = 'Calculate daily average of temp, ph, and nut levels from cache and save to Sensor model';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
{
    $towers = Tower::all();

    foreach ($towers as $tower) {
        $towerId = $tower->id;

        $cachedData = Cache::get('cachetower.' . $towerId, []);

        if (empty($cachedData)) {
             Log::channel('custom')->info("No cached data found for tower ID: {$towerId}");
            continue;
        }

        $tempTotal = 0;
        $phTotal = 0;
        $nutTotal = 0;
        $count = 0;

        foreach ($cachedData as $dataPoint) {
            $tempTotal += isset($dataPoint['data']['temperature']) ? (float)$dataPoint['data']['temperature'] : 0;
            $phTotal += isset($dataPoint['data']['ph']) ? (float)$dataPoint['data']['ph'] : 0;
            $nutTotal += isset($dataPoint['data']['nutrient_level']) ? (float)$dataPoint['data']['nutrient_level'] : 0;
            $count++;
        }

        if ($count === 0) {
            Log::channel('custom')->warning("No valid data points found in cache for tower ID: {$towerId}");
            continue;
        }

            $avgTemp = number_format($tempTotal / $count, 2, '.', '');
            $avgPh = number_format($phTotal / $count, 2, '.', '');
            $avgNut = number_format($nutTotal / $count, 2, '.', '');

        $sensorData = json_encode([
            'temp' => $avgTemp,
            'ph' => $avgPh,
            'nut' => $avgNut,
        ]);

        try {
            Sensor::create([
                'towerid' => $towerId,
                'towercode' => Crypt::decryptString($tower->towercode),
                'sensordata' => $sensorData,
                'status' => '1',
            ]);
            $this->info("Averaged sensor data saved for tower ID {$towerId}");
             Log::channel('custom')->info("Averaged sensor data saved for tower ID {$towerId}");

              Cache::forget('cachetower.' . $towerId);
             Log::channel('custom')->info("Cleared cached data for tower ID: {$towerId}");

        } catch (\Exception $e) {
            Log::channel('custom')->error("Failed to save sensor data for tower ID {$towerId}: " . $e->getMessage());
        }
    }

     Log::channel('custom')->info('Daily sensor data averages calculated and saved at ' . Carbon::now());
    $this->info('Daily sensor data averages calculated and saved.');
}

}
