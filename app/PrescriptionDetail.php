<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrescriptionDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'prescription_id', 'drug_name', 'quantity', 'type', 'days', 'time', 'created_by',
   ];
}
