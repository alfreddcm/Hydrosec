<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\Admin;
use App\Models\Worker;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;


class AuthManager extends Controller
{

    public function login()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admindashboard');
        } elseif (Auth::guard('worker')->check()) {
            return redirect()->route('workerdashboard');
        } elseif (Auth::guard('owner')->check()) {
            return redirect()->route('ownerdashboard');
        }

        return view('login');
    }

    public function register()
    {

        return view('register');
    }

    public function loginPost(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = $credentials['username'];
        $Password = $credentials['password'];

        $worker =Worker::where('status','1')->get();
        foreach ($worker as $user) {
            try {
                $storedusername = Crypt::decryptString($user->username);
                $storedPassword = $user->password;

                if ($username === $storedusername && Hash::check($Password, $storedPassword)) {
                    Auth::guard('worker')->loginUsingId($user->id);
                    $request->session()->regenerate();

                //to be added feature: Single device log(already have middleware)
                // $sessionToken = Str::random(60);
                // DB::table('tbl_adminaccount')->where('id', $admin->id)->update(['session_token' => $sessionToken]);
                // session(['session_token' => $sessionToken]);


                    return redirect()->route('workerdashboard')
                        ->with('success', 'You have successfully logged in as Worker!');
                }
            } catch (DecryptException $e) {
                return Redirect::back()->with('error', 'Invalid encryption key. Please contact support.');
            }

        }

        $admin = Admin::where('status','1')->get();

        foreach ($admin as $user) {
            try {
                $storedusername = Crypt::decryptString($user->username);
                $storedPassword = $user->password;

                if ($username === $storedusername && Hash::check($Password, $storedPassword)) {
                    Auth::guard('admin')->loginUsingId($user->id);
                    $request->session()->regenerate();
                    return redirect()->route('admindashboard')
                        ->with('success', 'You have successfully logged in as Admin!');
                }
            } catch (DecryptException $e) {
                return Redirect::back()->with('error', 'Invalid encryption key. Please contact support.');
            }
        }

        $owner =Owner::where('status','1')->get();

        foreach ($owner as $user) {
            try {

                $storedusername = Crypt::decryptString($user->username);
                $storedPassword = $user->password;

                if ($username === $storedusername && Hash::check($Password, $storedPassword)) {
                    Auth::guard('owner')->loginUsingId($user->id);
                    $request->session()->regenerate();
                    return redirect()->route('ownerdashboard')
                        ->with('success', 'You have successfully logged in as Owner!');
                }
            } catch (DecryptException $e) {
                return Redirect::back()->with('error', 'Invalid encryption key. Please contact support.');
            }
        }

        return back()->withErrors([
            'username' => 'Your provided credentials do not match in our records.',
            'password' => 'The password you entered is incorrect.',
        ]);
    }

    public function registerPost(Request $request)
    {

        $request->validate([
            'username' => 'required|string|max:255',
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250',
            'password' => 'required|min:8|confirmed',
        ]);
        $username=$request->username;

        $usernameExists = $this->checkUsername('username', $username);

        if ($usernameExists) {

            $usernamedeact = $this->checkUsernamedect('username', $username);
            
            if($usernamedeact){
                // Owner::set('status','1')->where('username',$username);

                return back()->withErrors(['react' => 'User has been deactivated. To reactivate click ']);

            }else{
                return back()->withErrors(['username' => 'The username has already been taken.']);
            }
            
        }

        Owner::create([
            'username' => Crypt::encryptString($username),
            'name' => Crypt::encryptString($request->name),
            'email' => Crypt::encryptString($request->email),
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('index')
            ->with('success', 'You have successfully registered & logged in!');

    }

    public function logout()
    {
        Session::flush();
        Auth::logout();

        return redirect(route('index'));

    }

    public function checkUsername($field, $value, ){
        {
            // Check in Owner table, excluding the current user
            $ownerCheck = Owner::where('status','1')->get()->filter(function ($owner) use ($field, $value) {
                return Crypt::decryptString($owner->$field) === $value;
            })->isNotEmpty();

            $adminCheck = Admin::where('status','1')->get()->filter(function ($admin) use ($field, $value) {
                return Crypt::decryptString($admin->$field) === $value;
            })->isNotEmpty();

            $workerCheck = Worker::where('status','1')->get()->filter(function ($worker) use ($field, $value) {
                return Crypt::decryptString($worker->$field) === $value;
            })->isNotEmpty();

            return $ownerCheck || $workerCheck || $adminCheck;
        }

    }
    public function checkUsernamedect($field, $value,){
        $ownerCheck = Owner::where('status','0')->get()->filter(function ($owner) use ($field, $value) {
            return Crypt::decryptString($owner->$field) === $value;
        })->isNotEmpty();

        $adminCheck = Admin::where('status','0')->get()->filter(function ($admin) use ($field, $value) {
            return Crypt::decryptString($admin->$field) === $value;
        })->isNotEmpty();

        $workerCheck = Worker::where('status','0')->get()->filter(function ($worker) use ($field, $value) {
            return Crypt::decryptString($worker->$field) === $value;
        })->isNotEmpty();

        return $ownerCheck || $workerCheck || $adminCheck;


    }

}


// $subject=table::where('scuriculim','1','strand_id','1')->get();
// foreach($subjec as $data){
//     $subject=subject::where('id',$data->sucjectid)->get();

//     <select>
//     <option value='{{$data->subject-id}}'>{{$subject->name}}</option>
//     </select>
// }

