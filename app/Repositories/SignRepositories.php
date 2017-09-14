<?php

namespace App\Repositories;

use App\Models\Clock;

class SignRepositories
{

    public function getRecord($date = null)
    {
        $today = app('Clock')->getAttendanceDate();
        if (empty($date)) {
            $date = $today;
        }
        $tableName = 'clock_' . date('Ym', strtotime($date));
        list($startTime,
            $endTime) = app('Clock')->getAttendanceDay($date);
        $staffSn = session()->get('staff.staff_sn');
        $where = [
            ['staff_sn', '=', $staffSn],
            ['created_at', '>=', $startTime],
            ['created_at', '<=', $endTime],
        ];
        $clockRecord = Clock::from($tableName)->where($where)
            ->orderBy('created_at', 'asc')
            ->get()->map(function ($model) {
                $model->setAttribute('clock_type', $model->clock_type);
                return $model;
            });
        $response = [
            'record' => $clockRecord->toArray(),
            'today' => $today,
        ];
        return $response;
    }

    public function sign($request, $distance)
    {
        $staffSn = app('CurrentUser')->staff_sn;
        $shopSn = app('CurrentUser')->staff_sn;
        switch ($request->get('type')) {
            case 'clock_in':
                $type = 1;
                break;
            case 'clock_out':
                $lastClockOut = $this->getLastClockOut($staffSn, $shopSn);
                if ($lastClockOut) {
                    $lastClockOut->is_abandoned = 1;
                    $lastClockOut->save();
                };
                $type = 2;
                break;
            default:
                return returnErr('hints.102');
                break;
        }
        if(app('LeaveRepos')->isLeaveClock()){
            return returnErr('hints.113');
        }
        $clockData = array_collapse([
            $request->all(),
            [
                'staff_sn' => $staffSn,
                'shop_sn' => $shopSn,
                'distance' => $distance,
                'attendance_type' => 1,
                'type' => $type,
            ],
        ]);
        $res = Clock::create($clockData);
        return returnRes($res->id, 'hints.112', 'hints.113');
    }

    protected function getLastClockOut($staffSn, $shopSn)
    {
        list($startTime,
            $endTime) = app('Clock')->getAttendanceDay();
        $lastClockOut = Clock::where('created_at', '>', $startTime)
            ->where(['staff_sn' => $staffSn, 'shop_sn' => $shopSn, 'type' => 2, 'is_abandoned' => 0])
            ->orderBy('created_at', 'desc')->first();
        return $lastClockOut;
    }

}
