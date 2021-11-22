<?php

namespace DSCribe;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use DScribe\Database;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Database::class,
            ]);
        }
    }
}
