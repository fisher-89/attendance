<?php

namespace App\Models;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Model;

class AttendanceStaff extends Model
{

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'attendance_staff_';
    protected $fillable = [
        'attendance_shop_id',
        'staff_sn',
        'staff_name',
        'shop_duty_id',
        'sales_performance_lisha',
        'sales_performance_go',
        'sales_performance_group',
        'sales_performance_partner',
        'working_days',
        'working_hours',
        'leaving_days',
        'leaving_hours',
        'transferring_days',
        'transferring_hours',
        'is_missing',
        'late_time',
        'early_out_time',
        'over_time',
        'is_leaving',
        'is_transferring',
        'clock_log',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $ym = array_has($attributes, 'ym') ? $attributes['ym'] : app('Clock')->getAttendanceDate('Ym');
        $this->table .= $ym;
    }

    /* 定义关联 Start */

    public function shop_duty()
    {
        return $this->belongsTo('App\Models\ShopDuty', 'shop_duty_id');
    }

    /* 定义关联 End */

    /* 访问器 Start */

    public function getClockInAttribute($value)
    {
        $timestamp = strtotime($value);
        return date('H:i', $timestamp);
    }

    public function getClockOutAttribute($value)
    {
        $timestamp = strtotime($value);
        return date('H:i', $timestamp);
    }

    /* 访问器 End */

}
