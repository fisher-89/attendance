<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clock extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'clock_';
    protected $fillable = [
        'parent_id',
        'staff_sn',
        'shop_sn',
        'clock_at',
        'punctual_time',
        'lng',
        'lat',
        'address',
        'distance',
        'attendance_type',
        'is_abandoned',
        'type',
        'photo',
        'thumb',
        'accuracy',
        'operator_sn',
        'remark',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $ym = array_has($attributes, 'ym') ? $attributes['ym'] : app('Clock')->getAttendanceDate('Ym');
        $this->setMonth($ym);
    }

    /* 关联 Start */

    /* 关联 End */

    /* 访问器 Start */

    public function getClockTypeAttribute($value)
    {
        $attendanceType = $this->getAttribute('attendance_type');
        $type = $this->getAttribute('type');
        switch ($attendanceType . $type) {
            case 11:
                if ($this->clock_at > $this->punctual_time) {
                    return '迟到';
                } else {
                    return '上班';
                }
                break;
            case 12:
                if ($this->clock_at < $this->punctual_time) {
                    return '早退';
                } else {
                    return '下班';
                }
                break;
            case 21:
                return '调动到达';
                break;
            case 22:
                return '调动出发';
                break;
            case 23:
                return '调动中';
                break;
            case 31:
                return '请假结束';
                break;
            case 32:
                return '请假开始';
                break;
        }
    }

    /* 访问器 End */

    /* 自定义方法 Start */

    public function setMonth($month)
    {
        if (!preg_match('/^\d{6}$/', $month)) {
            $month = date('Ym', strtotime($month));
        }
        $this->setTable('clock_' . $month);
        return $this;
    }

    /* 自定义方法 End */

}
