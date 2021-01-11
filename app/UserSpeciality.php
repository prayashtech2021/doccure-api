<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSpeciality extends Model{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'user_id', 'speciality_id ', 'service','created_by',
    ];


}
