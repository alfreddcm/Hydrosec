<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('schedule:run', function () {
    // Daytime schedule: every 20 minutes from 6 AM to 6 PM
    Artisan::call('tower:update-mode', [], [
        'cron' => '*/20 6-17 * * *',
    ]);
    Artisan::call('tower:update-mode', [], [
        'cron' => '*/15 18-22 * * *',
    ]);

    Artisan::call('tower:update-mode', [], [
     'cron' => '* * * * *',
    ]);

// Run every 5 hours
// Artisan::call('tower:update-mode', [], [
//     'cron' => '0 */5 * * *',
// ]);



});