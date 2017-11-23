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
     * 获取下一条未执行的请假
     * @param null $staffSn
     * @return mixed
     */
    public function getNextLeaveRequest($staffSn = null)
    {
        if (empty($staffSn)) {
            $staffSn = app('CurrentUser')->staff_sn;
        }
        $currentTime = date('Y-m-d H:i:s');
        $dayStartAt = app('Clock')->getAttendanceDay()[0];
        $leaveRequest = Leave::where([['staff_sn', '=', $staffSn], ['status', '=', 1]])
            ->where(function ($query) use ($currentTime, $dayStartAt) {
                $query->where(function ($query) use ($currentTime) {
                    $query->whereNull('clock_out_at')->where('end_at', '>', $currentTime);
                })->orWhere(function ($query) use ($currentTime, $dayStartAt) {
                    $query->whereNotNull('clock_out_at')
                        ->where(function ($query) use ($currentTime) {
                            $query->whereNull('clock_in_at')->orWhere('clock_in_at', '>', $currentTime);
                        })->where('end_at', '>=', $dayStartAt);
                });
            })
            ->orderBy('start_at', 'asc')
            ->first();
        return $leaveRequest;
    }

}
