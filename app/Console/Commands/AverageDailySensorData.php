<?php

namespace App\Console\Commands;

use App\Models\Tower;
use App\Models\Sensor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class AverageDailySensorData extends Command
{
    protected $signature = 'sensor:average-daily-data';
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

            // Retrieve cached data for the tower
            $cachedData = Cache::get('cachetower.' . $towerId, []);
            
            if (empty($cachedData)) {
                Log::info("No cached data found for tower ID: {$towerId}");
                continue;
            }

            $tempTotal = 0;
            $phTotal = 0;
            $nutTotal = 0;
            $count = 0;

            foreach ($cachedData as $dataPoint) {
                $tempTotal += $dataPoint['temperature'] ?? 0;
                $phTotal += $dataPoint['ph'] ?? 0;
                $nutTotal += $dataPoint['nutrient_levelnutrient_level'] ?? 0;
                $count++;
            }

            if ($count === 0) {
                Log::warning("No valid data points found in cache for tower ID: {$towerId}");
                continue;
            }

            $avgTemp = $tempTotal / $count;
            $avgPh = $phTotal / $count;
            $avgNut = $nutTotal / $count;

            $sensorData = json_encode([
                'temp' => $avgTemp,
                'ph' => $avgPh,
                'nut' => $avgNut,
            ]);

            Sensor::create([
                'towerid' => $towerId,
                'towercode' => Crypt::decryptString($tower->towercode),
                'sensordata' => $sensorData,
                'status' => '1',
            ]);

            $this->info("Averaged sensor data saved for tower ID {$towerId}");
            Log::info("Averaged sensor data saved for tower ID {$towerId}");
        }

        Log::info('Daily sensor data averages calculated and saved at ' . Carbon::now());
        $this->info('Daily sensor data averages calculated and saved.');
    }
}
