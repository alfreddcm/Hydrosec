<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Sensor;


Route::get('/sensor/latest', [Sensor::class, 'getLatestSensorData']);
Route::post('/sensor/latest', [Sensor::class, 'postSensorData']);
