<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorDataHistory extends Model
{
    protected $table = 'sensor_data_history';

    protected $fillable = [
        'towerid',
        'OwnerID',
        'sensor_data',
        'created_at',
    ];

    public $timestamps = false;
}
