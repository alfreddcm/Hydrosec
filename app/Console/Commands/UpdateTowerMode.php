<?php

namespace App\Console\Commands;

use App\Models\Tower;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UpdateTowerMode extends Command
{
    protected $signature = ' ';
    protected $description = 'Update the mode of the Tower model based on the time of day';

    public function __construct()
    {
        parent::__construct();
    }

//     public function handle()
//     {

//         $hour = now()->hour;

//         if ($hour >= 6 && $hour < 18) {
//             $mode = 1;
//         } elseif ($hour >= 18 && $hour < 22) {
//             $mode = 2;
//         } else {
//             $mode = 0;
//         }

//         $encryptedMode = Crypt::encryptString($mode);
//         Tower::query()->update(['mode' => $encryptedMode]);
//         $this->info("Tower mode updated to {$mode}");
        
//         Log::info("Tower mode updated to {$mode} at " . now());

//         $now = Carbon::now();
//         $oneDayLater = $now->copy()->addDay();
//         $daysBefore = 1;

//         

//         
        public function handle()
        {

                

        }
    }
