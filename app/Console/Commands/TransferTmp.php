<?php

namespace App\Console\Commands;

use App\Models\Transfer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TransferTmp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:transfer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto transfer,use for template.';

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
        Log::info('daily:transfer');
        Transfer::where(['status' => 0, 'leaving_date' => date('Y-m-d')])->each(function ($model) {
            $response = app('OA')->withoutPassport()->getDataFromApi('hr/staff_update', [
                'staff_sn' => $model->staff_sn,
                'shop_sn' => $model->arriving_shop_sn,
            ]);
            if ($response['status'] == 1) {
                if ($model->arriving_shop_duty_id == 1) {
                    app('OA')->withoutPassport()->getDataFromApi('hr/shop_update', [
                        'shop_sn' => $model->arriving_shop_sn,
                        'manager_sn' => $model->staff_sn,
                        'manager_name' => $model->staff_name,
                    ]);
                }
                $model->status = 2;
                $model->left_at = date('Y-m-d H:i:s');
                $model->arrived_at = date('Y-m-d H:i:s');
                $model->save();
            } else {
                Log::info('Update staff info fail');
                $this->info('bug');
            }
        });
    }
}
