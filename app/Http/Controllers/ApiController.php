<?php

namespace App\Http\Controllers;

use App\Models\Pump;
use App\Models\Tower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function pump(Request $request)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        Log::info('Pump request received', [
            'input' => $request->all(),
        ]);

        $validatedData = $request->validate([
            'pumped' => 'required',
            'ipAddress' => 'required',
            'macAddress' => 'required',
            'towercode' => 'required',
        ]);

        Log::info('Validated data', [
            'validatedData' => $validatedData,
        ]);

        $decryptedPump = $this->decrypt_data($validatedData['pumped'], $method, $key_str, $iv_str);
        $decryptedIpAddress = $this->decrypt_data($validatedData['ipAddress'], $method, $key_str, $iv_str);
        $decryptedMacAddress = $this->decrypt_data($validatedData['macAddress'], $method, $key_str, $iv_str);
        $decryptedTowercode = $this->decrypt_data($validatedData['towercode'], $method, $key_str, $iv_str);

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

                        
                        Pump::create([
                                'towerid' => $tower->id,
                                'status' => $validatedData['pumped'],
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
    }

    public function getmode(Request $request)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        Log::info('Mode request received', [
            'input' => $request->all(),
        ]);

        // Validate incoming request data
        $validatedData = $request->validate([
            'ipAddress' => 'required',
            'macAddress' => 'required',
            'towercode' => 'required',
        ]);

        // Decrypt the incoming request data
        $decryptedIpAddress = $this->decrypt_data($validatedData['ipAddress'], $method, $key_str, $iv_str);
        $decryptedMacAddress = $this->decrypt_data($validatedData['macAddress'], $method, $key_str, $iv_str);
        $decryptedTowercode = $this->decrypt_data($validatedData['towercode'], $method, $key_str, $iv_str);

        Log::info('Decrypted request data', [
            'decryptedIpAddress' => $decryptedIpAddress,
            'decryptedMacAddress' => $decryptedMacAddress,
            'decryptedTowercode' => $decryptedTowercode,
        ]);

        $towerData = Tower::all();
        Log::info('Retrieved tower data', [
            'towerData' => $towerData->toArray(),
        ]);

        foreach ($towerData as $tower) {
            $towercode = Crypt::decryptString($tower->towercode);
            Log::info('Checking tower code', [
                'currentTowerCode' => $towercode,
                'decryptedTowercode' => $decryptedTowercode,
            ]);

            $mode = Crypt::decryptString($tower->mode);
            $status = Crypt::decryptString($tower->status);
            Log::info('Decrypted mode and status', [
                'mode' => $mode,
                'status' => $status,
            ]);

            if ($towercode == $decryptedTowercode && $tower->ipAdd) {
                // Retrieve and decrypt IP and MAC addresses
                $ipmac = Tower::where('id', $tower->id)->first();
                $ip = Crypt::decryptString($ipmac->ipAdd);
                $mac = Crypt::decryptString($tower->macAdd);

                Log::info('Decrypted IP and MAC addresses from DB', [
                    'ipAddress' => $ip,
                    'macAddress' => $mac,
                ]);

                if ($ip == $decryptedIpAddress && $mac == $decryptedMacAddress) {
                    Log::info('IP and MAC address match', [
                        'ip' => $ip,
                        'decryptedIpAddress' => $decryptedIpAddress,
                        'mac' => $mac,
                        'decryptedMacAddress' => $decryptedMacAddress,
                    ]);

                    $encryptedMode = $this->encrypt_data($mode, $method, $key_str, $iv_str);
                    $encryptedStatus = $this->encrypt_data($status, $method, $key_str, $iv_str);

                    Log::info('Encrypted mode and status', [
                        'mode' => $encryptedMode,
                        'status' => $encryptedStatus,
                    ]);

                    $modestatus_data = [
                        'mode' => $encryptedMode,
                        'status' => $encryptedStatus,
                    ];

                    return response()->json(['modestat' => $modestatus_data]);
                } else {
                    Log::warning('IP or MAC address mismatch', [
                        'expectedIp' => $decryptedIpAddress,
                        'actualIp' => $ip,
                        'expectedMac' => $decryptedMacAddress,
                        'actualMac' => $mac,
                    ]);

                    return response()->json(['error' => 'IP or MAC address mismatch'], 400);
                }

            } else {
                $mode = 0;
                $status = 0;
                $encryptedMode = $this->encrypt_data($mode, $method, $key_str, $iv_str);
                $encryptedStatus = $this->encrypt_data($status, $method, $key_str, $iv_str);

                Log::info('Encrypted mode and status', [
                    'mode' => $encryptedMode,
                    'status' => $encryptedStatus,
                ]);

                $modestatus_data = [
                    'mode' => $encryptedMode,
                    'status' => $encryptedStatus,
                ];

                return response()->json(['modestat' => $modestatus_data]);
            }
        }

        Log::warning('Tower code not found', [
            'decryptedTowercode' => $decryptedTowercode,
        ]);

        return response()->json(['error' => 'Tower code not found'], 404);
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
