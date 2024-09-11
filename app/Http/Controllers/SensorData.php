<?php

namespace App\Http\Controllers;

use App\Mail\Alert;
use App\Models\Owner;
use App\Models\Pump;
use App\Models\Sensor;
use App\Models\Tower;
use App\Models\Towerlogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SensorData extends Controller
{

    public function getLatestSensorData(Request $request, $id)
    {
        $tid = $id;
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        try {
            $towerdata = Tower::where('id', $tid)
                ->first();
            $stat = Crypt::decryptString($towerdata->status);

            if ($stat == '1') {
                $oneHourAgo = Carbon::now()->subHour();
                $sdata = Sensor::where('towerid', $tid)
                    ->where('created_at', '>=', $oneHourAgo)
                    ->orderBy('id', 'desc')
                    ->first();

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
                        'light' => $light,
                    ];

                    return response()->json(['sensorData' => $decrypted_data]);
                } else {

                    $decrypted_data = [
                        'pH' => null,
                        'temperature' => null,
                        'nutrient_level' => null,
                        'light' => null,
                    ];

                    return response()->json(['sensorData' => $decrypted_data]);
                }
            } else {
                $decrypted_data = [
                    'pH' => null,
                    'temperature' => null,
                    'nutrient_level' => null,
                    'light' => null,
                ];

                return response()->json(['sensorData' => $decrypted_data]);
            }

        } catch (\Exception $e) {
            Log::error('Error fetching sensor data: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getPump($id)
    {
        try {

            $key_str = "ISUHydroSec2024!";
            $iv_str = "HydroVertical143";
            $method = "AES-128-CBC";

            $sensorData = Pump::where('towerid', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info("Fetched sensor data count: " . $sensorData->count());

            $events = [];

            if ($sensorData->isNotEmpty()) {
                foreach ($sensorData as $data) {

                    $decrypted_pump = $this->decrypt_data($data->status, $method, $key_str, $iv_str);
                    $formattedTimestamp = Carbon::parse($data->created_at)->format('D h:i A m/d/Y');

                    if ($decrypted_pump == 1) {
                        $events[] = [
                            'pump' => $decrypted_pump,
                            'event' => 'Start Pumping',
                            'timestamp' => $formattedTimestamp,
                        ];
                    } else {
                        $events[] = [
                            'pump' => $decrypted_pump,
                            'event' => 'Stop Pumping',
                            'timestamp' => $formattedTimestamp,
                        ];
                    }
                }
            } else {
                \Log::info("No pump data found for tower ID: {$id}");
            }

            \Log::info('Pumping events processed successfully');
            return response()->json($events);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch pump data for tower ID: ' . $id . '. Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch pump data'], 500);
        }
    }

    public function storedata(Request $request)
    {
        $alertMessages = [];

        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        $response = [
            'status' => '',
            'phCondition' => '',
            'tempCondition' => '',
            'volumeCondition' => '',
        ];

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

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
                'light' => 'required',
                'ipAddress' => 'required',
                'macAddress' => 'required',
                'towercode' => 'required',
            ]);

            Log::info('Validated data:', $validatedData);

            $decrypted_key = $this->decrypt_data($validatedData['key'], $method, $key_str, $iv_str);
            $decrypted_iv = $this->decrypt_data($validatedData['iv'], $method, $key_str, $iv_str);

            $decrypted_ph = (float) $this->decrypt_data($validatedData['phValue'], $method, $decrypted_key, $decrypted_iv);
            $decrypted_temp = (float) $this->decrypt_data($validatedData['temp'], $method, $decrypted_key, $decrypted_iv);
            $decrypted_nutrient = (float) $this->decrypt_data($validatedData['waterLevel'], $method, $decrypted_key, $decrypted_iv);
            $decrypted_light = $this->decrypt_data($validatedData['light'], $method, $decrypted_key, $decrypted_iv);
            $decrypted_ip = $this->decrypt_data($validatedData['ipAddress'], $method, $key_str, $iv_str);
            $decrypted_mac = $this->decrypt_data($validatedData['macAddress'], $method, $key_str, $iv_str);
            $decrypted_towercode = $this->decrypt_data($validatedData['towercode'], $method, $key_str, $iv_str);

            $towers = Tower::all(['id', 'name', 'towercode']);

            Log::info('Decrypted Data from tower:', [
                'key' => $decrypted_key,
                'iv' => $decrypted_iv,
                'phValue' => $decrypted_ph,
                'temp' => $decrypted_temp,
                'waterLevel' => $decrypted_nutrient,
                'light' => $decrypted_light,
                'ipAddress' => $decrypted_ip,
                'macAddress' => $decrypted_mac,
                'towercode' => $decrypted_towercode,
            ]);

            foreach ($towers as $tower) {
                $towercode = Crypt::decryptString($tower->towercode);

                if ($towercode == $decrypted_towercode) {
                    $ipmac = Tower::where('id', $tower->id)->first();

                    if ($ipmac && !is_null($ipmac->ipAdd)) {
                        $ip = Crypt::decryptString($ipmac->ipAdd);
                        $mac = Crypt::decryptString($ipmac->macAdd);
                        Log::info('Decrypted IP and MAC addresses:', [
                            'ipAddress' => $ip,
                            'macAddress' => $mac,
                        ]);

                        if ($ip == $decrypted_ip && $mac == $decrypted_mac) {

                            $_SESSION['tower_id'] = $ipmac->id;
                            $_SESSION['sensor_data'][] = [
                                'pH' => $decrypted_ph,
                                'temp' => $decrypted_temp,
                                'volume' => $decrypted_nutrient,
                                'light' => $decrypted_light,
                            ];
                            Log::info('Session count for sensor_data:', ['count' => count($_SESSION['sensor_data'])]);

                            if ($decrypted_ph < 1 || $decrypted_ph > 14 || is_nan($decrypted_ph)) {
                                $alertMessages[] = "pH value is invalid or out of range (1-14),";
                            }

                            if ($decrypted_temp < -16 || $decrypted_temp > 60 || is_nan($decrypted_temp)) {
                                $alertMessages[] = "Temperature value is invalid or out of range (-16°C to 60°C),";
                            }
                            if ($decrypted_nutrient < 0 || $decrypted_nutrient > 23 || is_nan($decrypted_nutrient)) {
                                $alertMessages[] = "Nutrient level is invalid or out of range (1-20),";
                            }

                            if (!empty($alertMessages)) {
                                $body = "The following conditions have been detected at Tower '" . Crypt::decryptString($ipmac->name) . "': ";
                                $body .= implode(", ", $alertMessages);

                                $details = [
                                    'title' => 'Alert: Sensor not Working please check the sensors',
                                    'body' => $body,
                                ];

                                $statusType = 'sensor_error';
                                $this->sendAlertEmail($details, $tower->id, $statusType);

                                return response()->json(['errors' => $alertMessages], 422);
                            } else {
                                $alertMessages[] = '';

                                try {
                                    if (count($_SESSION['sensor_data']) >= 5) {
                                        $triggerCounts = [
                                            'ph' => 0,
                                            'temp' => 0,
                                            'nut' => 0,
                                        ];

                                        $alerts = [];
                                        $triggeredConditions = [
                                            'ph' => [],
                                            'temp' => [],
                                            'nut' => [],
                                        ];

                                        foreach ($_SESSION['sensor_data'] as $data) {

                                            $phCondition = $this->getCondition((float) $data['pH'], 'pH');
                                            $tempCondition = $this->getCondition((float) $data['temp'], 'temp');
                                            $volumeCondition = $this->getCondition((float) $data['volume'], 'nutrient');

                                            $triggerConditions = [
                                                'phCondition' => ['Too acidic', 'Too basic', 'basic', 'acidic'],
                                                'volumeCondition' => ['25%', '15%', 'Empty'],
                                                'tempCondition' => ['Too Hot', 'hot'],
                                            ];

                                            if (in_array($phCondition, $triggerConditions['phCondition'])) {
                                                $triggerCounts['ph']++;
                                                $triggeredConditions['ph'][] = "pH: {$data['pH']} - $phCondition";
                                            }

                                            if (in_array($tempCondition, $triggerConditions['tempCondition'])) {
                                                $triggerCounts['temp']++;
                                                $triggeredConditions['temp'][] = "Temperature: {$data['temp']} - $tempCondition";
                                            }

                                            if (in_array($volumeCondition, $triggerConditions['volumeCondition'])) {
                                                $triggerCounts['nut']++;
                                                $triggeredConditions['nut'][] = "Nutrient Volume: {$data['volume']} - $volumeCondition";
                                            }

                                            Log::info('Sensor data condition check:', [
                                                'pH' => $phCondition,
                                                'Temperature' => $tempCondition,
                                                'Nutrient Volume' => $volumeCondition,
                                            ]);

                                            Log::info('Trigger counts:', [
                                                'pH Triggers' => $triggerCounts['ph'],
                                                'Temperature Triggers' => $triggerCounts['temp'],
                                                'Nutrient Volume Triggers' => $triggerCounts['nut'],
                                            ]);

                                        }

                                        $alertMessages = [];

                                        if ($triggerCounts['ph'] >= 3) {
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['ph']));
                                        }

                                        if ($triggerCounts['temp'] >= 3) {
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['temp']));
                                        }

                                        if ($triggerCounts['nut'] >= 3) {
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['nut']));
                                        }

                                        if (!empty($alertMessages)) {
                                            $body = "The following conditions have been detected at Tower '" . Crypt::decryptString($ipmac->name) . "': ";
                                            $body .= implode(", ", $alertMessages);

                                            $details = [
                                                'title' => 'Alert: Conditions Detected',
                                                'body' => $body,
                                            ];

                                            Log::info('Sending alert email with conditions:', ['conditions' => implode(", ", $alertMessages)]);
                                            $statusType = 'critical_condition';

                                            $this->sendAlertEmail($details, $tower->id, $statusType);
                                        }
                                        unset($_SESSION['sensor_data']);

                                    }

                                    Sensor::create([
                                        'towerid' => $tower->id,
                                        'pH' => $validatedData['phValue'],
                                        'temperature' => $validatedData['temp'],
                                        'nutrientlevel' => $validatedData['waterLevel'],
                                        'light' => $validatedData['light'],
                                        'k' => $validatedData['key'],
                                        'iv' => $validatedData['iv'],
                                        'status' => '1',
                                    ]);
                                } catch (Exception $e) {
                                    Log::error('Error while storing sensor data and sending email alert', ['exception' => $e->getMessage()]);
                                    return response()->json(['error' => 'Failed to process sensor data'], 500);
                                }
                            }

                            return response()->json(['status' => 'success', 'message' => 'Sensor data successfully stored']);
                        } else {

                            return response()->json(['errors' => 'IP or MAC addresses do not match'], 422);
                        }
                    } else {
                        $ipmac->ipAdd = Crypt::encryptString($decrypted_ip);
                        $ipmac->macAdd = Crypt::encryptString($decrypted_mac);
                        $ipmac->save();

                        Log::info('Updated Tower IP and MAC addresses:', ['id' => $tower->id]);
                        return response()->json(['status' => 'success'], 201);

                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Error processing request', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to process request'], 500);
        }
    }

    public function getdata($id, $column)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        try {

            $sensorData = Sensor::where('towerid', $id)
                ->orderBy('created_at', 'asc')
                ->get(['k', 'iv', $column, 'created_at']);

            $decryptedData = [];

            foreach ($sensorData as $sdata) {

                $decrypted_key = $this->decrypt_data($sdata->k, $method, $key_str, $iv_str);
                $decrypted_iv = $this->decrypt_data($sdata->iv, $method, $key_str, $iv_str);

                $decrypted_column = $this->decrypt_data($sdata->$column, $method, $decrypted_key, $decrypted_iv);

                $formattedTimestamp = $sdata->created_at->format('Y-m-d H:i:s');

                $decryptedData[] = [
                    'value' => (float) $decrypted_column,
                    'timestamp' => $formattedTimestamp,
                ];
            }

            return response()->json(['sensorData' => $decryptedData]);

        } catch (\Exception $e) {
            Log::error('Error fetching sensor data: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    private function getCondition($averageValue, $type)
    {
        $condition = '';

        switch ($type) {
            case 'pH':
                if ($averageValue < 5.5) {
                    $condition = 'Too acidic';
                } elseif ($averageValue < 6.0) {
                    $condition = 'Acidic';
                } elseif ($averageValue > 7.0) {
                    $condition = 'Too basic';
                } elseif ($averageValue > 6.5) {
                    $condition = 'Basic';
                } else {
                    $condition = 'Good';
                }
                break;

            case 'nutrient':
                if ($averageValue >= 25) {
                    $condition = 'Full';
                } elseif ($averageValue >= 17) {
                    $condition = '85%';
                } elseif ($averageValue >= 15) {
                    $condition = '75%';
                } elseif ($averageValue >= 12) {
                    $condition = '60%';
                } elseif ($averageValue >= 10) {
                    $condition = '50%';
                } elseif ($averageValue >= 7) {
                    $condition = '35%';
                } elseif ($averageValue >= 5) {
                    $condition = '25%';
                } elseif ($averageValue >= 2) {
                    $condition = '15%';
                } else {
                    $condition = 'Empty';
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

    private function sendAlertEmail($details, $towerId, $statusType)
    {
        if ($towerId) {
            $tower = Tower::find($towerId);
    
            if ($tower && $tower->OwnerID) {
                $owner = Owner::find($tower->OwnerID);
    
                if ($owner && $owner->email) {
                    $email = Crypt::decryptString($owner->email);
                    $decryptedTowerName = Crypt::decryptString($tower->name);
    
                    // Define cooldown period in minutes
                    $emailCooldown = 5; 
    
                    // Check last email sent timestamp based on status type
                    switch ($statusType) {
                        case 'sensor_error':
                            $lastEmailSentAt = $tower->last_sensor_error_email_sent_at;
                            break;
                        case 'critical_condition':
                            $lastEmailSentAt = $tower->last_critical_condition_email_sent_at;
                            break;
                        default:
                            $lastEmailSentAt = null;
                            break;
                    }
    
                    $now = Carbon::now();
    
                    // Convert lastEmailSentAt to Carbon instance if it's not null
                    if ($lastEmailSentAt) {
                        $lastEmailSentAt = Carbon::parse($lastEmailSentAt);
                    }
    
                    if ($lastEmailSentAt && $lastEmailSentAt->diffInMinutes($now) < $emailCooldown) {
                        $remainingTime = $emailCooldown - $lastEmailSentAt->diffInMinutes($now);
                        Log::info('Skipping email for ' . $statusType . ', cooldown remaining: ' . $remainingTime . ' minutes', ['tower_id' => $towerId]);
                        return;
                    }
    
                    $mailStatus = 'Not Sent';
    
                    try {
                        Mail::to($email)->send(new Alert($details));
                        $mailStatus = 'Sent';
                        Log::info('Alert email sent to', ['email' => $email, 'tower_id' => $towerId]);
                    } catch (\Exception $e) {
                        $mailStatus = 'Failed';
                        Log::error('Failed to send alert email', ['email' => $email, 'tower_id' => $towerId, 'error' => $e->getMessage()]);
                    } finally {
                        TowerLogs::create([
                            'ID_tower' => $towerId,
                            'activity' => Crypt::encryptString(
                                "Alert: Conditions detected - " . json_encode($details['body']) . " Mail Status: " . $mailStatus
                            ),
                        ]);
    
                        Log::info('Alert logged in tbl_towerlogs', ['tower_id' => $towerId, 'activity' => json_encode($details['body'])]);
    
                        // Update the last email sent timestamp based on status type
                        switch ($statusType) {
                            case 'sensor_error':
                                $tower->last_sensor_error_email_sent_at = $now;
                                break;
                            case 'critical_condition':
                                $tower->last_critical_condition_email_sent_at = $now;
                                break;
                        }
                        $tower->save();
                    }
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

}
