<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('index');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/register', function () {
    return view('register');
});

Route::get('/Owner/dashboard', function () {
    return view('Owner.dashboard');
});

Route::get('/Owner/ManageTower', function () {
    return view('Owner.ManageTower');
});

Route::get('/Owner/WorkerAccounts', function () {
    return view('Owner.WorkerAccounts');
});

Route::get('/Worker/dashboard', function () {
    return view('Worker.dashboard');
});

Route::get('/Worker/Nutrient', function () {
    return view('Worker.Nutrient');
});

Route::get('/Admin/dashboard', function () {
    return view('Admin.dashboard');
});

Route::get('/Admin/profile', function () {
    return view('Admin.profile');
});



//

Route::post('/check-email', function (Request $request) {
    $email = $request->input('email');
    $emailExists = DB::table('tbl_account')->where('email', $email)->exists();

    // dd('Email check result for ' . $email . ': ' . ($emailExists ? 'exists' : 'does not exist'));

    if ($emailExists) {
        return Redirect::back()->with('error', 'Email already in use');

    } else {
        return Redirect::to('/register?email=' . $email);
    }
})->name('checkEmail');
