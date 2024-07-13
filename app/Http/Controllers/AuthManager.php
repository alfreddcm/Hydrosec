<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
Use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;



class AuthManager extends Controller
{

    function login(){

        if (Auth::check()) {
            return redirect(route('ownerdashboard'));
        }

        return view('login');
    }

    function register(){
        if (Auth::check()) {
            return redirect(route('ownerdashboard'));
        }
        return view('register');
    }

    public function loginPost(Request $request) {
            $credentials = $request->validate([
            'username' => 'required|min:8',
            'password' => 'required'
            ]);


        if(Auth::attempt($credentials))
        {
            $request->session()->regenerate();
            return redirect()->route('ownerdashboard')
                ->with('success','You have successfully logged in!');
        }

        return back()->withErrors([
            'username' => 'Your provided credentials do not match in our records.']);
    }

    function registerPost(Request $request) {

        $request->validate([
            'username'=>'required|string|min:8|unique:tbl_useraccounts',
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:tbl_useraccounts',
            'password' => 'required|min:8|confirmed'
        ]);

        User::create([
            'username'=>$request->username,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return redirect()->route('login')
        ->with('success','You have successfully registered & logged in!');

    }
    function index()
    {
        if (!Auth::check()) {

            return redirect(route('index'));
        }
    }

    function logout(){
        Session::flush();
        Auth::logout();

        return redirect(route('index'));

    }

}
