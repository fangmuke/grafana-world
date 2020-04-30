<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorldLog extends Model
{
    protected $fillable = ['location_name', 'ip'];
}
