<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExperienceDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'hospital_name', 'from', 'to', 'designation', 'created_by',
   ];
}
