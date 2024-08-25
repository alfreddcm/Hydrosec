<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    protected $fillable = [
        'towerid',
        'pH',
        'temperature',
        'nutrientlevel',
        'iv',
        'status',
    ];

    // Optionally, specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'sensor';

    // If you want to handle timestamps manually or use a different format
    public $timestamps = true;
}
