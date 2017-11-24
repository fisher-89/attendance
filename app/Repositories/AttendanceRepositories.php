<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\AttendanceStaff;
use App\Models\Clock;
use App\Models\Transfer;
use App\Models\WorkingSchedule;

/**
 * 考勤数据仓库，所有时间转化为时间戳，方便计算
 * Class AttendanceRepositories
 * @package App\Repositories
 */
class AttendanceRepositories
{

    protected $date;
    protected $dayStartAt;
    protected $dayEndAt;
    protected $shopSn;
    /**
     * 店铺上下班时间
     * @var int
     */
    protected $shopStartAt;
    protected $shopEndAt;
    /**
     * 员工上下班时间
     * @var int
     */
    protected $staffStartAt;
    protected $staffEndAt;
    /**
     * 工作时长
     * @var int
     */
    protected $workingHours;
    /**
     * 报销单ID
     * @var int
     */
    protected $attendanceId;
    /**
     * 迟到/早退时间上限（小时），超过判断为漏签
     * @var int
     */
    protected $lateMax = 4;
    /**
     * 上一条打卡记录
     * @var Clock
     */
    protected $lastClock;
    /**
     * 店铺考勤记录
     * @var array
     */
    protected $shopRecord;
    /**
     * 员工考勤记录
     * @var array
     */
    protected $staffRecord;

    public function __construct($date = null, $shop = [])
    {
        $date = empty($date) ? date('Y-m-d') : $date;
        $shopClockIn = empty($shop['clock_in']) ? app('CurrentUser')->shop['clock_in'] : $shop['clock_in'];
        $shopClockOut = empty($shop['clock_out']) ? app('CurrentUser')->shop['clock_out'] : $shop['clock_out'];
        $shopSn = empty($shop['shop_sn']) ? app('CurrentUser')->shop_sn : $shop['shop_sn'];

        $timestamp = date('Y-m-d H:i:s') >= $date . ' ' . $shopClockOut ? strtotime($date) : strtotime($date) - 24 * 3600;
        $this->date = date('Y-m-d', $timestamp);
        list($this->dayStartAt, $this->dayEndAt) = app('Clock')->getAttendanceDay($this->date);
        $this->shopSn = $shopSn;
        $this->shopStartAt = strtotime($this->date . ' ' . $shopClockIn);
        $this->shopEndAt = strtotime($this->date . ' ' . $shopClockOut);
    }

    /**
     * 获取店铺考勤表
     * @return array
     */
    public function getAttendanceForm()
    {
        $this->shopRecord = $this->getShopAttendanceForm();
        if ($this->shopRecord->status == 0 && $this->shopRecord->details->count() == 0) {
            $this->makeAttendanceDetail();
            $this->shopRecord = $this->getShopAttendanceForm();
        }
        return $this->shopRecord;
    }

    /**
     * 刷新店铺考勤表
     * @return AttendanceRepositories|array|\Illuminate\Database\Eloquent\Model|mixed
     */
    public function refreshAttendanceForm($attendance = null, $force = false)
    {
        $this->shopRecord = $this->getShopAttendanceForm();
        if ($this->shopRecord->status <= 0 || $force) {
            $originalDetails = empty($attendance->details) ? $this->shopRecord->details->pluck([], 'staff_sn')->toArray() : array_pluck($attendance->details, [], 'staff_sn');
            $this->shopRecord->details->each(function ($staffAttendance) {
                $staffAttendance->setMonth($this->shopRecord->attendance_date)->forceDelete();
            });
            $this->shopRecord->is_missing = 0;
            $this->shopRecord->is_late = 0;
            $this->shopRecord->is_early_out = 0;
            $this->makeAttendanceDetail();
            $this->shopRecord = $this->getShopAttendanceForm();
            $this->shopRecord->details->each(function ($staffAttendance) use ($originalDetails) {
                if (array_has($originalDetails, $staffAttendance->staff_sn)) {
                    $origin = $originalDetails[$staffAttendance->staff_sn];
                    $data = array_only($origin, [
                        'sales_performance_lisha',
                        'sales_performance_go',
                        'sales_performance_group',
                        'sales_performance_partner',
                        'is_assistor',
                        'is_shift',
                    ]);
                    if ($origin['shop_duty_id'] == 2 && $staffAttendance->shop_duty_id != 1) {
                        $data['shop_duty_id'] = 2;
                    }
                    $staffAttendance->setMonth($this->shopRecord->attendance_date)->fill($data)->save();
                }
            });
            $this->shopRecord = $this->getShopAttendanceForm();
            return $this->shopRecord;
        } else {
            abort(500, '考勤表已提交，不可修改');
        }
    }

