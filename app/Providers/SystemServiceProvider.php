<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
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
        $this->app->bind('Curl', \App\Services\CurlService::class);
        $this->app->singleton('CurrentUser', \App\Services\CurrentUserService::class);
    }
}
