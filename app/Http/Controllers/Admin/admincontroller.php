<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\Admin;
use App\Models\Worker;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;




class admincontroller extends Controller
{
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
            'username'=> 'required',
        ]);

        $user = Worker::find($id);
        $user->name = Crypt::encryptString($request->input('name'));
        $user->username = Crypt::encryptString($request->input('username'));
        $user->save();

        return redirect()->route('UserAccounts')->with('success', 'User updated successfully.');
    }
    

    public function disableOwner()
    {
        try {
            $user = Owner::find(auth()->user()->id);
            $user->status = "disabled"; 
            $user->save();
        } catch (\Exception $exception) {
           
            return redirect()->route('UserAccounts')->withErrors(['error' => 'Unable to disable the account.']);
        }
    
        return redirect()->route('UserAccounts')->with('status', 'Account disabled successfully.');
    }
    
    public function disableWorker(int $id)
    {
        try {
            $user = Worker::find(auth()->user()->id);
            $user->status = "disabled"; 
            $user->save();

        } catch (\Exception $exception){
            return redirect()->route('UserAccounts')->withErrors(['error' => 'Unable to disable the account.']);

        }
        return redirect()->route('UserAccounts')->with('status', 'Account disabled successfully.');
    }


        //ownerupdate pass

    public function adminupdatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $user = Owner::find(auth()->user()->id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully');
    }
    
    //workerupdate pass
    public function adminupdatePassword2(Request $request)
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
}
