<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferTimeRecord extends Model
{
    //
    use SoftDeletes;

    protected $table = 'transferTimeRecord';
    protected $dates = ['deleted_at'];
    protected $fillable = [
    'parentid',
    'start_time',
    'end_time',
    ];
   
}
