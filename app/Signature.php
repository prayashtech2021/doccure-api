<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signature extends Model
{
    use SoftDeletes;
    protected $table = 'signatures';

    protected $fillable = [
        'user_id', 'signature_image','created_by',
   ];
}
