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

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $emailExists = DB::table('tbl_useraccounts')->where('email', $email)->exists();

        if ($emailExists) {
            return Redirect::back()->with('error', 'Email already in use');
        } else {
            return $this->store($request);
        }
    }

   /**
 * Send an email with OTP.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return RedirectResponse
 */


 public function store(Request $request)
 {
     $receiver = $request->email;
     $otp = rand(100000, 999999); // Generate a 6-digit OTP

     Session::put('otp', $otp);
     Session::put('otp_email', $receiver);

     try {
         Mail::to($receiver)->send(new OtpMail($otp));

         return redirect()->route('otp.show')->with(['email' => $receiver, 'success' => 'Email has been sent.']);
        } catch (\Exception $e) {
         return back()->with('error', 'Message could not be sent. '.$e->getMessage());
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

        return view('verifyotp', ['email' => $email, 'success' => $successMessage]);    }

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

        // Retrieve the OTP from the session
        $sessionOtp = Session::get('otp');
        $sessionEmail = Session::get('otp_email');

        // Verify the OTP and email
        if ($otp == $sessionOtp && $email == $sessionEmail) {
            // OTP is correct, clear the session and redirect to registration
            Session::forget('otp');
            Session::forget('otp_email');
            return Redirect::to('/register?email=' . $email);
        } else {
            // OTP is incorrect, return with an error message
            return Redirect::back()->withErrors(['otp' => 'Invalid OTP. Please try again.'])->withInput();
        }
    }

    public function cancel(){
        Session::forget('otp');
            Session::forget('otp_email');
            return Redirect::to('/');
    }

}
