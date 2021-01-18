<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistrationDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name','registration_year','created_by',
   ];
}
