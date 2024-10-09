<?php

namespace App\Http\Controllers;

use App\Events\SensorDataUpdated;
use App\Mail\Alert;
use App\Models\IntrusionDetection;
use App\Models\Owner;
use App\Models\Pump;
use App\Models\Sensor;
use App\Models\Tower;
use App\Models\Towerlog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SensorData extends Controller
{

    public function getPump($id)
    {
        try {

            $key_str = "ISUHydroSec2024!";
            $iv_str = "HydroVertical143";
            $method = "AES-128-CBC";

            $sensorData = Pump::where('towerid', $id)
                ->orderBy('created_at', 'desc')
                ->get();

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
        //\Log::info("No pump data found for tower ID: {$id}");
            }

            // \Log::info('Pumping events processed successfully');
            return response()->json($events);

        } catch (\Exception $e) {
            // \Log::error('Failed to fetch pump data for tower ID: ' . $id . '. Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch pump data'], 500);
        }
    }

    public function storedata(Request $request)
    {
        $alertMessages = [];
        $failedAttemptsKey = 'intrusion_failed_attempts_sensor' . $request->ip();
        $failedAttempts = Cache::get($failedAttemptsKey, 0);
        $attemptThreshold = 5;
        $adminEmail = 'hydrosec1@gmail.com';

        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        $response = [
            'status' => '',
            'phCondition' => '',
            'tempCondition' => '',
            'volumeCondition' => '',
        ];

        if (!session()->has('sensor_data')) {
            session(['sensor_data' => []]);
        }
        if (!session()->has('sensor_data_count')) {
            session(['sensor_data_count' => []]);
        }

        try {
            Log::info('Request received:', $request->all());

            $validatedData = $request->validate([
                'sensorData' => 'required',
                'Credentials' => 'required',
            ]);

            $keys = $this->decrypt_data($validatedData['Credentials'], $method, $key_str, $iv_str);
            $keyss = explode(',', $keys);
            $decrypted_ip = $keyss[0];
            $decrypted_mac = $keyss[1];
            $decrypted_towercode = $keyss[2];

            Log::info('Validated data:', $validatedData);
            Log::info('keys:', ['key' => $keys]);

            $data = $validatedData['sensorData'];

            Log::info('Data:', ['data' => $data]);

            $eplo = explode(',', $data);

            $pH = $eplo[0];
            $temp = $eplo[1];
            $nut = $eplo[2];
            $decrypted_light = $eplo[3];

            $towers = Tower::all(['id', 'name', 'towercode']);

            Log::info('from tower:', [
                'phValue' => $pH,
                'temp' => $temp,
                'waterLevel' => $nut,
                'light' => $decrypted_light,
                'ipAddress' => $decrypted_ip,
                'macAddress' => $decrypted_mac,
                'towercode' => $decrypted_towercode,
            ]);

            foreach ($towers as $tower) {
                $towercode = Crypt::decryptString($tower->towercode);

                if ($towercode == $decrypted_towercode) {
                    $ipmac = Tower::where('id', $tower->id)->first();
                    $towerinfocode = Crypt::decryptString($ipmac->towercode);

                    Cache::put($towerinfoid, $towerinfocode, 3600);

                    if ($ipmac && !is_null($ipmac->ipAdd)) {
                        $ip = Crypt::decryptString($ipmac->ipAdd);
                        $mac = Crypt::decryptString($ipmac->macAdd);
                        Log::info('Decrypted IP and MAC addresses:', ['ipAddress' => $ip, 'macAddress' => $mac]);

                        if ($ip == $decrypted_ip && $mac == $decrypted_mac) {
                            $statuss = Crypt::decryptString($ipmac->status);
                            $modee = Crypt::decryptString($ipmac->mode);
                            Log::info('Successfully decc', [
                                'mode' => $modee,
                                'stat' => $statuss,
                            ]);

                            if ($statuss != '1') {
                                $encryptedMode = $this->encrypt_data($modee, $key_str, $iv_str, $method);
                                $encryptedStatus = $this->encrypt_data($statuss, $key_str, $iv_str, $method);
                                Log::info('Successfully enc sensor data', [
                                    'mode' => $encryptedMode,
                                    'stat' => $encryptedStatus,
                                ]);

                                return response()->json(['modestat' => ['mode' => $encryptedMode, 'status' => $encryptedStatus]]);

                            } else {

                                if ($pH < 1 || $pH > 14 || is_nan($pH)) {
                                    $alertMessages[] = "pH value is invalid or out of range (1-14)";
                                }

                                if ($temp < -16 || $temp > 60 || is_nan($temp)) {
                                    $alertMessages[] = "Temperature value is invalid or out of range (-16°C to 60°C)";
                                }
                                if ($nut < 0 || $nut > 23 || is_nan($nut)) {
                                    $alertMessages[] = "Nutrient level is invalid or out of range (1-20)";
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

                                    $encryptedMode = $this->encrypt_data($modee, $key_str, $iv_str, $method);
                                    $encryptedStatus = $this->encrypt_data($statuss, $key_str, $iv_str, $method);
                                    Log::info('Successfully enc sensor data', [
                                        'mode' => $encryptedMode,
                                        'stat' => $encryptedStatus,
                                    ]);

                                    return response()->json(['modestat' => ['mode' => $encryptedMode, 'status' => $encryptedStatus]]);

                                    //return response()->json(['errors' => $alertMessages], 422);
                                } else {
                                    $alertMessages = [];

                                    try {
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

                                        $phCondition = $this->getCondition((float) $pH, 'pH');
                                        $tempCondition = $this->getCondition((float) $temp, 'temp');
                                        $volumeCondition = $this->getCondition((float) $nut, 'nutrient');

                                        $triggerConditions = [
                                            'phCondition' => ['Acidic', 'Alkaline'],
                                            'volumeCondition' => ['25%', '15%', 'critical low'],
                                            'tempCondition' => ['Cold', 'Hot'],
                                        ];

                                        if (in_array($phCondition, $triggerConditions['phCondition'])) {
                                            $triggerCounts['ph']++;
                                            $triggeredConditions['ph'][] = "pH: {$pH} - $phCondition";
                                        }

                                        if (in_array($tempCondition, $triggerConditions['tempCondition'])) {
                                            $triggerCounts['temp']++;
                                            $triggeredConditions['temp'][] = "Temperature: {$temp} - $tempCondition";
                                        }

                                        if (in_array($volumeCondition, $triggerConditions['volumeCondition'])) {
                                            $triggerCounts['nut']++;
                                            $triggeredConditions['nut'][] = "Nutrient Volume: {$nut} - $volumeCondition";
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

                                        $alertMessages = [];
                                        $triggeredData = [];

                                        if ($triggerCounts['ph']) {
                                            $triggeredData['ph'] = $pH;
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['ph']));
                                        }
                                        if ($triggerCounts['temp']) {
                                            $triggeredData['temp'] = $temp;
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['temp']));
                                        }
                                        if ($triggerCounts['nut']) {
                                            $triggeredData['nutlevel'] = $nut;
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['nut']));
                                        }

                                        if (!empty($alertMessages)) {
                                            $body = "The following conditions have been detected at Tower '" . Crypt::decryptString($ipmac->name) . "': ";
                                            $body .= implode(", ", $alertMessages);

                                            $details = [
                                                'title' => 'Alert: Conditions Detected',
                                                'body' => $body,
                                            ];

                                            // Store JSON data of triggered conditions only
                                            $sensorDataJson = json_encode($triggeredData);

                                            Sensor::create([
                                                'towercode' => $decrypted_towercode,
                                                'sensordata' => $sensorDataJson,
                                                'status' => '1',
                                            ]);

                                            Log::info('Sending alert email with conditions:', ['conditions' => implode(", ", $alertMessages)]);
                                            $statusType = 'critical_condition';

                                            $this->sendAlertEmail($details, $tower->id, $statusType);
                                        }

                                        $sd = [
                                            'ph' => $pH,
                                            'temperature' => $temp,
                                            'nutrient_level' => $nut,
                                            'light' => $decrypted_light,
                                        ];

                                        Log::info('Broadcasting sensor data', ['sensorData' => $sd, 'towerId' => $tower->id]);

                                        event(new SensorDataUpdated($sd, $tower->id));

                                        Log::info('Successfully broadcasted sensor data', [
                                            'sensorData' => $sd,
                                            'towerId' => $tower->id,
                                        ]);

                                        $encryptedMode = $this->encrypt_data($modee, $key_str, $iv_str, $method);
                                        $encryptedStatus = $this->encrypt_data($statuss, $key_str, $iv_str, $method);

                                        Log::info('Successfully enc sensor data', [
                                            'mode' => $encryptedMode,
                                            'stat' => $encryptedStatus,
                                        ]);

                                        return response()->json(['modestat' => ['mode' => $encryptedMode, 'status' => $encryptedStatus, 'success' => 'success']]);

                                    } catch (\Exception $e) {
                                        Log::error('Error storing data:', ['error' => $e->getMessage()]);

                                        $encryptedMode = $this->encrypt_data($modee, $key_str, $iv_str, $method);
                                        $encryptedStatus = $this->encrypt_data($statuss, $key_str, $iv_str, $method);
                                        Log::info('Successfully enc sensor data', [
                                            'mode' => $encryptedMode,
                                            'stat' => $encryptedStatus,
                                        ]);

                                        return response()->json(['modestat' => ['mode' => $encryptedMode, 'status' => $encryptedStatus]]);
                                    }
                                }
                            }
                        } else {

                            Log::warning('IP or MAC address mismatch', [
                                'expectedIp' => $decrypted_ip,
                                'actualIp' => $ip,
                                'expectedMac' => $decrypted_mac,
                                'actualMac' => $mac,
                            ]);

                            $failedAttempts = Cache::get($failedAttemptsKey, 0);
                            $failedAttempts++;
                            Cache::put($failedAttemptsKey, $failedAttempts, 3600);
                            return response()->json(['error' => 'IP or MAC address mismatch'], 400);
                        }

                    } else {

                        $ipmac->ipAdd = Crypt::encryptString($decrypted_ip);
                        $ipmac->macAdd = Crypt::encryptString($decrypted_mac);
                        $ipmac->save();

                        Log::info('Updated Tower IP and MAC addresses:', ['id' => $tower->id]);
                        return response()->json(['success' => 'Tower IP and MAC updated'], 201);

                    }
                }
                return response()->json(['error' => 'error'], 404);
            }
        } catch (ValidationException $e) {

            $failedAttempts = Cache::get($failedAttemptsKey, 0);
            $failedAttempts++;
            Cache::put($failedAttemptsKey, $failedAttempts, 3600);
            Log::warning('Validation failed', [
                'ipAddress' => $request->ip(),
                'failedAttempts' => $failedAttempts,
                'errors' => $e->errors(),
            ]);

            // return response()->json(['error' => 'Invalid data provided', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {

            $failedAttempts = Cache::get($failedAttemptsKey, 0);
            $failedAttempts++;
            Cache::put($failedAttemptsKey, $failedAttempts, 3600);
            Log::warning('Validation failed', [
                'ipAddress' => $request->ip(),
                'failedAttempts' => $failedAttempts,
                // 'errors' => $e->errors(),
            ]);

        } finally {
            if ($failedAttempts) {
                if ($failedAttempts >= $attemptThreshold) {
                    Log::alert('Intrusion detection: multiple failed attempts detected from IP ' . $request->ip(), [
                        'failedAttempts' => $failedAttempts,
                        'ipAddress' => $request->ip(),
                        'device' => $request->header('User-Agent'),
                    ]);

                    $towerinfoidString = isset($towerinfoid) ? ' on tower ' . $towerinfoid : '';
                    $towerinfocodeString = isset($towerinfocode) ? ' (' . $towerinfocode . ')' : ' on Sensors';

                    $failedAttemptsString = $towerinfoidString . $towerinfocodeString;
                    $encryptedFailedAttempts = Crypt::encryptString($failedAttemptsString);

                    IntrusionDetection::create([
                        'ip_address' => Crypt::encryptString($request->ip()),
                        'user_agent' => Crypt::encryptString($request->header('User-Agent')),
                        'failed_attempts' => $encryptedFailedAttempts,
                    ]);

                    $details = [
                        'title' => 'Intrusion Alert: Multiple Failed Attempts',
                        'body' => 'There have been ' . $failedAttempts . ' attempts on System sensor data sending from ' . $request->ip() . ', Device: ' . $request->header('User-Agent'),
                    ];

                    try {
                        Mail::to($adminEmail)->send(new Alert($details));
                        Log::info('Intrusion alert email sent to admin', ['adminEmail' => $adminEmail, 'failedAttempts' => $failedAttempts]);

                    } catch (\Exception $e) {
                        Log::error('Failed to send intrusion alert email', ['error' => $e->getMessage(), 'adminEmail' => $adminEmail]);

                    } finally {
                        Cache::forget($failedAttemptsKey);
                        // Cache::forget($towerinfoid);
                        Cache::forget($towerinfocode);
                    }
                }
            }

        }
    }

