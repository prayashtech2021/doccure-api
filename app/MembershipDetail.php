<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name','created_by',
   ];
}
