<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Owner;
use App\Models\Worker;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

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
        // Throttle the login attempts based on the username and IP address
        $maxAttempts = 5; // Max attempts before lockout
        $decayMinutes = 15; // Lockout time in minutes
        $throttleKey = strtolower($request->input('username')) . '|' . $request->ip();

        // Check if the user has exceeded the max login attempts
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'username' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
            ]);
        }

        //Validate CAPTCHA
        $request->validate([
            'g-recaptcha-response' => 'required',
        ]);

        $recaptchaResponse = $request->input('g-recaptcha-response');
        $recaptchaSecret = env('RECAPTCHA_SECRET_KEY');

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $recaptchaSecret,
            'response' => $recaptchaResponse,
        ]);

        $responseBody = $response->json();

        if (!$responseBody['success']) {
            return Redirect::back()->with('error', 'CAPTCHA verification failed.');
        }

        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = $credentials['username'];
        $password = $credentials['password'];

        // Check Worker credentials
        $workers = Worker::all();
        foreach ($workers as $user) {
            try {
                if (Crypt::decryptString($user->status) == '1') {
                    $storedUsername = Crypt::decryptString($user->username);
                    $storedPassword = $user->password;

                    if ($username === $storedUsername && Hash::check($password, $storedPassword)) {
                        Auth::guard('worker')->loginUsingId($user->id);
                        $request->session()->regenerate();
                        RateLimiter::clear($throttleKey);

                        return redirect()->route('workerdashboard')->with('success', 'You have successfully logged in as Worker!');
                    }
                    RateLimiter::hit($throttleKey);

                } else {
                    $storedUsername = Crypt::decryptString($user->username);
                    $storedPassword = $user->password;
                    if ($username === $storedUsername && Hash::check($password, $storedPassword)) {

                        return Redirect::back()->with('error', 'Account is not enable. Contact admin.');

                    }
                }
            } catch (DecryptException $e) {
                return Redirect::back()->with('error', 'Invalid encryption key. Please contact support.');
            }
        }

        // Check Admin credentials
        $admins = Admin::all();
        foreach ($admins as $user) {
            try {
                if (Crypt::decryptString($user->status) == '1') {
                    $storedUsername = Crypt::decryptString($user->username);
                    $storedPassword = $user->password;

                    if ($username === $storedUsername && Hash::check($password, $storedPassword)) {
                        Auth::guard('admin')->loginUsingId($user->id);
                        $request->session()->regenerate();
                        RateLimiter::clear($throttleKey);

                        return redirect()->route('admindashboard')->with('success', 'You have successfully logged in as Admin!');
                    }
                    RateLimiter::hit($throttleKey);

                } else {
                    $storedUsername = Crypt::decryptString($user->username);
                    $storedPassword = $user->password;

                    if ($username === $storedUsername && Hash::check($password, $storedPassword)) {

                        return Redirect::back()->with('error', 'Account is not enable. Contact admin.');

                    }
                }
            } catch (DecryptException $e) {
                return Redirect::back()->with('error', 'Invalid encryption key. Please contact support.');
            }
        }

        // Check Owner credentials
        $owners = Owner::all();
        foreach ($owners as $user) {
            try {
                if (Crypt::decryptString($user->status) == '1') {
                    $storedUsername = Crypt::decryptString($user->username);
                    $storedPassword = $user->password;

                    if ($username === $storedUsername && Hash::check($password, $storedPassword)) {
                        Auth::guard('owner')->loginUsingId($user->id);
                        $request->session()->regenerate();
                        RateLimiter::clear($throttleKey);

                        return redirect()->route('ownerdashboard')->with('success', 'You have successfully logged in as Owner!');
                    }
                    RateLimiter::hit($throttleKey);
                } else {
                    $storedUsername = Crypt::decryptString($user->username);
                    $storedPassword = $user->password;

                    if ($username === $storedUsername && Hash::check($password, $storedPassword)) {

                        return Redirect::back()->with('error', 'Account is not enable. Contact admin.');

                    }
                }
            } catch (DecryptException $e) {
                return Redirect::back()->with('error', 'Invalid encryption key. Please contact support.');
            }
        }

        return back()->withErrors([
            'username' => 'Your provided credentials do not match our records.',
            'password' => 'The password you entered is incorrect.',
        ]);
    }

    public function registerPost(Request $request)
    {

        $request->validate([
            'username' => 'required|string|max:255',
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
                'confirmed',
            ], [
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#).',
                'password.confirmed' => 'Password confirmation does not match.',
            ],
        ]);

        $username = $request->username;
        $usernameExists = $this->checkUsername('username', $username);

        if ($usernameExists) {

            $usernamedeact = $this->checkUsernamedect('username', $username);

            if ($usernamedeact) {
                return back()->withErrors(['react' => 'User has been deactivated. To reactivate click ']);

            } else {
                return back()->withErrors(['username' => 'The username has already been taken.']);
            }

        }

        Owner::create([
            'username' => Crypt::encryptString($username),
            'name' => Crypt::encryptString($request->name),
            'email' => Crypt::encryptString($request->email),
            'password' => Hash::make($request->password),
            'status' => Crypt::encryptString('1'),
        ]);

        return redirect()->route('login')
            ->with('success', 'You have successfully registered please logged in!');

    }

    public function logout()
    {
        Session::flush();
        Auth::logout();

        return redirect(route('index'));

    }

    public function checkUsername($field, $value, )
    {
        {
            // Check in Owner table, excluding the current user
            $ownerCheck = Owner::where('status', '1')->get()->filter(function ($owner) use ($field, $value) {
                return Crypt::decryptString($owner->$field) === $value;
            })->isNotEmpty();

            $adminCheck = Admin::where('status', '1')->get()->filter(function ($admin) use ($field, $value) {
                return Crypt::decryptString($admin->$field) === $value;
            })->isNotEmpty();

            $workerCheck = Worker::where('status', '1')->get()->filter(function ($worker) use ($field, $value) {
                return Crypt::decryptString($worker->$field) === $value;
            })->isNotEmpty();

            return $ownerCheck || $workerCheck || $adminCheck;
        }

    }
    public function checkUsernamedect($field, $value)
    {
        $ownerCheck = Owner::get()->contains(function ($owner) use ($field, $value) {
            return Crypt::decryptString($owner->status) === '0' &&
            Crypt::decryptString($owner->$field) === $value;
        });

        $adminCheck = Admin::get()->contains(function ($admin) use ($field, $value) {
            return Crypt::decryptString($admin->status) === '0' &&
            Crypt::decryptString($admin->$field) === $value;
        });

        $workerCheck = Worker::get()->contains(function ($worker) use ($field, $value) {
            return Crypt::decryptString($worker->status) === '0' &&
            Crypt::decryptString($worker->$field) === $value;
        });

        return $ownerCheck || $adminCheck || $workerCheck;
    }

}

// $subject=table::where('scuriculim','1','strand_id','1')->get();
// foreach($subjec as $data){
//     $subject=subject::where('id',$data->sucjectid)->get();

//     <select>
//     <option value='{{$data->subject-id}}'>{{$subject->name}}</option>
//     </select>
// }
