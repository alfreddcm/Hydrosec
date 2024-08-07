<?php

use App\Http\Controllers\AuthManager;
use App\Http\Controllers\PHPMailerController;
use App\Http\Controllers\Owner\OwnerProfile;
use App\Http\Controllers\Admin\admincontroller;


use Illuminate\Support\Facades\Route;


// Guest Routes: Only accessible to unauthenticated users
Route::middleware('guest')->group(function () {
    Route::get('/', function () {return view('index');})->name('index');

    Route::get('/login', [AuthManager::class, 'login'])->name('login');
    Route::post('/login', [AuthManager::class, 'loginPost'])->name('login.post');

    Route::get('/register', [AuthManager::class, 'register'])->name('register');
    Route::post('/register', [AuthManager::class, 'registerPost'])->name('register.post');

    Route::post('/check-email', [PHPMailerController::class, 'checkEmail'])->name('checkEmail');
    Route::post('/send-otp', [PHPMailerController::class, 'store'])->name('store');
    Route::post('/verify-otp', [PHPMailerController::class, 'verifyOtpPost'])->name('verifyOtpPost');
    Route::get('/verifyotp', [PHPMailerController::class, 'verifyOtp'])->name('otp.show');
    Route::get('/cancel', [PHPMailerController::class, 'cancel'])->name('cancel');
    Route::post('/resend-otp', [PHPMailerController::class, 'resendOtp'])->name('resendOtp');

});

// Authenticated Routes: Accessible to authenticated users
Route::get('/logout', [AuthManager::class, 'logout'])->name('logout');

// Routes for Owner with 'auth:owner' middleware
// Route::middleware(['auth:owner', 'singlesession'])->group(function () {

Route::middleware('auth:owner')->group(function () {
    Route::get('/Owner/dashboard', function () {return view('Owner.dashboard');})->name('ownerdashboard');
    Route::get('/Owner/ManageTower', function () {return view('Owner.ManageTower');})->name('ownermanagetower');
    Route::get('/Owner/WorkerAccounts', function () {return view('Owner.WorkerAccounts');})->name('ownerworkeraccount');
    Route::get('/Owner/Manageprofile', function () {return view('Owner.Manageprofile');})->name('ownermanageprofile');
    Route::post('/Owner/Manageprofile', [OwnerProfile::class, 'update'])->name('owner.profile.update');

    Route::get('/Owner/Updatepassword', function () {return view('Owner.Updatepassword');})->name('updatepassword');
    Route::get('/Owner/Addworker', function () {return view('Owner.Addworker');})->name('addworker');

    Route::post('/Owner/Addworker', function () {return view('Owner.Addworker');})->name('addworkerpost');


});

// Routes for Worker with 'auth:worker' middleware
Route::middleware('auth:worker')->group(function () {
    Route::get('/Worker/dashboard', function () {return view('Worker.dashboard');})->name('workerdashboard');
    Route::get('/Worker/Nutrient', function () {return view('Worker.Nutrient');})->name('workernutrient');
});

// Routes for Admin with 'auth:admin' middleware
Route::middleware('auth:admin')->group(function () {
    Route::get('/Admin/Dashboard', function () {return view('Admin.dashboard');})->name('admindashboard');
    Route::get('/Admin/profile', function () {return view('Admin.profile');})->name('adminprofile');
    Route::get('/Admin/UserAccounts', function () {return view('Admin.UserAccounts');})->name('UserAccounts');

    Route::get('/Admin/edit/{id}', [admincontroller::class, 'edit'])->name('admin.edit');
Route::post('/Admin/update/{id}', [admincontroller::class, 'update'])->name('admin.update');
Route::post('/Admin/update-password/{id}', [admincontroller::class, 'OwnerupdatePassword'])->name('admin.updatePassword');


    Route::delete('delete/{id}', [admincontroller::class, 'destroy']);

});
