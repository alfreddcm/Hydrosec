<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Sensor;

class SensorController extends Controller
{
    public function getLatestSensorData()
    {
        // Fetch the latest sensor data (the last inserted record)
        $latestSensorData = Sensor::orderBy('id', 'desc')->first();

        // Return the sensor data as JSON
        return response()->json($latestSensorData);
    }

    public function postSensordata(Request $request){
        

    }
}