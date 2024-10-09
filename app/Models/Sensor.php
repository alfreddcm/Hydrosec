<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class Sensor extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'towerid',
        'sensordata'
    ];

    // Optionally, specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'sensor';

    // If you want to handle timestamps manually or use a different format
    public $timestamps = true;
}
