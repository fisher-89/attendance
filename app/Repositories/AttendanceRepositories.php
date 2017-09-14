<?php
namespace App\Repositories;

use App\Models\Clock;
use App\Models\Transfer;

class AttendanceRepositories
{

    public function getAttendanceDataByShop($shopSn)
    {
        list($start, $end) = app('Clock')->getAttendanceDay();
        $clockData         = Clock::where([
            ['shop_sn', '=', $shopSn],
            ['created_at', '>', $start],
            ['created_at', '<', $end],
            ['attendanceType', '=', 1],
            ['is_abandoned', '=', 0],
        ])->get();
        return $clockData;

        $transferData = Transfer::where([
            ['leaving_shop_sn', '=', $shopSn],
            ['created_at', '>', $start],
            ['created_at', '<', $end],
            ['attendanceType', '=', 2],
            ['is_abandoned', '=', 0],
        ])->get();
    }

}
