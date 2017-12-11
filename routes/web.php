<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('', function () {
    $ver = date('mdHi', filemtime(public_path('/js/front.js')));
    return redirect('/f/sign?ver=' . $ver);
});

//非常重要
Route::group(['prefix' => 'f'], function () {
    Route::get('/{wa?}/{wb?}/{wc?}', 'UserController@showView');
});


/* ------ 正文开始 ------ */

//签卡
Route::group(['prefix' => 'clock'], function () {
    Route::post('info', 'SignController@getRecord'); //获取打卡记录
    Route::post('save', 'SignController@save'); //打卡
    Route::post('flash', 'SignController@flash'); //上班急速打卡
    Route::post('get_shop_staff', 'UserController@getShopStaff');//获取店员信息
});

//调动
Route::group(['prefix' => 'transfer'], function () {
    Route::post('record', 'TransferController@getTransferByStaff'); //获取员工的调动信息
    Route::post('next', 'TransferController@getNextTransfer'); //获取员工将要执行的调动
    Route::post('save', 'TransferController@save'); //调动打卡
});

//请假
Route::group(['prefix' => 'leave'], function () {
    Route::get('get_type', 'LeaveController@getLeaveType'); //获取请假类型
    Route::post('record', 'LeaveController@getLeaveRecord'); //获取请假记录
    Route::post('next', 'LeaveController@getNextRecord'); //获取将要生效的请假记录
    Route::post('save', 'LeaveController@clock'); //请假打卡
    Route::post('submit', 'LeaveController@submitLeaveRequest'); //请假申请
});

//考勤
Route::group(['prefix' => 'attendance'], function () {
    Route::post('locate', 'AttendanceController@locateShop'); //店铺定位
    Route::post('all', 'AttendanceController@getAllAttendanceRecords'); //店铺定位
    Route::post('sheet', 'AttendanceController@getAttendanceForm'); //获取店铺考勤表数据
    Route::post('refresh', 'AttendanceController@refreshAttendanceForm'); //刷新店铺考勤表数据
    Route::post('submit', 'AttendanceController@submit'); //提交
    Route::post('withdraw', 'AttendanceController@withdraw'); //撤回
});

//统计
Route::group(['prefix' => 'statistics'], function () {
    Route::post('personal', 'StatisticsController@getPersonalReport'); //店铺定位
});

//检查
Route::group(['prefix' => 'check'], function () {
    Route::post('get_all_shop', 'CheckController@getAllShop');//获取所有店铺
    Route::post('get_shop_staff', 'CheckController@getShopStaff');//获取店员
});

Route::any('re_login', 'UserController@reLogin');
Route::post('js_config', 'UserController@getJsConfig');
Route::post('clock_data', 'UserController@getClockData');
