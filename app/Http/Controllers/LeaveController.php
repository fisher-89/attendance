<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;

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

    public function getNextRecord()
    {
        return $this->leaveRepos->getNextLeaveRequest();
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
        if (empty($leave->clock_out_at)) {
            $prevClockRecord = app('Clock')->getLatestClock();
            $checkDistance = !empty($prevClockRecord) && $prevClockRecord->type == 1;
            $type = 2;
            $punctualTime = $leave->start_at;
            $leave->clock_out_at = $current;
        } else {
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
            'end_at' => ['required', 'date', 'after:start_at'],
            'type_id' => ['required', 'exists:leave_type,id'],
            'reason' => ['required', 'max:200'],
        ]);
        $formData = $this->makeFormData($request);
        $approvers = $this->getApprovers();
        if (empty($approvers)) {
            return returnErr('hints.132');
        }
        $params = [
            'process_code' => $this->processCode,
            'approvers' => $approvers,
            'form_data' => $formData,
            'callback_url' => url('/api/leave/callback'),
        ];
        $response = app('OA')->getDataFromApi('dingtalk/start_approval', $params);
        if ($response['status'] == 1) {
            $leaveData = $request->input();
            $leaveData['staff_sn'] = session()->get('staff.staff_sn');
            $leaveData['staff_name'] = session()->get('staff.realname');
            $leaveData['process_instance_id'] = $response['message'];
            Leave::create($leaveData);
        }
        return returnRes($response['status'], 'hints.130', 'hints.131');
    }

    protected function getApprovers()
    {
        $staff = app('CurrentUser');
        if ($staff->inShop() && !$staff->isShopManager()) {
            $approvers = $staff->shop['manager_sn'];
        } elseif ($staff->staff_sn != $staff->department['manager_sn']) {
            $approvers = $staff->department['manager_sn'];
        } elseif ($staff->department['parent_id'] > 0) {
            $approvers = $staff->department['_parent']['manager_sn'];
        } else {
            $approvers = '';
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
        if ($request->type == 'finish') {
            switch ($request->result) {
                case 'agree':
                    $leaveRequest->status = 1;
                    $this->autoClock($leaveRequest);
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

    protected function autoClock($leaveRequest)
    {
        $staff = app('OA')->withoutPassport()->getDataFromApi('get_user', ['staff_sn' => $leaveRequest->staff_sn])['message'][0];
        $basicClockData = [
            'parent_id' => $leaveRequest->id,
            'staff_sn' => $leaveRequest->staff_sn,
            'shop_sn' => $staff['shop_sn'],
            'attendance_type' => 3,
        ];

        if (substr($leaveRequest->start_at, 11, 5) <= $staff['shop']['clock_in']) {
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
        if (substr($leaveRequest->end_at, 11, 5) >= $staff['shop']['clock_out']) {
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
    }
}