    /**
     * 获得or初始化店铺考勤数据
     * @return $this|\Illuminate\Database\Eloquent\Model|mixed
     */
    protected function getShopAttendanceForm()
    {
        $attendance = Attendance::where([
            'shop_sn' => $this->shopSn,
            'attendance_date' => $this->date,
        ])->first();
        if (empty($attendance)) {
            $attendance = Attendance::create([
                'shop_sn' => $this->shopSn,
                'shop_name' => app('CurrentUser')->shop['name'],
                'department_id' => app('CurrentUser')->shop['department_id'],
                'manager_sn' => app('CurrentUser')->staff_sn,
                'manager_name' => app('CurrentUser')->realname,
                'attendance_date' => $this->date,
                'status' => 0,
                'is_missing' => 0,
                'is_late' => 0,
                'is_early_out' => 0,
            ]);
        } else {
            $attendance->details;
        }
        $this->attendanceId = $attendance->id;
        return $attendance;
    }

    protected function makeAttendanceDetail()
    {
        $scheduleModel = new WorkingSchedule(['ymd' => str_replace('-', '', $this->date)]);
        try {
            $staffGroupFromSchedule = $scheduleModel->where('shop_sn', $this->shopSn)->get()->toArray();
            $staffSnGroup = array_pluck($staffGroupFromSchedule, 'staff_sn');
            $staffGroupFromApi = app('OA')->getDataFromApi('get_user', ['staff_sn' => $staffSnGroup])['message'];
            $staffGroupFromApi = array_pluck($staffGroupFromApi, [], 'staff_sn');
            $staffGroup = [];
            foreach ($staffGroupFromSchedule as $v) {
                $staffGroup[] = array_collapse([$v, $staffGroupFromApi[$v['staff_sn']]]);
            }
        } catch (\PDOException $e) {
            $staffGroup = [];
        }
        foreach ($staffGroup as $staff) {
            $this->getAttendanceDataByStaff($staff);
        }
        $this->shopRecord->save();
    }

