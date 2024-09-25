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
use Livewire\Livewire;


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
                    ->select('pH', 'temperature', 'nutrientlevel', 'status', 'light') // Specify required columns
                    ->first();

                if ($sdata) {

                    $ph = $this->decrypt_data($sdata->pH, $method, $key_str, $iv_str);
                    $temp = $this->decrypt_data($sdata->temperature, $method, $key_str, $iv_str);
                    $volume = $this->decrypt_data($sdata->nutrientlevel, $method, $key_str, $iv_str);
                    $status = $this->decrypt_data($sdata->status, $method, $key_str, $iv_str);
                    $light = $this->decrypt_data($sdata->light, $method, $key_str, $iv_str);

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

            $data = $this->decrypt_data($validatedData['sensorData'], $method, $key_str, $iv_str);

            Log::info('Data:', ['data' => $data]);

            $eplo = explode(',', $data);

            $decrypted_ph = $eplo[0];
            $decrypted_temp = $eplo[1];
            $decrypted_nutrient = $eplo[2];
            $decrypted_light = $eplo[3];

            $towers = Tower::all(['id', 'name', 'towercode']);

            Log::info('from tower:', [
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
                    $towerinfoid = $ipmac->id;
                    $towerinfocode = Crypt::decryptString($ipmac->towercode);

                    Cache::put($towerinfoid, $towerinfocode, 3600);

                    if ($ipmac && !is_null($ipmac->ipAdd)) {
                        $ip = Crypt::decryptString($ipmac->ipAdd);
                        $mac = Crypt::decryptString($ipmac->macAdd);
                        Log::info('Decrypted IP and MAC addresses:', ['ipAddress' => $ip, 'macAddress' => $mac]);

                        if ($ip == $decrypted_ip && $mac == $decrypted_mac) {

                            if (Crypt::decryptString($ipmac->status) != '1') {

                                $sd = [
                                    'ph' => $decrypted_ph,
                                    'temperature' => $decrypted_temp,
                                    'nutrient_level' => $decrypted_nutrient,
                                    'light' => $decrypted_light,
                                ];

                                Log::info('Broadcasting sensor data', ['sensorData' => $sd, 'towerId' => $tower->id]);

                                // event(new SensorDataUpdated($sd, $tower->id));
                                Livewire::emit('sensorDataBeforeSave', $sd, $tower->id);


                            } else {

                                if ($decrypted_ph < 1 || $decrypted_ph > 14 || is_nan($decrypted_ph)) {
                                    $alertMessages[] = "pH value is invalid or out of range (1-14)";
                                }

                                if ($decrypted_temp < -16 || $decrypted_temp > 60 || is_nan($decrypted_temp)) {
                                    $alertMessages[] = "Temperature value is invalid or out of range (-16°C to 60°C)";
                                }
                                if ($decrypted_nutrient < 0 || $decrypted_nutrient > 23 || is_nan($decrypted_nutrient)) {
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

                                    return response()->json(['errors' => $alertMessages], 422);
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

                                        $phCondition = $this->getCondition((float) $decrypted_ph, 'pH');
                                        $tempCondition = $this->getCondition((float) $decrypted_temp, 'temp');
                                        $volumeCondition = $this->getCondition((float) $decrypted_nutrient, 'nutrient');

                                        $triggerConditions = [
                                            'phCondition' => ['Too acidic', 'Too basic', 'basic', 'acidic'],
                                            'volumeCondition' => ['25%', '15%', ' critical low'],
                                            'tempCondition' => ['Too Hot', 'hot'],
                                        ];

                                        if (in_array($phCondition, $triggerConditions['phCondition'])) {
                                            $triggerCounts['ph']++;
                                            $triggeredConditions['ph'][] = "pH: {$decrypted_ph} - $phCondition";
                                        }

                                        if (in_array($tempCondition, $triggerConditions['tempCondition'])) {
                                            $triggerCounts['temp']++;
                                            $triggeredConditions['temp'][] = "Temperature: {$decrypted_temp} - $tempCondition";
                                        }

                                        if (in_array($volumeCondition, $triggerConditions['volumeCondition'])) {
                                            $triggerCounts['nut']++;
                                            $triggeredConditions['nut'][] = "Nutrient Volume: {$decrypted_nutrient} - $volumeCondition";
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

                                        if ($triggerCounts['ph']) {
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['ph']));
                                        }

                                        if ($triggerCounts['temp']) {
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['temp']));
                                        }

                                        if ($triggerCounts['nut']) {
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

                                        $enph = $this->encrypt_data($decrypted_ph, $key_str, $iv_str, $method);
                                        $entemp = $this->encrypt_data($decrypted_temp, $key_str, $iv_str, $method);
                                        $ennut = $this->encrypt_data($decrypted_nutrient, $key_str, $iv_str, $method);
                                        $enlight = $this->encrypt_data($decrypted_light, $key_str, $iv_str, $method);

                                        $sd = [
                                            'ph' => $decrypted_ph,
                                            'temperature' => $decrypted_temp,
                                            'nutrient_level' => $decrypted_nutrient,
                                            'light' => $decrypted_light,
                                        ];

                                        Log::info('Broadcasting sensor data', ['sensorData' => $sd, 'towerId' => $tower->id]);

                                        event(new SensorDataUpdated($sd, $tower->id));

                                        Sensor::create([
                                            'towerid' => $tower->id,
                                            'pH' => $enph,
                                            'temperature' => $entemp,
                                            'nutrientlevel' => $ennut,
                                            'light' => $enlight,
                                            'status' => '1',
                                        ]);

                                        return response()->json([
                                            'status' => 'success',
                                            'message' => 'Data stored successfully.',
                                        ]);
                                    } catch (\Exception $e) {
                                        Log::error('Error storing data:', ['error' => $e->getMessage()]);

                                        return response()->json(['error' => 'Error storing data.'], 500);
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
                        $ipmac->ipAdd = Crypt::encryptString($decryptedIpAddress);
                        $ipmac->macAdd = Crypt::encryptString($decryptedMacAddress);
                        $ipmac->save();

                        Log::info('Updated Tower IP and MAC addresses:', ['id' => $tower->id]);
                        return response()->json(['success' => 'Tower IP and MAC updated'], 201);

                    }
                }
                return response()->json(['error' => 'Tower not found.'], 404);
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

            return response()->json(['error' => 'Invalid data provided', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {

            $failedAttempts = Cache::get($failedAttemptsKey, 0);
            $failedAttempts++;
            Cache::put($failedAttemptsKey, $failedAttempts, 3600);
            Log::warning('Validation failed', [
                'ipAddress' => $request->ip(),
                'failedAttempts' => $failedAttempts,
                'errors' => $e->errors(),
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
                        Cache::forget($towerinfoid);
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
                ->get([ $column, 'created_at']);

            $decryptedData = [];

            foreach ($sensorData as $sdata) {

                $decrypted_column = $this->decrypt_data($sdata->$column, $method, $key_str, $iv_str);
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
                    $condition = 'critical low';
                }

                break;

            case 'temp':
                if ($averageValue <= 18) {
                    $condition = 'too Cold';
                } elseif ($averageValue > 18 && $averageValue <= 25) {
                    $condition = 'cold';
                } elseif ($averageValue > 25 && $averageValue <= 30) {
                    $condition = 'Good';
                } else {
                    $condition = 'Hot';
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
