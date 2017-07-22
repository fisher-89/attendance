<?php

use Illuminate\Http\Request;

Route::get('/getuser', 'TransferController@getuser');   //获取用户信息
Route::get('/dd', 'TransferController@dd');
Route::post('/transferstatus', 'TransferController@transferstatus');   //到店签到
//调动
Route::group(['prefix' => 'transfer'], function() {
    Route::get('list', 'TransferController@list');
    Route::get('save', 'TransferController@save');
    Route::get('edit', 'TransferController@edit');
    Route::get('cancel', 'TransferController@cancel');
    Route::post('outshop', 'TransferController@outshop');  //离店签到
    Route::post('goshop', 'TransferController@goshop');    //到店签到
    Route::post('record', 'TransferController@record');  //到店签到
    Route::post('confirm', 'TransferController@confirm'); //店长确认
    Route::post('getTransferShop', 'TransferController@getTransferShop');    //获取该店调动员工列表 
});

//签卡
Route::group(['prefix' => 'sign'], function() {
    Route::post('save', 'SignController@save');
    Route::post('selects', 'SignController@selects');
});


//请假
Route::group(['prefix' => 'holiday'], function() {
    Route::post('list', 'HolidayController@list');
    Route::get('list', 'HolidayController@list');
    Route::post('cancel', 'HolidayController@cancel');
    Route::post('imports', 'HolidayController@imports');
    Route::get('imports', 'HolidayController@imports');
    Route::post('selects', 'SignController@selects');
});

Route::any('oaser', function() {
    return session('username');
})->middleware('initial');


//考勤
Route::group(['prefix' => 'attendance', 'middleware' => 'initial'], function() {
    Route::any('getshopinfo', 'AttendanceController@getShopInfo');
    Route::get('getrecordlist', 'AttendanceController@getrecordlist');
    Route::any('getshopattendinfo', 'AttendanceController@getShopAttendInfo');
    Route::post('searchstaff', 'AttendanceController@searchstaff'); //搜索
    Route::post('cancel', 'AttendanceController@cancel');
    Route::post('save', 'AttendanceController@save');
    Route::post('updata', 'AttendanceController@attendUpdata');
});
Route::any('attendance/getlist', 'AttendanceController@getlist');
Route::any('attendance/getstafflist', 'AttendanceController@getstafflist');

/* 数据统计 */
Route::group(['prefix' => 'statistic'], function() {
    Route::any('getlist', 'AttendanceController@getStaffData');
    Route::get('savelist', 'AttendanceController@staffData');
    Route::any('getstatistic', 'AttendanceController@getstatistic');
    Route::any('getstaffdetail', 'AttendanceController@getStaffDetail');
    Route::get('export', 'UpfileController@export');
});

//demo
Route::post('upfile', 'UpfileController@wang');