    /**
     * 获取员工的考勤数据
     * @param $staffSn
     * @return array
     */
    protected function getAttendanceDataByStaff($staff)
    {
        $staffSn = $staff['staff_sn'];
        $this->initStaffRecord($staff);
        $ym = date('Ym', strtotime($this->date));
        $clockModel = new Clock(['ym' => $ym]);
        $clockModel->where([
            ['staff_sn', '=', $staffSn],
            ['clock_at', '>', $this->dayStartAt],
            ['clock_at', '<', $this->dayEndAt],
            ['is_abandoned', '=', 0],
            ['shop_sn', '=', $this->shopSn],
        ])->orderBy('clock_at', 'asc')->each(function ($clock) {
            $clock->clock_at = strtotime($clock->clock_at);
            $clock->punctual_time = strtotime($clock->punctual_time);
            $clock->combined_type = $clock->attendance_type . $clock->type;

            /* 去除超出上下班时间的部分 */
            if ($clock->clock_at < $this->staffStartAt) {
                if ($clock->type == 1) {
                    $this->staffRecord['over_time'] = $this->countHoursBetween($clock->clock_at, $this->staffStartAt);
                }
                $clock->clock_at = $this->staffStartAt;
            }
            if ($clock->clock_at > $this->staffEndAt) {
                if ($clock->type == 2) {
                    $this->staffRecord['over_time'] = $this->countHoursBetween($this->staffEndAt, $clock->clock_at);
                }
                $clock->clock_at = $this->staffEndAt;
            }
            switch ($clock->combined_type) {
                case 11:
                    $this->recordWorkingClockIn($clock);
                    break;
                case 12:
                    $this->recordWorkingClockOut($clock, $this->lastClock);
                    break;
                case 21:
                    $this->recordTransferringClockIn($clock, $this->lastClock);
                    break;
                case 22:
                    $this->recordTransferringClockOut($clock, $this->lastClock);
                    break;
                case 31:
                    $this->recordLeavingClockIn($clock, $this->lastClock);
                    break;
                case 32:
                    $this->recordLeavingClockOut($clock, $this->lastClock);
                    break;
            }
            $this->lastClock = $clock;
        });

        if ($this->lastClock && $this->lastClock->clock_at < $this->staffEndAt) {
            if ($this->lastClock->type == 1) {
                if ($this->lastClock->attendance_type == 3) {
                    $clockModel = new Clock(['ym' => date('Ym', strtotime($this->date))]);
                    $latestClock = $this->getLatestClock($clockModel, $staffSn);
                    if (empty($latestClock)) {
                        $clockModel = new Clock(['ym' => date('Ym', strtotime($this->date . ' -1 month'))]);
                        $latestClock = $this->getLatestClock($clockModel, $staffSn);
                    }
                    if ($latestClock && $latestClock->type == 2 && $latestClock->attendance_type == 2) {
                        $duration = $this->countHoursBetween($this->lastClock->clock_at, $this->staffEndAt);
                        $this->addClockLog($this->lastClock->clock_at, $this->staffEndAt, 2);
                        $this->staffRecord['is_transferring'] = 1;
                        $this->staffRecord['transferring_hours'] += $duration;
                        $this->staffRecord['transferring_days'] += $duration / $this->workingHours;
                    } else {
                        $this->staffRecord['is_missing'] = 1;
                    }
                } else {
                    $this->staffRecord['is_missing'] = 1;
                }
            } else {
                switch ($this->lastClock->attendance_type) {
                    case 2:
                        $transfer = Transfer::find($this->lastClock->parent_id);
                        $this->staffRecord['is_transferring'] = 1;
                        if ($transfer->arriving_shop_sn == $this->lastClock->shop_sn) {
                            $duration = $this->countHoursBetween($this->lastClock->clock_at, $this->staffEndAt);
                            $this->staffRecord['transferring_hours'] += $duration;
                            $this->staffRecord['transferring_days'] += $duration / $this->workingHours;
                            $this->addClockLog($this->lastClock->clock_at, $this->staffEndAt, 2);
                        }
                        break;
                    case 3:
                        $duration = $this->countHoursBetween($this->lastClock->clock_at, $this->staffEndAt);
                        $this->staffRecord['is_leaving'] = 1;
                        $this->staffRecord['leaving_hours'] += $duration;
                        $this->staffRecord['leaving_days'] += $duration / $this->workingHours;
                        $this->addClockLog($this->lastClock->clock_at, $this->staffEndAt, 3);
                        break;
                }
            }
        } elseif (!$this->lastClock) {

            $clockModel = new Clock(['ym' => date('Ym', strtotime($this->date))]);
            $latestClock = $this->getLatestClock($clockModel, $staffSn);
            if (empty($latestClock)) {
                $clockModel = new Clock(['ym' => date('Ym', strtotime($this->date . ' -1 month'))]);
                $latestClock = $this->getLatestClock($clockModel, $staffSn);
            }

            if (!$latestClock || $latestClock->type == 1 || $latestClock->attendance_type == 1) {
                $this->staffRecord['is_missing'] = 1;
            } else {
                if ($latestClock->attendance_type == 2) {
                    $beforeLatestClock = app('Clock')->getPrevClock($latestClock, $this->dayStartAt);
                    if (empty($beforeLatestClock)) {
                        $start = $this->staffStartAt;
                    } else {
                        $start = max($latestClock->clock_at, $this->staffStartAt);
                    }
                    $duration = $this->countHoursBetween($start, $this->staffEndAt);
                    $this->addClockLog($start, $this->staffEndAt, $latestClock->attendance_type);
                    $this->staffRecord['is_transferring'] = 1;
                    $this->staffRecord['transferring_hours'] += $duration;
                    $this->staffRecord['transferring_days'] += $duration / $this->workingHours;
                } elseif ($latestClock->attendance_type == 3) {
                    $start = max($latestClock->punctual_time, $this->staffStartAt);
                    $duration = $this->countHoursBetween($start, $this->staffEndAt);
                    $this->addClockLog($start, $this->staffEndAt, $latestClock->attendance_type);
                    $this->staffRecord['is_leaving'] = 1;
                    $this->staffRecord['leaving_hours'] += $duration;
                    $this->staffRecord['leaving_days'] += $duration / $this->workingHours;
                }
            }
        }

        $oneDay = $this->staffRecord['working_days'] + $this->staffRecord['leaving_days'] + $this->staffRecord['transferring_days'];
        if (round($oneDay, 2) > 1) {
            $this->staffRecord['is_missing'] = 1;
        } elseif (!$this->staffRecord['is_transferring'] && round($oneDay, 2) != 1) {
            $this->staffRecord['is_missing'] = 1;
        }

        $this->shopRecord->is_missing = $this->staffRecord['is_missing'] == 1 ? 1 : $this->shopRecord['is_missing'];
        $this->shopRecord->is_late = round($this->staffRecord['late_time'], 2) > 0 ? 1 : $this->shopRecord['is_late'];
        $this->shopRecord->is_early_out = round($this->staffRecord['early_out_time'], 2) > 0 ? 1 : $this->shopRecord['is_early_out'];

        $attendanceStaffModel = new AttendanceStaff(['ym' => $ym]);
        $attendanceStaffModel->fill($this->staffRecord)->save();
    }

