<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Attendance_staff;
use App\Models\AttendanceStaff;
use App\Models\Holiday;
use App\Models\Sign;
use App\Models\Statistic;
use App\Services\OAService;
use App\Services\PluginService;
use App\Services\UpfileService;
use Illuminate\Http\Request;

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
            return app('AttendanceRepos', ['date' => $request->date])->refreshAttendanceForm();
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
        $form = Attendance::with('details')->find($request->id);
        if ($form->status <= 0) {
            $salesPerformance = [
                'sales_performance_lisha' => 0,
                'sales_performance_go' => 0,
                'sales_performance_group' => 0,
                'sales_performance_partner' => 0,
            ];
            foreach ($request->details as $detail) {
                AttendanceStaff::find($detail['id'])->update(array_only($detail, [
                    'sales_performance_lisha',
                    'sales_performance_go',
                    'sales_performance_group',
                    'sales_performance_partner',
                ]));
                $salesPerformance['sales_performance_lisha'] += $detail['sales_performance_lisha'];
                $salesPerformance['sales_performance_go'] += $detail['sales_performance_go'];
                $salesPerformance['sales_performance_group'] += $detail['sales_performance_group'];
                $salesPerformance['sales_performance_partner'] += $detail['sales_performance_partner'];
            }

            $form->status = 1;
            $form->submitted_at = date('Y-m-d H:i:s');
            $form->update($salesPerformance);
            return $form;
        } else {
            abort(500, '考勤表已提交');
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
        $form = Attendance::with('details')->find($request->id);
        if ($form->status == 1) {
            $form->status = 0;
            $form->save();
            return $form;
        } else {
            abort(500, '考勤表不可撤回');
        }

    }
}
