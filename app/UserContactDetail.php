<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserContactDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'line_1', 'line_2', 'country_id', 'state_id', 'city_id', 'postal_code','created_by',
   ];
}
