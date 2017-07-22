<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AttendanceRepositories;
use App\Services\PluginService;
use App\Services\UpfileService;
use App\Services\CrudService;
use App\Models\Attendance;
use App\Models\Attendance_staff;
use App\Models\Sign;
use App\Models\Holiday;
use App\Models\Statistic;
use App\Services\OAService;


class AttendanceController extends Controller
{
    public $attendanceRepos;
    public function __construct(AttendanceRepositories $attendanceRepos){
    	$this->attendanceRepos = $attendanceRepos;
    }

    /**
     * 获取考勤信息
     */
    public function getlist(Request $request,PluginService $plugin){
        $data = json_encode($plugin->dataTables($request,new Attendance()));
        // return $request->callback.'('.json_encode(['err'=>'err']).')';  
        return $request->callback.'('.$data.')';

    }

    /**
     * 获取店铺考勤数据
     */
    public function getrecordlist(Request $request){
        $staff_info =  $this->getShopInfo();
        if(empty($staff_info['message'][0]['shop_sn'])) return 'error';

        $params['shop_sn'] = [['id',$staff_info['message'][0]['shop_sn']]];
        // return $params;
        return \Crud::getlist($params,new Attendance)->toArray();
    }

    /**
    *  根据 店铺数据id，获取店员考勤信息
    *
    */
    public function getstafflist(Request $request){
        $staff_sn =  $request->input('staff_sn');
        if(!$staff_sn){
            return $request->callback.'('.json_encode(['err'=>'err']).')';
        };

        // $staff_sn = 41;
        $params['where'] = [['id',$staff_sn]];
        $parentInfo  = \Crud::getlist($params,new Attendance)->toArray();
        $params['where'] = [['parent',$staff_sn]];
        $staffRes = \Crud::getlist($params,new Attendance_staff)->toArray();
        if(!isset($staffRes[0])){
            return $request->callback.'('.json_encode(['err'=>'errs']).')';
        }
        $curTime =explode(' ', $staffRes[0]['submit_time']);

        //当日凌晨4点 至 次日凌晨4点
        $curTimeStamp = date('Y-m-d H:i:s',strtotime($curTime[0])+14400);
        $curMaxTimeStamp = date('Y-m-d H:i:s',strtotime($curTime[0]) + 100800);

        foreach ($staffRes as $key => $value) {
            $para['take'] =1;
            $para['selects'] = ['id','staff_sn','sign_time'];
            $para['where'] = [ ['staff_sn',$value['staff_sn']],['sign_time','>',$curTimeStamp],['sign_time','<',$curMaxTimeStamp] ];
            $signRes = \Crud::getlist($para,new Sign)->toArray();
            if(isset($signRes[0])){
                $staffRes[$key]['sign_time'] =$signRes[0]['sign_time'];
            }

            $para['selects'] = ['id','staff_sn','down_time'];
            $para['orderby'] = ['down_time','desc'];
            $para['where'] = [ ['staff_sn',$value['staff_sn']],['down_time','>',$curTimeStamp],['down_time','<',$curMaxTimeStamp] ];
            $signdownRes = \Crud::getlist($para,new Sign)->toArray();
            if(isset($signdownRes[0])){
                $staffRes[$key]['down_time'] =$signdownRes[0]['down_time'];
            }
        }

        $staffRes[0]['attachment'] = $parentInfo[0]['attachment'];
        $demo = json_encode(['err'=>'err']);

        return $request->callback.'('.json_encode($staffRes).')';
        return $request->callback.'('.$demo.')';

    }

    /** 根据staff_sn 获取店铺信息 
    *   @params      staff_sn = manager_sn 
    */
    public function getShopInfo(){
        $staff_sn =  session('staff_sn');
        if($staff_sn){
            $staff_holiday = Holiday::staff_holiday_all();
            $staff_shop = \Oa::getDataFromApi('get_shop',['manager_sn'=>$staff_sn]);
        }
        // 去除请假人员
        if(empty($staff_shop['message'])) return 'err0';
        foreach ($staff_shop['message'][0]['staff'] as $key => $value) {
            if(in_array($value['staff_sn'], $staff_holiday)){
                array_splice($staff_shop['message'][0]['staff'], $key,1);
            }
        }
        return $staff_shop;
        dd($staff_shop);
        return 'getCurInfo-err';
    }

