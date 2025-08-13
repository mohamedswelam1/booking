<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware aliases.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'role' => \App\Http\Middleware\EnsureRole::class,
        'throttle' => \App\Http\Middleware\CustomThrottleRequests::class,
    ];
}