    /**
     * 员工考勤记录初始化
     */
    protected function initStaffRecord($staff)
    {
        $this->lastClock = false;
        $this->staffStartAt = empty($staff['clock_in']) ? $this->shopStartAt : strtotime($this->date . ' ' . $staff['clock_in']);
        $this->staffEndAt = empty($staff['clock_out']) ? $this->shopEndAt : strtotime($this->date . ' ' . $staff['clock_out']);
        $this->workingHours = $this->countHoursBetween($this->staffStartAt, $this->staffEndAt);
        if ($this->workingHours == 0) {
            abort(500, '上班时间和下班时间不能相同');
        } elseif ($this->workingHours == 7 || $this->workingHours == 6) {
            $isShift = 1;
        } else {
            $isShift = 0;
        }
        $this->staffRecord = [
            'attendance_shop_id' => $this->attendanceId,
            'staff_sn' => $staff['staff_sn'],
            'staff_name' => $staff['staff_name'],
            'shop_duty_id' => $staff['shop_duty_id'],
            'sales_performance_lisha' => '',
            'sales_performance_go' => '',
            'sales_performance_group' => '',
            'sales_performance_partner' => '',
            'working_days' => 0,
            'working_hours' => 0,
            'leaving_days' => 0,
            'leaving_hours' => 0,
            'transferring_days' => 0,
            'transferring_hours' => 0,
            'is_missing' => 0,
            'late_time' => 0,
            'early_out_time' => 0,
            'over_time' => 0,
            'is_leaving' => 0,
            'is_transferring' => 0,
            'clock_log' => '',
            'working_start_at' => date('H:i:s', $this->staffStartAt),
            'working_end_at' => date('H:i:s', $this->staffEndAt),
            'staff_position_id' => $staff['position_id'],
            'staff_position' => $staff['position']['name'],
            'staff_department_id' => $staff['department_id'],
            'staff_department' => $staff['department']['name'],
            'staff_status_id' => $staff['status_id'],
            'staff_status' => $staff['status']['name'],
            'is_shift' => $isShift,
        ];
    }

    /**
     * 上班打卡
     * @param Clock $clock
     */
    protected function recordWorkingClockIn(Clock $clock)
    {
        if ($clock->clock_at > $this->staffStartAt) {
            $lateTime = $this->countHoursBetween($this->staffStartAt, $clock->clock_at);
            $this->addLateTime($lateTime);
            $clock->clock_at = $this->staffStartAt;
        }
    }

    /**
     * 下班打卡
     * @param Clock $clock
     * @param Clock $lastClock
     */
    protected function recordWorkingClockOut(Clock $clock, $lastClock)
    {
        if (!$lastClock || $lastClock->type != 1) {
            $this->staffRecord['is_missing'] = 1;
        } else {
            if ($clock->clock_at < $this->staffEndAt) {
                $this->staffRecord['early_out_time'] += $this->countHoursBetween($clock->clock_at, $this->staffEndAt);
                $clock->clock_at = $this->staffEndAt;
            }
            $duration = $this->countHoursBetween($lastClock->clock_at, $clock->clock_at);
            $this->addClockLog($lastClock->clock_at, $clock->clock_at, 1);
            $this->staffRecord['working_hours'] += $duration;
            $this->staffRecord['working_days'] += $duration / $this->workingHours;
        }
    }


