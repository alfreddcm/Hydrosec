<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use Illuminate\Support\Facades\Log;

class SensorData extends Controller
{
    public function getLatestSensorData()
{
    try {
        $sdata = Sensor::orderBy('id', 'desc')->take(4)->get();

        if ($sdata->isNotEmpty()) {
            $method = "AES-128-CBC";
            $key = "aaaaaaaaaaaaaaaa";
            $decrypted_data = [
                'pH' => [],
                'temperature' => [],
                'nutrient_level' => []
            ];

            foreach ($sdata as $sensor) {
                $iv_base64 = $sensor->iv;
                $iv = base64_decode($iv_base64);

                $decrypted_ph = $this->decrypt_data($sensor->pH, $method, $key, $iv);
                $decrypted_temp = $this->decrypt_data($sensor->temperature, $method, $key, $iv);
                $decrypted_nutrient = $this->decrypt_data($sensor->nutrientlevel, $method, $key, $iv);

                $decrypted_data['pH'][] = [
                    'timestamp' => $sensor->created_at,
                    'value' => $decrypted_ph
                ];
                $decrypted_data['temperature'][] = [
                    'timestamp' => $sensor->created_at,
                    'value' => $decrypted_temp
                ];
                $decrypted_data['nutrient_level'][] = [
                    'timestamp' => $sensor->created_at,
                    'value' => $decrypted_nutrient
                ];
            }

            return response()->json(['sensorData' => $decrypted_data]);
        } else {
            return view(route('ownerdashboard'), ['sensorData' => null, 'error' => 'No data found']);
        }
        
    } catch (\Exception $e) {
        Log::error('Error fetching sensor data: ' . $e->getMessage());
        return view(route('ownerdashboard'), ['sensorData' => null, 'error' => 'Internal Server Error']);
    }
}

    private function decrypt_data($encrypted_data, $method, $key, $iv)
    {
        try {

            $encrypted_data = base64_decode($encrypted_data);
            $decrypted_data = openssl_decrypt($encrypted_data, $method, $key, OPENSSL_NO_PADDING, $iv);
            $decrypted_data = rtrim($decrypted_data, "\0");
            $decoded_msg = base64_decode($decrypted_data);
            return $decoded_msg;
        } catch (\Exception $e) {
            Log::error('Decryption error: ' . $e->getMessage());
            return null;
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'phValue' => 'required|numeric',
            'temp' => 'required|numeric',
            'waterLevel' => 'required|numeric',
        ]);

        $iv = "aaaaaaaaaaaaaaaa";
        $iv_base64 = base64_encode($iv);

        $towerid='1';
        $pH=$request->phValue;
        $temperature=$request->temp;
        $nutrientlevel=$request->waterlevel;

        Sensor::create([
            'towerid' => $towerid,
            'pH' => $pH,
            'temperature' => $temperature,
            'nutrientlevel' => $nutrientlevel,
            'iv' => $iv_base64
        ]);

        return response()->json(['status' => 'success'], 201);

    }
    
}
