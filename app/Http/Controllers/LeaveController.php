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
        if ($leave->has_clock_out == 0) {
            $type = 2;
            $leave->has_clock_out = 1;
        } elseif ($leave->has_clock_out == 1) {
            $type = 1;
            $leave->has_clock_in = 1;
        } else {
            return returnErr('hints.113');
        }

        $clockData = $request->input();
        $clockData['attendance_type'] = 3;
        $clockData['type'] = $type;
        $response = app('Clock')->clock($clockData);
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
        $staff = session()->get('staff');

        //@todo 开发中临时使用本人为审批人，实际代码为下方注释部分
        return $staff['staff_sn'];
        // if (!empty($staff['shop']) && $staff['staff_sn'] != $staff['shop']['manager_sn']) {
        //     $approvers = $staff['shop']['manager_sn'];
        // } elseif ($staff['staff_sn'] != $staff['department']['manager_sn']) {
        //     $approvers = $staff['department']['manager_sn'];
        // } elseif ($staff['department']['parent_id'] > 0) {
        //     $approvers = $staff['department']['_parent']['manager_sn'];
        // } else {
        //     $approvers = '';
        // }
        // return $approvers;
    }

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

    public function dingTalkApprovalCallback(Request $request)
    {
        $leaveRequest = Leave::where('process_instance_id', $request->processInstanceId)->first();
        if ($request->type == 'finish') {
            switch ($request->result) {
                case 'agree':
                    $leaveRequest->status = 1;
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
}
