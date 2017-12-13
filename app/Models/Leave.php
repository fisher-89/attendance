<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use SoftDeletes;

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
        'attachment',
    ];

    /* ----- 访问器 Start ----- */

    public function getAttachmentAttribute($value)
    {
        return explode(';', $value);
    }

    /* ----- 访问器 End ----- */
}
