<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use App\Services\CrudService;
class CrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Crud', function ($app) {
            return new \App\Services\CrudService;
        });

        // $this->app->bind('CrudSe', function ($app) {
        //     return new \App\Services\CrudService;
        // });

        // $this->app->instance('CrudSe', new CrudService());
    }
}
