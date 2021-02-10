<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    //
    protected $fillable = [
        'provider_id', 'name','link',
   ];
}
