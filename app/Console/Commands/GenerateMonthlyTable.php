<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:copy-table {--month=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy attendance_staff and clock table';

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
        $curMonth = $this->option('month') ?: date('Ym');
        $nextMonth = date('Ym', strtotime($curMonth . ' +1 month'));
        try {
            DB::statement('create table attendance_staff_' . $nextMonth . ' like attendance_staff_' . $curMonth);
            $this->info('attendance_staff_' . $nextMonth . ' created');
            DB::statement('create table clock_' . $nextMonth . ' like clock_' . $curMonth);
            $this->info('clock_' . $nextMonth . ' created');
        } catch (QueryException $exception) {
            Log::error($exception->getMessage());
        }
    }
}
