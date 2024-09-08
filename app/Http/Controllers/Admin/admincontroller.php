<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Owner;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
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
                'regex:/[@$!%*?&]/'],
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
        $ownerCount = 0;
        $workerCount = 0;

        // Count owners with decrypted status of 1
        $owners = Owner::all();
        foreach ($owners as $owner) {
            if (Crypt::decryptString($owner->status) == '1') {
                $ownerCount++;
            }
        }

        // Count workers with decrypted status of 1
        $workers = Worker::all();
        foreach ($workers as $worker) {
            if (Crypt::decryptString($worker->status) == '1') {
                $workerCount++;
            }
        }

        return view('Admin.dashboard', compact('ownerCount', 'workerCount'));
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
            'name' => 'required|string|max:255',
            'username' => 'required',
        ]);

        $user = Worker::find($id);
        $user->name = Crypt::encryptString($request->input('name'));
        $user->username = Crypt::encryptString($request->input('username'));
        $user->save();

        return redirect()->route('UserAccounts')->with('success', 'User updated successfully.');
    }

    //ownerupdate pass

    public function adminupdatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Find the user by ID
        $user = Owner::find($request->id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        // Prepare email details
        $body = "Dear " . Crypt::decryptString($user->name) . ", your password has been changed to: " . $request->password;

        $details = [
            'title' => 'Alert: Password Change',
            'body' => $body,
        ];

        // Send email to the user
        $email = Crypt::decryptString($user->email);
        Mail::to($email)->send(new Alert($details));

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    //workerupdate pass
    public function adminupdatePassword2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

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

        // Update the user's password
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
            // Optionally log the exception for further analysis
            // Log::error('Failed to disable account: ' . $exception->getMessage());
    
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
    
                // Retrieve all workers associated with the owner
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
