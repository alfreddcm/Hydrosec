<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Owner;
use App\Models\Worker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;




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
        $usernameExists = $this->check('username', $request->username, Auth::id());
        $emailExists = $this->check('email', $request->email, Auth::id());

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

    public function check($field, $value, $currentUserId){
        {
            // Check in Owner table, excluding the current user
            $ownerCheck = Owner::where($field, Crypt::encryptString($value))
                ->where('id', '!=', $currentUserId)
                ->exists();

            // Check in Admin table
            // $adminCheck = Admin::all()->filter(function ($admin) use ($field, $value) {
            //     return Crypt::decryptString($admin->$field) === $value;
            // })->isNotEmpty();

            // Check in Worker table
            $workerCheck = Worker::all()->filter(function ($worker) use ($field, $value) {
                return Crypt::decryptString($worker->$field) === $value;
            })->isNotEmpty();

            return $ownerCheck || $workerCheck;
        }
    }
}


