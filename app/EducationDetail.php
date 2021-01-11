<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EducationDetail extends Model
{
    //
    protected $fillable = [
        'user_id', 'degeree', 'institute', 'year_of_completion', 'created_by',
   ];

}
