<?php

namespace App\Providers;

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ProductRepository;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind interfaces → Eloquent implementations (easy to swap / mock in tests)
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
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
