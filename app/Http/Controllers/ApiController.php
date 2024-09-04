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
                        // Store sensor data in session
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
    public function getmode(Request $request)
    {
        Log::info('Mode request received', [
            'input' => $request->all(),
        ]);

        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

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

        Log::info('Checking tower code', [
            'decryptedIpAddress' => $decryptedIpAddress,
            'decryptedMacAddress' => $decryptedMacAddress,
            'decryptedTowercode' => $decryptedTowercode,

        ]);

        $towerData = Tower::all();
        Log::info('tower', [
            'tower' => $towerData->all(),
        ]);

        foreach ($towerData as $tower) {
            // Decrypt stored tower code
            $towercode = Crypt::decryptString($tower->towercode);

            Log::info('Checking tower code', [
                'currentTowerCode' => $towercode,
                'decryptedTowercode' => $decryptedTowercode,
            ]);

            // Check if the tower code matches
            if ($towercode == $decryptedTowercode) {
                // Retrieve IP and MAC addresses for the tower
                $ipmac = Tower::where('id', $tower->id)->first();

                if ($ipmac) {
                    // Decrypt stored IP and MAC addresses
                    $ip = Crypt::decryptString($ipmac->ipAdd);
                    $mac = Crypt::decryptString($ipmac->macAdd);

                    Log::info('Decrypted IP and MAC addresses from DB', [
                        'ipAddress' => $ip,
                        'macAddress' => $mac,
                    ]);

                    // Check if IP and MAC addresses match
                    if ($ip == $decryptedIpAddress && $mac == $decryptedMacAddress) {
                        Log::info('Mode and status data', [
                            'ip' => $ip,
                            'ip2' => $decryptedIpAddress,
                        ]);
                        Log::info('Mode and status data', [
                            'mac' => $mac,
                            'mac2' => $decryptedMacAddress,
                        ]);
                        $mode = Crypt::decryptString($ipmac->mode);
                        $status = Crypt::decryptString($ipmac->status);

                        // Decrypt the mode and status using custom decryption method
                        $encryptedMode = $this->encrypt_data($mode, $method, $key_str, $iv_str);
                        $encryptedStatus = $this->encrypt_data($status, $method, $key_str, $iv_str);

                        // Prepare response data
                        $modestatus_data = [
                            'mode' => $encryptedMode,
                            'status' => $encryptedStatus,
                        ];

                        Log::info('Mode and status data', [
                            'mode' => $encryptedMode,
                            'status' => $encryptedStatus,
                        ]);

                        // Return response
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

    private function encrypt_data($data, $method, $key, $iv)
    {
        try {
            $data = base64_encode($data);
// zero-padding:
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
