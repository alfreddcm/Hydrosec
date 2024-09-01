<?php

namespace App\Http\Controllers;

use App\Mail\Alert;
use App\Models\Owner;
use App\Models\Sensor;
use App\Models\Tower;
use App\Models\Towerlogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

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
                    'nutrient_level' => [],
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
                $decrypted_data = [
                    'pH' => $this->decrypt_data($sdata->pH, $method, $decrypted_key, $decrypted_iv),
                    'temperature' => $this->decrypt_data($sdata->temperature, $method, $decrypted_key, $decrypted_iv),
                    'nutrient_level' => $this->decrypt_data($sdata->nutrientlevel, $method, $decrypted_key, $decrypted_iv),
                ];
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

    public function storedata(Request $request)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";
        $response = [
            'status' => '',
            'phCondition' => '',
            'tempCondition' => '',
            'volumeCondition' => '',
        ];

        // Start the session if it hasn't been started already
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize session data if not already set
        if (!isset($_SESSION['sensor_data'])) {
            $_SESSION['sensor_data'] = [];
        }

        try {Log::info('Request received:', $request->all());

            $validatedData = $request->validate([
                'phValue' => 'required',
                'temp' => 'required',
                'waterLevel' => 'required',
                'key' => 'required',
                'iv' => 'required',
                'pump' => 'required',
                'light' => 'required',
                'ipAddress' => 'required',
                'macAddress' => 'required',
                'towercode' => 'required',
            ]);
            
            Log::info('Validated data:', $validatedData);
            
            // Decrypt the incoming data
            $decrypted_key = $this->decrypt_data($validatedData['key'], $method, $key_str, $iv_str);
            $decrypted_iv = $this->decrypt_data($validatedData['iv'], $method, $key_str, $iv_str);
            
            $decrypted_ph = $this->decrypt_data($validatedData['phValue'], $method, $decrypted_key, $decrypted_iv);
            $decrypted_temp = $this->decrypt_data($validatedData['temp'], $method, $decrypted_key, $decrypted_iv);
            $decrypted_nutrient = $this->decrypt_data($validatedData['waterLevel'], $method, $decrypted_key, $decrypted_iv);
            $decrypted_pump = $this->decrypt_data($validatedData['pump'], $method, $decrypted_key, $decrypted_iv);
            $decrypted_light = $this->decrypt_data($validatedData['light'], $method, $decrypted_key, $decrypted_iv);
            
            $decrypted_ip = $this->decrypt_data($validatedData['ipAddress'], $method, $key_str, $iv_str);
            $decrypted_mac = $this->decrypt_data($validatedData['macAddress'], $method, $key_str, $iv_str);
            $decrypted_towercode = $this->decrypt_data($validatedData['towercode'], $method, $key_str, $iv_str);
            
            $towers = Tower::all(['id', 'name', 'towercode']);
            Log::info('Retrieved towers:', $towers->map(function ($tower) {
                return [
                    'name' => Crypt::decryptString($tower->name),
                    'towercode' => Crypt::decryptString($tower->towercode),
                ];
            })->toArray());
            
            foreach ($towers as $tower) {
                $towercode = Crypt::decryptString($tower->towercode);
                if ($towercode == $decrypted_towercode) {
                    $ipmac = Tower::where('id', $tower->id)->first();
            
                    if ($ipmac) {
                        $ip = $ipmac->ipAdd;
                        $mac = $ipmac->macAdd;
            
                        if ($ip == $decrypted_ip && $mac == $decrypted_mac) {
                            $_SESSION['tower_id'] = $tower->id;
            
                            $_SESSION['sensor_data'][] = [
                                'pH' => $decrypted_ph,
                                'temp' => $decrypted_temp,
                                'volume' => $decrypted_nutrient,
                                'pump' => $decrypted_pump,
                                'light' => $decrypted_light,
                            ];
            
                            try {
                                if (count($_SESSION['sensor_data']) >= 5) {
            
                                    $sumPh = 0;
                                    $sumTemp = 0;
                                    $sumVolume = 0;
            
                                    foreach ($_SESSION['sensor_data'] as $data) {
                                        Log::info('Sensor data:', $data);
            
                                        $sumPh += $data['pH'] ?? 0;
                                        $sumTemp += $data['temp'] ?? 0;
                                        $sumVolume += $data['volume'] ?? 0;
                                    }
            
                                    $averagePh = round($sumPh / count($_SESSION['sensor_data']), 2);
                                    $averageTemp = round($sumTemp / count($_SESSION['sensor_data']), 2);
                                    $averageVolume = round($sumVolume / count($_SESSION['sensor_data']), 2);
            
                                    $phCondition = $this->getCondition($averagePh, 'pH');
                                    $tempCondition = $this->getCondition($averageTemp, 'temp');
                                    $volumeCondition = $this->getCondition($averageVolume, 'nutrient');
            
                                    $_SESSION['allConditions'] = [
                                        'phCondition' => $phCondition,
                                        'tempCondition' => $tempCondition,
                                        'volumeCondition' => $volumeCondition,
                                        'averagePh' => $averagePh,
                                        'averageTemp' => $averageTemp,
                                        'averageVolume' => $averageVolume,
                                    ];
            
                                    $this->checkAndSendNotification($_SESSION['allConditions']);
    
            
                                    unset($_SESSION['sensor_data']);
                                    $_SESSION['sensor_data'] = [];
            
                                } else {
                                    $_SESSION['allConditions'] = [
                                        'phCondition' => '',
                                        'tempCondition' => '',
                                        'volumeCondition' => '',
                                        'averagePh' => '',
                                        'averageTemp' =>'',
                                        'averageVolume' => '',
                                    ];                                }
            
                                Sensor::create([
                                    'towerid' => $tower->id,
                                    'pH' => $validatedData['phValue'],
                                    'temperature' => $validatedData['temp'],
                                    'nutrientlevel' => $validatedData['waterLevel'],
                                    'k' => $validatedData['key'],
                                    'iv' => $validatedData['iv'],
                                    'status' => '1',
                                ]);
            
                                Log::info('Session data count:', ['count' => count($_SESSION['sensor_data'])]);
            
                                $response = [
                                    'status' => 'success',
                                    'ph_condition' =>  $_SESSION['allConditions']['phCondition'],
                                    'temperature_condition' =>  $_SESSION['allConditions']['tempCondition'],
                                    'nutrient_condition' =>  $_SESSION['allConditions']['volumeCondition'],
                                ];
            
                                return response()->json($response, 201);
            
                            } catch (\Exception $e) {
                                return response()->json([
                                    'error' => 'internal_server_error',
                                    'message' => $e->getMessage(),
                                ], 500);
                            }
            
                        } else {
                            $ipmac->ipAdd = $decrypted_ip;
                            $ipmac->macAdd = $decrypted_mac;
                            $ipmac->save();
            
                            Log::info('Updated Tower IP and MAC addresses:', ['id' => $tower->id]);
                            return response()->json(['status' => 'success'], 201);
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

    private function getCondition($averageValue, $type)
    {
        $condition = 'Unknown';

        switch ($type) {
            case 'pH':
                if ($averageValue < 5.5) {
                    $condition = 'Tooacidic';
                } elseif ($averageValue < 6.0) {
                    $condition = 'Acidic';
                } elseif ($averageValue > 7.0) {
                    $condition = 'Toobasic';
                } elseif ($averageValue > 6.5) {
                    $condition = 'Basic';
                } else {
                    $condition = 'Good';
                }
                break;

            case 'nutrient':
                if ($averageValue <= 15) {
                    $condition = 'CriticalLow';
                } elseif ($averageValue <= 30) {
                    $condition = 'Low';
                } elseif ($averageValue >= 90) {
                    $condition = 'Full';
                } elseif ($averageValue >= 50) {
                    $condition = 'Half';
                } else {
                    $condition = 'Good';
                }
                break;

            case 'temp':
                if ($averageValue <= 18) {
                    $condition = 'Cold';
                } elseif ($averageValue > 18 && $averageValue <= 25) {
                    $condition = 'Good';
                } elseif ($averageValue > 25 && $averageValue <= 30) {
                    $condition = 'Hot';
                } else {
                    $condition = 'Too Hot';
                }
                break;
        }
        return $condition;
    }

    private function checkAndSendNotification($conditions)
    {
        $triggerConditions = [
            'phCondition' => ['Tooacidic', 'Toobasic', 'basic', 'acidic'],
            'volumeCondition' => ['CriticalLow', 'Low'],
            'tempCondition' => ['hot', 'Too Hot'],
        ];

        $alerts = [];
        Log::info('Conditions for alert:', $conditions);
        Log::info('Sending alert email with conditions: ', $alerts);

        $this->sendAlertEmail($alerts);
    }

    private function sendAlertEmail($alerts)
    {
        $towerId = $_SESSION['tower_id'] ?? null;

        if ($towerId) {
            $tower = Tower::find($towerId);

            if ($tower && $tower->OwnerID) {
                $owner = Owner::find($tower->OwnerID);

                if ($owner && $owner->email) {
                    $email = Crypt::decryptString($owner->email);
                    $decryptedTowerName = Crypt::decryptString($tower->name);

                    $allConditions = $_SESSION['allConditions'] ?? [];
                    $body = "The following conditions have been detected at Tower '{$decryptedTowerName}': ";

                    $conditions = [];
                    
                    if (isset($allConditions['averagePh'])) {
                        $conditions[] = "pH: {$allConditions['averagePh']} - {$allConditions['phCondition']}  ";
                    }
                    if (isset($allConditions['averageTemp'])) {
                        $conditions[] = "Temperature: {$allConditions['averageTemp']} - {$allConditions['tempCondition']}  ";
                    }
                    if (isset($allConditions['averageVolume'])) {
                        $conditions[] = "Nutrient: {$allConditions['averageVolume']}% - {$allConditions['volumeCondition']}";
                    }
                    
                    $body .= implode(",", $conditions);
                    
                    $details = [
                        'title' => 'Alert: Conditions Detected',
                        'body' => $body,
                    ];
                    

                    $mailStatus = 'Not Sent';

                    try {
                        Mail::to($email)->send(new Alert($details));
                        $mailStatus = 'Sent';

                        Log::info('Alert email sent to', ['email' => $email, 'tower_id' => $towerId]);

                    } catch (\Exception $e) {
                        Log::error('Failed to send alert email', ['email' => $email, 'tower_id' => $towerId, 'error' => $e->getMessage()]);
                    }

                    TowerLogs::create([
                        'ID_tower' => $towerId,
                        'activity' => Crypt::encryptString(
                            "Alert: Conditions detected - " . json_encode($details['body']) . " Mail Status: " . $mailStatus
                        ),
                    ]);

                    Log::info('Alert logged in tbl_towerlogs', ['tower_id' => $towerId, 'activity' => json_encode($details['body'])]);

                } else {
                    Log::warning('Owner not found or email not available for Tower ID', ['tower_id' => $towerId]);
                }
            } else {
                Log::warning('Tower not found or owner not associated with Tower ID', ['tower_id' => $towerId]);
            }
        } else {
            Log::warning('Tower ID not available in session.');
        }
    }

}
