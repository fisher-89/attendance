<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
class Sign extends Model
{
    //
    use SoftDeletes;
    protected $table = 'sign';
    protected $dates = ['deleted_at'];
    protected $fillable = ['sign_time','down_time','staff_name','staff_sn'];

    /** 统计迟到 早退
     * 
     */
    static public function staff_arrived(){
    	$sql = "select id,staff_sn,sign_time,down_time from sign where ((date_format(sign_time,'%H') > 8) and (date_format(sign_time,'%H') <  12)) || ( (date_format(sign_time,'%H') >  12) ) ";
    	return DB::select($sql);
    }

    /**
     * 当月早上迟到
     */
    static public function staff_morning_arrived($staff_sn){
        $curTime = date('Y-m-01',time());
        $endTime = date('Y-m-d',strtotime(date('Y-m-01',time()).'+1 Month'));
        // $sql = "select id,staff_sn,sign_time,down_time from sign where (sign_time >=".$curTime." and sign_time < ". $endTime .") and staff_sn = ".$staff_sn;
        // $sql ="select * from (select id,staff_sn,sign_time,down_time from sign where sign_time > '".$curTime."' AND sign_time <'". $endTime ."' AND  staff_sn = ".$staff_sn.") as subQuery where (date_format(sign_time,'%H') > 8";

        $sql ="select * from ( select id,staff_sn,staff_name,sign_time from sign where sign_time >= '".$curTime."' and sign_time <'".$endTime."' and staff_sn=".$staff_sn.") AS subQuery WHERE date_format(sign_time,'%H') > 8 and date_format(sign_time,'%H') <  12";
        $res = DB::select($sql);
        return json_decode( json_encode($res,JSON_UNESCAPED_UNICODE),true);
        dd($r);
        
        return $curTime;
        $sql = "select id,staff_sn,sign_time,down_time from sign where ((date_format(sign_time,'%H') > 8) and (date_format(sign_time,'%H') <  12)) and staff_sn = ".$staff_sn;
        $res =  DB::select($sql);
        dd($res);
    }

    /**
     * 当月下午迟到
     */
    static public function staff_after_arrived($staff_sn){
        // $sql = "select id,staff_sn,sign_time,down_time from sign where ((date_format(sign_time,'%H') > 12) and (date_format(sign_time,'%H') <  18)) and staff_sn = ".$staff_sn;
        $curTime = date('Y-m-01',time());
        $endTime = date('Y-m-d',strtotime(date('Y-m-01',time()).'+1 Month'));

        $sql ="select * from ( select id,staff_sn,staff_name,sign_time from sign where sign_time >= '".$curTime."' and sign_time <'".$endTime."' and staff_sn=".$staff_sn.") AS subQuery WHERE date_format(sign_time,'%H') > 12 and date_format(sign_time,'%H') <  18";
        $res = DB::select($sql);
        return json_decode( json_encode($res),true);
    }

    /**
     * 合并 考勤数据
     * return $result
     */
    static public function user_attendance_data(){
        $result = [];
        $signData = self::where('sign_time','>','0')->select('id','sign_time','staff_sn','staff_name')->orderby('sign_time','asc')->get()->toarray();
        $downData = self::where('down_time','>','0')->select('id','down_time','staff_sn','staff_name')->orderby('down_time','asc')->get()->toarray();

        // 上班数据
        foreach ($signData as $key => $value) {
            // echo date('Y-m-d',strtotime($value['sign_time'])).'<br>';
            $tmp_sign_key =  date('Y-m-d',strtotime($value['sign_time']));
            if(empty($result['up'][$value['staff_sn'].'-'.$tmp_sign_key])){
                $result['up'][$value['staff_sn'].'-'.$tmp_sign_key] = $value;
            }
        }
    
        // 将下班数据存入对应的上班数据中
        foreach ($downData as $key => $value) {
            $tmp_down_key =  date('Y-m-d',strtotime($value['down_time']));
            $result['up'][$value['staff_sn'].'-'.$tmp_down_key]['down_time'] = $value['down_time'];
            $result['up'][$value['staff_sn'].'-'.$tmp_down_key]['id'] = $value['id'];
            $result['up'][$value['staff_sn'].'-'.$tmp_down_key]['staff_sn'] = $value['staff_sn'];
            $result['up'][$value['staff_sn'].'-'.$tmp_down_key]['staff_name'] = $value['staff_name'];
        }

        return $result['up'];
    }

}


