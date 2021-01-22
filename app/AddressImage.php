<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddressImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'address_id', 'image','created_by',
   ];
}
