<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use SoftDeletes;
    protected $table = 'transfer';
    protected $fillable = [
        'status',
    ];

    /* 关联 Start */

    public function arrivingShopDuty()
    {
        return $this->belongsTo('App\Models\ShopDuty', 'arriving_shop_duty_id');
    }

    /* 关联 End */
}
