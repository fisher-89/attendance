<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    //
    public $table = 'statistic';
    protected $fillable = [
    'staff_sn',
    'staff_name',
    'arrive',
    'holiday',
    'attendance',
    ];
}
