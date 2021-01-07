<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'id','short_name','name', 'tcode', 'status'
    ];
}
