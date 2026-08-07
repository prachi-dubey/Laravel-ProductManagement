<?php

namespace App\Providers;

use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         | XAMPP ships php-8.2.4 beside php-7.4, but they share etc/php.ini
         | which still has track_errors (invalid on PHP 8+).
         | Our wrappers set PHPRC to shop-api/.php — artisan serve must
         | forward that env to the child PHP process or it fatals.
         */
        if (! in_array('PHPRC', ServeCommand::$passthroughVariables, true)) {
            ServeCommand::$passthroughVariables[] = 'PHPRC';
        }
    }
}
