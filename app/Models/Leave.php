<?php

namespace App\Models;

use App\Events\LeaveUpdating;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $table = 'leave_requests';
    protected $fillable = [
        'staff_sn',
        'staff_name',
        'start_at',
        'end_at',
        'duration',
        'type_id',
        'reason',
        'status',
        'process_instance_id',
        'approver_sn',
        'approver_name',
    ];
    protected $events = [
        'updating' => LeaveUpdating::class,
    ];
}
