<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntrusionDetection extends Model
{
    use HasFactory;

        protected $table = 'intrutbl'; // Specify the table name

    protected $fillable = [
        'ip_address',
        'user_agent',
        'failed_attempts',
        'detected_at',
    ];
}
