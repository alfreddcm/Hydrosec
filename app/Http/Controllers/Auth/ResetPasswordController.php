<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    // Show the reset password form
    public function showResetForm() {
        return view('auth.reset-password');
    }

    // Handle the password reset
    public function reset(Request $request) {
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::where('email', session('email'))->first();
        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        // Update password and clear OTP
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('status', 'Password has been reset successfully.');
    }
}
