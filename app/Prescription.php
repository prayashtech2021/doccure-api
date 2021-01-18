<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'appointment_id', 'signature_id','created_by',
   ];
}
