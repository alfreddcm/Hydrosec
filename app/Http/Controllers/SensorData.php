<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Tower;




class SensorData extends Controller
{
    
    public function getLatestSensorData(Request $Request){
                $key_str = "ISUHydroSec2024!";
                $iv_str ="HydroVertical143";
                $method = "AES-128-CBC";

    try {
 
        $sdata = Sensor::
        where('towerid', $Request->towerid)->
        orderBy('id', 'desc')
        ->take(4)
        ->get();

        if ($sdata->isNotEmpty()) {

            $decrypted_data = [
                'pH' => [],
                'temperature' => [],
                'nutrient_level' => []
            ];

            foreach ($sdata as $sensor) {
                
                $key = $sensor->k;
                $iv = $sensor->iv;

                $decrypted_key = $this->decrypt_data($key, $method, $key_str, $iv_str);
                $decrypted_iv = $this->decrypt_data($iv, $method, $key_str, $iv_str);
    
                
                $decrypted_ph = $this->decrypt_data($sensor->pH, $method, $decrypted_key, $decrypted_iv);
                $decrypted_temp = $this->decrypt_data($sensor->temperature, $method, $decrypted_key, $decrypted_iv);
                $decrypted_nutrient = $this->decrypt_data($sensor->nutrientlevel, $method, $decrypted_key, $decrypted_iv);

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


    public function storedata(Request $request)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";
    
        try {
            Log::info('Request received:', $request->all());
    
            $validatedData = $request->validate([
                'phValue' => 'required',
                'temp' => 'required',
                'waterLevel' => 'required',
                'key' => 'required',
                'iv' => 'required',
                'ipAddress' => 'required',
                'macAddress' => 'required'
            ]);
    
            Log::info('Validated data:', $validatedData);
    
            $encrypted_ip = $validatedData['ipAddress'];
            $encrypted_mac = $validatedData['macAddress'];
            $key = $validatedData['key'];
            $iv = $validatedData['iv'];
    
            try {
                $decrypted_ip = $this->decrypt_data($encrypted_ip, $method, $key_str, $iv_str);
                $decrypted_mac = $this->decrypt_data($encrypted_mac, $method, $key_str, $iv_str);
    
                $decrypted_key = $this->decrypt_data($key, $method, $key_str, $iv_str);
                $decrypted_iv = $this->decrypt_data($iv, $method, $key_str, $iv_str);
    
                Log::info('Decrypted data:', [
                    'decrypted_ip' => $decrypted_ip,
                    'decrypted_mac' => $decrypted_mac,
                    'decrypted_key' => $decrypted_key,
                    'decrypted_iv' => $decrypted_iv
                ]);
            } catch (\Exception $e) {
                Log::error('Decryption failed:', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'decryption_failed', 'message' => $e->getMessage()], 400);
            }
    
            $tower = Tower::get();
            Log::info('Retrieved towers:', ['count' => $tower->count()]);
    
            foreach ($tower as $data) {
                $id = $data->id;
                $ip = $data->ipAdd;
                $mac = $data->macAdd;
    
                $decrypted_ip1 = $this->decrypt_data($ip, $method, $key_str, $iv_str);
                $decrypted_mac1 = $this->decrypt_data($mac, $method, $key_str, $iv_str);
    
                Log::info("Checking tower ID $id", [
                    'decrypted_ip1' => $decrypted_ip1,
                    'decrypted_mac1' => $decrypted_mac1
                ]);
    
                if ($decrypted_ip == $decrypted_ip1 && $decrypted_mac == $decrypted_mac1) {
                    Log::info("Match found for Tower ID: $id");
    
                    Sensor::create([
                        'towerid' => $id,
                        'pH' => $validatedData['phValue'],
                        'temperature' => $validatedData['temp'],
                        'nutrientlevel' => $validatedData['waterLevel'],
                        'k' => $validatedData['key'],
                        'iv' => $validatedData['iv'],
                        'status' => 'active'
                    ]);
    
                    Log::info('Sensor data stored successfully for Tower ID:', ['id' => $id]);
    
                    return response()->json(['status' => 'success'], 201);
                }
            }
    
            Log::warning('No matching tower found for the provided IP and MAC addresses.');
            return response()->json(['error' => 'notfound'], 404);
    
        } catch (ValidationException $e) {
            Log::error('Validation failed:', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::critical('An unexpected error occurred:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'internal_server_error', 'message' => $e->getMessage()], 500);
        }
    }
    
    
    
    
}
