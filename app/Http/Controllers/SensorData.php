<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Tower;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;



class SensorData extends Controller
{

    public function getLatestSensorData(Request $request, $id)
{
    $tid = $id;
    $towerdata = Tower::where('id', $tid)->get();

    // AES encryption parameters
    $key_str = "ISUHydroSec2024!";
    $iv_str = "HydroVertical143";
    $method = "AES-128-CBC";

    try {
        // Fetch the latest sensor data
        $sdata = Sensor::where('towerid', $tid)->orderBy('id', 'desc')->first();

        if ($sdata) {
            $decrypted_data = [
                'pH' => [],
                'temperature' => [],
                'nutrient_level' => []
            ];

            $key = $sdata->k;
            $iv = $sdata->iv;

            // Decrypt key and IV
            $decrypted_key = $this->decrypt_data($key, $method, $key_str, $iv_str);
            $decrypted_iv = $this->decrypt_data($iv, $method, $key_str, $iv_str);

            // Decrypt sensor data
            $decrypted_ph = $this->decrypt_data($sdata->pH, $method, $decrypted_key, $decrypted_iv);
            $decrypted_temp = $this->decrypt_data($sdata->temperature, $method, $decrypted_key, $decrypted_iv);
            $decrypted_nutrient = $this->decrypt_data($sdata->nutrientlevel, $method, $decrypted_key, $decrypted_iv);

            // Populate decrypted data array
            $decrypted_data['pH'][] = $decrypted_ph;
            $decrypted_data['temperature'][] = $decrypted_temp;
            $decrypted_data['nutrient_level'][] = $decrypted_nutrient;

            // Return JSON response
            return response()->json(['sensorData' => $decrypted_data]);
        } else {
            // Return a 404 response if no data is found
            return response()->json(['error' => 'No data found'], 404);
        }
    } catch (\Exception $e) {
        // Log the error and return a 500 response
        Log::error('Error fetching sensor data: ' . $e->getMessage());
        return response()->json(['error' => 'Internal Server Error'], 500);
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

    /**
     * Store a newly created tower in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
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
                'macAddress' => 'required',
                'towercode' => 'required'
            ]);

            Log::info('Validated data:', $validatedData);

            $encrypted_ip = $validatedData['ipAddress'];
            $encrypted_mac = $validatedData['macAddress'];
            $encrypted_towercode = $validatedData['towercode'];

            $key = $validatedData['key'];
            $iv = $validatedData['iv'];

            $decrypted_ip = $this->decrypt_data($encrypted_ip, $method, $key_str, $iv_str);
            $decrypted_mac = $this->decrypt_data($encrypted_mac, $method, $key_str, $iv_str);
            $decrypted_towercode = $this->decrypt_data($encrypted_towercode, $method, $key_str, $iv_str);

            $decrypted_key = $this->decrypt_data($key, $method, $key_str, $iv_str);
            $decrypted_iv = $this->decrypt_data($iv, $method, $key_str, $iv_str);

            Log::info('Decrypted data:', [
                'decrypted_ip' => $decrypted_ip,
                'decrypted_mac' => $decrypted_mac,
                'decrypted_key' => $decrypted_key,
                'decrypted_iv' => $decrypted_iv,
                'decrypted_towercode' => $decrypted_towercode
            ]);

            $towers = Tower::all(['id', 'name', 'towercode']);
            Log::info('Retrieved towers:', $towers->map(function ($tower) {
                return [
                    'name' => Crypt::decryptString($tower->name),
                    'towercode' => Crypt::decryptString($tower->towercode),
                ];
            })->toArray());

            foreach ($towers as $tower) {
                $id = $tower->id;
                $towercode = Crypt::decryptString($tower->towercode);
                Log::info(Crypt::decryptString($tower->towercode));
                Log::info($id);


                if ($towercode == $decrypted_towercode) {
                    $ipmac = Tower::where('id', $tower->id)->first();

                    if ($ipmac) {
                        $ip = $ipmac->ipAdd;
                        $mac = $ipmac->macAdd;

                        if ($ip || $mac || $ip == $decrypted_ip || $mac == $decrypted_mac) {
                            Sensor::create([
                                'towerid' => $tower->id,
                                'pH' => $validatedData['phValue'],
                                'temperature' => $validatedData['temp'],
                                'nutrientlevel' => $validatedData['waterLevel'],
                                'k' => $validatedData['key'],
                                'iv' => $validatedData['iv'],
                                'status' => 'active'
                            ]);

                            Log::info('Sensor data stored successfully for Tower ID:', ['id' => $tower->id]);

                            return response()->json(['status' => 'success'], 201);
                        } else {
                            if (!$ip || !$mac || $ip != $decrypted_ip || $mac != $decrypted_mac) {
                                $ipmac->ipAdd = $decrypted_ip;
                                $ipmac->macAdd = $decrypted_mac;
                                $ipmac->save();

                                Log::info('Updated Tower IP and MAC addresses:', ['id' => $tower->id]);
                            }

                            return response()->json(['status' => 'updated', 'id' => $tower->id], 200);
                        }
                    } else {
                        Log::warning('Tower record not found for ID:', ['id' => $tower->id]);
                        return response()->json(['error' => 'notfound'], 404);
                    }
                } else {
                    Log::warning('No matching tower code found:', ['provided' => $decrypted_towercode]);
                    return response()->json(['error' => 'notfound'], 404);
                }


            }

            Log::warning('No matching tower found for the provided tower code.');
            return response()->json(['error' => 'notregistered'], 404);

        } catch (ValidationException $e) {
            Log::error('Validation failed:', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::critical('An unexpected error occurred:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'internal_server_error', 'message' => $e->getMessage()], 500);
        }
    }




    private function getPHCondition($averagePH)
    {
        if ($averagePH < 5.5) {
            return 'Critical Low';
        } elseif ($averagePH < 6.0) {
            return 'Acidic';
        } elseif ($averagePH > 7.0) {
            return 'Critical High';
        } elseif ($averagePH > 6.5) {
            return 'Basic';
        } else {
            return 'Good';
        }
    }

    private function getNutrientCondition($averageWaterLevel)
    {
        if ($averageWaterLevel <= 15) {
            return 'Critical Low';
        } elseif ($averageWaterLevel <= 20) {
            return 'Low';
        } elseif ($averageWaterLevel >= 25) {
            return 'Critical High';
        } elseif ($averageWaterLevel >= 20) {
            return 'Nearing Good';
        } else {
            return 'Good';
        }
    }

private function getTemperatureCondition($averageTemp)
{
    if ($averageTemp <= 18) {
        return 'Cold';
    } elseif ($averageTemp > 18 && $averageTemp <= 25) {
        return 'Good';
    } elseif ($averageTemp > 25 && $averageTemp <= 30) {
        return 'Hot';
    } else {
        return 'Too Hot';
    }
}


}