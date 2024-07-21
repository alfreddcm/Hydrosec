<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthManager;
use App\Http\Controllers\PHPMailerController;

Route::get('/', function () { return view('index'); })->name('index');

// Guest Routes: Only accessible to unauthenticated users
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthManager::class, 'login'])->name('login');
    Route::post('/login', [AuthManager::class, 'loginPost'])->name('login.post');

    Route::get('/register', [AuthManager::class, 'register'])->name('register');
    Route::post('/register', [AuthManager::class, 'registerPost'])->name('register.post');
});

// Authenticated Routes: Accessible to authenticated users
Route::get('/logout', [AuthManager::class, 'logout'])->name('logout');

// Routes for Owner with 'auth:owner' middleware
Route::middleware('auth:owner')->group(function() {
    Route::get('/Owner/dashboard', function () { return view('Owner.dashboard'); })->name('ownerdashboard');
    Route::get('/Owner/ManageTower', function () { return view('Owner.ManageTower'); })->name('ownermanagetower');
    Route::get('/Owner/WorkerAccounts', function () { return view('Owner.WorkerAccounts'); })->name('ownerworkeraccount');
});

// Routes for Worker with 'auth:worker' middleware
Route::middleware('auth:worker')->group(function() {
    Route::get('/Worker/dashboard', function () { return view('Worker.dashboard'); })->name('workerdashboard');
    Route::get('/Worker/Nutrient', function () { return view('Worker.Nutrient'); })->name('workernutrient');
});

// Routes for Admin with 'auth:admin' middleware
Route::middleware('auth:admin')->group(function() {
    Route::get('/Admin/dashboard', function () { return view('Admin.dashboard'); })->name('admindashboard');
    Route::get('/Admin/profile', function () { return view('Admin.profile'); })->name('adminprofile');
});

// Public routes
Route::post('/check-email', [PHPMailerController::class, 'checkEmail'])->name('checkEmail');
Route::post('/send-otp', [PHPMailerController::class, 'store'])->name('store');
Route::post('/verify-otp', [PHPMailerController::class, 'verifyOtpPost'])->name('verifyOtpPost');
Route::get('/verifyotp', [PHPMailerController::class, 'verifyOtp'])->name('otp.show');
Route::get('/cancel', [PHPMailerController::class, 'cancel'])->name('cancel');
Route::post('/resend-otp', [PHPMailerController::class, 'resendOtp'])->name('resendOtp');
