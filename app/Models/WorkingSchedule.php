<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingSchedule extends Model
{
    protected $table = 'working_schedule_';

    public function __construct()
    {
        $this->table .= app('Clock')->getAttendanceDate('Ymd');
    }

    /* 关联 Start */

    public function shopDuty()
    {
        return $this->belongsTo('App\Models\ShopDuty', 'shop_duty_id');
    }

    /* 关联 End */
}
