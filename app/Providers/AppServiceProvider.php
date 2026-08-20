<?php

namespace App\Providers;

use App\Interfaces\Auth\UserRepositoryInterface;
use App\Interfaces\Category\CategoryRepositoryInterface;
use App\Interfaces\Order\OrderRepositoryInterface;
use App\Interfaces\Product\ProductRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
    }

    public function boot(): void
    {
    }
}