    /**
     * attendeidt 获取修改数据
     * param      attendid
     */
    public function getShopAttendInfo(Request $request){
        $Res = [];
        $id = $request->input('attendid');
        if(empty($id)) return 'err';
        //店铺信息
        $staff_sn =  session('staff_sn');
        if($staff_sn){
            $temShopInfo = \Oa::getDataFromApi('get_shop',['manager_sn'=>$staff_sn]);
            $Res['shopInfo'] = $temShopInfo['message'][0];
        }
        //店铺数据
        $params['where'] = [['id',$id]];
        $temShopData  = \Crud::getlist($params,new Attendance)->toArray();
        $Res['shopData'] = $temShopData[0];
        // return $shopInfo;
        // 店员数据
        $staff['where'] = [['parent',$id]];
        $Res['staffData']  = \Crud::getlist($staff,new Attendance_staff)->toArray();

        return $Res;
    }


    //店铺数据更新
    public function attendUpdata(Request $request){
        $input = $request->except('_url');
        $attend['id'] = $input['shop']['id'];
        $attendUpRes = Attendance::updata($input['shop']);
        if($attendUpRes){
            foreach ($input['staff'] as $key => $staffVal) {
                Attendance_staff::updata($staffVal);
            }
        }
        return $attendUpRes?['msg'=>config('hints.125')]:['msg'=>config('hints.126')];
    }

    /**  店铺考勤保存  
    *
    */
    public function save(Request $request,UpfileService $upfile){
    	$input = $request->except(['_url']);
        if(empty($input['shop']['shop_sn'])) return 'error';
        $curDayMax = date('Y-m-d H:i:s',strtotime(date('Y-m-d'),time())+100800); //当日结束时间
        $curDayMin = date('Y-m-d H:i:s' , strtotime(date('Y-m-d'),time())+14400);  //当时开始时间

        $params['take'] = 1;
        $params['where'] = [['shop_sn',$input['shop']['shop_sn']],['submit_time','>',$curDayMin],['submit_time','<',$curDayMax]];
        #查询当天是否提交，如果提交不让提交.
        $exists = \Crud::getlist($params,new Attendance)->toArray();
        if(!empty($exists[0]['id'])) return ['msg'=>config('hints.150')];

        #上传图片
        if(isset($input['shop']['oimg'])){
            $input['shop']['attachment'] = substr($upfile->toImg($input['shop']['oimg'],[200])[0],1);
        }

        $attendRes = \Crud::save($input['shop'],new Attendance);

        if(isset($attendRes['id'])){
            foreach ($input['staff'] as $key => $value) {
                $staffData[$key]['parent'] = $attendRes['id'];
                $staffData[$key]['staff_sn'] = $value['staff_sn'];
                $staffData[$key]['staff_name'] = $value['realname'];
                $staffData[$key]['achievement'] = $value['achievement'];
                $staffData[$key]['cooperate_money'] = $value['cooperate_money'];
                $staffData[$key]['goods_money'] = $value['goods_money'];
            }
            $res = \Crud::saveList($staffData,'Attendance_staff');
            return ['msg'=>config('hints.105')];
        }
    	return 'error';
    }

    /**  搜索员工  
    *   searchstaff
    */
    public function searchstaff(Request $request,OAService $oaservice){
       $realname = $request->input('realname');
       if(empty($realname)) return 'err';
       return $oaservice->getDataFromApi('get_user',['realname'=>$realname]);
    }

    /**  员工数据计算  计算数据存数据库
     *
     */
    public function staffData(Request $request){
        $data = json_encode(['err'=>'err']);
        // $month =  date('Y-m-d', str totime(date('Y-m-01', time()) . ' +1 month -1 day'));

        // 业绩数据
        $statisRes =array_specifyKey(Attendance_staff::statistic(),'staff_sn');
        // 请假数据
        $holidayRes = Attendance_staff::staff_holiday();
        // 迟到数据
        $signRes = json_decode(json_encode(Sign::staff_arrived()),true);
        // 员工打卡记录
        $attendanceRes = Sign::user_attendance_data();
        //请假计时统计 
        $caluRes = $this->holidayCalculate($holidayRes);
        //考勤数据中去除请假日期里打卡数据
        //统计请假次数
        $holiday_numbers =[];
        foreach ($holidayRes as $key => $holiVal) {
            $holiday_numbers[] = $holiVal['sponsor'];
            foreach ($signRes as $signKey => $signVal) {
                if( $holiVal['sponsor'] == $signVal['staff_sn'] ){
                    $holiTime = date('Y-m-d',strtotime($holiVal['start_time']));
                    $signTime = date('Y-m-d',strtotime($signVal['sign_time']));
                    if(($holiTime == $signTime) && ($holiVal['end_time'] > $signVal['sign_time']) ){
                      array_splice($signRes, $signKey,1);
                    }
                }
            }
        }

        // 请假天数
        foreach ($caluRes as $key => $value) {
            $statisRes[$key]['holiday'] = $value[0];
        }

        //出勤天数
        $totalDay = date('t',time());
        foreach ($statisRes as $key => $value) {
            $statisRes[$key]['attendance'] = $totalDay-$value['holiday'];
        }

        //员工打卡次数
        // $up_numbers = [];
        // foreach ($attendanceRes as $attendanceVal) {
        //     $up_numbers[]=$attendanceVal['staff_sn'];
        // }
        // $up_numbers_res = array_count_values($up_numbers);
        // foreach ($up_numbers_res as $key => $value) {
        //     $statisRes[$key]['attendance'] =$value?:0;
        // }
        // 迟到次数
        // $arrived_numbers = [];
        // foreach ($signRes as $key => $value) {
        //     $arrived_numbers[] = $value['staff_sn'];
        // }
        // $arrived_numbers_res = array_count_values($arrived_numbers);
        // dd($arrived_numbers_res);
        // foreach ($arrived_numbers_res as $key => $value) {
        //     $statisRes[$key]['arrive'] =$value?:1;
        // }
        //清空表 
        $delRes = \DB::delete('delete from statistic ');
        $res = \Crud::saveList($statisRes,'statistic');
        echo json_encode($res);

        die();

        $jsonStatisRes =json_encode($statisRes);
        return $request->callback.'('.$jsonStatisRes.')';
    }

