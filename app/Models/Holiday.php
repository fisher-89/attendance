<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Holiday extends Model
{
    //
    use SoftDeletes;
    protected $table = 'holiday';
    protected $dates = ['deleted_at'];
    protected $fillable = [''];
    


    // setFirstNameAttribute
    public function getSubjectStatusAttribute($val){
    	$accept = [
    		'取消',
    		'完成',
    		'撤销',
    	];

    	return $accept[$val];
    }

	// subject_result
	public function getSubjectResultAttribute($val){
    	$accept = [
    		'拒绝',
    		'同意',
    	];
    	return $accept[$val];
    }

    /**
     * 获取当月请假数据
     */
    static public function staff_holiday($staff_sn=''){
        $staff_sn = 112068;
        $curTime = date('Y-m-01',time());
        $endTime = date('Y-m-d',strtotime(date('Y-m-01').'+1 month'));
        // subject_result=1
        $sql = "select * from (select sponsor,start_time,end_time from holiday where sponsor = $staff_sn) as subQuery where start_time >= '".$curTime."' and start_time < '".$endTime."'";
        return DB::select($sql);
    }

    /**
     * 获取当日全部请假数据
     */
    static public function staff_holiday_all(){
        $curDay = date('Y-m-d',time());
        $curTime = strtotime(date('Y-m-d 09:00:00',time()));
        $sql = "select sponsor,start_time,end_time from holiday where  end_time >= '".$curDay."'";
        $holidayData =  json_decode(json_encode(DB::select($sql)),true);
        //全天请假人员
        $allDay=[];
        foreach ($holidayData as $key => $value) {
            $startTime = strtotime($value['start_time']);
            $endTime = strtotime($value['end_time']);
            if(floor(($endTime-$curTime)/86400) || (($endTime-$curTime)%86400/3600 >8)){
                $allDay[]=$value['sponsor'];
            }
        }
        return array_unique($allDay);
    }


}
