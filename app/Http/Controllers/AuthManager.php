<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomDecryptException;
use App\Models\Owner;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;
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

        $worker = DB::table('tbl_workeraccount')->get();
        foreach ($worker as $user) {
            try {
                $storedusername = Crypt::decryptString($user->username);
                $storedPassword = $user->password;

                if ($username === $storedusername && Hash::check($Password, $storedPassword)) {
                    Auth::guard('worker')->loginUsingId($user->id);
                    $request->session()->regenerate();
                    return redirect()->route('workerdashboard')
                        ->with('success', 'You have successfully logged in as Worker!');
                }
            } catch (DecryptException $e) {
                return Redirect::back()->with('error', 'Invalid encryption key. Please contact support.');
            }

        }

        $admin = DB::table('tbl_adminaccount')->get();

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

        $owner = DB::table('tbl_useraccounts')->get();

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
            'username' => 'required|string|min:8|unique:tbl_useraccounts',
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:tbl_useraccounts',
            'password' => 'required|min:8|confirmed',
        ]);

        Owner::create([
            'username' => Crypt::encryptString($request->username),
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

}
