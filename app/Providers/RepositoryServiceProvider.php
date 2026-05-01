<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\AccountRepositoryInterface::class,
            \App\Repositories\AccountRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TransferRepositoryInterface::class,
            \App\Repositories\TransferRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TransactionRepositoryInterface::class,
            \App\Repositories\TransactionRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
