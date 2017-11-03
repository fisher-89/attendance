<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clock;

class SignController extends Controller
{

    public $signRepos;

    public function __construct()
    {
        $this->signRepos = app('ClockRepos');
    }

    public function getRecord(Request $request)
    {
        $date = $request->get('date');
        $staffSn = $request->get('staff_sn');
        return $this->signRepos->getRecord($date, $staffSn);
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'type' => ['required'],
            'lng' => ['required', 'numeric'],
            'lat' => ['required', 'numeric'],
            'address' => ['max:200'],
        ]);
        return $this->signRepos->sign($request);

    }

    public function flash(Request $request)
    {
        $this->validate($request, [
            'lng' => ['required', 'numeric'],
            'lat' => ['required', 'numeric'],
            'address' => ['max:200'],
        ]);
        list($startTime,
            $endTime) = app('Clock')->getAttendanceDay();
        $clockIn = Clock::where([
            'staff_sn' => app('CurrentUser')->staff_sn,
            'type' => 1,
            'attendance_type' => 1,
            'is_abandoned' => 0,
        ])
            ->where('clock_at', '>', $startTime)
            ->where('clock_at', '<', $endTime)->count();
        if ($clockIn == 0) {
            $clockData = $request->input();
            $clockData['attendance_type'] = 1;
            $clockData['type'] = 1;
            $clockData['punctual_time'] = app('Clock')->getAttendanceDate() . ' ' . app('CurrentUser')->working_start_at;
            $response = app('Clock')->clock($clockData);
            return $response;
        } else {
            return ['status' => 0, 'msg' => '请勿重复打卡'];
        }

    }

}
