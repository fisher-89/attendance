<?php

namespace App\Models;

use App\Events\LeaveUpdating;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $table    = 'leave_requests';
    protected $fillable = ['staff_sn', 'staff_name', 'start_at', 'end_at', 'duration', 'type_id', 'reason', 'status', 'process_instance_id'];
    protected $events   = [
        'updating' => LeaveUpdating::class,
    ];

    /* 访问器 Start */

    // public function getStartAtAttribute($value)
    // {
    //     $timestamp = strtotime($value);
    //     return date('m-d H:i', $timestamp);
    // }

    // public function getEndAtAttribute($value)
    // {
    //     $timestamp = strtotime($value);
    //     return date('m-d H:i', $timestamp);
    // }

    /* 访问器 End */
}
