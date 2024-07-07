<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('/login', function () {
    return view('login');
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

