<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class World extends Model
{
    protected $fillable = ['location_name', 'geo_hash', 'metric'];
}
