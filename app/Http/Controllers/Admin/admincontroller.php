<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntrusionDetection;
use Carbon\Carbon;
use App\Mail\Alert;
use App\Models\Admin;
use App\Models\Owner;
use App\Models\Worker;
use App\Models\Tower;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class admincontroller extends Controller
{
    public function addowneraccount(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
            ],
        ], [
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#).',
        ]);

        $username = $request->username;
        $email = $request->email;

        if ($this->checkUser('username', $username, '1') || $this->checkUser('email', $email, '1')) {
            if ($this->checkUser('username', $username, '0') || $this->checkUser('email', $email, '0')) {
                return back()->withErrors(['react' => 'User has already existed and has been deactivated.']);
            } else {
                return back()->withErrors(['username' => 'The username or email already exists!']);
            }
        }

        Owner::create([
            'username' => Crypt::encryptString($username),
            'name' => Crypt::encryptString($request->name),
            'email' => Crypt::encryptString($request->email),
            'password' => Hash::make($request->password),
            'status' => Crypt::encryptString('1'),

        ]);

        return back()
            ->with('success', 'You have successfully added!');

    }

public function showCounts()
{
    // Count owners with decrypted status of 1
    $ownerCount = Owner::all()->filter(function ($owner) {
        $status = Crypt::decryptString($owner->status);
        Log::info('Owner status decrypted:', ['status' => $status]);
        return $status == '1';
    })->count();

    // Log the count of owners
    Log::info('Owner Count:', ['count' => $ownerCount]);

    // Count workers with decrypted status of 1
    $workerCount = Worker::all()->filter(function ($worker) {
        $status = Crypt::decryptString($worker->status);
        Log::info('Worker status decrypted:', ['status' => $status]);
        return $status == '1';
    })->count();

    // Log the count of workers
    Log::info('Worker Count:', ['count' => $workerCount]);

    // Fetch intrusion data and format date
    $intrusions = IntrusionDetection::all()->map(function ($intrusion) {
        $intrusion->formatted_detected_at = Carbon::parse($intrusion->detected_at)->format('h:i A d,m,Y');
        $intrusion->ip_address = Crypt::decryptString($intrusion->ip_address);
        $intrusion->user_agent = Crypt::decryptString($intrusion->user_agent);
        $intrusion->failed_attempts = Crypt::decryptString($intrusion->failed_attempts);

        // Log each intrusion entry
        Log::info('Intrusion Detection Entry:', [
            'ip_address' => $intrusion->ip_address,
            'user_agent' => $intrusion->user_agent,
            'failed_attempts' => $intrusion->failed_attempts,
            'formatted_detected_at' => $intrusion->formatted_detected_at,
        ]);

        return $intrusion;
    });

    // Return all data to the view
    return view('Admin.dashboard', compact('ownerCount', 'workerCount', 'intrusions'));
}

    public function edit($id)
    {
        $user = Owner::find($id);
        $user->name = Crypt::decryptString($user->name);

        $user->username = Crypt::decryptString($user->username);
        $user->email = Crypt::decryptString($user->email);
        return view('Admin.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required',
        ]);

        $user = Owner::find($id);
        $user->name = Crypt::encryptString($request->input('name'));
        $user->username = Crypt::encryptString($request->input('username'));
        $user->email = Crypt::encryptString($request->input('email'));
        $user->save();

        return redirect()->route('UserAccounts')->with('success', 'User updated successfully.');
    }

    public function edit2($id)
    {
        $worker = Worker::find($id);
        $worker->name = Crypt::decryptString($worker->name);

        $worker->username = Crypt::decryptString($worker->username);
        return view('Admin.edit2', compact('worker'));
    }

    public function update2(Request $request, $id)
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

        return redirect()->route('UserAccounts')->with('success', 'User updated successfully.');
    }

    //ownerupdate pass

    public function adminupdatePassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'idd' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
                'confirmed',
            ]]);

        if ($validator->fails()) {
            Log::warning('Password update failed validation.', ['errors' => $validator->errors(), 'input' => $request->all()]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Find the user by ID
        $user = Owner::where('id', $request->idd)->first();

        if (!$user) {
            Log::error('User not found during password update.', ['user_id' => $request->idd]);
            return redirect()->back()->with('error', 'User not found');
        }

        $user->password = Hash::make($request->password);
        $user->save();
        Log::info('User password updated.', ['username' => Crypt::decryptString($user->username), 'email' => $user->email]);

        $body = "Dear " . Crypt::decryptString($user->username) . ", your password has been changed to: " . $request->password;
        $details = [
            'title' => 'Alert: Password Change',
            'body' => $body,
        ];

        // Send email notification
        $email = Crypt::decryptString($user->email);
        Mail::to($email)->send(new Alert($details));

        // Log email sending
        Log::info('Password change notification sent.', ['email' => $email]);

        return redirect()->back()->with('success', 'Password updated successfully');
    }
    //workerupdate pass
    public function adminupdatePassword2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
                'confirmed',
            ]]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $user = Worker::find($request->id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    public function adminPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $user = Admin::find(auth()->user()->id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    //check email
    public function checkUser($field, $value, $status)
    {
        $models = [Owner::class, Admin::class, Worker::class];

        foreach ($models as $model) {
            $exists = $model::where('status', $status)
                ->get()
                ->filter(function ($record) use ($field, $value) {
                    return Crypt::decryptString($record->$field) === $value;
                })
                ->isNotEmpty();

            if ($exists) {
                return true;
            }
        }

        return false;
    }
    public function disableOwner()
    {
        try {
            $user = Owner::find(auth()->user()->id);
            if ($user) {
                $user->status = Crypt::encryptString("0");
                $user->save();

                // Retrieve all workers associated with the owner
                $workers = Worker::where('OwnerID', $user->id)->get();
                foreach ($workers as $worker) {
                    $worker->status = Crypt::encryptString("0");
                    $worker->save();
                }

                return redirect()->route('UserAccounts')->with('status', 'Account disabled successfully.');
            } else {
                return redirect()->route('UserAccounts')->withErrors(['error' => 'Owner not found.']);
            }
        } catch (\Exception $exception) {

            return redirect()->route('UserAccounts')->withErrors(['error' => 'Unable to disable the account.']);
        }
    }

    public function en()
    {
        try {
            $user = Owner::find(auth()->user()->id);
            if ($user) {
                $user->status = Crypt::encryptString("1");
                $user->save();
                $workers = Worker::where('OwnerID', $user->id)->get();
                foreach ($workers as $worker) {
                    $worker->status = Crypt::encryptString("1");
                    $worker->save();
                }

                return redirect()->route('UserAccounts')->with('status', 'Account enabled successfully.');
            } else {
                return redirect()->route('UserAccounts')->withErrors(['error' => 'Owner not found.']);
            }
        } catch (\Exception $exception) {
            // Optionally log the exception for further analysis
            // Log::error('Failed to enable account: ' . $exception->getMessage());

            return redirect()->route('UserAccounts')->withErrors(['error' => 'Unable to enable the account.']);
        }
    }

}
