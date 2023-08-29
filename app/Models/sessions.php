<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class sessions extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sessions';
}
