<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'attendance_shop';
    protected $fillable = [
        'shop_sn',
        'shop_name',
        'manager_sn',
        'manager_name',
        'attendance_date',
        'sales_performance_lisha',
        'sales_performance_go',
        'sales_performance_group',
        'sales_performance_partner',
        'attachment',
        'status',
        'submitted_at',
    ];

}
