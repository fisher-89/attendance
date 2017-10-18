<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
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
        $response = app('OA')->getDataFromApi('get_dingtalk_js_api_ticket');
        $jsApiTicket = $response['message'];
        $nonceStr = 'geRn9g2l3S';
        $timeStamp = time();
        /* 获取访问路由 start */
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

    public function getShopStaff(Request $request)
    {
        if (app('CurrentUser')->isShopManager()) {
            $staffSn = $request->staff_sn;
            $response = app('OA')->getDataFromApi('get_user', ['staff_sn' => $staffSn]);
            return $response['message'][0];
        } else {
            return ['status' => -1, 'message' => '用户不是店长'];
        }
    }
}
