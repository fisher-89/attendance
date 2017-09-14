<?php

namespace App\Repositories;

use App\Models\Leave;

class LeaveRepositories
{

    public function getRecordByStaff($take = 0, $skip = 0, $staffSn = null)
    {
        if (empty($staffSn)) {
            $staffSn = app('CurrentUser')->staff_sn;
        }
        $response = Leave::where('staff_sn', $staffSn)
            ->when($take > 0, function ($query) use ($skip, $take) {
                return $query->skip($skip)->take($take);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return $response;
    }

    /**
     * 判断当前打卡是否为请假打卡
     * @param null $staffSn
     * @return bool
     */
    public function isLeaveClock($staffSn = null){
        if (empty($staffSn)) {
            $staffSn = app('CurrentUser')->staff_sn;
        }
        $currentTime = date('Y-m-d H:i:s');
        $leaveCount = Leave::where(['staff_sn' => $staffSn, 'status' => 1, 'has_clock_out' => 0, 'has_clock_in' => 0])
            ->where([['start_at', '<', $currentTime], ['end_at', '>', $currentTime]])->count();
        return $leaveCount > 0 || $this->isLeaving();
    }

    /**
     * 判断当前是否在请假中
     * @param null $staffSn
     * @return bool
     */
    public function isLeaving($staffSn = null)
    {
        if (empty($staffSn)) {
            $staffSn = app('CurrentUser')->staff_sn;
        }
        $currentTime = date('Y-m-d H:i:s');
        $leaveCount = Leave::where(['staff_sn' => $staffSn, 'status' => 1, 'has_clock_out' => 1, 'has_clock_in' => 0])
            ->where([['start_at', '<', $currentTime]])->count();
        return $leaveCount > 0;
    }

    public function getNextLeaveRequest($staffSn = null)
    {
        if (empty($staffSn)) {
            $staffSn = app('CurrentUser')->staff_sn;
        }
        $currentTime = date('Y-m-d H:i:s');
        $leaveRequest = Leave::where(['staff_sn' => $staffSn, 'status' => 1, 'has_clock_in' => 0])
            ->where([['end_at', '>', $currentTime]])
            ->orderBy('start_at', 'asc')
            ->first();
        return $leaveRequest;
    }

}
