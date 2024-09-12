<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\Alert;
use App\Mail\OtpMail;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ResetPassword extends Controller
{

    public function reset(Request $request)
    {
        try{
        Log::info('Reset process started.', ['email' => $request->email]);
        $request->validate([
            'email' => 'required|email',
        ]);

        $inputEmail = $request->email;

        $users = Owner::all();

        foreach ($users as $user) {
            $decryptedEmail = Crypt::decryptString($user->email);

            if ($decryptedEmail === $inputEmail) {
                Log::info('Email matched with a user.', ['email' => $decryptedEmail]);

                $receiver = $request->email;
                $otp = random_int(100000, 999999);

                Session::put('otp', $otp);
                Session::put('otp_email', $receiver);

                try {
                    Mail::to($receiver)->send(new OtpMail($otp));
                    Log::info('OTP sent successfully.', ['email' => $receiver]);

                    return redirect()->route('verifyotpforgot')->with([
                        'email' => $inputEmail,
                        'success' => 'OTP has been sent to your email.',
                    ]);

                } catch (TransportExceptionInterface $e) {
                    Log::error('SMTP server connection failed.', ['email' => $receiver, 'exception' => $e->getMessage()]);
                    return back()->with('error', 'Failed to connect to the SMTP server. Please try again later.');
                } catch (\Exception $e) {
                    Log::error('Failed to send email.', ['email' => $receiver, 'exception' => $e->getMessage()]);
                    return back()->with('error', 'Message could not be sent. Please try again later.');
                }
            }
        }

        Log::warning('No user found with the provided email.', ['email' => $inputEmail]);
        return back()->with('error', 'User not found.');
    }catch (ThrottleRequestsException $e) {
        Log::warning('Too many OTP requests.', ['email' => $request->email]);
        return back()->with('error', 'Too many requests. Please wait a while before trying again.');
    }
}
    public function showOtpForgotForm()
    {
        return view('Reset.verifyotp');

    }

    public function verifyOtpForgot(Request $request)
    {
        $request->validate([
            'otp' => 'required|integer',
        ]);

        $inputOtp = $request->otp;
        $sessionOtp = $request->session()->get('otp');
        $email = $request->session()->get('otp_email');

        if ($inputOtp == $sessionOtp) {
            // OTP is valid, show password reset form
            return redirect()->route('reset-password-form')->with('email', $email);
        }

        return back()->with('error', 'Invalid OTP. Please try again.');
    }

    public function showResetPasswordForm()
    {
        return view('Reset.newpass');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => [
        'required',
        'string',
        'min:8', 
        'regex:/[a-z]/', 
        'regex:/[A-Z]/', 
        'regex:/[0-9]/',
        'regex:/[@$!%*?&#]/', 
        'confirmed',
    ],
        ]);

        $email = $request->session()->get('otp_email');

        $users = Owner::all();

        foreach ($users as $user) {
            $decryptedEmail = Crypt::decryptString($user->email);

            if ($decryptedEmail === $email) {
                $user->password = Hash::make($request->password);
                $user->save();

                $body = "Dear " . Crypt::decryptString($user->name) . " , Username: " . Crypt::decryptString($user->username) . ", your password has been changed to: " . $request->password;

                $details = [
                    'title' => 'Alert: Password Change',
                    'body' => $body,
                ];

                Mail::to($decryptedEmail)->send(new Alert($details));

                $request->session()->forget(['otp', 'otp_email']);
                return redirect()->route('login')->with('success', 'Password has been reset successfully.');
            }
        }

    }

}
