<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkingSchedule;
use Cache;

class UserController extends Controller
{

    public function showView(Request $request)
    {
        $ver = date('mdHi', filemtime(public_path('/js/front.js')));
        if ($request->get('ver') != $ver) {
            return redirect($request->url() . '?ver=' . $ver);
        } else {
            return view('front.index');
        }
    }

    /**
     * 重新获取用户信息
     */
    public function reLogin()
    {
        app('CurrentUser')->login();
        return app('CurrentUser')->getInfo();
    }

    /**
     * 获取钉钉配置
     * @return array
     */
    public function getJsConfig()
    {
        if (Cache::store('database')->has('jsApiTicket')) {
            $jsApiTicket = Cache::store('database')->get('jsApiTicket');
        } else {
            $response = app('OA')->getDataFromApi('get_dingtalk_js_api_ticket');
            $jsApiTicket = $response['message']['api_ticket'];
            $expiration = ($response['message']['expiration'] - time() - 1) / 60;
            if ($expiration > 0) {
                Cache::store('database')->put('jsApiTicket', $jsApiTicket, $expiration);
            }
        }
        $nonceStr = 'geRn9g2l3S';
        $timeStamp = time();
        /* 获取当前路由 start */
        $url = request('current_url');
        $preg = '/\?_url=.*?&/';
        if (preg_match($preg, $url)) {
            $url = preg_replace($preg, '?', $url);
        } else {
            $preg = '/\?_url=.*/';
            $url = preg_replace($preg, '', $url);
        }
        $url = urldecode($url);
        /* 获取当前路由 end */

        $plain = 'jsapi_ticket=' . $jsApiTicket .
            '&noncestr=' . $nonceStr .
            '&timestamp=' . $timeStamp .
            '&url=' . $url;
        $signature = sha1($plain);
        $config = [
            'agentId' => config('dingtalk.agentId'), //微应用id
            'corpId' => 'dingb8f2e19299cab872', //企业id
            'timeStamp' => $timeStamp, //生成签名的时间戳
            'nonceStr' => $nonceStr, //生成签名的随机串
            'signature' => $signature, //签名
        ];
        return $config;
    }

    public function getClockData(Request $request)
    {
        $date = $request->get('date');
        $staffSn = $request->has('staff_sn') ? $request->get('staff_sn') : app('CurrentUser')->staff_sn;
        $response = [];
        $response['clock_record'] = app('ClockRepos')->getRecord($date, $staffSn);
        $response['transfer'] = app('TransferRepos')->getNextRecord($staffSn);
        $response['leave'] = app('LeaveRepos')->getNextLeaveRequest($staffSn);
        return $response;
    }

    /**
     * 获取店铺包含的员工
     * @param Request $request
     * @return array
     */
    public function getShopStaff(Request $request)
    {
        if (app('CurrentUser')->isShopManager()) {
            $staffSn = $request->staff_sn;
            $response = app('OA')->getDataFromApi('get_user', ['staff_sn' => $staffSn]);
            $staff = $response['message'][0];
            $schedule = WorkingSchedule::where([
                'staff_sn' => $staff['staff_sn'],
                'shop_sn' => $staff['shop_sn']
            ])->first();
            $staff['shop'] = app('CurrentUser')->shop;
            $staff['working_start_at'] = empty($schedule->clock_in) ? $staff['shop']['clock_in'] : $schedule->clock_in;
            $staff['working_end_at'] = empty($schedule->clock_out) ? $staff['shop']['clock_out'] : $schedule->clock_out;
            return $staff;
        } else {
            return ['status' => -1, 'message' => '用户不是店长'];
        }
    }
}
