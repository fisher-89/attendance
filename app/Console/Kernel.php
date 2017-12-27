<?php

namespace App\Console;

use App\Console\Commands\CleanUpPhotos;
use App\Console\Commands\CleanUpTmpFiles;
use App\Console\Commands\RefreshAttendance;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        RefreshAttendance::class,
        CleanUpPhotos::class,
        CleanUpTmpFiles::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('file:clean-up-photos')->dailyAt('5:00');
        $schedule->command('file:clean-up-tmp-files')->dailyAt('5:05');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
