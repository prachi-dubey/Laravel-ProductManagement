<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
| Positional args (not named) so PHP 7.4 IDE parsers don't choke.
| Runtime still uses XAMPP PHP 8.2 via ./bin/artisan.
|
| withRouting($using, $web, $api, $commands, $channels, $pages, $health)
*/
return Application::configure(dirname(__DIR__))
    ->withRouting(
        null,
        __DIR__.'/../routes/web.php',
        __DIR__.'/../routes/api.php',
        __DIR__.'/../routes/console.php',
        null,
        null,
        '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
