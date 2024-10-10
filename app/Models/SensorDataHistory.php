<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorDataHistory extends Model
{
    protected $table = 'sensor_data_history';

    protected $fillable = [
        'towerid',
        'OwnerID',
                'plantVar',

        'sensor_data',
        'pump',
    ];

    public $timestamps = false;
}
