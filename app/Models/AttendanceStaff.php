<?php

namespace App\Models;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Model;

class AttendanceStaff extends Model
{

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table    = 'attendance_staff';
    protected $fillable = [
        'staff_sn',
        'staff_name',
        'attendance_shop_id',
        'sales_performance',
        'clock_in',
        'clock_out',
    ];

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

    // 员工业绩求合
    public static function statistic()
    {
        $firstDay = date('Y-m-01', time());
        $lastDay  = date('Y-m-d', strtotime(date('Y-m-01', time()) . ' +1 month'));
        // return $this->holiday();
        $resArr = self::where([['submit_time', '>', $firstDay], ['submit_time', '<', $lastDay]])->select(\DB::raw('sum(achievement+cooperate_money+goods_money) as achievement'), 'staff_sn', 'staff_name')->groupBy('staff_sn')->get()->toArray();

        // return static::staff_holiday($resArr);
        return $resArr;

        // return \DB::table('attendance_staff')->select(\DB::raw('sum(achievement) as achievement'),'staff_sn','staff_name')->groupBy('staff_sn')->get()->toArray();
    }

    //获取员工请假数据
    public static function staff_holiday()
    {
        $resData  = [];
        $firstDay = date('Y-m-01', time());
        $lastDay  = date('Y-m-d', strtotime(date('Y-m-01', time()) . ' +1 month'));
        return Holiday::where([['subject_result', 1], ['start_time', '>', $firstDay], ['start_time', '<', $lastDay], ['end_time', '>', $firstDay]])->select('sponsor', 'start_time', 'end_time')->get()->toArray();

        return $arrData;
    }

    // public function holiday(){
    //     return $this->belongsToMany('App\Models\Holiday','attendance_holiday','staff_sn','id');
    // }

    // 统计打卡
    public static function updata($params)
    {
        $obj = static::find($params['id']);
        if (!$obj) {
            return ['msg' => 'errs'];
        }
        $obj->achievement     = $params['achievement'];
        $obj->cooperate_money = $params['cooperate_money'];
        $obj->goods_money     = $params['goods_money'];
        $obj->updated_at      = time();
        return $obj->save();

    }
}
