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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;


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

        $towerinfoid = null;
        $towerinfocode = null;

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
            Log::channel('custom')->info('Request received:', $request->all());

            $validatedData = $request->validate([
                'sensorData' => 'required',
                'Credentials' => 'required',
            ]);

            $keys = $this->decrypt_data($validatedData['Credentials'], $method, $key_str, $iv_str);
            $keyss = explode(',', $keys);
            $decrypted_ip = $keyss[0];
            $decrypted_mac = $keyss[1];
            $decrypted_towercode = $keyss[2];

            Log::channel('custom')->info('Validated data:', $validatedData);
            Log::channel('custom')->info('keys:', ['key' => $keys]);

            $data = $validatedData['sensorData'];

            Log::channel('custom')->info('Data:', ['data' => $data]);

            $eplo = explode(',', $data);

            $pH = $eplo[0];
            $temp = $eplo[1];
            $nut = $eplo[2];
            $light = $eplo[3];

            $towers = Tower::all(['id', 'name', 'towercode']);

            Log::channel('custom')->info('from tower:', [
                'phValue' => $pH,
                'temp' => $temp,
                'waterLevel' => $nut,
                'light' => $light,
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
                        Log::channel('custom')->info('Decrypted IP and MAC addresses:', ['ipAddress' => $ip, 'macAddress' => $mac]);

                        if ($ip == $decrypted_ip && $mac == $decrypted_mac) {
                            $statuss = Crypt::decryptString($ipmac->status);
                            $modee = Crypt::decryptString($ipmac->mode);
                            Log::channel('custom')->info('Successfully decc', [
                                'mode' => $modee,
                                'stat' => $statuss,
                            ]);

                            if ($statuss != '1') {
                                $encryptedMode = $this->encrypt_data($modee, $key_str, $iv_str, $method);
                                $encryptedStatus = $this->encrypt_data($statuss, $key_str, $iv_str, $method);
                                Log::channel('custom')->info('Successfully enc sensor data', [
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
                                    $alertMessages[] = "Nutrient Solution level is invalid or out of range (1-20)";
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
                                    Log::channel('custom')->info('Successfully enc sensor data', [
                                        'mode' => $encryptedMode,
                                        'stat' => $encryptedStatus,
                                    ]);

                                    return response()->json(['modestat' => ['mode' => $encryptedMode, 'status' => $encryptedStatus]]);

                                } else {
                                    $alertMessages = [];

                                    try {
                                        // Initialize trigger counts in the cache if they don't exist
                                        if (!Cache::has('triggerCounts')) {
                                            Cache::put('triggerCounts', [
                                                'ph' => 0,
                                                'temp' => 0,
                                                'nut' => 0,
                                            ]);
                                        }

                                        $triggerCounts = Cache::get('triggerCounts');

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
                                            'phCondition' => [
                                                'Extreme acidity', // for pH < 4.5
                                                'Very strong acidity', // for 4.5 ≤ pH < 5.0
                                                'Strong acidity', // for 5.0 ≤ pH < 5.5
                                                //'Medium acidity', // for 5.5 ≤ pH < 6.0
                                                //'Slight acidity', // for 6.0 ≤ pH < 6.5
                                                'Very slight acidity', // for 6.5 ≤ pH < 7.0
                                                'Neutral', // for pH = 7.0
                                                'Slight alkalinity', // for 7.0 < pH ≤ 7.5
                                                'Moderate alkalinity', // for 7.5 < pH ≤ 8.0
                                                'Strong alkalinity', // for 8.0 < pH ≤ 8.5
                                                'Very strong alkalinity', // for 8.5 < pH ≤ 9.5
                                                'Extremely strong alkalinity', // for pH > 9.5
                                            ],
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
                                            $triggeredConditions['nut'][] = "Nutrient Solution Volume: {$nut} - $volumeCondition";
                                        }

                                        // Save updated trigger counts back to the cache
                                        Cache::put('triggerCounts', $triggerCounts);

                                        Log::channel('custom')->info('Sensor data condition check:', [
                                            'pH' => $phCondition,
                                            'Temperature' => $tempCondition,
                                            'Nutrient Solution Volume' => $volumeCondition,
                                        ]);

                                        Log::channel('custom')->info('Trigger counts:', [
                                            'pH Triggers' => $triggerCounts['ph'],
                                            'Temperature Triggers' => $triggerCounts['temp'],
                                            'Nutrient Solution Volume Triggers' => $triggerCounts['nut'],
                                        ]);

                                        $alertMessages = [];
                                        $triggeredData = [];

                                        if ($triggerCounts['ph'] >= 5) {
                                            $triggeredData['ph'] = $pH;
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['ph']));
                                            $triggerCounts['ph'] = 0; // Reset pH trigger count to zero

                                            Cache::put('triggerCounts', $triggerCounts);
                                        }

                                        if ($triggerCounts['temp'] >= 5) {
                                            $triggeredData['temp'] = $temp;
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['temp']));
                                            $triggerCounts['temp'] = 0;

                                            Cache::put('triggerCounts', $triggerCounts);
                                        }

                                        if ($triggerCounts['nut'] >= 5) {
                                            $triggeredData['nutlevel'] = $nut;
                                            $alertMessages = array_merge($alertMessages, array_unique($triggeredConditions['nut']));
                                            $triggerCounts['nut'] = 0;
                                            Cache::put('triggerCounts', $triggerCounts);
                                        }

                                        if (!empty($alertMessages)) {
                                            $body = "The following conditions have been detected at Tower '" . Crypt::decryptString($ipmac->name) . "': ";
                                            $body .= implode(", ", $alertMessages);

                                            $details = [
                                                'title' => 'Alert: Conditions Detected',
                                                'body' => $body,
                                            ];

                                            $sensorDataJson = json_encode($triggeredData);

                                            Log::channel('custom')->info('Sending alert email with conditions:', ['conditions' => implode(", ", $alertMessages)]);
                                            $statusType = 'critical_condition';

                                            $this->sendAlertEmail($details, $tower->id, $statusType);
                                        }

                                        $sd = [
                                            'ph' => $pH,
                                            'temperature' => $temp,
                                            'nutrient_level' => $nut,
                                            'light' => $light,
                                        ];

                                        Log::channel('custom')->info('Broadcasting sensor data', ['sensorData' => $sd, 'towerId' => $tower->id]);

                                        event(new SensorDataUpdated($sd, $tower->id));

                                        Log::channel('custom')->info('Successfully broadcasted sensor data', [
                                            'sensorData' => $sd,
                                            'towerId' => $tower->id,
                                        ]);

                                        $encryptedMode = $this->encrypt_data($modee, $key_str, $iv_str, $method);
                                        $encryptedStatus = $this->encrypt_data($statuss, $key_str, $iv_str, $method);

                                        Log::channel('custom')->info('Successfully enc sensor data', [
                                            'mode' => $encryptedMode,
                                            'stat' => $encryptedStatus,
                                        ]);

                                        return response()->json(['modestat' => ['mode' => $encryptedMode, 'status' => $encryptedStatus, 'success' => 'success']]);

                                    } catch (\Exception $e) {
                                        Log::channel('custom')->error('Error storing data:', ['error' => $e->getMessage()]);

                                        $encryptedMode = $this->encrypt_data($modee, $key_str, $iv_str, $method);
                                        $encryptedStatus = $this->encrypt_data($statuss, $key_str, $iv_str, $method);
                                        Log::channel('custom')->info('Successfully enc sensor data', [
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

                        Log::channel('custom')->info('Updated Tower IP and MAC addresses:', ['id' => $tower->id]);
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
                'errorMessage' => $e->getMessage(),
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
                        Log::channel('custom')->info('Intrusion alert email sent to admin', ['adminEmail' => $adminEmail, 'failedAttempts' => $failedAttempts]);

                    } catch (\Exception $e) {
                        Log::channel('custom')->error('Failed to send intrusion alert email', ['error' => $e->getMessage(), 'adminEmail' => $adminEmail]);

                    } finally {
                        if (Cache::has($failedAttemptsKey)) {
                            Cache::forget($failedAttemptsKey);
                        }

                        if (Cache::has($towerinfoid)) {
                            Cache::forget($towerinfoid);
                        }

                        if (Cache::has($towerinfocode)) {
                            Cache::forget($towerinfocode);
                        }
                    }
                }
            }

        }
    }

