<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingSchedule extends Model
{
    protected $table = 'working_schedule_';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $ymd = array_has($attributes, 'ym') ? $attributes['ymd'] : app('Clock')->getAttendanceDate('Ymd');
        $this->table .= $ymd;
    }

    /* 关联 Start */

    public function shopDuty()
    {
        return $this->belongsTo('App\Models\ShopDuty', 'shop_duty_id');
    }

    /* 关联 End */
}
