<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use App\Models\User;


class checkemail extends Controller
{
    function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $emailExists = User::where('email', $email)->exists();

        if ($emailExists) {
            return redirect('/')->with('error', 'Email already in use');
        }

        return redirect()->route('register', ['email' => $email]);
    }
}
