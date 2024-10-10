<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Owner;
use App\Models\SensorDataHistory;
use App\Models\Tower;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class OwnerProfile extends Controller
{

    //
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password_confirmation' => 'required',
        ]);

        // Decrypt and check if the username or email already exists in any of the three tables
        $usernameExists = $this->checkUsername('username', $request->username, Auth::id());
        $emailExists = $this->checkEmail('email', $request->email, Auth::id());

        if ($usernameExists) {
            return back()->withErrors(['username' => 'The username has already been taken.']);
        }

        if ($emailExists) {
            return back()->withErrors(['email' => 'The email has already been taken.']);
        }

        if (!Hash::check($request->password_confirmation, Auth::user()->password)) {
            return back()->withErrors(['password_confirmation' => 'The provided password does not match your current password.']);
        }

        $owner = Owner::find(Auth::id());
        if ($owner) {
            $owner->update([
                'name' => Crypt::encryptString($request->name),
                'username' => Crypt::encryptString($request->username),
                'email' => Crypt::encryptString($request->email),
            ]);

            return redirect()->route('ownermanageprofile')->with('success', 'Profile updated successfully.');

        }
    }

    public function checkUsername($field, $value, $currentUserId)
    {

        $ownerCheck = Owner::where($field, Crypt::encryptString($value))
            ->where('id', '!=', $currentUserId)
            ->exists();

        $adminCheck = Admin::all()->filter(function ($admin) use ($field, $value) {
            return Crypt::decryptString($admin->$field) === $value;
        })->isNotEmpty();

        $workerCheck = Worker::all()->filter(function ($worker) use ($field, $value) {
            return Crypt::decryptString($worker->$field) === $value;
        })->isNotEmpty();

        return $ownerCheck || $workerCheck || $adminCheck;
    }

    public function checkEmail($field, $value, $currentUserId)
    {
        $ownerCheck = Owner::where($field, Crypt::encryptString($value))
            ->where('id', '!=', $currentUserId)
            ->exists();

        return $ownerCheck;
    }

    public function addworker(Request $request)
    {
        $credentials = $request->validate([
            'tower' => 'required',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
            ]]);

        $usernameExists = $this->checkUsernameWorker('username', $request->username);

        if ($usernameExists) {
            $workerCheck2 = Worker::get();
            foreach ($workerCheck2 as $data) {
                if (Crypt::decryptString($data->username) == $credentials->username && Crypt::decryptString($data->status) == '0') {
                    return back()->withErrors(['error' => 'The this user has disable.']);
                }
            }

            return back()->withErrors(['username' => 'The username has already been taken.']);
        } else {

            Worker::create([
                'towerid' => $request->tower,
                'username' => Crypt::encryptString($request->username),
                'name' => Crypt::encryptString($request->name),
                'password' => Hash::make($request->password),
                'OwnerID' => Auth::id(),
                'status' => Crypt::encryptString('1'),

            ]);

            // Redirect with a success message
            return redirect()->route('ownerworkeraccount')->with('success', 'Account successfully created.');
        }
    }

    public function checkUsernameWorker($field, $value)
    {
        $workerCheck = Worker::all()->filter(function ($worker) use ($field, $value) {
            return Crypt::decryptString($worker->$field) === $value;
        })->isNotEmpty();

        $adminCheck = Admin::all()->filter(function ($admin) use ($field, $value) {
            return Crypt::decryptString($admin->$field) === $value;
        })->isNotEmpty();

        $ownerCheck = Owner::all()->filter(function ($worker) use ($field, $value) {
            return Crypt::decryptString($worker->$field) === $value;
        })->isNotEmpty();

        return $ownerCheck || $workerCheck || $adminCheck;
    }

    public function edit($id)
    {
        $user = Worker::find($id);
        $user->name = Crypt::decryptString($user->name);
        $user->username = Crypt::decryptString($user->username);

        return view('Owner.edit', compact('user'));
    }

    public function workerupdate(Request $request, $id)
    {

        $request->validate([
            'tower' => 'required',
            'name' => 'required|string|max:255',
            'username' => 'required',
        ]);

        $user = Worker::find($id);
        $user->towerid = $request->tower;
        $user->name = Crypt::encryptString($request->input('name'));
        $user->username = Crypt::encryptString($request->input('username'));
        $user->save();

        return redirect()->route('ownerworkeraccount')->with('success', 'User updated successfully.');
    }

    public function workerPassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/', // Lowercase letter
                'regex:/[A-Z]/', // Uppercase letter
                'regex:/[0-9]/', // Digit
                'regex:/[@$!%*?&#]/', // Special character
                'confirmed', // Password confirmation
            ], [
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#).',
                'password.confirmed' => 'Password confirmation does not match.',
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Fetch the worker by ID
        $user = Worker::where('id', $request->id)->first();

        // Check if the user exists
        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        // Update the worker's password
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    public function workerdis(Request $request, $id)
    {

        $user = Worker::find($id);
        $user->status = crypt::encryptString('0');
        $user->save();

        return redirect()->route('ownerworkeraccount')->with('success', 'User disable successfully.');
    }
    public function workeren(Request $request, $id)
    {

        $user = Worker::find($id);
        $user->status = crypt::encryptString('1');
        $user->save();

        return redirect()->route('ownerworkeraccount')->with('success', 'User enable successfully.');
    }

    public function decryptSensorData()
{
    $key_str = "ISUHydroSec2024!";
    $iv_str = "HydroVertical143";
    $method = "AES-128-CBC";

    // Retrieve towers owned by the authenticated user
    $towers = Tower::where('OwnerID', Auth::id())->get();
    $allDecryptedData = [];

    foreach ($towers as $tower) {
        $sensorDataHistory = SensorDataHistory::where('towerid', $tower->id)->get();

        if ($sensorDataHistory->isEmpty()) {
            continue;
        }

        foreach ($sensorDataHistory as $data) {
            $code = Crypt::decryptString($tower->towercode);
            $plantVar = Crypt::decryptString($tower->plantVar); // Decrypt plantVar
            $sensorDataArray = json_decode($data->sensor_data, true);
            $pumpDataArray = json_decode($data->pump, true);

            // Initialize decrypted entries
            $decryptedEntries = [];

            // Process sensor data
            if (is_array($sensorDataArray)) {
                foreach ($sensorDataArray as $sensorData) {
                    if (isset($sensorData['pH'], $sensorData['temperature'], $sensorData['nutrientlevel'], $sensorData['light'])) {
                        try {
                            $decryptedEntry = [
                                'pH' => (float) $this->decrypt_data($sensorData['pH'], $method, $key_str, $iv_str),
                                'temperature' => (float) $this->decrypt_data($sensorData['temperature'], $method, $key_str, $iv_str),
                                'nutrientlevel' => (float) $this->decrypt_data($sensorData['nutrientlevel'], $method, $key_str, $iv_str),
                                'light' => (float) $this->decrypt_data($sensorData['light'], $method, $key_str, $iv_str),
                                'created_at' => Carbon::parse($data->created_at),
                            ];
                            $decryptedEntries[] = $decryptedEntry;
                        } catch (\Exception $e) {
                            \Log::error("Decryption error for sensor data: " . $e->getMessage(), ['sensorData' => $sensorData]);
                        }
                    } else {
                        \Log::error("Missing required sensor data keys: ", $sensorData);
                    }
                }
            } else {
                \Log::error("Invalid sensor data format: ", ['sensorDataArray' => $sensorDataArray]);
            }

            // Process pump data
            if (is_array($pumpDataArray)) {
                foreach ($pumpDataArray as $pdata) {
                    if (isset($pdata['status'], $pdata['created_at'])) {
                        try {
                            $decryptedStatus = $this->decrypt_data($pdata['status'], $method, $key_str, $iv_str);

                            // Ensure the decrypted status is numeric (like 0 or 1)
                            $decryptedPumpEntry = [
                                'pump_status' => (float) $decryptedStatus, // Cast to float if needed
                                'pump_created_at' => Carbon::parse($pdata['created_at']),
                            ];

                            $decryptedEntries[] = $decryptedPumpEntry;
                        } catch (\Exception $e) {
                            \Log::error("Decryption error for pump data: " . $e->getMessage(), ['pumpData' => $pdata]);
                        }
                    } else {
                        \Log::error("Missing required pump data keys: ", $pdata);
                    }
                }
            } else {
                \Log::error("Invalid pump data format: ", ['pumpDataArray' => $pumpDataArray]);
            }

            // Use $data->id as the key for decrypted data
            $startDate = Carbon::parse($data->created_at)->format('m/d/Y');
            $endDate = Carbon::parse($data->created_at)->format('m/d/Y');

            $allDecryptedData[$data->id] = [
                'towercode' => $code,
                'plantVar' => $plantVar, // Add decrypted plantVar to the result
                'data' => $decryptedEntries,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];
        }
    }

    // Return the view with the decrypted data
    return view('Owner.dashboard', ['allDecryptedData' => $allDecryptedData]);
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
