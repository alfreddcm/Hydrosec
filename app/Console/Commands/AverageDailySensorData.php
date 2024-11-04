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
            Log::info("Retrieved cached data for tower ID {$towerId}: " . json_encode($cachedData));

            if (empty($cachedData)) {
                Log::info("No cached data found for tower ID: {$towerId}");
                continue;
            }

            $tempTotal = 0;
            $phTotal = 0;
            $nutTotal = 0;
            $count = 0;

            foreach ($cachedData as $dataPoint) {
                // Validate and accumulate values
                if (isset($dataPoint['temperature']) && is_numeric($dataPoint['temperature'])) {
                    $tempTotal += $dataPoint['temperature'];
                }
                if (isset($dataPoint['ph']) && is_numeric($dataPoint['ph'])) {
                    $phTotal += $dataPoint['ph'];
                }
                if (isset($dataPoint['nutrient_level']) && is_numeric($dataPoint['nutrient_level'])) {
                    $nutTotal += $dataPoint['nutrient_level'];
                }
                $count++;
            }

            if ($count === 0) {
                Log::warning("No valid data points found in cache for tower ID: {$towerId}");
                continue;
            }

            $avgTemp = $count > 0 ? $tempTotal / $count : 0;
            $avgPh = $count > 0 ? $phTotal / $count : 0;
            $avgNut = $count > 0 ? $nutTotal / $count : 0;

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
                Log::info("Averaged sensor data saved for tower ID {$towerId}");
            } catch (\Exception $e) {
                Log::error("Failed to save sensor data for tower ID {$towerId}: " . $e->getMessage());
            }
        }

        Log::info('Daily sensor data averages calculated and saved at ' . Carbon::now());
        $this->info('Daily sensor data averages calculated and saved.');
    }
}
