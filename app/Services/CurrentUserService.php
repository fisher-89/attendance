<?php

/**
 * 当前用户功能类
 * create by Fisher 2017/1/15 <fisher9389@sina.com>
 */

namespace App\Services;

use App\Models\WorkingSchedule;

class CurrentUserService
{

    protected $userInfo = [];

    public function __construct()
    {
        if ($this->isLogin()) {
            $this->userInfo = session('staff');
        }
    }

    public function __get($name)
    {
        return isset($this->userInfo[$name]) ? $this->userInfo[$name] : '无效属性';
    }

    /**
     * 登录，从OA获取用户信息
     */
    public function login()
    {
        $userInfo = app('OA')->getDataFromApi('get_current_user');
        if ($userInfo['status'] == 1) {
            $staffFromApi = $userInfo['message'];
            if (!empty($staffFromApi['shop_sn'])) {
                $schedule = WorkingSchedule::where([
                    'staff_sn' => $staffFromApi['staff_sn'],
                    'shop_sn' => $staffFromApi['shop_sn']
                ])->first();

                $staffFromApi['working_start_at'] = empty($schedule->clock_in) ?
                    $staffFromApi['shop']['clock_in'] :
                    $schedule->clock_in;
                $staffFromApi['working_end_at'] = empty($schedule->clock_out) ?
                    $staffFromApi['shop']['clock_out'] :
                    $schedule->clock_out;
                $staffFromApi['working_hours'] = (strtotime($staffFromApi['working_end_at']) - strtotime($staffFromApi['working_start_at'])) / 3600;
                $staffFromApi['shop_duty_id'] = $schedule->shop_duty_id;
            }
            session()->put('staff_sn', $staffFromApi['staff_sn']);
            session()->put('staff', $staffFromApi);
            $this->userInfo = $staffFromApi;
        }
    }

    /**
     * 是否为店长
     * @return bool
     */
    public function isShopManager()
    {
        if ($this->inShop()) {
            return $this->userInfo['shop_duty_id'] == 1;
        } else {
            return false;
        }
    }

    public function inShop()
    {
        return !empty($this->userInfo['shop_sn']);
    }

    /**
     * 获取当前员工信息
     * @return array
     */
    public function getInfo()
    {
        return array_except($this->userInfo, ['user_token', 'user_token_expiration', 'password', 'salt']);
    }

    /**
     * 检查员工是否登录
     * @return type
     */
    public function isLogin()
    {
        return session()->has('staff');
    }

    /**
     * 判断当前员工是否为开发者
     * @return boolean
     */
    public function isDeveloper()
    {
        return $this->userInfo['username'] == 'developer' ? true : false;
    }

}
