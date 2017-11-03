<?php

namespace App\Repositories;

use App\Models\Transfer;
use App\Models\WorkingSchedule;
use DB;
use Mockery\Exception;

class TransferRepositories
{

    /**
     * 查询员工调动状态
     */
    public function getRecordByStaff($staffSn, $request)
    {
        $skip = $request->has('skip') ? $request->get('skip') : 0;
        $take = $request->get('take');
        $data = Transfer::where([['staff_sn', '=', $staffSn]])->orderBy('leaving_date', 'desc')
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
            $type = 2;
            $transfer->status = 1;
            $transfer->left_at = date('Y-m-d H:i:s');
            $transfer->leaving_shop_sn = app('CurrentUser')->shop_sn;
            $transfer->leaving_shop_name = empty(app('CurrentUser')->shop['name']) ? '' : app('CurrentUser')->shop['name'];
        } elseif ($transfer->status == 1) {
            $type = 1;
            $transfer->status = 2;
            $transfer->arrived_at = date('Y-m-d H:i:s');
        } else {
            return returnErr('hints.113');
        }

        $clockData = $request->input();
        $clockData['attendance_type'] = 2;
        $clockData['type'] = $type;
        DB::beginTransaction();
        try {
            $response = app('Clock')->clock($clockData, false);
            if ($response['status'] == 1) {
                if ($transfer->status == 1) {
                    $params = $where = [
                        'staff_sn' => $transfer->staff_sn,
                        'shop_sn' => $transfer->arriving_shop_sn,
                    ];
                    $changeStaff = app('OA')->getDataFromApi('hr/staff_update', $params);
                    if ($changeStaff['status'] == 1) {
                        $params['staff_name'] = $transfer->staff_name;
                        $params['shop_duty_id'] = 3;
                        WorkingSchedule::updateOrCreate($where, $params);
                    } else {
                        DB::rollBack();
                        return ['status' => 0, 'msg' => $changeStaff['message']];
                    }
                } elseif ($transfer->status == 2) {
                    if ($transfer->arriving_shop_duty_id == 1) {
                        $params = [
                            'shop_sn' => $transfer->arriving_shop_sn,
                            'manager_sn' => $transfer->staff_sn,
                            'manager_name' => $transfer->staff_name,
                        ];
                        $changeShop = app('OA')->getDataFromApi('hr/shop_update', $params);
                        if ($changeShop['status'] == 1) {
                            $shopManager = WorkingSchedule::where('shop_sn', $transfer->arriving_shop_sn)
                                ->where('shop_duty_id', 1)->first();
                            if (empty($shopManager)) {
                                WorkingSchedule::where('shop_sn', $transfer->arriving_shop_sn)
                                    ->where('staff_sn', $transfer->staff_sn)->update(['shop_duty_id' => 1]);
                            }
                        } else {
                            DB::rollBack();
                            return ['status' => 0, 'msg' => $changeShop['message']];
                        }
                    }
                }
                $transfer->save();
                app('CurrentUser')->login();
                DB::commit();
            } else {
                DB::rollBack();
                return ['status' => 0, 'msg' => '打卡失败'];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
        return $response;
    }

}
