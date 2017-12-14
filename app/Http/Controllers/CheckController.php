<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckController extends Controller
{
    public function getAllShop()
    {
        $shop = [];
        $response = app('OA')->getDataFromApi('get_shop')['message'];
        foreach ($response as $v) {
            $shop[] = array_only($v, ['shop_sn', 'name']);
        }
        return $shop;
    }

    public function getShopStaff(Request $request)
    {
        $response = app('OA')->getDataFromApi('get_user', ['shop_sn' => $request->shop_sn])['message'];
        return array_map(function ($value) {
            $value['shopStatusColor'] = '#666';
            $value['shopStatusName'] = '计算中...';
            return $value;
        }, $response);
    }
}
