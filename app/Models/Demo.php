<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Demo extends Model
{
    use SoftDeletes;

    protected $table = 'demo';

    protected $fillable = [
    	'uname',
    	'contents',
    	'thumb',
    ];

    protected $dates = ['deleted_at'];
}
