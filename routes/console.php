<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('tower:update-mode')->everyTenSeconds();
Schedule::command('app:harvestremainder')->everyTenSeconds();
Schedule::command('app:pumpreminder')->everyTenSeconds();
