<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class otp extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'otp';

    protected $fillable = ['email', 'otp'];
}
