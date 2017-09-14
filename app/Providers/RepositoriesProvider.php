<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoriesProvider extends ServiceProvider
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
        $this->app->singleton('ClockRepos', \App\Repositories\SignRepositories::class);
        $this->app->singleton('TransferRepos', \App\Repositories\TransferRepositories::class);
        $this->app->singleton('LeaveRepos', \App\Repositories\LeaveRepositories::class);
        $this->app->singleton('AttendanceRepos',\App\Repositories\AttendanceRepositories::class);
    }
}
