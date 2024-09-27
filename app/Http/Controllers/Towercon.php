<?php

namespace App\Http\Controllers;

use App\Models\Pump;
use App\Models\Sensor;
use App\Models\SensorDataHistory;
use App\Models\Tower;
use App\Models\Towerlog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Towercon extends Controller
{
    /**
     * Store a newly created tower in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required',
            'towercode' => 'required|string|max:4',
        ], [
            'towercode.required' => 'The tower code is required.',
            'towercode.string' => 'The tower code must be a string.',
            'towercode.max' => 'The tower code may not be greater than 4 characters.',
        ]);

        // Check if a tower with the given code already exists
        $existingTower = Tower::all();
        foreach ($existingTower as $tower) {
            $codedecrypted = Crypt::decryptString($tower->towercode);
            if ($codedecrypted === $request->input('towercode')) {
                return redirect()->back()->with('error', 'Tower with this code already exists.');
            }
        }
        $ownerId = auth::id();

        $tower = Tower::create([
            'OwnerID' => $ownerId,
            'name' => Crypt::encryptString($request->name),
            'towercode' => Crypt::encryptString($request->towercode),
            'status' => Crypt::encryptString('0'),
            'mode' => Crypt::encryptString('0'),
        ]);

        // Check if the tower was created successfully
        if ($tower) {
            return redirect()->back()->with('success', 'Tower added and owner updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to create tower');
        }
    }

    public function updateDates(Request $request)
    {
        $days = (int) $request->input('days', 0);
        $newDays = (int) $request->input('newDays', 0);
        $towerId = $request->input('tower_id');

        $tower = Tower::find($towerId);

        $hour = now()->hour;

        if ($hour >= 6 && $hour < 18) {
            $mode = 1;
        } elseif ($hour >= 18 && $hour < 22) {
            $mode = 2;
        } else {
            $mode = 0;
        }

        if ($tower) {
            if ($days > 0) {

                $startdate = Carbon::now();
                $enddate = $startdate->copy()->addDays($days);
                $tower->status = Crypt::encryptString('1');
                $tower->mode = Crypt::encryptString($mode);
                $tower->startdate = $startdate;
                $tower->enddate = $enddate;
                $tower->save();

                Log::info('New cycle started', [
                    'tower_id' => $tower->id,
                    'date_started' => $startdate,
                    'date_end' => $enddate,
                ]);

                Log::info('New cycle started', [
                    'tower_id' => $tower->id,
                    'date_started' => $startdate,
                    'date_end' => $enddate,
                ]);

                Towerlog::create([
                    'ID_tower' => $towerId,
                    'activity' => Crypt::encryptString("New cycle started. Tower ID: {$tower->id}, Start date: {$startdate}, End date: {$enddate}"),
                ]);

                return redirect()->back()->with('success', 'Cycle started successfully!');
            } elseif ($newDays > 0) {
                // Handle updating an existing cycle
                $startdate = $tower->startdate;
                $enddate = Carbon::now()->addDays($newDays);

                $tower->enddate = $enddate;
                $tower->save();

                Log::info('Cycle dates updated', [
                    'tower_id' => $tower->id,
                    'date_end' => $enddate,
                ]);

                return redirect()->back()->with('success', 'Cycle dates updated successfully!');
            }

            return redirect()->back()->with('error', 'Invalid input.');
        }

        Log::error('Failed to handle cycle - Tower not found', [
            'tower_id' => $towerId,
        ]);

        return redirect()->back()->with('error', 'Tower not found!');
    }

    public function stop(Request $request)
    {
        \Log::info('Stop method called', ['request' => $request->all()]);

        $towerId = $request->input('tower_id');
        \Log::info('Tower ID retrieved', ['towerId' => $towerId]);

        $tow = Tower::where('id', $towerId)->first();
        if (!$tow) {
            \Log::error('Tower not found', ['towerId' => $towerId]);
            return redirect()->back()->with('error', 'Tower not found.');
        }

        \Log::info('Tower retrieved', ['tow' => $tow]);

        try {
            $stat = Crypt::encryptString('0');
            \Log::info('Status encrypted', ['encryptedStatus' => $stat]);

            $tow->startdate = null;
            $tow->enddate = null;
            $tow->status = $stat;
            $tow->save();

            \Log::info('Tower status updated and saved', ['tow' => $tow]);
        } catch (\Exception $e) {
            \Log::error('Error encrypting status', ['exception' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update tower status.');
        }

        $ownerID = $tow->OwnerID;
        \Log::info('Owner ID retrieved', ['ownerID' => $ownerID]);

        $sensorData = Sensor::where('towerid', $towerId)
            ->orderBy('created_at', 'desc')
            ->get();

        \Log::info('Sensor data retrieved', ['sensorData' => $sensorData]);

        $pumps = Pump::where('towerid', $towerId)->get();
        if ($sensorData->isEmpty() && $pumps->isEmpty()) {
            \Log::info('No sensor or pump data available');
            return redirect()->back()->with('success', 'No sensor or pump data to save.');
        }

        $pumpDataArray = $pumps->map(function ($pump) {
            return [
                'status' => $pump->status,
                'created_at' => $pump->created_at->toDateTimeString(),
            ];
        })->toArray();

        \Log::info('Pump data retrieved', ['pumpDataArray' => $pumpDataArray]);

        // Format sensor data
        $sensorDataArray = $sensorData->map(function ($data) {
            return [
                'pH' => $data->pH,
                'temperature' => $data->temperature,
                'nutrientlevel' => $data->nutrientlevel,
                'light' => $data->light,
                'created_at' => $data->created_at->toDateTimeString(),
            ];
        })->toArray();

        \Log::info('Sensor data formatted', ['sensorDataArray' => $sensorDataArray]);

        try {
            SensorDataHistory::create([
                'towerid' => $towerId,
                'OwnerID' => $ownerID,
                'sensor_data' => json_encode($sensorDataArray),
                'pump' => json_encode($pumpDataArray), // Save pump data to the new column
                'created_at' => Carbon::now(),
            ]);

            \Log::info('Sensor and pump data saved');
        } catch (\Exception $e) {
            \Log::error('Error saving sensor or pump data', ['exception' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save data.');
        }

        // Create the activity log
        $activityLog = [
            'Message' => 'Tower ' . Crypt::decryptString($tow->name) . ' has been set to done cycle.',
            'Date' => Carbon::now()->toDateTimeString(),
        ];

        \Log::info('Activity log created', ['activityLog' => $activityLog]);

        try {
            Towerlog::create([
                'ID_tower' => $tow->id,
                'activity' => Crypt::encryptString(json_encode($activityLog)), // Ensure JSON encoding if storing as a string
            ]);

            \Log::info('Activity log saved');
        } catch (\Exception $e) {
            \Log::error('Error encrypting activity log', ['exception' => $e->getMessage()]);
        }

        Sensor::truncate();
        Pump::truncate();

        \Log::info('Sensor table truncated');

        return redirect()->back()->with('success', 'Cycle stopped, sensor data saved, and log entry created successfully!');
    }

    public function stopdis(Request $request)
    {
        \Log::info('Dis method called', ['request' => $request->all()]);

        $towerId = $request->input('tower_id');
        \Log::info('Tower ID retrieved', ['towerId' => $towerId]);

        $tow = Tower::where('id', $towerId)->first();
        if (!$tow) {
            \Log::error('Tower not found', ['towerId' => $towerId]);
            return redirect()->back()->with('error', 'Tower not found.');
        }

        \Log::info('Tower retrieved', ['tow' => $tow]);

        try {
            $stat = Crypt::encryptString('0');
            \Log::info('Status encrypted', ['encryptedStatus' => $stat]);

            $tow->status = $stat;

            $tow->save();
            \Log::info('Tower status updated and saved', ['tow' => $tow]);

        } catch (\Exception $e) {
            \Log::error('Error encrypting status', ['exception' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update tower status.');
        }

    
        // Create the activity log
        $activityLog = [
            'Message' => 'Tower ' . Crypt::decryptString($tow->name) . ' has been set to disabled.',
            'Date' => Carbon::now()->toDateTimeString(),
        ];

        \Log::info('Activity log created', ['activityLog' => $activityLog]);

        try {
            Towerlog::create([
                'ID_tower' => $tow->id,
                'activity' => Crypt::encryptString(json_encode($activityLog)), // Ensure JSON encoding if storing as a string
            ]);

            \Log::info('Activity log saved');
        } catch (\Exception $e) {
            \Log::error('Error encrypting activity log', ['exception' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Cycle stopped, Tower has set to disabled!');
    }

     public function en(Request $request)
    {
        \Log::info('en method called', ['request' => $request->all()]);

        $towerId = $request->input('tower_id');
        \Log::info('Tower ID retrieved', ['towerId' => $towerId]);

        $tow = Tower::where('id', $towerId)->first();
        if (!$tow) {
            \Log::error('Tower not found', ['towerId' => $towerId]);
            return redirect()->back()->with('error', 'Tower not found.');
        }

        \Log::info('Tower retrieved', ['tow' => $tow]);

        try {
            $stat = Crypt::encryptString('1');
            \Log::info('Status encrypted', ['encryptedStatus' => $stat]);

            $tow->status = $stat;

            $tow->save();
            \Log::info('Tower status updated and saved', ['tow' => $tow]);

        } catch (\Exception $e) {
            \Log::error('Error encrypting status', ['exception' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update tower status.');
        }

       
        $activityLog = [
            'Message' => 'Tower ' . Crypt::decryptString($tow->name) . ' has been set to Ative Status.',
            'Date' => Carbon::now()->toDateTimeString(),
        ];

        \Log::info('Activity log created', ['activityLog' => $activityLog]);

        try {
            Towerlog::create([
                'ID_tower' => $tow->id,
                'activity' => Crypt::encryptString(json_encode($activityLog)),
            ]);

            \Log::info('Activity log saved');
        } catch (\Exception $e) {
            \Log::error('Error encrypting activity log', ['exception' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Tower set to Enabled successfully!');
    }

    public function restartCycle(Request $request)
    {
        $tower = Tower::find($request->tower_id);

        if ($tower) {
            $tower->startdate = null;
            $tower->enddate = null;

            $tower->status = Crypt::encryptString('0');
            $tower->save();

            return redirect()->back()->with('success', 'Tower cycle restarted successfully.');
        }

        return redirect()->back()->with('error', 'Failed to restart the tower cycle.');
    }

    public function modestat(Request $request, $id)
    {
        $tower = Tower::find($id)->first();

        $decrypted_data = [
            'mode' => Crypt::decryptString($tower->mode),
            'status' => Crypt::decryptString($tower->status),
        ];

        return response()->json(['modestat' => $decrypted_data]);
    }

    private function encrypt_data($data)
    {
        $key_str = "ISUHydroSec2024!";
        $iv_str = "HydroVertical143";
        $method = "AES-128-CBC";

        try {
            $data = base64_encode($data);
            $str_padded = $data;
            $pad = 16 - strlen($str_padded) % 16;
            if (strlen($str_padded) % 16) {
                $str_padded = str_pad($str_padded, strlen($str_padded) + $pad, "\0");
            }

            $result = openssl_encrypt($str_padded, $method, $key_str, OPENSSL_NO_PADDING, $iv_str);
            $result = base64_encode($result);

            return $result;
        } catch (\Exception $e) {
            Log::error('Encryption error: ' . $e->getMessage());
            return null;
        }
    }

    //for sensory hisstory data

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
