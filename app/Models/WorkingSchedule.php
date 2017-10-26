<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingSchedule extends Model
{

    protected $table = 'working_schedule_';
    protected $primaryKey = ['shop_sn', 'staff_sn'];
    public $incrementing = false;
    protected $fillable = [
        'shop_sn',
        'staff_sn',
        'staff_name',
        'shop_duty_id',
        'clock_in',
        'clock_out',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $ymd = array_has($attributes, 'ymd') ? $attributes['ymd'] : app('Clock')->getAttendanceDate('Ymd');
        $this->table .= $ymd;
    }

    /* 关联 Start */

    public function shopDuty()
    {
        return $this->belongsTo('App\Models\ShopDuty', 'shop_duty_id');
    }

    /* 关联 End */
}
