<?php

namespace App\Http\Controllers;

use App\Models\Clock;
use App\Models\Leave;
use App\Models\Transfer;
use App\Models\LeaveType;
use App\Models\WorkingSchedule;
use Illuminate\Http\Request;
use DB;
use Log;

class LeaveController extends Controller
{
    protected $leaveRepos;
    protected $processCode = 'PROC-WIYJONZV-5MXNEVF4OY5F3WWUHXKT1-7EWI607J-D';

    public function __construct()
    {
        $this->leaveRepos = app('LeaveRepos');
    }

    public function getLeaveType()
    {
        return LeaveType::orderBy('sort', 'asc')->get()->toArray();
    }

    public function getLeaveRecord(Request $request)
    {
        return $this->leaveRepos->getRecordByStaff($request->get('take', 0), $request->get('skip', 0));
    }

    public function getNextRecord(Request $request)
    {
        $staffSn = $request->has('staff_sn') ? $request->get('staff_sn') : app('CurrentUser')->staff_sn;
        return $this->leaveRepos->getNextLeaveRequest($staffSn);
    }

    /**
     * 请假打卡
     * @param Request $request
     * @return array
     */
    public function clock(Request $request)
    {
        $leaveID = $request->parent_id;
        $leave = Leave::where('status', 1)->find($leaveID);
        $current = date('Y-m-d H:i:s');
        Clock::where('parent_id', $request->parent_id)
            ->where('shop_sn', '')
            ->update(['shop_sn' => app('CurrentUser')->shop_sn]);
        if (empty($leave->clock_out_at)) {
            $prevClockRecord = app('Clock')->getLatestClock();
            $checkDistance = !empty($prevClockRecord) && $prevClockRecord->type == 1;
            $type = 2;
            $punctualTime = $leave->start_at;
            $leave->clock_out_at = $current;
        } else {
            Clock::where('parent_id', $request->parent_id)
                ->where('type', 1)
                ->update(['is_abandoned' => 1]);
            $checkDistance = false;
            $type = 1;
            $punctualTime = $leave->end_at;
            $leave->clock_in_at = $current;
        }

        $clockData = $request->input();
        $clockData['clock_at'] = $current;
        $clockData['attendance_type'] = 3;
        $clockData['type'] = $type;
        $clockData['punctual_time'] = $punctualTime;
        $response = app('Clock')->clock($clockData, $checkDistance);
        if ($response['status'] == 1) {
            $leave->save();
        }
        return $response;
    }

    /**
     * 提交请假申请
     * @param Request $request
     * @return array
     */
    public function submitLeaveRequest(Request $request)
    {
        $this->validate($request, [
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at', 'before:' . date('Y-m-d', strtotime('+1 month'))],
            'type_id' => ['required', 'exists:leave_type,id'],
            'reason' => ['required', 'max:200'],
        ]);
        $leaveRequestExist = Leave::where('staff_sn', app('CurrentUser')->staff_sn)
            ->where('start_at', '<=', $request->end_at)
            ->where('end_at', '>=', $request->start_at)
            ->where('status', '>=', 0)
            ->first();
        if (!empty($leaveRequestExist)) {
            return ['status' => 0, 'msg' => '与其他请假条时间冲突'];
        }
        $formData = $this->makeFormData($request);
        $approvers = $this->getApprovers();
        if (empty($approvers)) {
            return returnErr('hints.132');
        }
        $params = [
            'process_code' => $this->processCode,
            'approvers' => $approvers['staff_sn'],
            'form_data' => $formData,
            'callback_url' => url('/api/leave/callback'),
        ];
        $response = app('OA')->getDataFromApi('dingtalk/start_approval', $params);
        if ($response['status'] == 1) {
            $leaveData = $request->input();
            $leaveData['staff_sn'] = app('CurrentUser')->staff_sn;
            $leaveData['staff_name'] = app('CurrentUser')->realname;
            $leaveData['approver_sn'] = $approvers['staff_sn'];
            $leaveData['approver_name'] = $approvers['name'];
            $leaveData['process_instance_id'] = $response['message'];
            Leave::create($leaveData);
        } else {
            return $response['message'];
        }
        return returnRes($response['status'], 'hints.130', 'hints.131');
    }

