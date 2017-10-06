<?php

namespace App\Repositories;

use App\Models\Clock;
use App\Models\Transfer;

class TransferRepositories
{

    /**
     * 查询员工调动状态
     */
    public function getRecordByStaff($staffSn, $request)
    {
        $skip = $request->has('skip') ? $request->get('skip') : 0;
        $take = $request->get('take');
        $data = Transfer::where([['staff_sn', '=', $staffSn]])->orderBy('left_at', 'desc')
            ->when($take, function ($query) use ($skip, $take) {
                return $query->skip($skip)->take($take);
            })->get();
        return $data->toArray();
    }

    /**
     * 查询待执行的调动
     */
    public function getNextRecord($staffSn)
    {
        $data = Transfer::where([['staff_sn', '=', $staffSn], ['leaving_date', '<=', date('Y-m-d')]])
            ->whereIn('status', [0, 1])
            ->orderBy('leaving_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->first();
        return $data;
    }

    /**
     * 调动打卡
     * @param int $transferID 调动ID
     */
    public function clock($transferID, $request)
    {
        $transfer = Transfer::find($transferID);
        if ($transfer->status == 0) {
            $prevClockRecord = app('Clock')->getLatestClock();
            $checkDistance = !empty($transfer->leaving_shop_sn) && (!empty($prevClockRecord) && $prevClockRecord->type == 1);
            $type = 2;
            $transfer->status = 1;
            $transfer->left_at = date('Y-m-d H:i:s');
        } elseif ($transfer->status == 1) {
            $checkDistance = !empty($transfer->arriving_shop_sn) && time() > strtotime(app('Clock')->getAttendanceDate() . ' ' . app('CurrentUser')->shop['clock_out']);
            $type = 1;
            $transfer->status = 2;
            $transfer->arrived_at = date('Y-m-d H:i:s');
        } else {
            return returnErr('hints.113');
        }

        $clockData = $request->input();
        $clockData['attendance_type'] = 2;
        $clockData['type'] = $type;
        $response = app('Clock')->clock($clockData, $checkDistance);
        if ($response['status'] == 1) {
            if ($transfer->status == 1) {
                $params = [
                    'staff_sn' => $transfer->staff_sn,
                    'shop_sn' => $transfer->arriving_shop_sn,
                ];
                app('OA')->getDataFromApi('hr/staff_update', $params);
            }
            $transfer->save();
            app('CurrentUser')->login();
        }
        return $response;
    }

}
