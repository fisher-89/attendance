<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoriesProvider extends ServiceProvider
{
    /**
     * 服务提供者加是否延迟加载.
     *
     * @var bool
     */
    protected $defer = true;

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
        $this->app->singleton('AttendanceRepos', \App\Repositories\AttendanceRepositories::class);
    }

    /**
     * 获取由提供者提供的服务.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'ClockRepos',
            'TransferRepos',
            'LeaveRepos',
            'AttendanceRepos',
        ];
    }
}