    protected function getApprovers()
    {
        $staff = app('CurrentUser');
        if ($staff->inShop() && $staff->shop['manager_sn'] != $staff->staff_sn && $staff->shop['manager_sn'] > 0) {
            $approvers = [
                'staff_sn' => $staff->shop['manager_sn'],
                'name' => $staff->shop['manager_name']
            ];
        } elseif ($staff->department['manager_sn'] > 0 && $staff->staff_sn != $staff->department['manager_sn']) {
            $approvers = [
                'staff_sn' => $staff->department['manager_sn'],
                'name' => $staff->department['manager_name']
            ];
        } elseif ($staff->department['parent_id'] > 0 && $staff->department['_parent']['manager_sn']) {
            $approvers = [
                'staff_sn' => $staff->department['_parent']['manager_sn'],
                'name' => $staff->department['_parent']['manager_name']
            ];
        } else {
            $approvers = [];
        }
        return $approvers;
    }

    /**
     * 生成钉钉审批所需信息
     * @param Request $request
     * @return array
     */
    protected function makeFormData(Request $request)
    {
        return [
            '开始时间' => $request->start_at,
            '结束时间' => $request->end_at,
            '请假时长' => $request->duration,
            '请假类型' => $request->type_name,
            '请假原因' => $request->reason,
        ];
    }

    /**
     * 钉钉审批通过后回调
     * @param Request $request
     * @return int
     */
    public function dingTalkApprovalCallback(Request $request)
    {
        $leaveRequest = Leave::where('process_instance_id', $request->processInstanceId)->first();
        if (empty($leaveRequest)) {
            return 0;
        }
        if ($request->type == 'start') {
            $staff = app('OA')->withoutPassport()->getDataFromApi('get_user', ['dingding' => $request->staffId])['message'];
            if (!empty($staff)) {
                $staff = $staff[0];
                $leaveRequest->approver_sn = $staff['staff_sn'];
                $leaveRequest->approver_name = $staff['realname'];
            } else {
                $leaveRequest->approver_name = '未知审批人';
            }
        } elseif ($request->type == 'finish') {
            switch ($request->result) {
                case 'agree':
                    $leaveRequest->status = 1;
                    $this->changeClockRecord($leaveRequest);
                    break;
                case 'refuse';
                    $leaveRequest->status = -1;
                    break;
            }
        } elseif ($request->type == 'terminate') {
            $leaveRequest->status = -2;
        }
        return $leaveRequest->save() ? 1 : 0;
    }

    protected function changeClockRecord(&$leaveRequest)
    {

        $staff = app('OA')->withoutPassport()->getDataFromApi('get_user', ['staff_sn' => $leaveRequest->staff_sn])['message'][0];

        $startAt = $leaveRequest->start_at;
        $endAt = $leaveRequest->end_at;

        $ymStart = app('Clock')->getAttendanceDate('Ym', $startAt);
        $ymEnd = app('Clock')->getAttendanceDate('Ym', $endAt);
        list($clockInTimestamp, $clockOutTimestamp) = $this->getClockRange($staff, $startAt, $endAt);

        $startTimestamp = strtotime($leaveRequest->start_at);
        $endTimestamp = strtotime($leaveRequest->end_at);

        $basicClockData = [
            'parent_id' => $leaveRequest->id,
            'staff_sn' => $leaveRequest->staff_sn,
            'shop_sn' => $staff['shop_sn'],
            'attendance_type' => 3,
            'is_abandoned' => 0,
            'operator_sn' => 100000,
        ];

        $clockModelStart = new Clock(['ym' => $ymStart]);
        $clockModelEnd = new Clock(['ym' => $ymEnd]);
        $startClockRecord = $clockModelStart->where(['parent_id' => $leaveRequest->id, 'attendance_type' => 3, 'type' => 2])->first();
        $endClockRecord = $clockModelEnd->where(['parent_id' => $leaveRequest->id, 'attendance_type' => 3, 'type' => 1])->first();

        if (($startTimestamp <= $clockInTimestamp || $startTimestamp < time()) && empty($startClockRecord)) {
            $clockData = array_collapse([$basicClockData, [
                'clock_at' => $leaveRequest->start_at,
                'punctual_time' => $leaveRequest->start_at,
                'type' => 2,
            ]]);
            $response = app('Clock')->clock($clockData, false);
            if ($response['status'] == 1) {
                $leaveRequest->clock_out_at = $leaveRequest->start_at;
            }
        }
        if (($endTimestamp >= $clockOutTimestamp || $endTimestamp < time()) && empty($endClockRecord)) {
            $clockData = array_collapse([$basicClockData, [
                'clock_at' => $leaveRequest->end_at,
                'punctual_time' => $leaveRequest->end_at,
                'type' => 1,
            ]]);
            $response = app('Clock')->clock($clockData, false);
            if ($response['status'] == 1) {
                $leaveRequest->clock_in_at = $leaveRequest->end_at;
            }
        }

        DB::beginTransaction();
        try {
            $where = [
                ['staff_sn', '=', $leaveRequest->staff_sn],
                ['clock_at', '>=', $leaveRequest->start_at],
                ['clock_at', '<=', $leaveRequest->end_at],
            ];
            $startClockModel = new Clock(['ym' => $ymStart]);
            $endClockModel = new Clock(['ym' => $ymEnd]);
            if (strtotime($leaveRequest->end_at) < time()) {
                if ($ymStart == $ymEnd) {
                    $startClockModel->where($where)->get()->each(function ($model) use (&$endTimestamp, $ymStart) {
                        $this->moveClockRecord($model->setMonth($ymStart), $endTimestamp);
                    });
                } else {
                    $startClockModel->where($where)->get()->each(function ($model) use (&$startTimestamp, $ymStart) {
                        $this->moveClockRecord($model->setMonth($ymStart), $startTimestamp, false);
                    });
                    $endClockModel->where($where)->get()->each(function ($model) use (&$endTimestamp, $ymEnd) {
                        $this->moveClockRecord($model->setMonth($ymEnd), $endTimestamp);
                    });
                }
            } elseif (strtotime($leaveRequest->start_at) < time()) {
                if ($ymStart == date('Ym')) {
                    Clock::where($where)->get()->each(function ($model) use (&$startTimestamp) {
                        $this->moveClockRecord($model, $startTimestamp, false);
                    });
                } else {
                    $startClockModel->where($where)->get()->each(function ($model) use (&$startTimestamp, $ymStart) {
                        $this->moveClockRecord($model->setMonth($ymStart), $startTimestamp, false);
                    });
                    Clock::where($where)->get()->each(function ($model) use (&$startTimestamp) {
                        $this->moveClockRecord($model, $startTimestamp, false);
                    });
                }
            }
        } catch (\Exception $err) {
            Log::error($err->getMessage());
            DB::rollBack();
        }
        DB::commit();
    }

