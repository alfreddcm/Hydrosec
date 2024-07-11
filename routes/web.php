<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthManager;

Route::get('/', function () { return view('index');});


Route::get('/login', [AuthManager::class, 'login'])->name('login');
Route::post('/login', [AuthManager::class, 'loginPost'])->name('login.post');

Route::get('/register', [AuthManager::class, 'register'])-> name('register');
Route::post('/register', [AuthManager::class, 'registerPost'])->name('register.post');

Route::get('/logout', [AuthManager::class, 'logout'])->name('logout');



Route::get('/Owner/dashboard', function () { return view('Owner.dashboard');});
Route::get('/Owner/ManageTower', function () {return view('Owner.ManageTower');});
Route::get('/Owner/WorkerAccounts', function () {return view('Owner.WorkerAccounts');});

Route::get('/Worker/dashboard', function () {return view('Worker.dashboard');});
Route::get('/Worker/Nutrient', function () {return view('Worker.Nutrient');});

Route::get('/Admin/dashboard', function () {return view('Admin.dashboard');});
Route::get('/Admin/profile', function () {return view('Admin.profile');});


//check email
Route::post('/check-email', function (Request $request) {
    $email = $request->input('email');
    $emailExists = DB::table('tbl_useraccount')->where('email', $email)->exists();

    // dd('Email check result for ' . $email . ': ' . ($emailExists ? 'exists' : 'does not exist'));

    if ($emailExists) { return Redirect::back()->with('error', 'Email already in use');
    } else { return Redirect::to('/register?email=' . $email);
    }})->name('checkEmail');
