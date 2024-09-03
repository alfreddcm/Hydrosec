<?php

namespace App\Http\Controllers;

use App\Mail\Alert;
use App\Models\Owner;
use App\Models\Sensor;
use App\Models\Tower;
use App\Models\Towerlogs;
use App\Models\Pump;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class SensorData extends Controller
{

    public function getLatestSensorData(Request $request, $id)
    {
        $tid = $id;
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";
    
        try {
            $towerdata = Tower::where('id', $tid)->first();   
            $stat=$towerdata->status;
            

            $sdata = Sensor::where('towerid', $tid)->orderBy('id', 'desc')->first();
    
            if ($sdata) {
                $key = $sdata->k;
                $iv = $sdata->iv;
    
                $decrypted_key = $this->decrypt_data($key, $method, $key_str, $iv_str);
                $decrypted_iv = $this->decrypt_data($iv, $method, $key_str, $iv_str);
    
                $ph = $this->decrypt_data($sdata->pH, $method, $decrypted_key, $decrypted_iv);
                $temp = $this->decrypt_data($sdata->temperature, $method, $decrypted_key, $decrypted_iv);
                $volume = $this->decrypt_data($sdata->nutrientlevel, $method, $decrypted_key, $decrypted_iv);
                $status = $this->decrypt_data($sdata->status, $method, $decrypted_key, $decrypted_iv);
                $light = $this->decrypt_data($sdata->light, $method, $decrypted_key, $decrypted_iv);
    
                $decrypted_data = [
                    'pH' => $ph,
                    'temperature' => $temp,
                    'nutrient_level' => $volume,
                    'status' => $stat,
                    'light' => $light,
                ];
            
    
                return response()->json(['sensorData' => $decrypted_data]);
            } else {
                return response()->json(['error' => 'No data found'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching sensor data: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    
    public function getdata($id, $column)
{
    $key_str = "ISUHydroSec2024!";
    $iv_str = "HydroVertical143";
    $method = "AES-128-CBC";

    try {
        // Fetch all relevant sensor data for the specified tower ID
        $sensorData = Sensor::where('towerid', $id)
                            ->orderBy('created_at', 'asc') // Order by time for graph plotting
                            ->get(['k', 'iv', $column, 'created_at']);

        $decryptedData = [];

        foreach ($sensorData as $sdata) {
            // Decrypt the key and IV for each record
            $decrypted_key = $this->decrypt_data($sdata->k, $method, $key_str, $iv_str);
            $decrypted_iv = $this->decrypt_data($sdata->iv, $method, $key_str, $iv_str);

            // Decrypt the specified column (e.g., temperature, pH)
            $decrypted_column = $this->decrypt_data($sdata->$column, $method, $decrypted_key, $decrypted_iv);

            // Format timestamp and append the decrypted data
            $formattedTimestamp = $sdata->created_at->format('Y-m-d H:i:s');

            $decryptedData[] = [
                'value' => (float) $decrypted_column, // Ensure values are floats for consistency
                'timestamp' => $formattedTimestamp,
            ];
        }

        return response()->json(['sensorData' => $decryptedData]);

    } catch (\Exception $e) {
        Log::error('Error fetching sensor data: ' . $e->getMessage());
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}
    
    public function getPump($id)
    {
        try {
            \Log::info("Fetching pump data for tower ID: {$id}");
    
            $key_str = "ISUHydroSec2024!";
            $iv_str = "HydroVertical143";
            $method = "AES-128-CBC";
    
            \Log::info("Encryption method: {$method}");
    
            // Fetch the sensor data from the database
            $sensorData = Pump::where('towerid', $id)
                                ->orderBy('created_at', 'desc')
                                ->get();
    
            \Log::info("Fetched sensor data count: " . $sensorData->count());
    
            $events = [];
            $lastStatus = null;
    
            foreach ($sensorData as $data) {
                \Log::info("Processing record with ID: {$data->id}");    
                \Log::info("Encrypted pump value: {$data->pump}");
    
                // Decrypt the pump column
                $decrypted_pump = $this->decrypt_data($data->pump, $method, $key_str ,$iv_str);

                $formattedTimestamp = Carbon::parse($data->created_at)->format('D h:i A m/d/Y');

    
                // Normalize decrypted pump value to an integer (0 or 1)
                $decrypted_pump = (int)round(floatval($decrypted_pump));
                if ($decrypted_pump == 1) {
                        \Log::info("Detected start pumping event at timestamp: {$formattedTimestamp}");
                        $events[] = [
                            'event' => 'Start Pumping',
                            'timestamp' => $formattedTimestamp
                        ];
                    }else{
                        \Log::info("Detected start pumping event at timestamp: {$formattedTimestamp}");
                        $events[] = [
                            'event' => 'Stop Pumping',
                            'timestamp' => $formattedTimestamp
                        ];
                    }
    
                \Log::info("Normalized decrypted pump value: {$decrypted_pump}");
    
                // if ($lastStatus === null) {
                //     // Initialize lastStatus with the first record
                //     $lastStatus = $decrypted_pump;
                //     \Log::info("Initial lastStatus set to: {$lastStatus}");
                //     continue;
                // }
    
                // \Log::info("Comparing lastStatus: {$lastStatus} with currentStatus: {$decrypted_pump}");
    
                // // Check for transition from 0 to 1 (start pumping)
                // if ($lastStatus == 0 && $decrypted_pump == 1) {
                //     \Log::info("Detected start pumping event at timestamp: {$data->created_at}");
                //     $events[] = [
                //         'event' => 'Start Pumping',
                //         'timestamp' => $data->created_at
                //     ];
                // }
    
                // // Check for transition from 1 to 0 (stop pumping)
                // if ($lastStatus == 1 && $decrypted_pump == 0) {
                //     \Log::info("Detected stop pumping event at timestamp: {$data->created_at}");
                //     $events[] = [
                //         'event' => 'Stop Pumping',
                //         'timestamp' => $data->created_at
                //     ];
                // }
    
                // // Update lastStatus for the next iteration
                // $lastStatus = $decrypted_pump;
                // \Log::info("Updated lastStatus to: {$lastStatus}");
            }
    
            // if (empty($events)) {
            //     \Log::info('No pumping events found');
            //     return response()->json(['message' => 'No pumping events found'], 404);
            // }
    
            \Log::info('Pumping events found and returned successfully');
            return response()->json($events);
    
        } catch (\Exception $e) {
            \Log::error('Failed to fetch pump data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch pump data'], 500);
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

        try {
            Log::info('Request received:', $request->all());

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

            \Log::info('Decrypted Data:', [
                'key' => $decrypted_key,
                'iv' => $decrypted_iv,
                'phValue' => $decrypted_ph,
                'temp' => $decrypted_temp,
                'waterLevel' => $decrypted_nutrient,
                'pump' => $decrypted_pump,
                'light' => $decrypted_light,
                'ipAddress' => $decrypted_ip,
                'macAddress' => $decrypted_mac,
                'towercode' => $decrypted_towercode,
            ]);
            
            foreach ($towers as $tower) {
                $towercode = Crypt::decryptString($tower->towercode);
                
                if ($towercode == $decrypted_towercode) {
                    $ipmac = Tower::where('id', $tower->id)->first();
            
                    if ($ipmac) {
                        $ip = Crypt::decryptString($ipmac->ipAdd);
                        $mac = Crypt::decryptString($ipmac->macAdd);
                        \Log::info('Decrypted IP and MAC addresses:', [
                            'ipAddress' => $ip,
                            'macAddress' => $mac,
                        ]);
                        if ($ip == $decrypted_ip && $mac == $decrypted_mac) {
                            // Store sensor data in session
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
                                    // Calculate averages and conditions
                                    $sumPh = array_sum(array_column($_SESSION['sensor_data'], 'pH'));
                                    $sumTemp = array_sum(array_column($_SESSION['sensor_data'], 'temp'));
                                    $sumVolume = array_sum(array_column($_SESSION['sensor_data'], 'volume'));
            
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
                                        'averageTemp' => '',
                                        'averageVolume' => '',
                                    ];
                                }
         

                                Sensor::create([
                                    'towerid' => $tower->id,
                                    'pH' => $validatedData['phValue'],
                                    'temperature' => $validatedData['temp'],
                                    'nutrientlevel' => $validatedData['waterLevel'],
                                    'pump' => $validatedData['pump'],
                                    'light' => $validatedData['light'],

                                    'k' => $validatedData['key'],
                                    'iv' => $validatedData['iv'],
                                    'status' => '1',
                                ]);
            
                                Log::info('Session data count:', ['count' => count($_SESSION['sensor_data'])]);
            
                                return response([
                                    'status' => 'success',
                                    'ph_condition' => $_SESSION['allConditions']['phCondition'],
                                    'temperature_condition' => $_SESSION['allConditions']['tempCondition'],
                                    'nutrient_condition' => $_SESSION['allConditions']['volumeCondition'],
                                ], 201);
            
                            } catch (\Exception $e) {
                                return response()->json([
                                    'error' => 'internal_server_error',
                                    'message' => $e->getMessage(),
                                ], 500);
                            }
                        } else {
                            // Update IP and MAC addresses
                            $ipmac->ipAdd = Crypt::encryptString($decrypted_ip);
                            $ipmac->macAdd = Crypt::encryptString($decrypted_mac);
                            $ipmac->save();
            
                            Log::info('Updated Tower IP and MAC addresses:', ['id' => $tower->id]);
                            return response()->json(['status' => 'success'], 201);
                        }
                    }
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
