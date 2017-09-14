<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clock extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table    = 'clock_';
    protected $fillable = [
        'parent_id',
        'staff_sn',
        'shop_sn',
        'lng',
        'lat',
        'address',
        'distance',
        'attendance_type',
        'type',

    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = $this->table . date('Ym');
    }

    /* 关联 Start */

    /* 关联 End */

    /* 访问器 Start */

//    public function getCreatedAtAttribute($value)
//    {
//        $timestamp = strtotime($value);
//        return date('H:i', $timestamp);
//    }

    public function getClockTypeAttribute($value)
    {
        $attendanceType = $this->getAttribute('attendance_type');
        $type           = $this->getAttribute('type');
        switch ($attendanceType . $type) {
            case 11:
                return '上班';
                break;
            case 12:
                return '下班';
                break;
            case 21:
                return '调动到达';
                break;
            case 22:
                return '调动出发';
                break;
            case 31:
                return '请假返回';
                break;
            case 32:
                return '请假外出';
                break;
        }
    }

    /* 访问器 End */

}
