<?php

namespace App\Console\Commands;

use App\Repositories\AttendanceRepositories;
use Illuminate\Console\Command;

class RefreshAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:refresh {--date=} {--shop_sn=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate Attendance Time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Refreshing');
        $date = $this->option('date');
        $shopSn = $this->option('shop_sn');
        $shop = app('OA')->withoutPassport()->getDataFromApi('get_shop', ['shop_sn' => $shopSn])['message'][0];
        $this->info('Date:' . $date . ';ShopSn:' . $shopSn . ';ShopName:' . $shop['name']);
        app('AttendanceRepos', ['date' => $date, 'shop' => $shop])->refreshAttendanceForm(null, true);
        $this->info('Refresh Success');
    }
}
