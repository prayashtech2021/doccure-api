<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'degeree', 'institute', 'year_of_completion', 'created_by',
   ];

}
