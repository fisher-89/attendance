<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    //
    protected $table = 'Attendance';
    protected $fillable = [
    	'staff_sn',
    	'achievement',
        'attachment',
    	'shop_name',
    	'shop_sn',
    ];

    // public function sss(){
    // 	return {'a':'asdasd'};
    // }

    // public function getStatusAttribute($value){
    //     $tmpArr = ['异常','正常'];
    //     return $tmpArr($value);
    // }

    public static function updata($params){
        $obj = static::find($params['id']);
        if(!$obj){
            return ['msg'=>'errs'];
        }
        $obj->achievement = $params['achievement'];
        if(!empty($params['attachment'])){
            $obj->attachment = $params['attachment'];
        }
        return $obj->save();

    }
}