    /**
     * 调动出发
     * @param Clock $clock
     * @param Clock $lastClock
     */
    protected function recordTransferringClockOut(Clock $clock, $lastClock)
    {
        if ($lastClock) {
            if ($lastClock->type == 1) {
                if ($lastClock->clock_at > $clock->clock_at) {
                    $clock->clock_at = $lastClock->clock_at;
                } else {
                    $duration = $this->countHoursBetween($lastClock->clock_at, $clock->clock_at);
                    $this->addClockLog($lastClock->clock_at, $clock->clock_at, 1);
                    $this->staffRecord['working_hours'] += $duration;
                    $this->staffRecord['working_days'] += $duration / $this->workingHours;
                }
            }
        } elseif ($clock->clock_at > $this->staffStartAt) {
            $lateTime = $this->countHoursBetween($this->staffStartAt, $clock->clock_at);
            $this->addLateTime($lateTime);
            $clock->clock_at = $this->staffStartAt;
        }
        $this->staffRecord['is_transferring'] = 1;
    }

    /**
     * 调动到达
     * @param Clock $clock
     * @param Clock $lastClock
     */
    protected function recordTransferringClockIn(Clock $clock, $lastClock)
    {
        if ($lastClock && $lastClock->combined_type != 31 && $lastClock->combined_type != 22) {
            $this->staffRecord['is_missing'] = 1;
        } else {
            if ($lastClock && ($lastClock->combined_type == 31 || $lastClock->parent_id == $clock->parent_id)) {
                $start = $lastClock->clock_at;
            } else {
                $prevClock = app('Clock')->getPrevClock($clock, date('Y-m-d H:i:s', $this->staffStartAt));
                if (empty($prevClock)) {
                    $start = $this->staffStartAt;
                } elseif ($prevClock->attendance_type == 2 && $prevClock->type == 2) {
                    $beforePrevClock = app('Clock')->getPrevClock($prevClock, $this->dayStartAt);
                    if (empty($beforePrevClock)) {
                        $start = $this->staffStartAt;
                    } else {
                        $start = strtotime($prevClock->clock_at);
                    }
                } elseif ($prevClock->attendance_type == 3 && $prevClock->type == 1) {
                    $start = strtotime($prevClock->clock_at);
                } else {
                    $this->staffRecord['is_missing'] = 1;
                    return;
                }

                $start = max($start, $this->staffStartAt);
                $start = min($start, $this->staffEndAt);

            }
            $this->staffRecord['is_transferring'] = 1;
            if ($start > $clock->clock_at) {
                $clock->clock_at = $start;
            } else {
                $duration = $this->countHoursBetween($start, $clock->clock_at);
                $this->addClockLog($start, $clock->clock_at, 2);
                $this->staffRecord['transferring_hours'] += $duration;
                $this->staffRecord['transferring_days'] += $duration / $this->workingHours;
            }
        }
    }

    /**
     * 请假出发
     * @param Clock $clock
     * @param Clock $lastClock
     */
    protected function recordLeavingClockOut(Clock $clock, $lastClock)
    {
        if ($lastClock && !($lastClock->type == 1 || $lastClock->combined_type == 22)) {
            $this->staffRecord['is_missing'] = 1;
        } else {
            if ($clock->clock_at < $clock->punctual_time) {
                $this->staffRecord['early_out_time'] += $this->countHoursBetween($clock->clock_at, $clock->punctual_time);
            }
            $clock->clock_at = $clock->punctual_time;
            $clock->clock_at = min($clock->clock_at, $this->staffEndAt);
            $clock->clock_at = max($clock->clock_at, $this->staffStartAt);
            if ($lastClock) {
                $duration = $this->countHoursBetween($lastClock->clock_at, $clock->clock_at);
                if ($lastClock->combined_type == 22) {
                    $this->addClockLog($lastClock->clock_at, $clock->clock_at, 2);
                    $this->staffRecord['transferring_hours'] += $duration;
                    $this->staffRecord['transferring_days'] += $duration / $this->workingHours;
                } else {
                    $this->addClockLog($lastClock->clock_at, $clock->clock_at, 1);
                    $this->staffRecord['working_hours'] += $duration;
                    $this->staffRecord['working_days'] += $duration / $this->workingHours;
                }
            } elseif ($clock->clock_at > $this->staffStartAt) {
                $prevClock = app('Clock')->getPrevClock($clock);
                if ($prevClock && $prevClock->attendance_type == 2 && $prevClock->type == 2) {
                    $start = max(strtotime($prevClock->clock_at), $this->staffStartAt);
                    $duration = $this->countHoursBetween($start, $clock->clock_at);
                    $this->addClockLog($start, $clock->clock_at, 2);
                    $this->staffRecord['transferring_hours'] += $duration;
                    $this->staffRecord['transferring_days'] += $duration / $this->workingHours;
                } else {
                    $this->staffRecord['is_missing'] = 1;
                }
            }
            $this->staffRecord['is_leaving'] = 1;
        }
    }

