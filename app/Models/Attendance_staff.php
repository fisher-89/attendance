<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Holiday;
class Attendance_staff extends Model
{
    //
    protected $table = 'Attendance_staff';
    protected $fillable = [
    	'staff_sn',
    	'parent',
    	'achievement',
    	'cooperate_money',
    	'goods_money',
    	'shop_name',
    	'shop_sn',
    ];

    // 员工业绩求合
    static public function statistic(){
    	$firstDay = date('Y-m-01',time());
    	$lastDay =  date('Y-m-d', strtotime(date('Y-m-01', time()) . ' +1 month'));
    	// return $this->holiday();
    	$resArr = self::where([['submit_time','>',$firstDay],['submit_time','<',$lastDay]])->select(\DB::raw('sum(achievement+cooperate_money+goods_money) as achievement'),'staff_sn','staff_name')->groupBy('staff_sn')->get()->toArray();

    	// return static::staff_holiday($resArr);
    	return $resArr;

    	// return \DB::table('attendance_staff')->select(\DB::raw('sum(achievement) as achievement'),'staff_sn','staff_name')->groupBy('staff_sn')->get()->toArray();
    }

    //获取员工请假数据
    static public function staff_holiday(){
    	$resData =  [];
        $firstDay = date('Y-m-01',time());
        $lastDay =  date('Y-m-d', strtotime(date('Y-m-01', time()) . ' +1 month'));
        return Holiday::where([['subject_result',1],['start_time','>',$firstDay],['start_time','<',$lastDay],['end_time','>',$firstDay]])->select('sponsor','start_time','end_time')->get()->toArray();

    	return $arrData;
    }

    // public function holiday(){
    // 	return $this->belongsToMany('App\Models\Holiday','attendance_holiday','staff_sn','id');
    // }

    // 统计打卡
    static public function updata($params){
        $obj = static::find($params['id']);
        if(!$obj){
            return ['msg'=>'errs'];
        }
        $obj->achievement = $params['achievement'];
        $obj->cooperate_money = $params['cooperate_money'];
        $obj->goods_money = $params['goods_money'];
        $obj->updated_at = time();
        return $obj->save();

    }
}
