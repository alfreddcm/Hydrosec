<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TowerLogs extends Model
{ 
    use HasFactory;

    protected $table = 'tbl_towerlogs';

    protected $fillable = [
        'ID_tower',
        'activity',
    ];
}