    /**
     * 请假返回
     * @param Clock $clock
     * @param Clock $lastClock
     */
    protected function recordLeavingClockIn(Clock $clock, $lastClock)
    {
        $prevClock = app('Clock')->getPrevClock($clock);
        if ($prevClock && $prevClock->attendance_type == 3 && $prevClock->type == 2) {
            if ($clock->clock_at > $clock->punctual_time) {
                $lateTime = $this->countHoursBetween($clock->punctual_time, $clock->clock_at);
                $this->addLateTime($lateTime);
            }
            $clock->clock_at = $clock->punctual_time;
            $clock->clock_at = min($clock->clock_at, $this->staffEndAt);
            $clock->clock_at = max($clock->clock_at, $this->staffStartAt);
            if ($lastClock) {
                $start = $lastClock->clock_at;
            } else {
                $start = $this->staffStartAt;
            }
            $this->staffRecord['is_leaving'] = 1;
            $duration = $this->countHoursBetween($start, $clock->clock_at);
            $this->addClockLog($start, $clock->clock_at, 3);
            $this->staffRecord['leaving_hours'] += $duration;
            $this->staffRecord['leaving_days'] += $duration / $this->workingHours;
        } else {
            $this->staffRecord['is_missing'] = 1;
        }
    }

    protected function countHoursBetween($startTime, $endTime)
    {
        $duration = ($endTime - $startTime) / 3600;
        return $duration > 0 ? $duration : 0;
    }

    protected function addLateTime($lateTime)
    {
//        if ($lateTime > $this->lateMax) {
//            $this->staffRecord['is_missing'] = 1;
//        }
        $this->staffRecord['late_time'] += $lateTime;
    }

    protected function addClockLog($start, $end, $type)
    {
        if ($start < $end) {
            $typeGroup = [1 => 'w', 2 => 't', 3 => 'l'];
            $startClock = date('Hi', $start);
            $endClock = date('Hi', $end);
            if (empty($this->staffRecord['clock_log'])) {
                $clockLogString = $startClock . $typeGroup[$type] . $endClock;
            } elseif (substr($this->staffRecord['clock_log'], -4) == $startClock) {
                $clockLogString = $typeGroup[$type] . $endClock;
            } else {
                $this->staffRecord['is_missing'] = 1;
                $clockLogString = $startClock . $typeGroup[$type] . $endClock;
            }
            $this->staffRecord['clock_log'] .= $clockLogString;
        }
    }

    /**
     * 获取上一条状态开始的打卡
     * @param $clockModel
     * @param $staffSn
     * @return mixed
     */
    protected function getLatestClock($clockModel, $staffSn)
    {
        $latestClock = $clockModel->where([
            ['staff_sn', '=', $staffSn],
            ['clock_at', '<', $this->dayEndAt],
            ['is_abandoned', '=', 0],
        ])->orderBy('clock_at', 'desc')->first();
        if ($latestClock && $latestClock->attendance_type == 3 && $latestClock->type == 1) {
            $latestClock = $clockModel->where([
                ['staff_sn', '=', $staffSn],
                ['clock_at', '<', $this->dayEndAt],
                ['attendance_type', '<>', 3],
                ['is_abandoned', '=', 0],
            ])->orderBy('clock_at', 'desc')->first();
        }
        if (!empty($latestClock)) {
            $latestClock->clock_at = strtotime($latestClock->clock_at);
        }
        return $latestClock;
    }
}
