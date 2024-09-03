<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('schedule:run', function () {
    // Daytime schedule: every 20 minutes from 6 AM to 6 PM
    Artisan::call('tower:update-mode', [], [
        'cron' => '*/20 6-17 * * *',
    ]);

    // Nighttime schedule: every 15 minutes from 6 PM to 10 PM
    Artisan::call('tower:update-mode', [], [
        'cron' => '*/15 18-22 * * *',
    ]);
// php artisan schedule:work
});