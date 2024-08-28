<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorData;


Route::middleware('api')->group(function () {
    Route::post('/iba', [SensorData::class, 'storedata'])->name('postdata');
});
// Route::post('/iba', [SensorData::class, 'storedata']);
