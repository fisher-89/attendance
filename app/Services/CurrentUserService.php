<?php

/**
 * 当前用户功能类
 * create by Fisher 2017/1/15 <fisher9389@sina.com>
 */

namespace App\Services;

class CurrentUserService
{

    protected $userInfo;

    public function __construct()
    {
        if ($this->isLogin()) {
            $this->userInfo = session('staff');
        } else {
            abort(500, '当前用户不存在');
        }
    }

    public function __get($name)
    {
        return isset($this->userInfo[$name]) ? $this->userInfo[$name] : '无效属性';
    }

    /**
     * 登录，从OA获取用户信息
     */
    public function login(){
        $userInfo = app('OA')->getDataFromApi('get_current_user');
        if ($userInfo['status'] == 1) {
            session()->put('staff_sn', $userInfo['message']['staff_sn']);
            session()->put('staff', $userInfo['message']);
        }
    }

    /**
     * 是否为店长
     * @return bool
     */
    public function isManager(){
        if($this->inShop()){
            return $this->userInfo->shop->manager_sn == $this->userInfo->staff_sn;
        }else{
            return false;
        }
    }

    public function inShop(){
        return $this->userInfo->shop_sn != 0;
    }

    /**
     * 获取当前员工信息
     * @return array
     */
    public function getInfo()
    {
        return array_except($this->userInfo, ['user_token', 'user_token_expiration']);
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
