<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'clinic_information_id', 'clinic_image','created_by',
   ];
}
