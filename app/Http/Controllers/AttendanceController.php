<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Attendance_staff;
use App\Models\AttendanceStaff;
use App\Models\Holiday;
use App\Models\Sign;
use App\Models\Statistic;
use App\Models\WorkingSchedule;
use App\Services\UpfileService;
use Illuminate\Http\Request;
use Artisan;

class AttendanceController extends Controller
{

    public $attendanceRepos;

    public function __construct()
    {
//        $this->attendanceRepos = app('AttendanceRepos');
    }

    /**
     * 店铺定位
     * @param Request $request
     * @return mixed
     */
    public function locateShop(Request $request)
    {
        $shopSn = app('CurrentUser')->shop_sn;
        $shop = app('OA')->getDataFromApi('get_shop', ['shop_sn' => $shopSn])['message'][0];
        if (empty($shop['lng']) || empty($shop['lat'])) {
            $params = $request->input();
            $params['shop_sn'] = $shopSn;
            $response = app('OA')->getDataFromApi('set_shop', $params);
            app('CurrentUser')->login();
            return $response;
        } else {
            app('CurrentUser')->login();
            return ['status' => -1, 'message' => '该店铺已配置坐标', 'lng' => $shop['lng'], 'lat' => $shop['lat']];
        }
    }

    /**
     * 获取店铺考勤表数据
     * @return mixed
     */
    public function getAttendanceForm(Request $request)
    {
        if (app('CurrentUser')->isShopManager()) {
            return app('AttendanceRepos', ['date' => $request->date])->getAttendanceForm();
        }
    }


    /**
     * 刷新店铺考勤表数据
     * @return mixed
     */
    public function refreshAttendanceForm(Request $request)
    {
        if (app('CurrentUser')->isShopManager()) {
            return app('AttendanceRepos', ['date' => $request->attendance_date])->refreshAttendanceForm($request);
        }
    }

    /**
     * 接口刷新考勤表
     * @param Requet $request
     * @return array
     */
    public function refreshByApi(Request $request)
    {
        $id = $request->get('id');
        $attendance = Attendance::find($id);
        $date = $attendance->attendance_date;
        $ymd = date('Ymd', strtotime($date));
        $shopSn = $attendance->shop_sn;

        $attendanceList = $attendance->details->pluck('staff_sn')->toArray();
        $workingScheduleModel = new WorkingSchedule(['ymd' => $ymd]);
        $workingScheduleList = $workingScheduleModel->where('shop_sn', $shopSn)->get()->pluck('staff_sn')->toArray();

        sort($workingScheduleList);
        sort($attendanceList);
        if ($workingScheduleList == $attendanceList) {
            Artisan::call('attendance:refresh', ['--date' => $date, '--shop_sn' => $shopSn]);
            return ['status' => 1, 'message' => '刷新成功'];
        } else {
            return ['status' => -1, 'message' => '考勤表与排班表人员不同'];
        }
    }

    /**
     * 提交考勤表
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|static|static[]
     */
    public function submit(Request $request)
    {
        $form = Attendance::find($request->id);
        if ($form->status <= 0) {
            $currentUserSn = app('CurrentUser')->staff_sn;
            $currentUserName = app('CurrentUser')->realname;
            $attendanceData = [
                'sales_performance_lisha' => 0,
                'sales_performance_go' => 0,
                'sales_performance_group' => 0,
                'sales_performance_partner' => 0,
                'manager_sn' => $currentUserSn,
                'manager_name' => $currentUserName,
            ];
            if (empty($request->details)) {
                return ['status' => 0, 'msg' => '不可提交空考勤表'];
            }
            foreach ($request->details as $detail) {
                $attendanceStaffModel = new AttendanceStaff(['ym' => $form->attendance_date]);
                $attendanceStaffModel->find($detail['id'])->setMonth($form->attendance_date)
                    ->fill(array_only($detail, [
                        'sales_performance_lisha',
                        'sales_performance_go',
                        'sales_performance_group',
                        'sales_performance_partner',
                        'shop_duty_id',
                        'is_assistor',
                        'is_shift',
                    ]))->fill(['status' => 1, 'manager_sn' => $currentUserSn])->save();
                $attendanceData['sales_performance_lisha'] += $detail['sales_performance_lisha'];
                $attendanceData['sales_performance_go'] += $detail['sales_performance_go'];
                $attendanceData['sales_performance_group'] += $detail['sales_performance_group'];
                $attendanceData['sales_performance_partner'] += $detail['sales_performance_partner'];
            }
            $form->status = 1;
            $form->submitted_at = date('Y-m-d H:i:s');
            $form->details;
            $form->update($attendanceData);
            return ['status' => 1, 'msg' => $form];
        } else {
            return ['status' => 0, 'msg' => '考勤表已提交'];
        }
    }

    /**
     * 撤回考勤表
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|static|static[]
     */
    public function withdraw(Request $request)
    {
        $form = Attendance::find($request->id);
        if ($form->status == 1) {
            $form->details->each(function ($staffAttendance) {
                $staffAttendance->setMonth($staffAttendance->attendance_date)->fill(['status' => 0])->save();
            });
            $form->status = 0;
            $form->save();
            $form->details;
            return $form;
        } else {
            abort(500, '考勤表不可撤回');
        }

    }
}
