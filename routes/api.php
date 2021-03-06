<?php

//请假
Route::group(['prefix' => 'leave'], function () {
    Route::any('callback', 'LeaveController@dingTalkApprovalCallback'); //钉钉审批接口回调
});

//考勤
Route::group(['prefix' => 'attendance'], function () {
    Route::post('refresh', 'AttendanceController@refreshByApi');//刷新考勤数据
    Route::post('make', 'AttendanceController@makeByApi');//生成考勤数据
});
