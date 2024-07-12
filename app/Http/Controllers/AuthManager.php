<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
Use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;


class AuthManager extends Controller
{

    function login(){
        return view('login');
    }

    function register(){
        return view('register');
    }

    public function loginPost(Request $request) {
        // Validate the form input
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'g-recaptcha-response' => 'required'
        ]);

        // Verify the reCAPTCHA response
        $recaptchaResponse = $request->input('g-recaptcha-response');
        $recaptchaSecret = env('RECAPTCHA_SECRET_KEY'); // Ensure you have this in your .env file
        $recaptcha = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $recaptchaSecret,
            'response' => $recaptchaResponse,
        ]);

        if (!$recaptcha->json()['success']) {
            return redirect()->route('login')->with('error', 'ReCAPTCHA verification failed. Please try again.');
        }

        $credentials = $request->only('username', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->route('login')->with('error', 'Login details are invalid');
    }

    function registerPost(Request $request) {
        $request->validate([
            'fullname' => 'required',
            'username' => 'required',
            'email' => 'required|email|unique:tbl_useraccount',
            'password' => 'required|min:8|confirmed',
        ]);

        // Additional password complexity check
        $password = $request->password;
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            return redirect(route('register'))->with('error', 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.');
        }

        $data = [
            'fullname' => $request->fullname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $password,
        ];

        try {
            $user = User::create($data);

        } catch (\Exception $e) {
            return redirect(route('register'))->with('error', 'An error occurred: ' . $e->getMessage());
        }

        if (!$user) {
            return redirect(route('register'))->with('error', 'Registration failed. Try again');
        }

        return redirect(route('login'))->with('success', 'Registration successful');

    }

    function logout(){
        Session::flush();
        Auth::logout();

        return redirect(route('/'));

    }

}