    public function getClockRange($staff, $startAt, $endAt)
    {
        $ymdStart = app('Clock')->getAttendanceDate('Ymd', $startAt);
        $ymdEnd = app('Clock')->getAttendanceDate('Ymd', $endAt);
        $tableStart = DB::table('information_schema.TABLES')
            ->where('table_name', 'working_schedule_' . $ymdStart)
            ->count();
        $tableEnd = DB::table('information_schema.TABLES')
            ->where('table_name', 'working_schedule_' . $ymdEnd)
            ->count();
        $scheduleModelStart = $tableStart > 0 ? new WorkingSchedule(['ymd' => $ymdStart]) : new WorkingSchedule();
        $scheduleModelEnd = $tableEnd > 0 ? new WorkingSchedule(['ymd' => $ymdEnd]) : new WorkingSchedule();

        $workingScheduleStart = $scheduleModelStart->where('staff_sn', $staff['staff_sn'])
            ->where('shop_sn', $staff['shop_sn'])->first();
        $workingScheduleEnd = $scheduleModelEnd->where('staff_sn', $staff['staff_sn'])
            ->where('shop_sn', $staff['shop_sn'])->first();

        $clockIn = empty($workingScheduleStart['clock_in']) ? $staff['shop']['clock_in'] : $workingScheduleStart['clock_in'];
        $clockOut = empty($workingScheduleEnd['clock_out']) ? $staff['shop']['clock_out'] : $workingScheduleEnd['clock_out'];
        $clockInTimestamp = strtotime(app('Clock')->getAttendanceDate('Y-m-d', $startAt) . ' ' . $clockIn);
        $clockOutTimestamp = strtotime(app('Clock')->getAttendanceDate('Y-m-d', $endAt) . ' ' . $clockOut);
        return [$clockInTimestamp, $clockOutTimestamp];
    }

    protected function moveClockRecord($model, &$timestamp, $plus = true)
    {
        if ($model->attendance_type == 1) {
            $model->fill(['is_abandoned' => 1])->save();
        } elseif ($model->attendance_type == 2) {
            if ($plus) {
                $timestamp++;
            } else {
                $timestamp--;
            }
            $clockAt = date('Y-m-d H:i:s', $timestamp);
            $model->fill(['clock_at' => $clockAt])->save();
            if ($model->type == 2) {
                Transfer::find($model->parent_id)->update(['left_at' => $clockAt]);
            } elseif ($model->type == 1) {
                Transfer::find($model->parent_id)->update(['arrived_at' => $clockAt]);
            }
        }
    }

    protected function autoClock($clock_at, $staff)
    {
        $ymd = date('Ymd', strtotime($clock_at));
        $workingScheduleModel = new WorkingSchedule(['ymd' => $ymd]);
        $workingSchedule = $workingScheduleModel->where('staff_sn', $staff['staff_sn'])
            ->get();
    }
}
