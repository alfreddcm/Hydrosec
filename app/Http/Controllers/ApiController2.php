<?php

namespace App\Http\Controllers;

use App\Models\Pump;
use App\Models\Tower;
use App\Models\IntrusionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{

    public function pump(Request $request)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        $failedAttemptsKey = 'intrusion_failed_attempts_' . $request->ip();
        $failedAttempts = Cache::get($failedAttemptsKey, 0);
        $attemptThreshold = 5;
        $adminEmail = 'hydrosec1@gmail.com';

        Log::info('Pump request received', [
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

            Log::info('Decrypted data', [
                'decryptedPump' => $decryptedPump,
                'decryptedIpAddress' => $decryptedIpAddress,
                'decryptedMacAddress' => $decryptedMacAddress,
                'decryptedTowercode' => $decryptedTowercode,
            ]);

            $towerData = Tower::all();

            foreach ($towerData as $tower) {
                $towercode = Crypt::decryptString($tower->towercode);

                Log::info('Checking tower code', [
                    'currentTowerCode' => $towercode,
                    'decryptedTowercode' => $decryptedTowercode,
                ]);

                if ($towercode == $decryptedTowercode) {
                    $ipmac = Tower::where('id', $tower->id)->first();

                    if ($ipmac) {
                        $ip = Crypt::decryptString($ipmac->ipAdd);
                        $mac = Crypt::decryptString($ipmac->macAdd);

                        Log::info('Decrypted IP and MAC addresses from DB', [
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

                            Log::info('Pump data processed successfully', [
                                'towerId' => $ipmac->id,
                                'pump' => $decryptedPump,
                                'towercode' => $decryptedTowercode,
                            ]);

                            return response()->json(['success' => 'Data processed successfully'], 200);
                        } else {
                            Log::warning('IP or MAC address mismatch', [
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
                        Log::warning('No matching IP/MAC data found for tower', [
                            'towerId' => $tower->id,
                        ]);
                    }
                }
            }

            Log::warning('Tower code not found', [
                'decryptedTowercode' => $decryptedTowercode,
            ]);

            return response()->json(['error' => 'Tower code not found'], 404);

        } catch (ValidationException $e) {
            // Increment failed attempts on validation failure
            $failedAttempts++;
            Cache::put($failedAttemptsKey, $failedAttempts, 3600); // Store attempts for 1 hour
            Log::warning('Validation failed', [
                'ipAddress' => $request->ip(),
                'failedAttempts' => $failedAttempts,
                'errors' => $e->errors(),
            ]);

            return response()->json(['error' => 'Invalid data provided', 'details' => $e->errors()], 422);

        } catch (\Exception $e) {
            Log::error('Error processing request', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while processing the request'], 500);
        } finally {
            // Intrusion detection: Check if failed attempts exceed the threshold
            if ($failedAttempts >= $attemptThreshold) {
                Log::alert('Intrusion detection: multiple failed validation attempts from IP ' . $request->ip(), [
                    'failedAttempts' => $failedAttempts,
                    'ipAddress' => $request->ip(),
                ]);

                IntrusionRecord::create([
                    'ip_address' => Crypt::encryptString($request->ip()),
                    'failed_attempts' => Crypt::encryptString((string) $failedAttempts),
                ]);

                // Send an intrusion alert email to the admin
                $details = [
                    'subject' => 'Intrusion Alert: Multiple Failed Attempts',
                    'body' => 'There have been ' . $failedAttempts . ' failed attempts from IP ' . $request->ip() . '.',
                ];

                try {
                    Mail::to($adminEmail)->send(new Alert($details));
                    Log::info('Intrusion alert email sent to admin', ['adminEmail' => $adminEmail, 'failedAttempts' => $failedAttempts]);

                } catch (\Exception $e) {
                    Log::error('Failed to send intrusion alert email', ['error' => $e->getMessage(), 'adminEmail' => $adminEmail]);
                }
            }
        }
    }

    public function getmode(Request $request)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";
        $failedAttemptsKey = 'intrusion_failed_attempts_' . $request->ip();
        $failedAttempts = Cache::get($failedAttemptsKey, 0);
        $attemptThreshold = 5;
        $adminEmail = "hydrosec1@gmail.com";
        Log::info('Mode request received', ['input' => $request->all()]);

        try {

            $validatedData = $request->validate(['Credentials' => 'required']);
            $cred = $this->decrypt_data($validatedData['Credentials'], $method, $key_str, $iv_str);
            $info = explode(',', $cred);
            $decryptedIpAddress = $info[0];
            $decryptedMacAddress = $info[1];
            $decryptedTowercode = $info[2];

            Log::info('Decrypted request data', [
                'decryptedIpAddress' => $decryptedIpAddress,
                'decryptedMacAddress' => $decryptedMacAddress,
                'decryptedTowercode' => $decryptedTowercode,
            ]);

            // Retrieve and check tower data
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
                        } else {

                            $failedAttempts++;
                            Cache::put($failedAttemptsKey, $failedAttempts, 3600); // Store attempts for 1 hour
                            Log::warning('IP or MAC address mismatch', [
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

                        Log::info('Updated Tower IP and MAC addresses:', ['id' => $tower->id]);
                        return response()->json(['success' => 'Tower IP and MAC updated'], 201);
                    }
                } else {

                    $failedAttempts++;
                    Cache::put($failedAttemptsKey, $failedAttempts, 3600); // Store attempts for 1 hour
                    return response()->json(['error' => 'Invalid tower code'], 400);
                }
            }

            Log::warning('Tower code not found', ['decryptedTowercode' => $decryptedTowercode]);
            return response()->json(['error' => 'Tower code not found'], 404);

        } catch (ValidationException $e) {

            $failedAttempts++;
            Cache::put($failedAttemptsKey, $failedAttempts, 3600); // Store failed attempts for 1 hour
            Log::warning('Validation failed', [
                'ipAddress' => $request->ip(),
                'failedAttempts' => $failedAttempts,
                'errors' => $e->errors(),
            ]);

            return response()->json(['error' => 'Invalid data provided', 'details' => $e->errors()], 422);

        } catch (\Exception $e) {

            Log::error('Error processing mode request', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while processing the request'], 500);
        } finally {

            if ($failedAttempts >= $attemptThreshold) {
                Log::alert('Intrusion detection: multiple failed attempts detected from IP ' . $request->ip(), [
                    'failedAttempts' => $failedAttempts,
                    'ipAddress' => $request->ip(),
                ]);

                IntrusionRecord::create([
                    'ip_address' => Crypt::encryptString($request->ip()),
                    'failed_attempts' => Crypt::encryptString((string) $failedAttempts),
                ]);

                $details = [
                    'subject' => 'Intrusion Alert: Multiple Failed Attempts Detected',
                    'body' => 'There have been ' . $failedAttempts . ' failed login attempts from IP ' . $request->ip() . '.',
                ];

                try {
                    Mail::to($adminEmail)->send(new Alert($details));
                    Log::info('Intrusion alert email sent to admin', ['adminEmail' => $adminEmail, 'failedAttempts' => $failedAttempts]);

                } catch (\Exception $e) {
                    Log::error('Failed to send intrusion alert email', ['error' => $e->getMessage(), 'adminEmail' => $adminEmail]);
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
            Log::error('Encryption error: ' . $e->getMessage());
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
            Log::error('Decryption error: ' . $e->getMessage());
            return null;
        }
    }
}
