<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CurlService;

class CurlServiceProvider extends ServiceProvider
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
        //
        $this->app->instance('Curl', new CurlService());
    }
}
