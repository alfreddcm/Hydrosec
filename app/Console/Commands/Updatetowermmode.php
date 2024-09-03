<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use App\Models\Tower;
use Illuminate\Support\Facades\Log;



class UpdateTowerMode extends Command
{
    protected $signature = 'tower:update-mode';
    protected $description = 'Update the mode of the Tower model based on the time of day';

    public function __construct()
    {
        parent::__construct();
    }

    // public function handle()
    // {
    //     $hour = now()->hour;
    //     $mode = ($hour >= 6 && $hour < 18) ? 0 : 1;

    //     // Encrypt the mode value
    //     $encryptedMode = Crypt::encryptString($mode);

    //     // Update all records in the Tower model
    //     Tower::query()->update(['mode' => $encryptedMode]);

    //     $this->info("Tower mode updated to {$mode}");
    //     Log::info("Tower mode updated to {$mode} at " . now());

    // }

    public function handle()
    {

            Log::info('Test scheduled command ran at ' . now());
            $this->info('Test scheduled command ran');

    }
}
