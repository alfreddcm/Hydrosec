<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorData;

// Route::get('/sensor/latest', [SensorData::class, 'getLatestSensorData']);
Route::post('/api', [SensorData::class, 'store'])->name('postdata');
