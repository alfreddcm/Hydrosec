<?php

namespace App\Console\Commands;

use App\Models\Sensor;
use App\Models\Tower;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AverageDailySensorData extends Command
{
    protected $signature = 'sensor:adda';
    protected $description = 'Calculate daily average of temp, ph, and nut levels from JSON file and save to Sensor model';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (Carbon::now()->format('H:i') !== '23:59') {
            Log::channel('custom')->info('Command skipped, not 11:59 PM.');
            return;
        }

        $towers = Tower::all();

        foreach ($towers as $tower) {
            $towerId = $tower->id;
            $filePath = "tower_data/tower_{$towerId}.json"; // Path to the JSON file

            // Check if the file exists
            if (!Storage::exists($filePath)) {
                Log::channel('custom')->info("No data file found for tower ID: {$towerId}");
                continue;
            }

            // Retrieve and decode the JSON data from the file
            $cachedData = json_decode(Storage::get($filePath), true) ?: [];

            if (empty($cachedData)) {
                Log::channel('custom')->info("No data found in file for tower ID: {$towerId}");
                continue;
            }

            $tempTotal = 0;
            $phTotal = 0;
            $nutTotal = 0;
            $count = 0;

            foreach ($cachedData as $dataPoint) {
                $tempTotal += isset($dataPoint['data']['temperature']) ? (float) $dataPoint['data']['temperature'] : 0;
                $phTotal += isset($dataPoint['data']['ph']) ? (float) $dataPoint['data']['ph'] : 0;
                $nutTotal += isset($dataPoint['data']['nutrient_level']) ? (float) $dataPoint['data']['nutrient_level'] : 0;
                $count++;
            }

            if ($count === 0) {
                Log::channel('custom')->warning("No valid data points found in file for tower ID: {$towerId}");
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

                // Delete the JSON file after saving to the database
                Storage::delete($filePath);
                Log::channel('custom')->info("Deleted data file for tower ID: {$towerId}");

            } catch (\Exception $e) {
                Log::channel('custom')->error("Failed to save sensor data for tower ID {$towerId}: " . $e->getMessage());
            }
        }

        Log::channel('custom')->info('Daily sensor data averages calculated and saved at ' . Carbon::now());
        $this->info('Daily sensor data averages calculated and saved.');
    }
}
