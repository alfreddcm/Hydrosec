<?php

use App\Http\Controllers\Admin\admincontroller;
use App\Http\Controllers\AuthManager;
use App\Http\Controllers\Auth\ResetPassword;
use App\Http\Controllers\Owner\OwnerProfile;
use App\Http\Controllers\PHPMailerController;
use App\Http\Controllers\SensorData;
use App\Http\Controllers\Towercontroller;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('index');
    })->name('index');

    Route::get('/login', [AuthManager::class, 'login'])->name('login');
    Route::post('/login', [AuthManager::class, 'loginPost'])->name('login.post');

    Route::get('/register', [AuthManager::class, 'register'])->name('register');
    Route::post('/register', [AuthManager::class, 'registerPost'])->name('register.post');

    Route::post('/check-email', [PHPMailerController::class, 'checkEmail'])->name('checkEmail');
    Route::post('/send-otp', [PHPMailerController::class, 'store'])->name('store');
    Route::post('/verify-otp', [PHPMailerController::class, 'verifyOtpPost'])->name('verifyOtpPost');
    Route::get('/verifyotp', [PHPMailerController::class, 'verifyOtp'])->name('otp.show');
    Route::get('/cancel', [PHPMailerController::class, 'cancel'])->name('cancel');
    Route::get('/resend-otp', [PHPMailerController::class, 'resendOtp'])->name('resendotp');

    Route::post('/forgot-password', [ResetPassword::class, 'reset'])->name('forgot-password')->middleware('throttle:5,1');;

    Route::get('/verify-otp-forgot', [ResetPassword::class, 'showOtpForgotForm'])->name('verifyotpforgot');
    Route::post('/verify-otp-forgot', [ResetPassword::class, 'verifyOtpForgot'])->name('verifyotpforgotpost');
    Route::get('/reset-password', [ResetPassword::class, 'showResetPasswordForm'])->name('reset-password-form');
    Route::post('/reset-password', [ResetPassword::class, 'resetPassword']);
    Route::get('/resend-otp2', [ResetPassword::class, 'resendOtp'])->name('resendotp2');

});

Route::get('/logout', [AuthManager::class, 'logout'])->name('logout');

// Routes for Owner with 'auth:owner' middleware
// Route::middleware(['auth:owner', 'singlesession'])->group(function () {

Route::middleware('auth:owner')->group(function () {
Route::get('/Owner/dashboard', [Towercontroller::class, 'decryptSensorData'])->name('ownerdashboard');
    Route::get('/Owner/ManageTower', function () { return view('Owner.ManageTower'); })->name('ownermanagetower');
    Route::get('/Owner/WorkerAccounts', function () { return view('Owner.WorkerAccounts'); })->name('ownerworkeraccount');
   
    Route::get('/Owner/Manageprofile', function () { return view('Owner.Manageprofile'); })->name('ownermanageprofile');
    Route::post('/Owner/Manageprofile', [OwnerProfile::class, 'update'])->name('owner.profile.update');

    Route::get('/Owner/Updatepassword', function () { return view('Owner.Updatepassword'); })->name('updatepassword');
    Route::get('/Owner/Addworker', function () { return view('Owner.Addworker'); })->name('addworker');

    Route::post('/Owner/Addworker', function () { return view('Owner.Addworker'); })->name('addworkerpost');
    Route::post('/Owner/Addworker', [OwnerProfile::class, 'addworker'])->name('addownerworkeraccount');

    Route::get('/Owner/edit/{id}', [OwnerProfile::class, 'edit'])->name('ownerworker.edit');
    Route::post('/Owner/update/{id}', [OwnerProfile::class, 'workerupdate'])->name('ownerworker.update');

    Route::post('/Owner/update-password', [OwnerProfile::class, 'workerPassword'])->name('owner.workerupdatePassword');

    Route::post('/Owner/worker-dis/{id}', [OwnerProfile::class, 'workerdis'])->name('ownerworker.dis');
    Route::post('/Owner/worker-en/{id}', [OwnerProfile::class, 'workeren'])->name('ownerworker.en');

    Route::get('/startcycle', [SensorData::class, 'startcycle'])->name('startcycle');

    Route::post('/addtower', [Towercontroller::class, 'store'])->name('posttower');
    Route::get('/towerdata/{id}', function () { return view('Owner.tower'); })->name('towerdata');
    Route::get('/sensor-data/{id}', [SensorData::class, 'getLatestSensorData'])->name('getsensor');
    Route::get('/get-data/{towerId}/{column}', [SensorData::class, 'getdata'])->name('getsensor');
    Route::get('/pump-data/{id}', [SensorData::class, 'getPump']);

    Route::post('/cycle', [Towercontroller::class, 'updateDates'])->name('cycle');
    Route::get('/modestat/{id}', [Towercontroller::class, 'modestat'])->name('modestat');

    Route::post('/tower/stop', [TowerController::class, 'stop'])->name('tower.stop');
    Route::post('/tower/stopdis', [TowerController::class, 'stopdis'])->name('tower.stopdis');
 Route::post('/tower/restart', [TowerController::class, 'restartCycle'])->name('tower.restart');

});

Route::middleware('auth:worker')->group(function () {
    Route::get('/Worker/dashboard', function () { return view('Worker.dashboard'); })->name('workerdashboard');
    Route::get('/Worker/Nutrient', function () { return view('Worker.Nutrient'); })->name('workernutrient');

    Route::get('/Worker/sensor-data/{id}', [SensorData::class, 'getLatestSensorData'])->name('getsensor');
    Route::get('/Worker/get-data/{towerId}/{column}', [SensorData::class, 'getdata'])->name('getsensor');
    Route::get('/Worker/pump-data/{id}', [SensorData::class, 'getPump']);
    Route::get('/Worker/get-data/{towerId}/{column}', [SensorData::class, 'getdata'])->name('getsensor');
    Route::get('/Worker/sensor-data/{id}', [SensorData::class, 'getLatestSensorData'])->name('getsensor');
    Route::get('/Worker/pump-data/{id}', [SensorData::class, 'getPump']);
    Route::get('/Worker/modestat/{id}', [Towercontroller::class, 'modestat'])->name('modestat');

});

Route::middleware('auth:admin')->group(function () {
    Route::get('/Admin/Dashboard', [admincontroller::class, 'showCounts'])->name('admindashboard');
    Route::get('/Admin/profile', function () {
        return view('Admin.profile');
    })->name('adminprofile');
    Route::get('/Admin/UserAccounts', function () {
        return view('Admin.UserAccounts');
    })->name('UserAccounts');

    Route::post('/Admin/UserAccounts', [admincontroller::class, 'addowneraccount'])->name('PUserAccounts');

    Route::get('/Admin/edit/{id}', [admincontroller::class, 'edit'])->name('admin.edit');
    Route::get('/Admin/edit2/{id}', [admincontroller::class, 'edit2'])->name('admin.edit2');

    Route::post('/Admin/update/{id}', [admincontroller::class, 'update'])->name('admin.update');
    Route::post('/Admin/update2/{id}', [admincontroller::class, 'update2'])->name('admin.update2');

    Route::post('/Admin/update-password', [admincontroller::class, 'adminupdatePassword'])->name('admin.updatePassword');
    Route::post('/Admin/update-password2', [admincontroller::class, 'adminupdatePassword2'])->name('admin.updatePassword2');

    Route::post('/Admin/worker-dis/{id}', [admincontroller::class, 'disableOwner'])->name('admin.dis');
    Route::post('/Admin/worker-en/{id}', [admincontroller::class, 'en'])->name('admin.en');

});