//daily

    public function dailyData($towerId)
    {
        $data = Sensor::where('towerid', $towerId)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('AVG(JSON_EXTRACT(sensordata, "$.temp")) as avg_temp'),
                DB::raw('AVG(JSON_EXTRACT(sensordata, "$.ph")) as avg_ph'),
                DB::raw('AVG(JSON_EXTRACT(sensordata, "$.nut")) as avg_nut')
            )
            ->groupBy('date')
            ->get();

        return response()->json($data);
    }

//ph,temp,nut
    public function getdata($id, $column)
    {
        try {
            $filePath = "tower_data/tower_{$id}.json";

            if (Storage::exists($filePath)) {
                $cachedData = json_decode(Storage::get($filePath), true) ?: [];

                $decryptedData = [];
                foreach ($cachedData as $dataPoint) {
                    if (isset($dataPoint['data'][$column])) {
                        $decryptedData[] = [
                            'type' => $column,
                            'value' => (float) $dataPoint['data'][$column],
                            'timestamp' => \Carbon\Carbon::parse($dataPoint['timestamp'])->format('m-d H:i'),
                        ];
                    }
                }

            } else {
                $decryptedData = [];
            }
            if (empty($decryptedData)) {
                return response()->json(['message' => 'No data available for the specified column'], 404);
            }
            return response()->json(['sensorData' => $decryptedData]);

        } catch (\Exception $e) {
            Log::error('Error fetching sensor data from cache: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getLastData($id)
    {
        try {
            $filePath = "tower_data/tower_{$id}.json";

            if (Storage::exists($filePath)) {
                $cachedData = json_decode(Storage::get($filePath), true) ?: [];

                $lastDataPoint = !empty($cachedData) ? end($cachedData) : null;

                if ($lastDataPoint) {
                    return response()->json([
                        'sensorData' => [
                            'nutrient_level' => $lastDataPoint['data']['nutrient_level'] ?? null,
                            'ph' => $lastDataPoint['data']['ph'] ?? null,
                            'light' => $lastDataPoint['data']['light'] ?? null,
                            'temperature' => $lastDataPoint['data']['temperature'] ?? null,
                            'timestamp' => \Carbon\Carbon::parse($lastDataPoint['timestamp'])->toDateTimeString(),
                        ],
                    ]);
                }

                return response()->json(['message' => 'No data available'], 404);
            }

            return response()->json(['message' => 'No data file available for this tower'], 404);

        } catch (\Exception $e) {
            Log::channel('custom')->error('Error fetching last data from JSON file: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    private function getCondition($averageValue, $type)
    {
        $condition = '';

        switch ($type) {
            case 'pH':
                if ($averageValue < 4.5) {
                    $condition = 'Extreme acidity';
                } elseif ($averageValue >= 4.5 && $averageValue < 5.0) {
                    $condition = 'Very strong acidity';
                } elseif ($averageValue >= 5.0 && $averageValue < 5.5) {
                    $condition = 'Strong acidity';
                } elseif ($averageValue >= 5.5 && $averageValue < 6.0) {
                    $condition = 'Medium acidity';
                } elseif ($averageValue >= 6.0 && $averageValue < 6.5) {
                    $condition = 'Slight acidity';
                } elseif ($averageValue >= 6.5 && $averageValue < 7.0) {
                    $condition = 'Very slight acidity';
                } elseif ($averageValue == 7.0) {
                    $condition = 'Neutral';
                } elseif ($averageValue > 7.0 && $averageValue <= 7.5) {
                    $condition = 'Slight alkalinity';
                } elseif ($averageValue > 7.5 && $averageValue <= 8.0) {
                    $condition = 'Moderate alkalinity';
                } elseif ($averageValue > 8.0 && $averageValue <= 8.5) {
                    $condition = 'Strong alkalinity';
                } elseif ($averageValue > 8.5 && $averageValue <= 9.5) {
                    $condition = 'Very strong alkalinity';
                } elseif ($averageValue > 9.5) {
                    $condition = 'Extremely strong alkalinity';
                } else {
                    $condition = 'Unknown';
                }

                break;

            case 'nutrient':
                if ($averageValue > 5) {
                    $condition = 'Good';
                } elseif ($averageValue >= 5) {
                    $condition = '25%';
                } elseif ($averageValue >= 2) {
                    $condition = '15%';
                } else {
                    $condition = 'critical low';
                }

                break;

            case 'temp':
                if ($averageValue < 25) {
                    $condition = 'Cold';
                } elseif ($averageValue > 25 && $averageValue <= 30) {
                    $condition = 'Good';
                } elseif ($averageValue > 30) {
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
