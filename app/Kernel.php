<?php

namespace App\Http;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{

    protected $middleware = [
        // Other middleware
        \App\Http\Middleware\HandleDecryptionErrors::class,
    ];

    protected $routeMiddleware = [
        // ...
        'admin' => \App\Http\Middleware\Admin::class,
        'owner' => \App\Http\Middleware\User::class,
        'worker' => \App\Http\Middleware\Admin::class,
        //'singlesession' => \App\Http\Middleware\Singlesession::class,

    ];

    protected $middlewareGroups = [
        'web' => [
            // ...
            \App\Http\Middleware\Singlesession::class,
            \App\Http\Middleware\SecureHeaders::class,

        ],
        'api' => [

            'throttle:api', 
        ],
    ];

}
