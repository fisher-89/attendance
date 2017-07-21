<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redis extends Model
{
    protected $table = 'redis';
    protected $dates = ['deleted_at'];
    protected $fillable = ['uname'];
}
