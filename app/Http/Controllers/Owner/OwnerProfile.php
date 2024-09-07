<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Owner;
use App\Models\Tower;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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
        // Check in Owner table, excluding the current user
        $ownerCheck = Owner::where($field, Crypt::encryptString($value))
            ->where('id', '!=', $currentUserId)
            ->exists();

        $adminCheck = Admin::all()->filter(function ($admin) use ($field, $value) {
            return Crypt::decryptString($admin->$field) === $value;
        })->isNotEmpty();

        // Check in Worker table
        $workerCheck = Worker::all()->filter(function ($worker) use ($field, $value) {
            return Crypt::decryptString($worker->$field) === $value;
        })->isNotEmpty();

        return $ownerCheck || $workerCheck || $adminCheck;
    }

    public function checkEmail($field, $value, $currentUserId)
    {{
        // Check in Owner table, excluding the current user
        $ownerCheck = Owner::where($field, Crypt::encryptString($value))
            ->where('id', '!=', $currentUserId)
            ->exists();

        // Check in Worker table
        $workerCheck = Worker::all()->filter(function ($worker) use ($field, $value) {
            return Crypt::decryptString($worker->$field) === $value;
        })->isNotEmpty();

        return $ownerCheck || $workerCheck;
    }}

    public function addworker(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Check if the username already exists
        $usernameExists = $this->checkUsernameWorker('username', $request->username);

        if ($usernameExists) {
            // Redirect back with an error message if the username is taken
            return back()->withErrors(['username' => 'The username has already been taken.']);
        } else {
            // Create a new worker account
            Worker::create([
                'username' => Crypt::encryptString($request->username),
                'name' => Crypt::encryptString($request->name),
                'password' => Hash::make($request->password),
                'OwnerID' => Auth::id(),

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

        return view('owner.edit', compact('user'));
    }

    public function workerupdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required',
            'towerid' => 'required',

        ]);

        $user = Worker::find($id);
        $user->name = Crypt::encryptString($request->input('name'));
        $user->username = Crypt::encryptString($request->input('username'));
        $user->OwnerID = Auth::id();
        $user->towerid = $request->input('towerid');

        $user->save();

        return redirect()->route('ownerworkeraccount')->with('success', 'User updated successfully.');
    }

    public function workerPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $user = Worker::find(auth()->user()->id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully');
    }

}
