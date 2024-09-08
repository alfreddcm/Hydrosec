<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorData;
use App\Http\Controllers\ApiController;

Route::middleware('api')->group(function () {
    Route::post('/iba', [SensorData::class, 'storedata'])->name('postdata');
    Route::post('/pump', [ApiController::class, 'pump']);
    Route::post('/get-mode', [ApiController::class, 'getmode']);
});

// Route::post('/iba', [SensorData::class, 'storedata']);


