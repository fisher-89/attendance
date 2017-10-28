<?php

namespace App\Repositories;

use App\Models\Clock;

class SignRepositories
{

    public function getRecord($date = null, $staffSn = null)
    {
        $today = app('Clock')->getAttendanceDate();
        if (empty($date)) {
            $date = $today;
        }
        if (empty($staffSn)) {
            $staffSn = app('CurrentUser')->staff_sn;
        }
        $tableName = 'clock_' . date('Ym', strtotime($date));
        list($startTime,
            $endTime) = app('Clock')->getAttendanceDay($date);
        $where = [
            ['staff_sn', '=', $staffSn],
            ['clock_at', '>=', $startTime],
            ['clock_at', '<=', $endTime],
        ];
        $clockRecord = Clock::from($tableName)->where($where)
            ->orderBy('clock_at', 'asc')
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

    public function sign($request)
    {
        $staffSn = $request->has('staff_sn') ? $request->get('staff_sn') : app('CurrentUser')->staff_sn;
        $shopSn = $request->has('shop_sn') ? $request->get('shop_sn') : app('CurrentUser')->shop_sn;
        switch ($request->get('type')) {
            case 'clock_in':
                $type = 1;
                $punctualTime = app('CurrentUser')->working_start_at;
                break;
            case 'clock_out':
                $lastClockOut = $this->getLastClockOut($staffSn, $shopSn);
                if ($lastClockOut) {
                    $lastClockOut->is_abandoned = 1;
                    $lastClockOut->save();
                };
                $type = 2;
                $punctualTime = app('CurrentUser')->working_end_at;
                break;
            default:
                return returnErr('hints.102');
                break;
        }
        $clockData = $request->input();
        $clockData['attendance_type'] = 1;
        $clockData['type'] = $type;
        $clockData['punctual_time'] = app('Clock')->getAttendanceDate() . ' ' . $punctualTime;
        $response = app('Clock')->clock($clockData);
        return $response;
    }

    protected function getLastClockOut($staffSn, $shopSn)
    {
        list($startTime,
            $endTime) = app('Clock')->getAttendanceDay();
        $lastClockOut = Clock::where('clock_at', '>', $startTime)
            ->where(['staff_sn' => $staffSn, 'shop_sn' => $shopSn, 'attendance_type' => 1, 'type' => 2, 'is_abandoned' => 0])
            ->orderBy('clock_at', 'desc')->first();
        return $lastClockOut;
    }

}
