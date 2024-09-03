<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\UpdateTowerMode;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            UpdateTowerMode::class,
        ]);
    }
}
