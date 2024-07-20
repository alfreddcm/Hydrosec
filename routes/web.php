<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthManager;
use App\Http\Controllers\PHPMailerController;

Route::get('/', function () { return view('index');})->name('index');


Route::get('/login', [AuthManager::class, 'login'])->name('login');
Route::post('/login', [AuthManager::class, 'loginPost'])->name('login.post');

Route::get('/register', [AuthManager::class, 'register'])-> name('register');
Route::post('/register', [AuthManager::class, 'registerPost'])->name('register.post');

Route::get('/logout', [AuthManager::class, 'logout'])->name('logout');



Route::get('/Owner/dashboard', function () { return view('Owner.dashboard');})->name('ownerdashboard')->middleware('auth');
Route::get('/Owner/ManageTower', function () {return view('Owner.ManageTower');})->name('ownermanagetower')->middleware('auth');
Route::get('/Owner/WorkerAccounts', function () {return view('Owner.WorkerAccounts');})->name('ownerworkeraccount')->middleware('auth');

Route::get('/Worker/dashboard', function () {return view('Worker.dashboard');})->name('workerashboard');
Route::get('/Worker/Nutrient', function () {return view('Worker.Nutrient');});

Route::get('/Admin/dashboard', function () {return view('Admin.dashboard');})->name('admindashboard');
Route::get('/Admin/profile', function () {return view('Admin.profile');});


Route::post('/check-email', [PHPMailerController::class, 'checkEmail'])->name('checkEmail');
Route::post('/send-otp', [PHPMailerController::class, 'store'])->name('store');
Route::post('/verify-otp', [PHPMailerController::class, 'verifyOtpPost'])->name('verifyOtpPost');
Route::get('/verifyotp', [PHPMailerController::class, 'verifyOtp'])->name('otp.show');
Route::get('/cancel', [PHPMailerController::class, 'cancel'])->name('cancel');
Route::post('/resend-otp', [PHPMailerController::class, 'resendOtp'])->name('resendOtp');

