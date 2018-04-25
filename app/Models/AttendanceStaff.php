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
        'working_start_at',
        'working_end_at',
        'staff_position_id',
        'staff_position',
        'staff_position_level',
        'staff_department_id',
        'staff_department',
        'staff_status_id',
        'staff_status',
        'is_assistor',
        'is_shift',
        'shop_sn',
        'shop_name',
        'manager_sn',
        'attendance_date',
        'department_id',
        'status',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $ym = array_has($attributes, 'ym') ? $attributes['ym'] : app('Clock')->getAttendanceDate('Ym');
        $this->setMonth($ym);
    }

    /* 定义关联 Start */

    public function shop_duty()
    {
        return $this->belongsTo('App\Models\ShopDuty', 'shop_duty_id');
    }

    /* 定义关联 End */

    /* 自定义方法 Start */

    public function setMonth($month)
    {
        if (!preg_match('/^\d{6}$/', $month)) {
            $month = date('Ym', strtotime($month));
        }
        $this->setTable('attendance_staff_' . $month);
        return $this;
    }

    /* 自定义方法 End */

}
