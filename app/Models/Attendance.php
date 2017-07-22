<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model {

    protected $table = 'attendance';
    protected $fillable = [
        'staff_sn',
        'achievement',
        'attachment',
        'shop_name',
        'shop_sn',
    ];

    public static function updata($params) {
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
