<?php

use Illuminate\Support\Facades\Route;

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