//ph,temp,nut
    public function getdata($id, $column)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        try {
            $sensorData = Sensor::where('towerid', $id)
                ->orderBy('created_at', 'asc')
                ->get(['sensordata', 'created_at']);

            $decryptedData = [];

            foreach ($sensorData as $sdata) {
                $decodedData = json_decode($sdata->sensordata, true);

                $formattedTimestamp = $sdata->created_at->format('Y-m-d H:i:s');

                if (isset($decodedData['ph'])) {
                    $decryptedData[] = [
                        'type' => 'pH',
                        'value' => (float) $decodedData['ph'],
                        'timestamp' => $formattedTimestamp,
                    ];
                }

                if (isset($decodedData['temp'])) {
                    $decryptedData[] = [
                        'type' => 'temperature',
                        'value' => (float) $decodedData['temp'],
                        'timestamp' => $formattedTimestamp,
                    ];
                }

                if (isset($decodedData['nutlevel'])) {
                    $decryptedData[] = [
                        'type' => 'nutrient level',
                        'value' => (float) $decodedData['nutlevel'],
                        'timestamp' => $formattedTimestamp,
                    ];
                }

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
                if ($averageValue < 5.6) {
                    $condition = 'Acidic';
                } elseif ($averageValue >= 5.6 && $averageValue < 7.0) {
                    $condition = 'Good';
                } elseif ($averageValue == 7.0) {
                    $condition = 'Neutral';
                } elseif ($averageValue > 7.0) {
                    $condition = 'Alkaline';
                } else {
                    $condition = 'Unknown';
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
                    $condition = 'critical low';
                }

                break;

            case 'temp':
                if ($averageValue < 20) {
                    $condition = 'Cold';
                } elseif ($averageValue >= 20 && $averageValue <= 25) {
                    $condition = 'Mild';
                } elseif ($averageValue > 25 && $averageValue <= 30) {
                    $condition = 'Good';
                } elseif ($averageValue > 30 && $averageValue <= 40) {
                    $condition = 'Warm';
                } elseif ($averageValue > 40) {
                    $condition = 'Hot';
                } else {
                    $condition = 'Unknown';
                }
                break;

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
                    $emailCooldown = 1;

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

                        Towerlog::create([
                            'ID_tower' => $towerId,
                            'activity' => Crypt::encryptString(
                                "Alert: Conditions detected - " . json_encode($details['body']) . " Mail Status: " . $mailStatus
                            ),
                        ]);

                        Log::info('Alert logged in tbl_Towerlog', ['tower_id' => $towerId, 'activity' => json_encode($details['body'])]);

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

    private function encrypt_data($data, $key, $iv, $method)
    {

        try {
            $data = base64_encode($data);
            $str_padded = $data;
            $pad = 16 - strlen($str_padded) % 16;
            if (strlen($str_padded) % 16) {
                $str_padded = str_pad($str_padded, strlen($str_padded) + $pad, "\0");
            }

            $result = openssl_encrypt($str_padded, $method, $key, OPENSSL_NO_PADDING, $iv);
            $result = base64_encode($result);

            return $result;
        } catch (\Exception $e) {
            Log::error('Encryption error: ' . $e->getMessage());
            return null;
        }
    }

}