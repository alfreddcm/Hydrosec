<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
Use App\Models\User;
use Illuminate\Support\Facades\Http;


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

    function registerPost(Request $request){
        $request->validate([
            'email'=>'required|email|unique:tbl_accounts',
            'username'=>'required',
            'fullname'=>'required',
            'password'=>'required'

        ]);

        $data['email']=$request->email;
        $data['username']=$request->username;
        $data['fullname']=$request->fullname;
        $data['password']=$request->password;

        $user=User::create($data);
        if(!$user){
            return redirect(route('/register'))->with('error','Registration failed. Try again');

        }
        return redirect(route('/login'))->with('success','Registration succesfull');
    }

    function logout(){
        Session::flush();
        Auth::logout();

        return redirect(route('/'));

    }

}
