<?php

namespace App\Http\Controllers;

use App\Mail\Alert;
use App\Models\IntrusionDetection;
use App\Models\Pump;
use App\Models\Tower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{

    public function pump(Request $request)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        $failedAttemptsKey = 'intrusion_failed_attempts_pump' . $request->ip();
        $failedAttempts = Cache::get($failedAttemptsKey, 0);
        $attemptThreshold = 5;
        $adminEmail = 'hydrosec1@gmail.com';

        Log::channel('custom')->info('Pump request received', [
            'input' => $request->all(),
        ]);

        try {

            $validatedData = $request->validate([
                'Pumped' => 'required',
            ]);

            Cache::forget($failedAttemptsKey);

            $pumpi = $this->decrypt_data($validatedData['Pumped'], $method, $key_str, $iv_str);
            $pumi = explode(',', $pumpi);
            $decryptedPump = $pumi[0];
            $decryptedIpAddress = $pumi[1];
            $decryptedMacAddress = $pumi[2];
            $decryptedTowercode = $pumi[3];

            Log::channel('custom')->info('Decrypted data', [
                'decryptedPump' => $decryptedPump,
                'decryptedIpAddress' => $decryptedIpAddress,
                'decryptedMacAddress' => $decryptedMacAddress,
                'decryptedTowercode' => $decryptedTowercode,
            ]);

            $towerData = Tower::all();

            foreach ($towerData as $tower) {
                $towercode = Crypt::decryptString($tower->towercode);

                Log::channel('custom')->info('Checking tower code', [
                    'currentTowerCode' => $towercode,
                    'decryptedTowercode' => $decryptedTowercode,
                ]);

                if ($towercode == $decryptedTowercode) {
                    $ipmac = Tower::where('id', $tower->id)->first();

                    if ($ipmac) {
                        $ip = Crypt::decryptString($ipmac->ipAdd);
                        $mac = Crypt::decryptString($ipmac->macAdd);

                        Log::channel('custom')->info('Decrypted IP and MAC addresses from DB', [
                            'ipAddress' => $ip,
                            'macAddress' => $mac,
                        ]);

                        if ($ip == $decryptedIpAddress && $mac == $decryptedMacAddress) {
                            session(['tower_id' => $ipmac->id]);

                            session()->push('pump_data', [
                                'pump' => $decryptedPump,
                                'towercode' => $decryptedTowercode,
                            ]);

                            $pump = $this->encrypt_data($decryptedPump, $method, $key_str, $iv_str);

                            Pump::create([
                                'towerid' => $tower->id,
                                'status' => $pump,
                            ]);

                            Log::channel('custom')->info('Pump data processed successfully', [
                                'towerId' => $ipmac->id,
                                'pump' => $decryptedPump,
                                'towercode' => $decryptedTowercode,
                            ]);

                            return response()->json(['success' => 'Data processed successfully'], 200);
                        } else {
                            Log::channel('custom')->warning('IP or MAC address mismatch', [
                                'expectedIp' => $decryptedIpAddress,
                                'actualIp' => $ip,
                                'expectedMac' => $decryptedMacAddress,
                                'actualMac' => $mac,
                            ]);

                            // Increment failed attempts
                            $failedAttempts++;
                            Cache::put($failedAttemptsKey, $failedAttempts, 3600); // Store attempts for 1 hour

                            return response()->json(['error' => 'IP or MAC address mismatch'], 400);
                        }
                    } else {
                        Log::channel('custom')->warning('No matching IP/MAC data found for tower', [
                            'towerId' => $tower->id,
                        ]);
                    }
                }
            }

            Log::channel('custom')->warning('Tower code not found', [
                'decryptedTowercode' => $decryptedTowercode,
            ]);

            return response()->json(['error' => 'Tower code not found'], 404);

        } catch (ValidationException $e) {
            // Increment failed attempts on validation failure
            $failedAttempts++;
            Cache::put($failedAttemptsKey, $failedAttempts, 3600); // Store attempts for 1 hour
            Log::channel('custom')->warning('Validation failed', [
                'ipAddress' => $request->ip(),
                'failedAttempts' => $failedAttempts,
                'errors' => $e->errors(),
            ]);

            return response()->json(['error' => 'Invalid data provided', 'details' => $e->errors()], 422);

        } catch (\Exception $e) {
            Log::channel('custom')->error('Error processing request', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while processing the request'], 500);
        } finally {
            if ($failedAttempts >= $attemptThreshold) {
                Log::alert('Intrusion detection: multiple failed attempts detected from IP ' . $request->ip(), [
                    'failedAttempts' => $failedAttempts,
                    'ipAddress' => $request->ip(),
                    'device' => $request->header('User-Agent'),
                ]);

                IntrusionDetection::create([
                    'ip_address' => Crypt::encryptString($request->ip()),
                    'user_agent' => Crypt::encryptString($request->header('User-Agent')),
                    'failed_attempts' => Crypt::encryptString(' Pump Status'),
                ]);

                $details = [
                    'title' => 'Intrusion Alert: Multiple Failed Attempts',
                    'body' => 'There have been attempts on retrieving Pump Status from IP ' . $request->ip() . ', Device: ' . $request->header('User-Agent'),
                ];

                try {
                    Mail::to($adminEmail)->send(new Alert($details));
                    Log::channel('custom')->info('Intrusion alert email sent to admin', ['adminEmail' => $adminEmail, 'failedAttempts' => $failedAttempts]);

                } catch (\Exception $e) {
                    Log::channel('custom')->error('Failed to send intrusion alert email', ['error' => $e->getMessage(), 'adminEmail' => $adminEmail]);

                } finally {
                    Cache::forget($failedAttemptsKey);
                }
            }
        }
    }

    public function getmode(Request $request)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";
        $failedAttemptsKey = 'intrusion_failed_attempts_mode' . $request->ip();
        $failedAttempts = Cache::get($failedAttemptsKey, 0);
        $attemptThreshold = 5;
        $adminEmail = "hydrosec1@gmail.com";
        Log::channel('custom')->info('Mode request received', ['input' => $request->all()]);

        try {

            $validatedData = $request->validate(['Credentials' => 'required']);
            $cred = $this->decrypt_data($validatedData['Credentials'], $method, $key_str, $iv_str);
            $info = explode(',', $cred);
            $decryptedIpAddress = $info[0];
            $decryptedMacAddress = $info[1];
            $decryptedTowercode = $info[2];

            Log::channel('custom')->info('Decrypted request data', [
                'decryptedIpAddress' => $decryptedIpAddress,
                'decryptedMacAddress' => $decryptedMacAddress,
                'decryptedTowercode' => $decryptedTowercode,
            ]);

            $towerData = Tower::all();
            foreach ($towerData as $tower) {
                $towercode = Crypt::decryptString($tower->towercode);
                $mode = Crypt::decryptString($tower->mode);
                $status = Crypt::decryptString($tower->status);

                if ($towercode == $decryptedTowercode) {
                    $ipmac = Tower::where('id', $tower->id)->first();

                    if ($ipmac && !is_null($ipmac->ipAdd)) {
                        $ip = Crypt::decryptString($ipmac->ipAdd);
                        $mac = Crypt::decryptString($tower->macAdd);

                        if ($ip == $decryptedIpAddress && $mac == $decryptedMacAddress) {
                            Cache::forget($failedAttemptsKey);

                            $encryptedMode = $this->encrypt_data($mode, $method, $key_str, $iv_str);
                            $encryptedStatus = $this->encrypt_data($status, $method, $key_str, $iv_str);

                            return response()->json(['modestat' => ['mode' => $encryptedMode, 'status' => $encryptedStatus]]);
                            Log::channel('custom')->info('Response', [
                                'mode' => $encryptedMode,
                                'status' => $encryptedStatus,
                            ]);

                        } else {

                            $failedAttempts++;
                            Cache::put($failedAttemptsKey, $failedAttempts, 3600); // Store attempts for 1 hour
                            Log::channel('custom')->warning('IP or MAC address mismatch', [
                                'expectedIp' => $decryptedIpAddress,
                                'actualIp' => $ip,
                                'expectedMac' => $decryptedMacAddress,
                                'actualMac' => $mac,
                            ]);

                            return response()->json(['error' => 'IP or MAC address mismatch'], 400);
                        }
                    } else {

                        $ipmac->ipAdd = Crypt::encryptString($decryptedIpAddress);
                        $ipmac->macAdd = Crypt::encryptString($decryptedMacAddress);
                        $ipmac->save();

                        Log::channel('custom')->info('Updated Tower IP and MAC addresses:', ['id' => $tower->id]);
                        return response()->json(['success' => 'Tower IP and MAC updated'], 201);
                    }
                } else {

                    $failedAttempts++;
                    Cache::put($failedAttemptsKey, $failedAttempts, 3600); // Store attempts for 1 hour
                    return response()->json(['error' => 'Invalid tower code'], 400);
                }
            }

            Log::channel('custom')->warning('Tower code not found', ['decryptedTowercode' => $decryptedTowercode]);
            return response()->json(['error' => 'Tower code not found'], 404);

        } catch (ValidationException $e) {

            $failedAttempts++;
            Cache::put($failedAttemptsKey, $failedAttempts, 3600);
            Log::channel('custom')->warning('Validation failed', [
                'ipAddress' => $request->ip(),
                'failedAttempts' => $failedAttempts,
                'errors' => $e->errors(),
            ]);

            return response()->json(['error' => 'Invalid data provided', 'details' => $e->errors()], 422);

        } catch (\Exception $e) {

            Log::channel('custom')->error('Error processing mode request', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while processing the request'], 500);
        } finally {
            if ($failedAttempts >= $attemptThreshold) {
                Log::alert('Intrusion detection: multiple failed attempts detected from IP ' . $request->ip(), [
                    'failedAttempts' => $failedAttempts,
                    'ipAddress' => $request->ip(),
                    'device' => $request->header('User-Agent'),
                ]);

                IntrusionDetection::create([
                    'ip_address' => Crypt::encryptString($request->ip()),
                    'user_agent' => Crypt::encryptString($request->header('User-Agent')),
                    'failed_attempts' => Crypt::encryptString(' on Mode/Status'),
                ]);

                $details = [
                    'title' => 'Intrusion Alert: Multiple Failed Attempts',
                    'body' => 'There have been  attempts on retrieving Mode and Status from IP ' . $request->ip() . ', Device: ' . $request->header('User-Agent'),
                ];

                try {
                    Mail::to($adminEmail)->send(new Alert($details));
                    Log::channel('custom')->info('Intrusion alert email sent to admin', ['adminEmail' => $adminEmail, 'failedAttempts' => $failedAttempts]);

                } catch (\Exception $e) {
                    Log::channel('custom')->error('Failed to send intrusion alert email', ['error' => $e->getMessage(), 'adminEmail' => $adminEmail]);

                } finally {
                    Cache::forget($failedAttemptsKey);
                }
            }
        }
    }

    private function encrypt_data($data, $method, $key, $iv)
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
            Log::channel('custom')->error('Encryption error: ' . $e->getMessage());
            return null;
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
            Log::channel('custom')->error('Decryption error: ' . $e->getMessage());
            return null;
        }
    }
}
