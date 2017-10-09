<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'attendance_shop';
    protected $fillable = [
        'shop_sn',
        'shop_name',
        'manager_sn',
        'manager_name',
        'attendance_date',
        'sales_performance_lisha',
        'sales_performance_go',
        'sales_performance_group',
        'sales_performance_partner',
        'attachment',
        'status',
        'submitted_at',
        'is_missing',
        'is_late',
        'is_early_out',
    ];

    /* 定义关联 Start */

    public function detail()
    {
        $ym = date('Ym', strtotime($this->attendance_date));
        return $this->hasMany(new \App\Models\AttendanceStaff(['ym' => $ym]), 'attendance_shop_id')
            ->with('shop_duty')->orderBy('shop_duty_id', 'desc');
    }

    /* 定义关联 End */

}