    /**
     * 获取员工考勤业绩请假数据
     */

    public function getStaffData(Request $request,PluginService $plugin){
        $data = json_encode($plugin->dataTables($request,new Statistic()));
        // return $request->callback.'('.json_encode(['err'=>'err']).')';  
        return $request->callback.'('.$data.')';
    }

    /**
     * 获取员工迟到请假
     */
    public function getStaffDetail(Request $request){
        // $staff_sn = '112068';
        // $staff_sn = $request->input('staff_sn');
        // $Res = [];
        // //迟到
        // $Res['arrive'] =  Sign::staff_morning_arrived($staff_sn);
        // $after =  Sign::staff_after_arrived($staff_sn);
        // //请假
        // $Res['holiday'] = Holiday::staff_holiday($staff_sn);
        // foreach ($after as $key => $value) {
        //     array_push($Res['arrive'], $value);
        // }
        // return $Res;

        $staff_sn = $request->input('staff_sn');
        if(empty($staff_sn)){ 
            return '';
        }
        $Res = [];
        //迟到
        $Res['arrive'] =  Sign::staff_morning_arrived($staff_sn);
        $after =  Sign::staff_after_arrived($staff_sn);
        //请假
        $Res['holiday'] = Holiday::staff_holiday($staff_sn);
        foreach ($after as $key => $value) {
            array_push($Res['arrive'], $value);
        }
        return $Res;

    }

    /**
     *    请假日期计算
     */
    public function holidayCalculate(Array $dataArr){
        $tongji = [];
        foreach ($dataArr as $key => $value) {
            //计算小时
            $inStart = strtotime($value['start_time']);
            $inEnd = strtotime($value['end_time']);
            
            $holiRes = '';            
            //请假天数            
            $day = floor(($inEnd-$inStart)/86400);
            $startHour = (($inStart%86400/3600)>18)?18:($inStart%86400/3600);
            $endHour = (($inEnd%86400/3600)>18)?18:($inEnd%86400/3600);
            //计算结果
            // echo $endHour;die;
            if($startHour < $endHour){
                if($endHour>12){
                    $holiRes = $day+(($endHour - ($inStart%86400/3600))-1) /8;
                }else{
                    $holiRes = $day+(($inEnd%86400/3600) - ($inStart%86400/3600)) / 8;
                }
            }else if($startHour == $endHour){
                $holiRes = $day;
            }else{
                if($endHour > 12){
                    $holiRes = (($endHour-1+(($day?:1)*8))-$startHour)/8;
                }else{
                    $holiRes = (($endHour+(($day?:1)*8))-$startHour)/8;
                }
            }

            $tongji[$value['sponsor']][] = $holiRes;
        }

        // dump($tongji);
        $caluRes = [];
        foreach ($tongji as $key => $value) {
            $caluRes[$key][] = array_sum($value);
        }

        return $caluRes;
        // dd($caluRes);
        // die();


    }

    /**
     * 测试
     */
    public function getstatistic(Request $request){
        #return Statistic::get();

        // 114服务器 上这样写的
        return Statistic::select('id','staff_sn','staff_name','attendance','achievement','arrive','holiday')->get();
    }

    /**
     * 导出员工考勤业绩请假数据
     *
     */
    public function exportStaffData(){

    }
}
