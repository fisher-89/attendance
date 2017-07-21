<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
	use SoftDeletes;
    protected $table = 'transfer';
    protected $dates = ['deleted_at'];
    protected $fillable = [
    'staff_sn',
    'staff_name',
    'go_shop_sn',
    'go_shop_name',
    'out_shop_sn',
    'out_shop_name',
    'budget',
    ];
    protected $statusArr = [
    	0=>'未启程',
    	1=>'行程中',
    	2=>'调动完成',
    	3=>'调动取消'
    ];

    public function getStatusAttribute($value){
    	return $this->statusArr[$value];
    }

    public function getBudgetAttribute($value){
    	return date("Y-m-d" , $value);
    }

    public function getAbnormalAttribute($value){
    	if($value > 0){
    		return '超出('.$value.')天';
    	}
    	return '正常';
    }
}
