<?php

use App\Helper\ApiErrorResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

/*
| Positional args (not named) so PHP 7.4 IDE parsers don't choke.
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
        $exceptions->shouldRenderJsonWhen(function (Request $request, $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            return ApiErrorResponse::fromThrowable($e, $request);
        });
    })->create();
