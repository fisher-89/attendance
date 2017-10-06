<?php
//
////签卡
//Route::group(['prefix' => 'clock'], function () {
//    Route::post('info', 'SignController@getRecord'); //获取打卡记录
//    Route::post('save', 'SignController@save'); //打卡
//});
//
////调动
//Route::group(['prefix' => 'transfer'], function () {
//    Route::post('record', 'TransferController@getTransferByStaff'); //获取员工的调动信息
//    Route::post('next', 'TransferController@getNextTransfer'); //获取员工将要执行的调动
//    Route::post('save', 'TransferController@save'); //调动打卡
//});
//
////请假
Route::group(['prefix' => 'leave'], function () {
//    Route::get('get_type', 'LeaveController@getLeaveType'); //获取请假类型
//    Route::post('record', 'LeaveController@getLeaveRecord'); //获取请假记录
//    Route::post('next', 'LeaveController@getNextRecord'); //获取将要生效的请假记录
//    Route::post('save', 'LeaveController@clock'); //请假打卡
//    Route::post('submit', 'LeaveController@submitLeaveRequest'); //请假申请
    Route::any('callback', 'LeaveController@dingTalkApprovalCallback'); //钉钉审批接口回调
});
//
////考勤
//Route::group(['prefix' => 'attendance'], function () {
//    Route::post('sheet', 'AttendanceController@getAttendanceSheet');//获取店铺考勤表数据
//
//    Route::any('getshopinfo', 'AttendanceController@getShopInfo');
//    Route::get('getrecordlist', 'AttendanceController@getrecordlist');
//    Route::any('getshopattendinfo', 'AttendanceController@getShopAttendInfo');
//    Route::post('searchstaff', 'AttendanceController@searchstaff'); //搜索
//    Route::post('cancel', 'AttendanceController@cancel');
//    Route::post('save', 'AttendanceController@save');
//    Route::post('updata', 'AttendanceController@attendUpdata');
//});
//Route::any('attendance/getstafflist', 'AttendanceController@getstafflist');
//
///* 数据统计 */
//Route::group(['prefix' => 'statistic'], function () {
//    Route::any('getlist', 'AttendanceController@getStaffData');
//    Route::any('savelist', 'AttendanceController@staffData');
//    Route::any('getstatistic', 'AttendanceController@getstatistic');
//    Route::any('getstaffdetail', 'AttendanceController@getStaffDetail');
//    Route::any('export', 'UpfileController@export');
//});
//
////demo
//Route::post('upfile', 'UpfileController@wang');
