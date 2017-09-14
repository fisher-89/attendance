<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('OA', function ($app) {
            return new \App\Services\OAService;
        });
        $this->app->bind('Clock', function () {
            return new \App\Services\ClockService;
        });
        $this->app->bind('DingTalk', function () {
            return new \App\Services\DingTalk\DingTalkClient;
        });
    }
}
