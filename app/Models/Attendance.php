<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table    = 'attendance_shop';
    protected $fillable = [
        'shop_sn',
        'shop_name',
        'sales_performance',
        'attachment',
        'status',
        'submitted_at',
    ];

    public static function updata($params)
    {
        $obj = static::find($params['id']);
        if (!$obj) {
            return ['msg' => 'errs'];
        }
        $obj->achievement = $params['achievement'];
        if (!empty($params['attachment'])) {
            $obj->attachment = $params['attachment'];
        }
        return $obj->save();
    }

}
