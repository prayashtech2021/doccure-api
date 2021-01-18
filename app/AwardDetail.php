<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AwardDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'award_year','created_by',
   ];
}
