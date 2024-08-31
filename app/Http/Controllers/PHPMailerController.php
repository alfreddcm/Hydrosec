<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use App\Models\Owner;
use App\Models\Admin;
use App\Models\Worker;

class PHPMailerController extends Controller
{
    /**
     * Show the email form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return View|ViewFactory
     */
    public function index(Request $request)
    {
        return view('sendEmail');
    }

    public function checkEmail(Request $request){
    $email = $request->input('email');
    $users = Owner::all();

    foreach ($users as $user) {
        try {
            $emailStored = Crypt::decryptString($user->email);
            if ($email === $emailStored) {
                if ($user->status == '0') {
                    return Redirect::back()->with('deact', 'Account is deactivated. Contact support.');
                } elseif ($user->status == '1') {
                    return Redirect::back()->with('error', 'Email already in use');
                }
            }
        } catch (DecryptException $e) {
            return Redirect::back()->with('error', 'Invalid encryption key. Please contact support.');
        }
    }
    return $this->store($request);
}



    /**
     * Send an email with OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse
     */


    public function store(Request $request)
    {

        //ranbytes
        $receiver = $request->email;
        $otp = random_int(100000, 999999);

        Session::put('otp', $otp);
        Session::put('otp_email', $receiver);

        try {
            Mail::to($receiver)->send(new OtpMail($otp));

            return redirect()->route('otp.show')->with(['email' => $receiver, 'success' => 'OTP has been sent to your email.']);
        } catch (TransportExceptionInterface $e) {
            return back()->with('error', 'Failed to connect to the SMTP server. Please try again later.');
        } catch (\Exception $e) {
            return back()->with('error', 'Message could not be sent. Please try again later.');
        }
    }


    /**
     * Show the OTP verification form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return View|ViewFactory
     */
    public function verifyOtp(Request $request)
    {

        $email = $request->session()->get('otp_email');
        $successMessage = $request->session()->get('success');

        return view('verifyotp', ['email' => $email, 'success' => $successMessage]);
    }

    /**
     * Verify the OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse
     */
    public function verifyOtpPost(Request $request)
    {
        $otp = $request->input('otp');
        $email = $request->input('email');

        $sessionOtp = Session::get('otp');
        $sessionEmail = Session::get('otp_email');

        if ($otp == $sessionOtp && $email == $sessionEmail) {
            Session::forget('otp');
            Session::forget('otp_email');
            return Redirect::to('/register?email=' . $email);
        } else {
            return Redirect::back()->with('error', 'Invalid OTP. Please try again.')->withInput();
        }
    }

    public function resendOtp()
    {
        $otp = Session::get('otp');
        $receiver = Session::get('otp_email');

        try {
            Mail::to($receiver)->send(new OtpMail($otp));

            return back()->with([
                'email' => $receiver,
                'success' => 'OTP has been sent.'
            ]);
        } catch (TransportExceptionInterface $e) {
            return back()->with('error', 'Failed to connect to the SMTP server. Please try again later.');
        } catch (\Exception $e) {
            return back()->with('error', 'Message could not be sent. Please try again later.');
        }
    }

    public function cancel()
    {
        Session::forget('otp');
        Session::forget('otp_email');
        return Redirect::to('/');
    }

}